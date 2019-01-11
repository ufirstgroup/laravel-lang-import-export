![Laravel-Lang-Import-Export by HighSolutions](https://raw.githubusercontent.com/highsolutions/laravel-lang-import-export/master/intro.jpg)

Laravel-Lang-Import-Export
==========================

This package provides artisan commands to import and export language files from and to CSV. This can be used to send translations to agencies that normally work with Excel-like files.

It turns some navigation.php file...

```php
<?php

return array (
  'commands' =>
  array (
    'next' => 'Next',
    'prev' => 'Previous',
    'play' => 'Play',
  ),
  'tips' =>
  array (
    'next' => 'Navigate to the next item',
    'prev' => 'Navigate to the previous item',
    'play' => 'Autoplay the slide show',
  ),
);
```
...to the following CSV...

```CSV
navigation.commands.next,Next
navigation.commands.prev,Previous
navigation.commands.play,Play
navigation.tips.next,"Navigate to the next item"
navigation.tips.prev,"Navigate to the previous item"
navigation.tips.play,"Autoplay the slide show"

```
...and vice versa.

Installation
------------

Add the following line to the `require` section of your Laravel webapp's `composer.json` file:

```javascript
    "require": {
        "HighSolutions/laravel-lang-import-export": "5.4.*"
    }
```

Run `composer update` to install the package.

This package uses Laravel 5.5 Package Auto-Discovery.
For previous versions of Laravel, you need to update `config/app.php` by adding an entry for the service provider:

```php
    'providers' => array(
        /* ... */
        'HighSolutions\LangImportExport\LangImportExportServiceProvider'
    )
```

Usage
-----

The package currently provides two commands, one for exporting the files and one for importing them back:

### Export

```bash
php artisan lang:export
php artisan lang:export en * path/to/export
php artisan lang:export en auth -A -X
```

When you call command without parameters, export file will be generated for all localization files within default locale. But you can define **locale** explicitly. You can also export only one file (second parameter - **group**) and define where to store file (you can provide name with and without .csv extension). When you use **output** argument, default path is base_path() -> catalog of your whole project.
But there is few more useful parameters:

| name of parameter | description                             | is required? | default value                      |
|-------------------|-----------------------------------------|--------------|------------------------------------|
| locale           | The locale to be exported                | NO           | default lang of application        |
| group            | The name of translation file to export   | NO           | \* - all files                     |
| output           | Filename of exported translation files   | NO           | storage/app/lang-import-export.csv |
| -A / --append    | Append name of group to the name of file | NO           | empty                              |
| -X / --excel     | Set file encoding (UTF-16) for Excel     | NO           | UTF-8                              |
| -D / --delimiter | Field delimiter                          | NO           | ,                                  |
| -E / --enclosure | Field enclosure                          | NO           | "                                  |

### Import

```
php artisan lang:import
php artisan lang:import en * path/to/import
php artisan lang:import en auth -X
```

When you call command without parameters - it will try to read default file of export command without parameters for default locale and all localization files. You can of course specify all parameters (**locale**, **group**, **input**) and there is few more options:

| name of parameter | description                                  | is required? | default value                      |
|-------------------|----------------------------------------------|--------------|------------------------------------|
| locale            | The locale to be imported                    | NO           | default lang of application        |
| group             | The name of translation file to import       | NO           | * - all files                      |
| output            | Filename of translation files to be imported | NO           | storage/app/lang-import-export.csv |
| -X / --excel      | Set file encoding from Excel                 | NO           | UTF-8                              |
| -D / --delimiter  | Field delimiter                              | NO           | ,                                  |
| -E / --enclosure  | Field enclosure                              | NO           | "                                  |
| -C / --escape     | Field escape                                 | NO           | \                                  |

Changelog
------------

5.4.5
* Laravel 5.7 support

5.4.4
* Laravel 5.6 support

5.4.3
- support Package Auto-Discovery

5.4.2
- resolve problems with PSR-4 autoloading

5.4.1
- improved import command
- improved Excel support
- support of [LaravelLocalization](https://github.com/mcamara/laravel-localization) routes files

5.4.0
- refactor whole repository
- add support for Excel
- add support for export and import all localization files
- any arguments are not required

Roadmap
------------

* Removing tabs from text
* Option for deleting export file after importing.
* Option for excluding certain files (and system ones).
* Unit tests!

Credits
------------

This package was originally created by [UFirst](http://github.com/ufirstgroup) and is available here: [Laravel-lang-import-export](https://github.com/ufirstgroup/laravel-lang-import-export).

Currently is developed by [HighSolutions](https://highsolutions.org), software house from Poland in love in Laravel.
