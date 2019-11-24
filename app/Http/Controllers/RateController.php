<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

/**
 * Class RateController
 * @package App\Http\Controllers
 */
class RateController extends Controller
{
    /**
     * Save a new exchange rate
     *
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function post(Request $request)
    {
        // Validate the form using Lumen's validation
        $this->validate($request, [
            'source' => 'required|different:target|exists:currencies,symbol',
            'target' => 'required|different:source|exists:currencies,symbol',
            'rate' => 'required|numeric',
        ]);

        // Validate the currencies
        $source = Currency::where('symbol', strtoupper($request->get('source')))->firstOrFail();
        $target = Currency::where('symbol', strtoupper($request->get('target')))->firstOrFail();
        $precision = env('APP_RATE_PRECISION', 4);

        $new = ExchangeRate::create([
            'source_id' => $source->id,
            'target_id' => $target->id,
            'rate' => round($request->get('rate'), $precision)
        ]);

        // Create the reverse rate
        ExchangeRate::create([
            'source_id' => $target->id,
            'target_id' => $source->id,
            'rate' => round(1 / $new->rate, $precision)
        ]);

        return $new;
    }
}
