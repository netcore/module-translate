## This module is a wrapper for netcore/translations

Module allows you to manage available languages in page and manage translations for them.

## Pre-installation
This package is part of Netcore CMS ecosystem and is only functional in a project that has following packages installed:

https://github.com/netcore/netcore

https://github.com/netcore/module-admin

https://github.com/nWidart/laravel-modules

## Installation
 
 Require this package with composer:
 ```$xslt
 composer require netcore/module-translate

```
 Publish config, assets, migrations. Migrate and seed:
 
 ```$xslt
 php artisan module:publish-config Translate
 php artisan module:publish Translate
 php artisan module:publish-migration Translate
 php artisan migrate
 php artisan module:seed Translate
```

## Usage

You can configure if you want to use languages with string translations or if you only want to use string translation. It is configured in `config/netcore/module-translate.php` config file

To import translations place your translations file in `resources/seed_translation` translations file name should be `transaltions.xlsx` but if you want to change file name, you can do it config file.

Translations are stored in excel file sheet. So sheet should look like this

![Translations sheet](https://node-eu.takescreen.io/media/9/94f7983fca0ffe6f803a5841097397fd.png)

Every new column is new language. Remember before adding new language to be sure that it is created in Languages page.

To import new translations run

```$xslt
php artisan transaltions:import
```

To get you translations in you page you can use

In blade: 
```@lang('validation.url')```

In php:

```trans('validation.url)```

More info about package can be found in [netcore/translations package] (https://github.com/netcore/translations)