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


Finally add the following line to the `providers` array of your `app/config/app.php` file:

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
php artisan lang:export en * --output=export
php artisan lang:export en auth --A --X
```

When you call command without parameters, export file will be generated for all localization files within default locale. But you can define **locale** explicitly. You can also export only one file (second parameter - **group**).
But there is few more useful parameters:

| name of parameter | description                              | is required? | default value                      |
|-------------------|------------------------------------------|--------------|------------------------------------|
| locale            | The locale to be exported                | NO           | default lang of application        |
| group             | The name of translation file to export   | NO           | \* - all files                     |
| --O / --output    | Filename of exported translation files   | NO           | storage/app/lang-import-export.csv |
| --A / --append    | Append name of group to the name of file | NO           | empty                              |
| --X / --excel     | Set file encoding for Excel              | NO           | UTF-8                              |
| --D / --delimiter | Field delimiter                          | NO           | ,                                  |
| --E / --enclosure | Field enclosure                          | NO           | "                                  |

### Import

```
php artisan lang-import:csv en auth /path/to/file
php artisan lang-import:csv --delimiter=";" --enclosure='"' --escape='\\' en_US auth /some/file
```

You have to pass  the __locale__, the __group__ and the __path to the CSV file__ as arguments. The group is the name of the langauge file without its extension. When you exported all files, write *. You may define options to match the CSV format of your input file.

### Credits

This package was originally created by [UFirst](http://github.com/ufirstgroup) and is available here: [Laravel-lang-import-export](https://github.com/ufirstgroup/laravel-lang-import-export).
