<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Request;

/**
 * Class ExchangeController
 * @package App\Http\Controllers
 */
class ExchangeController extends Controller
{
    /**
     * Convert from one currency to another
     *
     * @param string $from
     * @param string $to
     * @return float
     */
    public function convert(string $from, string $to)
    {
        // Validate the currencies
        $source = Currency::where('symbol', strtoupper($from))->firstOrFail();
        $target = Currency::where('symbol', strtoupper($to))->firstOrFail();

        /** @var ExchangeRate $exchange */
        $exchange = ExchangeRate::between($source, $target)->latest()->firstOrFail();

        $amount = Request::get('amount', 1);
        return round($amount * $exchange->rate, getenv('APP_RATE_PRECISION', 4));
    }
}
