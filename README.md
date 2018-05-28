## This module is a wrapper for netcore/translations

Module allows you to manage available languages in page and manage translations for them.

## Pre-installation
This package is part of Netcore CMS ecosystem and is only functional in a project that has following packages installed:

https://github.com/netcore/netcore

https://github.com/netcore/module-admin

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

- To show your translated strings in page you should use "lg" function like this
```$xslt
lg('group.key', 'Lorem ipsum')
```
- or if you want to use replaceable attributes in translated string
```$xslt
lg('group.key', ['attribute' => 'value'], null, 'Lorem ipsum :attribute')
```
 - and if you want to use this function in blade files, just add @, for example:
 ```$xslt
@lg('group.key', 'Lorem ipsum')
 ```

## Finding translations

- This command will find all translations in project which uses "lg" function and create Excel sheet with keys and values(in available languages) in resources/seed_translations folder with the name specified in config (default: translations)

```$xslt
php artisan translations:find
```

## Importing translations

- This command will import translations located in Excel sheet to database

```$xslt
php artisan translations:import
```

##
More info about core package can be found in (https://github.com/netcore/translations)
