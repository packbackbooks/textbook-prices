# Packback Textbook Price Client

[![Latest Version](https://img.shields.io/github/release/Packbackbooks/textbook-prices.svg?style=flat-square)](https://github.com/Packbackbooks/textbook-prices/releases)
[![Software License](https://img.shields.io/badge/license-APACHE%202.0-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/packbackbooks/textbook-prices/master.svg?style=flat-square&1)](https://travis-ci.org/packbackbooks/textbook-prices)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/Packbackbooks/textbook-prices.svg?style=flat-square)](https://scrutinizer-ci.com/g/Packbackbooks/textbook-prices/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/Packbackbooks/textbook-prices.svg?style=flat-square)](https://scrutinizer-ci.com/g/Packbackbooks/textbook-prices)
[![Total Downloads](https://img.shields.io/packagist/dt/Packbackbooks/textbook-prices.svg?style=flat-square)](https://packagist.org/packages/Packbackbooks/textbook-prices)

This public project is designed to make getting used, new, and rental book
prices from the most common textbook sellers easy. The following clients
can be accessed with this project:

- AbeBooks
- Amazon
- BookRenter (via CommissionJunction)
- Cengage (via CommissionJunction)
- Chegg
- Skyo (via CommissionJunction)
- ValoreBooks

## Getting prices from a provider

All clients extend the abstract PriceClient class. As an example, get prices
from Abe Books for a group of ISBNs is done as follows:

```php
$isbns = [
    // ISBNS
];
$abeBooks = new AbeBooksPriceClient([
    'access_key' => <ABEBOOKS ACCESS KEY>
]);

$prices = $abeBooks->getPricesForIsbns($isbns);

```

## Including in your project

Add the following to your `composer.json` file:

```json
"require": {
        "packbackbooks/textbook-prices": "~0.1.2"
}
```

You must also have `"minimum-stability": "dev"` in your `composer.json` file so that Composer can fetch `dev-master` versions of packages.

## Notes

This package is compliant with [PSR-1][], [PSR-2][] and [PSR-4][]. If you notice compliance oversights, please send
a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


