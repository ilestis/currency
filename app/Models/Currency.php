<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Currency
 * @package App\Models
 * @property integer $id
 * @property string $name
 * @property string $symbol
 */
class Currency extends Model
{
    /**
     * Mass-fillable fields for Eloquent
     * @var array
     */
    public $fillable = [
        'name',
        'symbol'
    ];

    /**
     * Hidden fields from the API
     * @var array
     */
    public $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get all rates attached to a model
     * @return HasMany
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class, 'source_id');
    }
}
