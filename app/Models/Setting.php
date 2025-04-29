<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Définit un paramètre dans la base de données
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public static function set($key, $value)
    {
        // S'assurer que la valeur n'est jamais NULL
        $value = $value === null ? '' : $value;
        
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Récupère un paramètre de la base de données
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = '')
    {
        $setting = self::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }
}