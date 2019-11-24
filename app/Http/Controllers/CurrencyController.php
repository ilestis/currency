<?php


namespace App\Http\Controllers;


use App\Models\Currency;

/**
 * Class CurrencyController
 * @package App\Http\Controllers
 */
class CurrencyController extends Controller
{
    /**
     * Get all currencies as a json
     * @return mixed
     */
    public function index()
    {
        return Currency::paginate();
    }
}
