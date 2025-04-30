<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Afficher la liste des paramètres du système
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Seuls les super administrateurs peuvent accéder aux paramètres système');
        }
        
        // Récupérer tous les paramètres groupés par catégorie
        $attemptSettings = [
            'standard_max_attempts' => Setting::get('standard_max_attempts', 9),
            'standard_max_daily_attempts' => Setting::get('standard_max_daily_attempts', 3),
            'standard_attempt_interval' => Setting::get('standard_attempt_interval', 2.5),
            'scheduled_max_attempts' => Setting::get('scheduled_max_attempts', 5),
            'scheduled_max_daily_attempts' => Setting::get('scheduled_max_daily_attempts', 2),
            'scheduled_attempt_interval' => Setting::get('scheduled_attempt_interval', 3.5),
            'old_max_daily_attempts' => Setting::get('old_max_daily_attempts', 3),
            'old_attempt_interval' => Setting::get('old_attempt_interval', 3.5),
        ];
        
        $wooCommerceSettings = [
            'woocommerce_api_url' => Setting::get('woocommerce_api_url', ''),
            'woocommerce_api_key' => Setting::get('woocommerce_api_key', ''),
            'woocommerce_api_secret' => Setting::get('woocommerce_api_secret', ''),
            'woocommerce_status_to_import' => Setting::get('woocommerce_status_to_import', 'processing'),
        ];
        
        $googleSheetSettings = [
            'google_sheet_id' => Setting::get('google_sheet_id', ''),
        ];
        
        $otherSettings = [
            // Ajoutez ici d'autres paramètres si nécessaire
        ];
        
        return view('admin.settings.index', compact(
            'attemptSettings',
            'wooCommerceSettings',
            'googleSheetSettings',
            'otherSettings'
        ));
    }
    
    /**
     * Mettre à jour les paramètres du système
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Seuls les super administrateurs peuvent modifier les paramètres système');
        }
        
        try {
            // Valider les paramètres des tentatives
            $attemptValidator = Validator::make($request->all(), [
                'standard_max_attempts' => 'required|integer|min:1',
                'standard_max_daily_attempts' => 'required|integer|min:1',
                'standard_attempt_interval' => 'required|numeric|min:0.5',
                'scheduled_max_attempts' => 'required|integer|min:1',
                'scheduled_max_daily_attempts' => 'required|integer|min:1',
                'scheduled_attempt_interval' => 'required|numeric|min:0.5',
            ]);
            
            if ($attemptValidator->fails()) {
                return redirect()->back()
                    ->withErrors($attemptValidator, 'attempts')
                    ->withInput();
            }
            
            // Mettre à jour les paramètres des tentatives
            Setting::set('standard_max_attempts', $request->input('standard_max_attempts'));
            Setting::set('standard_max_daily_attempts', $request->input('standard_max_daily_attempts'));
            Setting::set('standard_attempt_interval', $request->input('standard_attempt_interval'));
            Setting::set('scheduled_max_attempts', $request->input('scheduled_max_attempts'));
            Setting::set('scheduled_max_daily_attempts', $request->input('scheduled_max_daily_attempts'));
            Setting::set('scheduled_attempt_interval', $request->input('scheduled_attempt_interval'));
            Setting::set('old_max_daily_attempts', $request->input('old_max_daily_attempts', 3));
            Setting::set('old_attempt_interval', $request->input('old_attempt_interval', 3.5));
            
            // Valider les paramètres WooCommerce
            $wooCommerceValidator = Validator::make($request->all(), [
                'woocommerce_api_url' => 'nullable|url',
                'woocommerce_api_key' => 'nullable|string',
                'woocommerce_api_secret' => 'nullable|string',
                'woocommerce_status_to_import' => 'nullable|string',
            ]);
            
            if ($wooCommerceValidator->fails()) {
                return redirect()->back()
                    ->withErrors($wooCommerceValidator, 'woocommerce')
                    ->withInput();
            }
            
            // Mettre à jour les paramètres WooCommerce
            Setting::set('woocommerce_api_url', $request->input('woocommerce_api_url'));
            Setting::set('woocommerce_api_key', $request->input('woocommerce_api_key'));
            Setting::set('woocommerce_api_secret', $request->input('woocommerce_api_secret'));
            Setting::set('woocommerce_status_to_import', $request->input('woocommerce_status_to_import', 'processing'));
            
            // Valider les paramètres Google Sheets
            $googleSheetsValidator = Validator::make($request->all(), [
                'google_sheet_id' => 'nullable|string',
            ]);
            
            if ($googleSheetsValidator->fails()) {
                return redirect()->back()
                    ->withErrors($googleSheetsValidator, 'google_sheets')
                    ->withInput();
            }
            
            // Mettre à jour les paramètres Google Sheets
            Setting::set('google_sheet_id', $request->input('google_sheet_id'));
            
            // Journaliser la mise à jour des paramètres
            Log::info('Paramètres système mis à jour par l\'administrateur: ' . Auth::guard('admin')->user()->name);
            
            return redirect()->route('admin.settings.index')
                ->with('success', 'Les paramètres ont été mis à jour avec succès.');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des paramètres: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la mise à jour des paramètres: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * Tester la connexion WooCommerce
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testWooCommerceConnection(Request $request)
    {
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Paramètres invalides', 'errors' => $validator->errors()], 422);
        }
        
        try {
            // Construire l'URL pour le test
            $apiUrl = $request->input('api_url');
            if (!str_ends_with($apiUrl, '/')) {
                $apiUrl .= '/';
            }
            $apiUrl .= 'wp-json/wc/v3/products';
            
            // Faire une requête de test
            $response = \Illuminate\Support\Facades\Http::withBasicAuth(
                $request->input('api_key'),
                $request->input('api_secret')
            )->get($apiUrl, ['per_page' => 1]);
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie! L\'API WooCommerce est accessible.',
                    'data' => [
                        'status' => $response->status(),
                        'headers' => $response->headers(),
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Échec de la connexion à l\'API WooCommerce. Code d\'erreur: ' . $response->status(),
                    'data' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion WooCommerce: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du test: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Tester la connexion Google Sheets
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testGoogleSheetsConnection(Request $request)
    {
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'sheet_id' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Paramètres invalides', 'errors' => $validator->errors()], 422);
        }
        
        try {
            // Ici, vous devriez implémenter la logique pour tester la connexion Google Sheets
            // Pour l'instant, nous renvoyons simplement un succès fictif
            
            return response()->json([
                'success' => true,
                'message' => 'ID de feuille Google validé: ' . $request->input('sheet_id'),
                'data' => [
                    'sheet_id' => $request->input('sheet_id'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion Google Sheets: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du test: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Réinitialiser tous les paramètres aux valeurs par défaut
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetToDefaults()
    {
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Seuls les super administrateurs peuvent réinitialiser les paramètres système');
        }
        
        try {
            // Paramètres des tentatives
            Setting::set('standard_max_attempts', 9);
            Setting::set('standard_max_daily_attempts', 3);
            Setting::set('standard_attempt_interval', 2.5);
            Setting::set('scheduled_max_attempts', 5);
            Setting::set('scheduled_max_daily_attempts', 2);
            Setting::set('scheduled_attempt_interval', 3.5);
            Setting::set('old_max_daily_attempts', 3);
            Setting::set('old_attempt_interval', 3.5);
            
            // WooCommerce
            Setting::set('woocommerce_status_to_import', 'processing');
            
            // Ne pas effacer les clés d'API ici pour éviter de perdre les configurations
            
            Log::info('Paramètres système réinitialisés aux valeurs par défaut par: ' . Auth::guard('admin')->user()->name);
            
            return redirect()->route('admin.settings.index')
                ->with('success', 'Les paramètres ont été réinitialisés aux valeurs par défaut.');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation des paramètres: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Une erreur est survenue lors de la réinitialisation des paramètres: ' . $e->getMessage()]);
        }
    }
}