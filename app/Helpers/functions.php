<?php

use App\Models\Setting;

if (!function_exists('getSetting')) {
    function getSetting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('setSetting')) {
    function setSetting($key, $value)
    {
        // S'assurer que la valeur n'est jamais NULL
        $value = $value === null ? '' : $value;
        
        return Setting::set($key, $value);
    }
}

if (!function_exists('tunisianRegions')) {
    function tunisianRegions()
    {
        return [
            'Ariana', 'Béja', 'Ben Arous', 'Bizerte', 'Gabès', 'Gafsa', 'Jendouba',
            'Kairouan', 'Kasserine', 'Kébili', 'Le Kef', 'Mahdia', 'La Manouba',
            'Médenine', 'Monastir', 'Nabeul', 'Sfax', 'Sidi Bouzid', 'Siliana',
            'Sousse', 'Tataouine', 'Tozeur', 'Tunis', 'Zaghouan'
        ];
    }
}