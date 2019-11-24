<?php

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            'Euro' => 'EUR',
            'Swiss Franc' => 'CHF',
            'British Pound' => 'GBP',
            'US Dollar' => 'USD',
            'Japanese Yen' => 'JPY',
            'Canadian Dollar' => 'CAD'
        ];

        foreach ($currencies as $name => $symbol) {
            $params = [
                'name' => $name,
                'symbol' => $symbol
            ];
            $currency = Currency::firstOrNew($params);
            $currency->fill($params)->save();
        }
    }
}
