<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Affiche toutes les commandes assignées à l'employé
     */
    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('employee.orders.index', compact('orders'));
    }

    /**
     * Affiche les commandes standards (à confirmer) assignées à l'employé
     */
    public function standard()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->where('status', 'new')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('employee.orders.standard', compact('orders'));
    }

    /**
     * Affiche les commandes datées assignées à l'employé
     */
    public function dated()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->where('status', 'dated')
            ->orderBy('callback_date')
            ->paginate(15);
        
        return view('employee.orders.dated', compact('orders'));
    }

    /**
     * Affiche les commandes anciennes assignées à l'employé
     */
    public function old()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->whereRaw('current_attempts >= max_attempts')
            ->where('status', 'recall')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        
        return view('employee.orders.old', compact('orders'));
    }

    /**
     * Recherche de commandes
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = Order::where('user_id', $user->id);
        
        if ($request->filled('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        
        if ($request->filled('customer_phone')) {
            $query->where(function($q) use ($request) {
                $q->where('customer_phone1', 'like', '%' . $request->customer_phone . '%')
                  ->orWhere('customer_phone2', 'like', '%' . $request->customer_phone . '%');
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('employee.orders.search', compact('orders'));
    }

    /**
     * Affiche les détails d'une commande
     */
    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que la commande est assignée à cet employé
        if ($order->user_id !== $user->id) {
            return redirect()->route('employee.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $history = $order->history()->orderBy('created_at', 'desc')->get();
        
        return view('employee.orders.show', compact('order', 'history'));
    }

    /**
     * Formulaire de traitement d'une commande
     */
    public function process(Order $order)
    {
        $user = Auth::user();
        
        // Vérifier que la commande est assignée à cet employé
        if ($order->user_id !== $user->id) {
            return redirect()->route('employee.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        return view('employee.orders.process', compact('order'));
    }

    /**
     * Mise à jour du statut d'une commande
     */
    public function updateStatus(Request $request, Order $order)
{
    $user = Auth::user();
    
    // Vérifier que la commande est assignée à cet employé
    if ($order->user_id !== $user->id) {
        return redirect()->route('employee.orders.index')
            ->with('error', 'Vous n\'avez pas accès à cette commande.');
    }

    // Règles de validation selon le statut choisi
    $rules = [
        'status' => 'required|in:confirmed,dated,recall,canceled',
        'private_note' => 'required|string',
    ];

    // Ajouter des règles spécifiques selon le statut
    if ($request->status === 'dated') {
        $rules['callback_date'] = 'required|date|after:today';
    }
    
    if ($request->status === 'confirmed') {
        $rules['confirmed_total_price'] = 'required|numeric|min:0';
    }

    $request->validate($rules);

    // Traitement spécifique selon le statut
    switch ($request->status) {
        case 'confirmed':
            // Vérifier que tous les champs obligatoires sont remplis
            if (empty($order->customer_name) || empty($order->delivery_address) || 
                empty($order->region) || empty($order->city)) {
                return back()->withErrors([
                    'error' => 'Tous les champs client sont obligatoires pour confirmer une commande.'
                ])->withInput();
            }
            
            // Mettre à jour le prix confirmé
            $order->confirmed_total_price = $request->confirmed_total_price;
            $order->status = Order::STATUS_CONFIRMED;
            
            // Décrémenter le stock des produits
            foreach ($order->products as $product) {
                $product->stock -= $product->pivot->quantity;
                $product->save();
            }
            
            break;
            
        case 'dated':
            $order->callback_date = $request->callback_date;
            $order->status = Order::STATUS_DATED;
            break;
            
        case 'canceled':
            $order->status = Order::STATUS_CANCELED;
            break;
            
        case 'recall':
            // Ne change pas le statut, juste incrémente les tentatives
            break;
    }
    
    // Enregistrer la tentative
    $order->recordAttempt($request->status, $request->private_note, $user);

    return redirect()->route('employee.orders.standard')
        ->with('success', 'Commande traitée avec succès.');
}
}