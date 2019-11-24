<?php

use Illuminate\Database\Seeder;
use App\Models\Currency;
use App\Models\ExchangeRate;
use  \Illuminate\Support\Arr;

class RatesSeeder extends Seeder
{
    // At first we only have a few rates available, so we'll have to figure
    // out the rates matrix.
    protected $rates = [
        ['EUR', 'USD', 1.1956],
        ['EUR', 'CHF', 1.1689],
        ['EUR', 'GBP', 0.8848],
        ['USD', 'JPY', 111.4500],
        ['CHF', 'USD', 1.0223],
        ['GBP', 'CAD', 1.6933],
    ];

    /**
     * Known currencies indexed on their symbol
     * @var array
     */
    protected $currencies = [];

    /**
     * Exchange matrix
     * @var array
     */
    protected $matrix = [];

    /**
     * The precision of numbers
     * @var integer
     */
    protected $precision;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->precision = env('APP_RATE_PRECISION', 4);

        $this->loadCurrencies()
            ->initMatrix();
    }

    /**
     * Build the rest of the currency matrix based on known values
     *
     * @return $this
     */
    protected function initMatrix(): self
    {
        // Set up the initial matrix
        $currencyKeys = array_keys($this->currencies);
        foreach ($currencyKeys as $key) {
            $currencyMatrix = [];
            foreach ($currencyKeys as $subkey) {
                // Do we already have this value?
                $currencyMatrix[$subkey] = $this->rate($key, $subkey);
            }
            $this->matrix[$key] = $currencyMatrix;
        }

        // That was the easy part. Now we need to fill out the currency exchange rates we know.
        foreach ($this->matrix as $source => $rates) {
            foreach ($rates as $target => $rate) {
                if ($rate === false) {
                    $this->matrix[$source][$target] = $this->exchange($source, $target);
                }
            }
        }

        $this->save();

        return $this;
    }

    /**
     * Load and cache existing currencies
     *
     * @return $this
     */
    protected function loadCurrencies(): self
    {
        /** @var Currency $currency */
        foreach(Currency::get() as $currency) {
            $this->currencies[$currency->symbol] = $currency;
        }

        return $this;
    }

    /**
     * Calculate the reverse exchange rate
     *
     * @param float $rate
     * @return float
     */
    protected function reverse(float $rate): float
    {
        return round(1 / $rate, $this->precision);
    }

    /**
     * Get the exchange rate between to currencies based on what is known.
     *
     * @param string $source
     * @param string $target
     * @return bool|float|int|mixed
     */
    protected function rate(string $source, string $target)
    {
        // Converting itself should always be one
        if ($source === $target) {
            return 1;
        }

        //  Value exists from the data we were provided?
        foreach ($this->rates as $exchangeRate) {
            list($sourceSymbol, $targetSymbol, $rate) = $exchangeRate;
            // If we have a perfect match, use that
            if ($sourceSymbol === $source && $targetSymbol === $target) {
                return $rate;
            } elseif ($sourceSymbol === $target && $targetSymbol === $source) {
                // Found the exchange rate in the other direction
                return $this->reverse($rate);
            }
        }

        // We didn't find anything of value
        return false;
    }

    /**
     * Complex case, find the exchange rate with a pivot currency
     *
     * @param $source
     * @param $target
     * @return float|bool
     */
    protected function exchange($source, $target)
    {
        // Find a currency that both source and target have
        $callback = function ($source) {
            return $source !== 1 && $source !== false;
        };

        $sourceCurrencies = array_filter($this->matrix[$source], $callback);
        $targetCurrencies = array_filter($this->matrix[$target], $callback);

        // Loop through the pivot currencies we found
        $matching = array_keys(array_intersect_key($sourceCurrencies, $targetCurrencies));
        foreach ($matching as $pivot) {
            // We need to convert twice to the target using the pivot
            return round(
                (1 / $targetCurrencies[$pivot]) * $sourceCurrencies[$pivot],
                $this->precision
            );
        }

        return false;
    }

    /**
     * Save the matrix to the db
     *
     * @return bool
     * @throws Exception
     */
    protected function save(): bool
    {
        // First things first, let's get rid of old data.
        ExchangeRate::getQuery()->delete();

        // We have everything, let's save it in the db.
        $matrices = 0;
        foreach ($this->matrix as $sourceSymbol => $rates) {
            foreach ($rates as $targetSymbol => $rate) {
                // Keep self-references matrices to keep the db clean
//                if ($sourceSymbol === $targetSymbol) {
//                    continue;
//                }

                // If there is still no valid rate, we're missing enough seeding data.
                if ($rate === false) {
                    throw new Exception("Unfinished matrix for $sourceSymbol to $targetSymbol");
                }

                /** @var Currency $source */
                $source = Arr::get($this->currencies, $sourceSymbol, false);
                /** @var Currency $target */
                $target = Arr::get($this->currencies, $targetSymbol, false);

                ExchangeRate::create([
                    'source_id' => $source->id,
                    'target_id' => $target->id,
                    'rate' => $rate
                ]);
                $matrices++;
            }
        }

        $this->command->info("Created $matrices matrices.");

        return true;
    }
}
