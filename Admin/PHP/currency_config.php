<?php
/**
 * currency_config.php
 * Single source of truth for exchange rates across the system.
 *
 * Rates are expressed as: 1 foreign unit = X PHP
 * Default display currency is PHP.
 * All monetary values stored in DB are in their original currency.
 * When deducting from the PHP budget, multiply by the rate below.
 */

define('CURRENCY_BASE', 'PHP');

// How many PHP = 1 unit of the foreign currency
define('EXCHANGE_RATES', [
    'PHP' => 1.00,
    'USD' => 58.50,
    'EUR' => 64.30,
    'JPY' => 0.39,
    'GBP' => 74.20,
    'CNY' => 8.10,
    'SGD' => 43.50,
]);

// How many USD = 1 unit of the foreign currency (for JS use)
// PHP/USD rate: 1 USD = 58.50 PHP  →  1 PHP = 1/58.50 USD
define('USD_RATES', [
    'PHP' => 0.01709,   // 1/58.50
    'USD' => 1.00,
    'EUR' => 1.0986,    // 64.30/58.50
    'JPY' => 0.00667,   // 0.39/58.50
    'GBP' => 1.2684,    // 74.20/58.50
    'CNY' => 0.13846,   // 8.10/58.50
    'SGD' => 0.74359,   // 43.50/58.50
]);

define('CURRENCY_SYMBOLS', [
    'PHP' => '₱',
    'USD' => '$',
    'EUR' => '€',
    'JPY' => '¥',
    'GBP' => '£',
    'CNY' => '¥',
    'SGD' => 'S$',
]);

// Country → default currency mapping (ISO 3166-1 alpha-2)
define('COUNTRY_CURRENCY', [
    'PH' => 'PHP',   // Philippines
    'US' => 'USD',   // United States
    'DE' => 'EUR',   // Germany
    'FR' => 'EUR',   // France
    'IT' => 'EUR',   // Italy
    'ES' => 'EUR',   // Spain
    'GB' => 'GBP',   // United Kingdom
    'JP' => 'JPY',   // Japan
    'CN' => 'CNY',   // China
    'SG' => 'SGD',   // Singapore
    'AU' => 'AUD',   // Australia (fallback USD)
    'CA' => 'CAD',   // Canada (fallback USD)
]);

/**
 * Convert an amount from its original currency to PHP.
 *
 * @param float  $amount
 * @param string $from_currency
 * @return float
 */
function to_php(float $amount, string $from_currency): float {
    $rates = EXCHANGE_RATES;
    $from  = strtoupper($from_currency);
    $rate  = $rates[$from] ?? 1.0;
    return $amount * $rate;
}

/**
 * Convert an amount from PHP to a target currency.
 *
 * @param float  $amount_php
 * @param string $to_currency
 * @return float
 */
function from_php(float $amount_php, string $to_currency): float {
    $rates = EXCHANGE_RATES;
    $to    = strtoupper($to_currency);
    $rate  = $rates[$to] ?? 1.0;
    return $rate > 0 ? $amount_php / $rate : $amount_php;
}

/**
 * Format an amount with its currency symbol.
 *
 * @param float  $amount
 * @param string $currency
 * @param int    $decimals
 * @return string
 */
function fmt_currency(float $amount, string $currency, int $decimals = 2): string {
    $symbols = CURRENCY_SYMBOLS;
    $sym     = $symbols[strtoupper($currency)] ?? strtoupper($currency) . ' ';
    return $sym . number_format($amount, $decimals);
}

/**
 * Return the PHP equivalent label string, e.g. "≈ ₱1,234.56"
 *
 * @param float  $amount
 * @param string $from_currency
 * @return string
 */
function php_equivalent(float $amount, string $from_currency): string {
    if (strtoupper($from_currency) === 'PHP') return '';
    $php = to_php($amount, $from_currency);
    return '≈ ' . fmt_currency($php, 'PHP', 2);
}
