# Currency Converter

Welcome to the API currency converter. This project is built on the Lumen framework and requires >= PHP 7.2 and a database.

## Installation

Clone the project from github and navigate to your local path. While the project is cloning, go ahead and set up yourself a database.

First step is to set up your local config.

> cp .env.example .env
>
> nano .env

Most settings can be left as is, but you'll want to change the database settings.

* `{DB_DATABASE}` for the database name
* `{DB_USERNAME}` for the database user
* `{DB_PASSWORD}` for the database password

Install the project's dependencies

> composer install --no-dev

Set up the project's database

> php artisan migrate

Initialise the currencies in the database. You can edit these values later with the API.

> php artisan db:seed

You should see the following output.

    Seeding: CurrenciesSeeder
    Seeded:  CurrenciesSeeder (0.02 seconds)
    Seeding: RatesSeeder
    Created 36 matrices.
    Seeded:  RatesSeeder (0.19 seconds)
    Database seeding completed successfully.

### Changing initial values

These commands have set up your currencies and exchange rates in the database. If you wish to tweak the initial currencies, edit `database\seeds\CurrenciesSeeder.php`. If you wish to tweak the initial exchange rates, edit `database/seeds/RatesSeeder.php` and re-run `php artisan db:seed`.

## Running the API

To serve your project locally, simply use the build-int PHP development server on port 8000:

> php -S localhost:8000 -t public

Now go to `http://localhost:8000` and you should see the available currencies as a `json`.

# API

To get an exchange rate from the API, call the following url.

> https://localhost:8000/exchange/{from}-{to}?rate={amount}

The `{from}` and `{to}` parameters can be replaced with any currency provided by the API index.

The `{amount}` parameter can be any amount. If no rate is provided, 1.00 will be used. Please note that the output will always round to 4 precision points.

## Changing Rates

You can change the exchange rate between two currencies by making a `POST` request on `https://localhost:8000/rates` with the following parameters.

* `{source}` | `string, required` | The Symbol of the source currency.
* `{target}` | `string, required` | The Symbol of the target currency.
* `{rate}` | `string, required` | The exchange rate.


### Curl Example

The following curl example will allow you to quickly change the exchange rate between two currencies. You only need to provide one exchange rate, the reverse will be done automatically.

> curl http://localhost:8000/rates -X POST -d "source=CHF&target=EUR&rate=1.19"

# Settings

You can change the precision of exchange rates during the setting up process by editing the `.env` file and changing the value for `APP_RATE_PRECISION`.

# Running Tests

You can run tests with phpunit. Before you do, make sure to re-seed your db.

> php artisan db:seed
>
> ./vendor/bin/phpunit
