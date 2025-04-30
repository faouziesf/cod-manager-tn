<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WooCommerceImportService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ImportController extends Controller
{
    /**
     * Afficher le formulaire d'importation WooCommerce
     *
     * @return \Illuminate\Http\Response
     */
    public function showWooCommerceForm()
    {
        // Récupérer les paramètres WooCommerce actuels
        $settings = [
            'api_url' => Setting::where('key', 'woocommerce_api_url')->value('value'),
            'api_key' => Setting::where('key', 'woocommerce_api_key')->value('value'),
            'api_secret' => Setting::where('key', 'woocommerce_api_secret')->value('value'),
            'status_to_import' => Setting::where('key', 'woocommerce_status_to_import')->value('value') ?? 'processing'
        ];
        
        return view('admin.import.woocommerce', compact('settings'));
    }
    
    /**
     * Importer les commandes depuis WooCommerce
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importFromWooCommerce(Request $request)
    {
        // Valider les paramètres
        $validated = $request->validate([
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'status_to_import' => 'required|string'
        ]);
        
        // Enregistrer ou mettre à jour les paramètres
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => 'woocommerce_' . $key],
                ['value' => $value]
            );
        }
        
        try {
            // Utiliser le service d'importation
            $importService = new WooCommerceImportService();
            $result = $importService->importOrders(Auth::guard('admin')->id());
            
            // Rediriger avec un message de succès
            return redirect()->route('admin.import.woocommerce.form')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            // Gérer les erreurs
            return redirect()->route('admin.import.woocommerce.form')
                ->withErrors(['error' => 'Erreur lors de l\'importation: ' . $e->getMessage()]);
        }
    }
}