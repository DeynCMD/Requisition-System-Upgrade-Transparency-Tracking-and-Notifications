/**
 * currency.js
 * Single source of truth for currency handling on the frontend.
 * Must stay in sync with Admin/PHP/currency_config.php
 *
 * Usage (include before any page script):
 *   <script src="../../Admin/JS/currency.js"></script>
 */

const Currency = (() => {

  // PHP-per-foreign-unit rates (same as currency_config.php)
  const PHP_RATES = {
    PHP: 1.00,
    USD: 58.50,
    EUR: 64.30,
    JPY: 0.39,
    GBP: 74.20,
    CNY: 8.10,
    SGD: 43.50,
  };

  // USD-per-foreign-unit (for DigiKey prices which come in USD)
  const USD_RATES = {
    PHP: 0.01709,
    USD: 1.00,
    EUR: 1.0986,
    JPY: 0.00667,
    GBP: 1.2684,
    CNY: 0.13846,
    SGD: 0.74359,
  };

  const SYMBOLS = {
    PHP: '₱',
    USD: '$',
    EUR: '€',
    JPY: '¥',
    GBP: '£',
    CNY: '¥',
    SGD: 'S$',
  };

  // Country → default currency
  const COUNTRY_CURRENCY = {
    PH: 'PHP', US: 'USD', DE: 'EUR', FR: 'EUR', IT: 'EUR',
    ES: 'EUR', GB: 'GBP', JP: 'JPY', CN: 'CNY', SG: 'SGD',
  };

  const DEFAULT_CURRENCY = 'PHP';

  /**
   * Detect user's likely currency from browser locale.
   * Falls back to PHP.
   */
  function detectCurrency() {
    try {
      const locale  = navigator.language || 'en-PH';
      const country = locale.split('-')[1]?.toUpperCase();
      return COUNTRY_CURRENCY[country] || DEFAULT_CURRENCY;
    } catch {
      return DEFAULT_CURRENCY;
    }
  }

  /**
   * Convert a USD-denominated price to target currency.
   * DigiKey returns prices in USD, so this is the main conversion.
   *
   * @param {number} priceUSD
   * @param {string} targetCurrency
   * @returns {number}
   */
  function fromUSD(priceUSD, targetCurrency) {
    const cur = (targetCurrency || DEFAULT_CURRENCY).toUpperCase();
    if (cur === 'USD') return priceUSD;
    // USD → PHP → target
    const php = priceUSD * PHP_RATES['USD'];
    return cur === 'PHP' ? php : php / PHP_RATES[cur];
  }

  /**
   * Convert any currency amount to PHP.
   *
   * @param {number} amount
   * @param {string} fromCurrency
   * @returns {number}
   */
  function toPHP(amount, fromCurrency) {
    const cur = (fromCurrency || DEFAULT_CURRENCY).toUpperCase();
    return amount * (PHP_RATES[cur] || 1);
  }

  /**
   * Get the symbol for a currency code.
   *
   * @param {string} currency
   * @returns {string}
   */
  function symbol(currency) {
    return SYMBOLS[(currency || DEFAULT_CURRENCY).toUpperCase()] || currency;
  }

  /**
   * Format a number with its currency symbol.
   *
   * @param {number} amount
   * @param {string} currency
   * @param {number} decimals
   * @returns {string}
   */
  function format(amount, currency, decimals = 2) {
    const cur = (currency || DEFAULT_CURRENCY).toUpperCase();
    const sym = SYMBOLS[cur] || cur + ' ';
    return sym + Number(amount).toLocaleString('en-PH', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });
  }

  /**
   * Format with 4 decimal places (for unit prices).
   */
  function format4(amount, currency) {
    return format(amount, currency, 4);
  }

  /**
   * Return the PHP equivalent string for display, e.g. "≈ ₱1,234.56"
   * Returns empty string if currency is already PHP.
   *
   * @param {number} amount
   * @param {string} fromCurrency
   * @returns {string}
   */
  function phpEquivalent(amount, fromCurrency) {
    const cur = (fromCurrency || DEFAULT_CURRENCY).toUpperCase();
    if (cur === 'PHP') return '';
    const php = toPHP(amount, cur);
    return '≈ ' + format(php, 'PHP', 2);
  }

  /**
   * Build a small HTML badge showing the PHP equivalent.
   * Returns '' if already PHP.
   *
   * @param {number} amount
   * @param {string} fromCurrency
   * @returns {string}
   */
  function phpBadge(amount, fromCurrency) {
    const eq = phpEquivalent(amount, fromCurrency);
    if (!eq) return '';
    return `<span class="php-equiv">${eq}</span>`;
  }

  /**
   * Return an array of supported currency codes.
   */
  function supported() {
    return Object.keys(PHP_RATES);
  }

  return {
    detectCurrency,
    fromUSD,
    toPHP,
    symbol,
    format,
    format4,
    phpEquivalent,
    phpBadge,
    supported,
    PHP_RATES,
    USD_RATES,
    SYMBOLS,
    DEFAULT: DEFAULT_CURRENCY,
  };
})();


// ── Urgency badge helper (mirrors urgency_helper.php) ──
const Urgency = (() => {
  const CLASS = { critical:'urgency-critical', high:'urgency-high', normal:'urgency-normal' };
  const ICON  = { critical:'<i class="fas fa-circle-exclamation"></i>', high:'<i class="fas fa-arrow-up"></i>', normal:'<i class="fas fa-minus"></i>' };

  function key(u) { return (u||'').toLowerCase().trim() === 'critical' ? 'critical' : (u||'').toLowerCase().trim() === 'high' ? 'high' : 'normal'; }

  return {
    /** CSS class string */
    cls:   u => CLASS[key(u)],
    /** Full badge HTML */
    badge: u => u ? `<span class="urgency-badge ${CLASS[key(u)]}">${ICON[key(u)]} ${u}</span>` : '',
    /** Table row class */
    rowCls: u => key(u) === 'normal' ? '' : `urgency-row-${key(u)}`,
  };
})();
