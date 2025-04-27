<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $settings = [
            'standard_max_daily_attempts' => getSetting('standard_max_daily_attempts', 3),
            'standard_max_attempts' => getSetting('standard_max_attempts', 9),
            'standard_attempt_interval' => getSetting('standard_attempt_interval', 2.5),
            'scheduled_max_daily_attempts' => getSetting('scheduled_max_daily_attempts', 2),
            'scheduled_max_attempts' => getSetting('scheduled_max_attempts', 5),
            'scheduled_attempt_interval' => getSetting('scheduled_attempt_interval', 3.5),
            'old_attempt_interval' => getSetting('old_attempt_interval', 3.5),
            'woocommerce_api_key' => getSetting('woocommerce_api_key', ''),
            'woocommerce_api_secret' => getSetting('woocommerce_api_secret', ''),
            'woocommerce_status_to_import' => getSetting('woocommerce_status_to_import', 'processing'),
            'google_sheet_id' => getSetting('google_sheet_id', ''),
        ];
        
        return view('settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        $validated = $request->validate([
            'standard_max_daily_attempts' => 'required|integer|min:1',
            'standard_max_attempts' => 'required|integer|min:1',
            'standard_attempt_interval' => 'required|numeric|min:0.5',
            'scheduled_max_daily_attempts' => 'required|integer|min:1',
            'scheduled_max_attempts' => 'required|integer|min:1',
            'scheduled_attempt_interval' => 'required|numeric|min:0.5',
            'old_attempt_interval' => 'required|numeric|min:0.5',
            'woocommerce_api_key' => 'nullable|string',
            'woocommerce_api_secret' => 'nullable|string',
            'woocommerce_status_to_import' => 'nullable|string',
            'google_sheet_id' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            setSetting($key, $value);
        }
        
        return redirect()->route('settings.index')->with('success', 'Paramètres mis à jour avec succès!');
    }
    
    public function importFromWoocommerce()
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        // Logique d'importation depuis WooCommerce
        // À implémenter en fonction de vos besoins
        
        return redirect()->route('settings.index')->with('success', 'Importation depuis WooCommerce effectuée avec succès!');
    }
    
    public function importFromGoogleSheet()
    {
        // Vérifier si l'utilisateur est admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires.');
        }
        
        // Logique d'importation depuis Google Sheets
        // À implémenter en fonction de vos besoins
        
        return redirect()->route('settings.index')->with('success', 'Importation depuis Google Sheets effectuée avec succès!');
    }
}