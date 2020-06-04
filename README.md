laravel-lang-import-export
==========================

This package provides artisan commands to import and export language files from and to CSV. This can be used to send translations to agencies that normally work with Excel-like files.

It turns some navigation.php file...

```php
<?php

return [
  'commands' => [
    'next' => 'Next',
    'prev' => 'Previous',
    'play' => 'Play',
  ]
  'tips' => [
    'next' => 'Navigate to the next item',
    'prev' => 'Navigate to the previous item',
    'play' => 'Autoplay the slide show',
  ]
];
```
...to the following CSV...

```CSV
key,en
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

### Laravel 7.*

```bash
composer require ufirst/lang-import-export:^7.0.0
```

Finally add the following line to the `providers` array of your `app/config/app.php` file:

```php
    'providers' => [
        /* ... */
        'UFirst\LangImportExport\LangImportExportServiceProvider'
    ]
```

### Laravel 5.*

For Laravel 5.* checkout the [legacy branch](https://github.com/ufirstgroup/laravel-lang-import-export/tree/legacy) and require version ^5.1.2

> The usage of the legacy version of this package is slightly different


> As an alternative you can checkout the fork of this repository [highsolutions/laravel-lang-import-export](https://github.com/highsolutions/laravel-lang-import-export)


Usage
-----

The package currently provides two commands, one for exporting the files and one for importing them back:

### Export

```bash
# export all locales with all groups to console
php artisan lang-export:csv
# export all locales with all groups to csv file
php artisan lang-export:csv --output /some/file.csv
# custom csv delimiter and enclosure
php artisan lang-export:csv --delimiter=";" --enclosure='"' --output=/some/file.csv
# export single locale
php artisan lang-export:csv -l en --output=/some/path/translations-en.csv
# export single translation group
php artisan lang-export:csv -g navigation --output=/some/path/navigation-all-langs.csv
```

You can optionally pass the __-l__  (locale) and the __-g__  (group) as options. The group is the name of the langauge file without its extension. You may define options for your desired CSV format.

### Import


```bash
# import translations from csv
php artisan lang-import:csv /some/file.csv
# import from custom csv format
php artisan lang-import:csv --delimiter=";" --enclosure='"' --escape='\\' /some/file.csv
```

During import the locale is extracted from the first row of the CSV file. Translation groups are guessed from the translation keys e.g. __navigation.tips.next__ is imported to __navigation__ group


### Changelog

7.0.0
- added support for laravel:^7.0.0
- added feature to export all locales and groups in a single run
- the import command will guess the locale and the translation group from the csv
- added symfony/var-exporter for new array syntax during the import command
5.1.2
- legacy version
- compatible with laravel:^5.4
