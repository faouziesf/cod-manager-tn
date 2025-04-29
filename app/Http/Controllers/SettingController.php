<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    /**
     * Affiche la page des paramètres
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupération des paramètres existants sous forme de tableau associatif
        $settings = [
            'standard_max_daily_attempts' => Setting::where('key', 'standard_max_daily_attempts')->first()->value ?? 3,
            'standard_max_attempts' => Setting::where('key', 'standard_max_attempts')->first()->value ?? 10,
            'standard_attempt_interval' => Setting::where('key', 'standard_attempt_interval')->first()->value ?? 24,
            
            'scheduled_max_daily_attempts' => Setting::where('key', 'scheduled_max_daily_attempts')->first()->value ?? 3,
            'scheduled_max_attempts' => Setting::where('key', 'scheduled_max_attempts')->first()->value ?? 10,
            'scheduled_attempt_interval' => Setting::where('key', 'scheduled_attempt_interval')->first()->value ?? 24,
            
            'old_max_daily_attempts' => Setting::where('key', 'old_max_daily_attempts')->first()->value ?? 3,
            'old_attempt_interval' => Setting::where('key', 'old_attempt_interval')->first()->value ?? 48,
            
            'woocommerce_api_key' => Setting::where('key', 'woocommerce_api_key')->first()->value ?? '',
            'woocommerce_api_secret' => Setting::where('key', 'woocommerce_api_secret')->first()->value ?? '',
            'woocommerce_api_url' => Setting::where('key', 'woocommerce_api_url')->first()->value ?? '',
            'woocommerce_status_to_import' => Setting::where('key', 'woocommerce_status_to_import')->first()->value ?? 'processing',
            
            'google_sheet_id' => Setting::where('key', 'google_sheet_id')->first()->value ?? '',
        ];
        
        return view('settings.index', compact('settings'));
    }

    /**
     * Met à jour les paramètres du système
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'standard_max_daily_attempts' => 'required|integer|min:1',
                'standard_max_attempts' => 'required|integer|min:1',
                'standard_attempt_interval' => 'required|numeric|min:0.1',
                'scheduled_max_daily_attempts' => 'required|integer|min:1',
                'scheduled_max_attempts' => 'required|integer|min:1',
                'scheduled_attempt_interval' => 'required|numeric|min:0.1',
                'old_max_daily_attempts' => 'required|integer|min:1',
                'old_attempt_interval' => 'required|numeric|min:0.1',
                'woocommerce_api_url' => 'nullable|url',
            ]);

            // Mise à jour des paramètres
            $this->updateOrCreateSetting('standard_max_daily_attempts', $request->standard_max_daily_attempts);
            $this->updateOrCreateSetting('standard_max_attempts', $request->standard_max_attempts);
            $this->updateOrCreateSetting('standard_attempt_interval', $request->standard_attempt_interval);
            
            $this->updateOrCreateSetting('scheduled_max_daily_attempts', $request->scheduled_max_daily_attempts);
            $this->updateOrCreateSetting('scheduled_max_attempts', $request->scheduled_max_attempts);
            $this->updateOrCreateSetting('scheduled_attempt_interval', $request->scheduled_attempt_interval);
            
            $this->updateOrCreateSetting('old_max_daily_attempts', $request->old_max_daily_attempts);
            $this->updateOrCreateSetting('old_attempt_interval', $request->old_attempt_interval);
            
            $this->updateOrCreateSetting('woocommerce_api_key', $request->woocommerce_api_key);
            $this->updateOrCreateSetting('woocommerce_api_secret', $request->woocommerce_api_secret);
            $this->updateOrCreateSetting('woocommerce_api_url', $request->woocommerce_api_url);
            $this->updateOrCreateSetting('woocommerce_status_to_import', $request->woocommerce_status_to_import);
            
            // Traitement spécial pour google_sheet_id pour éviter la valeur NULL
            $this->updateOrCreateSetting('google_sheet_id', $request->google_sheet_id ?: '');

            return redirect()->route('admin.settings.index')->with('success', 'Paramètres mis à jour avec succès');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')->with('error', 'Erreur lors de la mise à jour des paramètres: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour ou crée un paramètre
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    private function updateOrCreateSetting($key, $value)
    {
        // S'assurer que la valeur n'est jamais NULL
        $value = $value === null ? '' : $value;
        
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Importe les commandes depuis WooCommerce
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importWoocommerce()
    {
        try {
            // Récupérer les paramètres WooCommerce
            $apiKey = Setting::where('key', 'woocommerce_api_key')->first()->value ?? '';
            $apiSecret = Setting::where('key', 'woocommerce_api_secret')->first()->value ?? '';
            $apiUrl = Setting::where('key', 'woocommerce_api_url')->first()->value ?? '';
            $statusToImport = Setting::where('key', 'woocommerce_status_to_import')->first()->value ?? 'processing';
            
            // Vérifier si les paramètres sont configurés
            if (empty($apiKey) || empty($apiSecret) || empty($apiUrl)) {
                return redirect()->route('admin.settings.index')->with('error', 'Veuillez configurer les paramètres WooCommerce avant d\'importer des commandes.');
            }
            
            // Construire l'URL de l'API
            $endpoint = rtrim($apiUrl, '/') . '/wp-json/wc/v3/orders';
            
            // Paramètres de requête
            $queryParams = [
                'status' => $statusToImport,
                'per_page' => 100, // Récupérer un maximum de 100 commandes
            ];
            
            // Faire la requête à l'API WooCommerce
            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->get($endpoint, $queryParams);
            
            // Vérifier si la requête a réussi
            if (!$response->successful()) {
                return redirect()->route('admin.settings.index')
                    ->with('error', 'Erreur lors de la connexion à WooCommerce: ' . $response->status() . ' - ' . $response->body());
            }
            
            // Récupérer les commandes
            $orders = $response->json();
            
            // Vérifier s'il y a des commandes à importer
            if (empty($orders)) {
                return redirect()->route('admin.settings.index')
                    ->with('info', 'Aucune nouvelle commande à importer depuis WooCommerce.');
            }
            
            // Commencer une transaction pour l'importation
            DB::beginTransaction();
            
            $admin_id = Auth::id();
            $importCount = 0;
            
            // Traiter chaque commande
            foreach ($orders as $wooOrder) {
                // Vérifier si la commande existe déjà
                $existingOrder = Order::where('external_id', 'wc_' . $wooOrder['id'])->first();
                
                if ($existingOrder) {
                    continue; // Passer à la commande suivante si elle existe déjà
                }
                
                // Créer la nouvelle commande
                $order = new Order();
                $order->admin_id = $admin_id;
                $order->external_id = 'wc_' . $wooOrder['id'];
                $order->external_source = 'woocommerce';
                
                // Informations client
                $order->customer_name = $wooOrder['billing']['first_name'] . ' ' . $wooOrder['billing']['last_name'];
                $order->customer_phone1 = $wooOrder['billing']['phone'] ?? '';
                
                // Adresse de livraison
                $order->delivery_address = implode(', ', array_filter([
                    $wooOrder['shipping']['address_1'] ?? '',
                    $wooOrder['shipping']['address_2'] ?? ''
                ]));
                
                $order->region = $wooOrder['shipping']['state'] ?? '';
                $order->city = $wooOrder['shipping']['city'] ?? '';
                
                // Prix
                $order->total_price = $wooOrder['total'];
                
                // Statut
                $order->status = 'new';
                
                // Tentatives
                $order->max_attempts = getSetting('standard_max_attempts', 9);
                $order->max_daily_attempts = getSetting('standard_max_daily_attempts', 3);
                
                // Sauvegarder la commande
                $order->save();
                
                // Traiter les produits de la commande
                foreach ($wooOrder['line_items'] as $item) {
                    // Vérifier si le produit existe déjà
                    $product = Product::where('admin_id', $admin_id)
                        ->where('external_id', 'wc_' . $item['product_id'])
                        ->first();
                    
                    // Si le produit n'existe pas, le créer
                    if (!$product) {
                        $product = new Product();
                        $product->admin_id = $admin_id;
                        $product->external_id = 'wc_' . $item['product_id'];
                        $product->name = $item['name'];
                        $product->description = $item['description'] ?? '';
                        $product->sku = $item['sku'] ?? '';
                        $product->price = $item['price'];
                        $product->stock = $item['quantity'] ?? 100; // Stock par défaut
                        $product->active = true;
                        $product->category = ''; // À remplir si disponible
                        
                        // Informations supplémentaires
                        $product->dimensions = json_encode([
                            'weight' => $item['weight'] ?? '',
                            'dimensions' => [
                                'length' => $item['dimensions']['length'] ?? '',
                                'width' => $item['dimensions']['width'] ?? '',
                                'height' => $item['dimensions']['height'] ?? ''
                            ]
                        ]);
                        
                        // Attributs
                        $attributes = [];
                        if (isset($item['attributes']) && is_array($item['attributes'])) {
                            foreach ($item['attributes'] as $attr) {
                                $attributes[$attr['name']] = $attr['value'];
                            }
                        }
                        $product->attributes = !empty($attributes) ? json_encode($attributes) : null;
                        
                        $product->save();
                    }
                    
                    // Attacher le produit à la commande
                    $order->products()->attach($product->id, [
                        'quantity' => $item['quantity'],
                        'confirmed_price' => $item['price'],
                    ]);
                }
                
                // Enregistrer l'historique
                $order->addHistory('import', 'Commande importée depuis WooCommerce');
                
                $importCount++;
            }
            
            DB::commit();
            
            return redirect()->route('admin.settings.index')
                ->with('success', $importCount . ' commandes importées avec succès depuis WooCommerce.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.settings.index')
                ->with('error', 'Erreur lors de l\'importation depuis WooCommerce: ' . $e->getMessage());
        }
    }

    /**
     * Importe les commandes depuis Google Sheet
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importGoogleSheet()
    {
        try {
            // Logique d'importation Google Sheet à implémenter
            
            return redirect()->route('admin.settings.index')->with('success', 'Importation Google Sheet effectuée avec succès');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index')->with('error', 'Erreur lors de l\'importation: ' . $e->getMessage());
        }
    }
}