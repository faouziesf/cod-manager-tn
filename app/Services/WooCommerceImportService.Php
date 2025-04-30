<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderHistory;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class WooCommerceImportService
{
    protected $apiUrl;
    protected $apiKey;
    protected $apiSecret;
    protected $statusToImport;
    
    /**
     * Constructeur qui récupère les paramètres de configuration depuis la base de données
     */
    public function __construct()
    {
        $this->apiUrl = Setting::where('key', 'woocommerce_api_url')->value('value');
        $this->apiKey = Setting::where('key', 'woocommerce_api_key')->value('value');
        $this->apiSecret = Setting::where('key', 'woocommerce_api_secret')->value('value');
        $this->statusToImport = Setting::where('key', 'woocommerce_status_to_import')->value('value') ?? 'processing';
    }
    
    /**
     * Importer les commandes depuis WooCommerce
     *
     * @param int $adminId L'id de l'admin qui effectue l'importation
     * @return array Résultat de l'importation (nombre de commandes importées, etc.)
     * @throws Exception Si une erreur survient pendant l'importation
     */
    public function importOrders($adminId)
    {
        if (!$this->apiUrl || !$this->apiKey || !$this->apiSecret) {
            throw new Exception("Configuration WooCommerce incomplète. Veuillez configurer l'URL, la clé et le secret API dans les paramètres.");
        }
        
        try {
            Log::info("Début de l'importation WooCommerce pour l'admin ID: $adminId");
            
            // Construire l'URL avec le statut à importer
            $importUrl = $this->apiUrl;
            if (!str_ends_with($importUrl, '/')) {
                $importUrl .= '/';
            }
            $importUrl .= 'wp-json/wc/v3/orders';
            
            // Faire la requête à l'API WooCommerce
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get($importUrl, [
                    'status' => $this->statusToImport,
                    'per_page' => 100, // Limiter à 100 commandes par requête
                ]);
                
            if (!$response->successful()) {
                $error = "Erreur API WooCommerce: " . $response->status() . " - " . $response->body();
                Log::error($error);
                throw new Exception($error);
            }
            
            $orders = $response->json();
            
            if (empty($orders)) {
                Log::info("Aucune nouvelle commande à importer");
                return [
                    'imported' => 0,
                    'skipped' => 0,
                    'errors' => 0,
                    'message' => 'Aucune nouvelle commande à importer'
                ];
            }
            
            $imported = 0;
            $skipped = 0;
            $errors = 0;
            
            // Récupérer les paramètres de tentatives standard
            $maxAttempts = Setting::where('key', 'standard_max_attempts')->value('value') ?? 9;
            $maxDailyAttempts = Setting::where('key', 'standard_max_daily_attempts')->value('value') ?? 3;
            
            foreach ($orders as $wooOrder) {
                try {
                    // Vérifier si la commande existe déjà
                    $existingOrder = Order::where('external_id', $wooOrder['id'])->first();
                    if ($existingOrder) {
                        Log::info("Commande WooCommerce #{$wooOrder['id']} déjà importée, ignorée");
                        $skipped++;
                        continue; // Passer à la commande suivante
                    }
                    
                    // Créer une nouvelle commande
                    $order = new Order();
                    $order->admin_id = $adminId;
                    $order->external_id = $wooOrder['id'];
                    $order->external_source = 'woocommerce';
                    $order->customer_name = $wooOrder['billing']['first_name'] . ' ' . $wooOrder['billing']['last_name'];
                    $order->customer_phone1 = $wooOrder['billing']['phone'] ?? '';
                    $order->delivery_address = $this->formatAddress($wooOrder['shipping']);
                    $order->region = $wooOrder['shipping']['state'] ?? '';
                    $order->city = $wooOrder['shipping']['city'] ?? '';
                    $order->status = 'new';
                    $order->max_attempts = $maxAttempts;
                    $order->max_daily_attempts = $maxDailyAttempts;
                    $order->total_price = $wooOrder['total'];
                    $order->save();
                    
                    // Importer les produits de la commande
                    foreach ($wooOrder['line_items'] as $item) {
                        // Chercher si le produit existe déjà ou en créer un nouveau
                        $product = Product::firstOrCreate(
                            ['external_id' => $item['product_id'], 'admin_id' => $adminId],
                            [
                                'name' => $item['name'],
                                'price' => $item['price'],
                                'stock' => 0,
                                'active' => true,
                                'description' => $item['description'] ?? null
                            ]
                        );
                        
                        // Ajouter le produit à la commande
                        OrderProduct::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $item['quantity'],
                            'confirmed_price' => $item['total']
                        ]);
                    }
                    
                    // Créer l'historique
                    OrderHistory::create([
                        'order_id' => $order->id,
                        'user_id' => $adminId,
                        'action' => 'Importation WooCommerce',
                        'note' => "Commande importée depuis WooCommerce (ID: {$wooOrder['id']})"
                    ]);
                    
                    $imported++;
                    Log::info("Commande WooCommerce #{$wooOrder['id']} importée avec succès");
                } catch (Exception $e) {
                    $errors++;
                    Log::error("Erreur lors de l'importation de la commande WooCommerce #{$wooOrder['id']}: " . $e->getMessage());
                }
            }
            
            $message = "Importation terminée. Commandes importées: $imported, ignorées: $skipped, erreurs: $errors";
            Log::info($message);
            
            return [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'message' => $message
            ];
        } catch (Exception $e) {
            Log::error('Erreur d\'importation WooCommerce: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Formater l'adresse à partir des données WooCommerce
     *
     * @param array $shipping Données d'adresse de livraison
     * @return string Adresse formatée
     */
    protected function formatAddress($shipping)
    {
        $parts = [
            $shipping['address_1'] ?? '',
            $shipping['address_2'] ?? '',
            $shipping['city'] ?? '',
            $shipping['state'] ?? '',
            $shipping['postcode'] ?? '',
            $shipping['country'] ?? ''
        ];
        
        return implode(', ', array_filter($parts));
    }
}