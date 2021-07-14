# Trait for implementing model localization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/s-shiryaev/laravel-translatable.svg?style=flat-square)](https://packagist.org/packages/s-shiryaev/laravel-translatable)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/s-shiryaev/laravel-translatable/run-tests?label=tests)](https://github.com/s-shiryaev/laravel-translatable/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/s-shiryaev/laravel-translatable/Check%20&%20fix%20styling?label=code%20style)](https://github.com/s-shiryaev/laravel-translatable/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/s-shiryaev/laravel-translatable.svg?style=flat-square)](https://packagist.org/packages/s-shiryaev/laravel-translatable)


This package contains a trait to make it easier to work with translating Eloquent models.


## Installation

You can install the package via composer:

```bash
composer require s-shiryaev/laravel-translatable
```

## Usage

Just add the `SShiryaev\LaravelTranslatable\Translatable` trait to the model and create a property `translatable`, which holds an array with all the names of attributes you wish to make translatable:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SShiryaev\LaravelTranslatable\Translatable;

class Currency extends Model
{
    use HasFactory, Translatable;

    protected $translatable = ['name', 'code'];

    protected $fillable = [
        'id',
        'name_ru',
        'name_en',
        'code_ru',
        'code_en',
        'code_de',
        'active',
        'sort',
    ];
}
```

Now, when accessing the properties of the model, the value will be returned in accordance with the application locale:
```php
App::setLocale('ru');
$currency = new Currency(['name_ru' => 'Доллар', 'name_en' => 'Dollar']);
echo $currency->name; //Доллар
```

Also, when converting a model and their eloquent collection to an array (for example, in presenters), field values will be returned according to the application locale:
```php
App::setLocale('ru');

$currency = Currency::find(1);
$currency->toArray(); //['name' => 'Доллар', 'name_en' => 'Dollar']

$currencies = Currency::all();
$currencies->toArray(); //[0 => ['name' => 'Доллар', 'name_en' => 'Dollar']]
```

Sometimes, when converting to an array, you need to get the original fields of the model without translation. This can be done by passing an optional parameter in the `toArray()` method:
```php
$currency = Currency::find(1);
$currency->toArray(false); //['name_ru' => 'Доллар', 'name_en' => 'Dollar']

$currencies = Currency::all();
$currencies->toArray(false); //[0 => ['name_ru' => 'Доллар', 'name_en' => 'Dollar']]
```
Translation also works with relationships.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sergey Shiryaev](https://github.com/s-shiryaev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
