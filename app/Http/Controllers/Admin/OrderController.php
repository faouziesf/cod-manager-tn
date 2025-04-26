<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Affiche toutes les commandes
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $orders = $admin->orders()->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Affiche les commandes standards (à confirmer)
     */
    public function standard()
    {
        $admin = Auth::guard('admin')->user();
        $orders = $admin->orders()
            ->where('status', 'new')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.orders.standard', compact('orders'));
    }

    /**
     * Affiche les commandes datées
     */
    public function dated()
    {
        $admin = Auth::guard('admin')->user();
        $orders = $admin->orders()
            ->where('status', 'dated')
            ->orderBy('callback_date')
            ->paginate(15);
        
        return view('admin.orders.dated', compact('orders'));
    }

    /**
     * Affiche les commandes anciennes (nombre max de tentatives atteint)
     */
    public function old()
    {
        $admin = Auth::guard('admin')->user();
        $orders = $admin->orders()
            ->whereRaw('current_attempts >= max_attempts')
            ->where('status', 'recall')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
        
        return view('admin.orders.old', compact('orders'));
    }

    /**
     * Recherche de commandes
     */
    public function search(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $query = $admin->orders();
        
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
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        $users = User::where('admin_id', $admin->id)->get();
        
        return view('admin.orders.search', compact('orders', 'users'));
    }

    /**
     * Formulaire de création de commande
     */
    public function create()
    {
        $admin = Auth::guard('admin')->user();
        $products = $admin->products()->get();
        $users = $admin->users()->get();
        
        return view('admin.orders.create', compact('products', 'users'));
    }

    /**
     * Enregistrement d'une nouvelle commande
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        $admin = Auth::guard('admin')->user();
        
        // Vérifier que le produit appartient à cet admin
        $product = Product::findOrFail($request->product_id);
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.create')
                ->with('error', 'Le produit sélectionné n\'est pas valide.');
        }
        
        // Vérifier que l'utilisateur appartient à cet admin si spécifié
        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
            if ($user->admin_id !== $admin->id) {
                return redirect()->route('admin.orders.create')
                    ->with('error', 'L\'utilisateur sélectionné n\'est pas valide.');
            }
        }
        
        $order = new Order();
        $order->admin_id = $admin->id;
        $order->product_id = $request->product_id;
        $order->user_id = $request->user_id;
        $order->customer_name = $request->customer_name;
        $order->customer_phone1 = $request->customer_phone1;
        $order->customer_phone2 = $request->customer_phone2;
        $order->delivery_address = $request->delivery_address;
        $order->region = $request->region;
        $order->city = $request->city;
        $order->quantity = $request->quantity;
        $order->status = 'new';
        $order->max_attempts = $request->max_attempts ?? 3;
        $order->current_attempts = 0;
        $order->save();
        
        // Créer une entrée dans l'historique
        $history = new OrderHistory();
        $history->order_id = $order->id;
        $history->user_id = Auth::id();
        $history->status = 'new';
        $history->private_note = 'Commande créée';
        $history->save();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Commande créée avec succès.');
    }

    /**
     * Affiche les détails d'une commande
     */
    public function show(Order $order)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $history = $order->history()->orderBy('created_at', 'desc')->get();
        
        return view('admin.orders.show', compact('order', 'history'));
    }

    /**
     * Formulaire d'édition d'une commande
     */
    public function edit(Order $order)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }
        
        $products = $admin->products()->get();
        $users = $admin->users()->get();
        
        return view('admin.orders.edit', compact('order', 'products', 'users'));
    }

    /**
     * Mise à jour d'une commande
     */
    public function update(Request $request, Order $order)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone1' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'region' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:new,confirmed,dated,recall,canceled',
            'callback_date' => 'nullable|required_if:status,dated|date',
            'private_note' => 'nullable|string',
        ]);

        // Vérifier que le produit appartient à cet admin
        $product = Product::findOrFail($request->product_id);
        if ($product->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.edit', $order)
                ->with('error', 'Le produit sélectionné n\'est pas valide.');
        }
        
        // Vérifier que l'utilisateur appartient à cet admin si spécifié
        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
            if ($user->admin_id !== $admin->id) {
                return redirect()->route('admin.orders.edit', $order)
                    ->with('error', 'L\'utilisateur sélectionné n\'est pas valide.');
            }
        }
        
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Mettre à jour la commande
        $order->product_id = $request->product_id;
        $order->user_id = $request->user_id;
        $order->customer_name = $request->customer_name;
        $order->customer_phone1 = $request->customer_phone1;
        $order->customer_phone2 = $request->customer_phone2;
        $order->delivery_address = $request->delivery_address;
        $order->region = $request->region;
        $order->city = $request->city;
        $order->quantity = $request->quantity;
        $order->status = $newStatus;
        
        // Traitement spécifique selon le statut
        if ($newStatus === 'dated') {
            $order->callback_date = $request->callback_date;
        } elseif ($newStatus === 'recall') {
            $order->current_attempts += 1;
        }
        
        $order->save();
        
        // Créer une entrée dans l'historique si le statut a changé ou si une note est fournie
        if ($oldStatus !== $newStatus || $request->filled('private_note')) {
            $history = new OrderHistory();
            $history->order_id = $order->id;
            $history->user_id = Auth::id();
            $history->status = $newStatus;
            $history->private_note = $request->private_note;
            $history->save();
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Commande mise à jour avec succès.');
    }

    /**
     * Suppression d'une commande
     */
    public function destroy(Order $order)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que la commande appartient à cet admin
        if ($order->admin_id !== $admin->id) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Vous n\'avez pas accès à cette commande.');
        }

        // Supprimer également l'historique
        $order->history()->delete();
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Commande supprimée avec succès.');
    }

    public function export()
    {
        return Excel::download(new OrdersExport(Auth::guard('admin')->user()->id), 'commandes.xlsx');
    }

    public function importForm()
    {
        return view('admin.orders.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);

        $import = new OrdersImport(Auth::guard('admin')->user()->id);
        Excel::import($import, $request->file('file'));

        return redirect()->route('admin.orders.index')
            ->with('success', 'Commandes importées avec succès. ' . $import->getRowCount() . ' lignes importées.');
    }

}