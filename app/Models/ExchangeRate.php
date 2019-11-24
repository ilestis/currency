<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class ExchangeRate
 * @package App\Models
 * @property integer $id
 * @property integer $source_id
 * @property integer $target_id
 * @property float $rate
 * @property Currency $source
 * @property Currency $target
 */
class ExchangeRate extends Model
{
    /**
     * Mass-fillable fields for Eloquent
     * @var array
     */
    public $fillable = [
        'source_id',
        'target_id',
        'rate'
    ];

    /**
     * Scope for the Eloquent model to get the exchange between two currencies
     * @param Builder $builder
     * @param Currency $source
     * @param Currency $target
     * @return Builder
     */
    public function scopeBetween(Builder $builder, Currency $source, Currency $target): Builder
    {
        return $builder
            ->where('source_id', $source->id)
            ->where('target_id', $target->id);
    }


    /**
     * Scope for the Eloquent model to always get the latest rate
     * @param Builder $builder
     * @return Builder
     */
    public function scopeLatest(Builder $builder): Builder
    {
        return $builder->orderBy('created_at', 'DESC');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function source(): HasOne
    {
        return $this->hasOne(Currency::class, 'source_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function target(): HasOne
    {
        return $this->hasOne(Currency::class, 'target_id');
    }
}
