<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        $orders = Order::where('admin_id', $admin_id)
            ->where('assigned_to', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('employee.orders.index', compact('orders'));
    }
    
    public function standard()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        $order = Order::standard()
            ->where('admin_id', $admin_id)
            ->where('assigned_to', $user_id)
            ->orderBy('attempt_count')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $needsMoreOrders = false;
        if (!$order) {
            $needsMoreOrders = true;
        }
        
        return view('employee.orders.standard', compact('order', 'needsMoreOrders'));
    }
    
    public function scheduled()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        $order = Order::scheduled()
            ->where('admin_id', $admin_id)
            ->where('assigned_to', $user_id)
            ->orderBy('attempt_count')
            ->orderBy('scheduled_date')
            ->first();
        
        // Vérifier s'il y a des rendez-vous à afficher
        $reminder = null;
        if ($order && $order->attempt_count == 0) {
            $reminder = "Rappel : Vous avez un rendez-vous aujourd'hui avec le client " . $order->customer_name;
        }
        
        return view('employee.orders.scheduled', compact('order', 'reminder'));
    }
    
    public function old()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        $order = Order::old()
            ->where('admin_id', $admin_id)
            ->where('assigned_to', $user_id)
            ->orderBy('last_attempt_at')
            ->first();
        
        return view('employee.orders.old', compact('order'));
    }
    
    public function search(Request $request)
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        $search = $request->input('search');
        $status = $request->input('status');
        
        $query = Order::where('admin_id', $admin_id)
            ->where('assigned_to', $user_id);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone1', 'like', "%{$search}%")
                  ->orWhere('customer_phone2', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(25);
        
        return view('employee.orders.search', compact('orders', 'search', 'status'));
    }
    
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        if ($order->admin_id != $admin_id || $order->assigned_to != $user_id) {
            return redirect()->route('employee.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $order->load('products', 'histories.user');
        
        return view('employee.orders.show', compact('order'));
    }
    
    public function process(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur a accès à cette commande
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        if ($order->admin_id != $admin_id || $order->assigned_to != $user_id) {
            return redirect()->route('employee.dashboard')->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $validated = $request->validate([
            'action' => 'required|in:confirm,cancel,no_answer,schedule',
            'note' => 'nullable|string',
            'confirmed_price' => 'nullable|required_if:action,confirm|numeric|min:0',
            'scheduled_date' => 'nullable|required_if:action,schedule|date|after:today',
        ]);
        
        switch ($validated['action']) {
            case 'confirm':
                // Vérifier que tous les champs obligatoires sont remplis
                if (empty($order->customer_name) || empty($order->delivery_address) || empty($order->region) || empty($order->city)) {
                    return back()->with('error', 'Tous les champs clients sont obligatoires pour confirmer une commande')->withInput();
                }
                
                $order->confirm($validated['confirmed_price'], $validated['note']);
                $message = 'Commande confirmée avec succès!';
                break;
                
            case 'cancel':
                if (empty($validated['note'])) {
                    return back()->with('error', 'La note est obligatoire pour annuler une commande')->withInput();
                }
                
                $order->cancel($validated['note']);
                $message = 'Commande annulée avec succès!';
                break;
                
            case 'no_answer':
                if (empty($validated['note'])) {
                    return back()->with('error', 'La note est obligatoire pour marquer une tentative sans réponse')->withInput();
                }
                
                $order->recordAttempt('no_answer', $validated['note']);
                $message = 'Tentative enregistrée avec succès!';
                break;
                
            case 'schedule':
                if (empty($validated['note']) || empty($validated['scheduled_date'])) {
                    return back()->with('error', 'La note et la date sont obligatoires pour programmer une commande')->withInput();
                }
                
                $order->schedule($validated['scheduled_date'], $validated['note']);
                $message = 'Commande programmée avec succès!';
                break;
        }
        
        return redirect()->back()->with('success', $message);
    }
    
    public function requestMoreOrders()
    {
        $admin_id = Auth::user()->admin_id;
        $user_id = Auth::id();
        
        // Trouver les 10 plus anciennes commandes non assignées
        $unassignedOrders = Order::where('admin_id', $admin_id)
            ->where('status', 'new')
            ->whereNull('assigned_to')
            ->orderBy('created_at')
            ->limit(10)
            ->get();
            
        foreach ($unassignedOrders as $order) {
            $order->update(['assigned_to' => $user_id]);
            $order->addHistory('assign', 'Commande auto-assignée à l\'employé');
        }
        
        return redirect()->route('employee.orders.standard')
            ->with('success', 'Vous avez reçu ' . count($unassignedOrders) . ' nouvelles commandes!');
    }
}