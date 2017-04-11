<?php

namespace HighSolutions\LangImportExport;

use Illuminate\Support\ServiceProvider;
use HighSolutions\LangImportExport\Console\ExportToCsvCommand;
use HighSolutions\LangImportExport\Console\ImportFromCsvCommand;

class LangImportExportServiceProvider extends ServiceProvider 
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerExportToCsvCommand();
		$this->registerImportFromCsvCommand();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('LangImportExportLangListService', function() {
			return new LangListService;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'lang-export.csv', 'lang-import.csv'
		];
	}

	private function registerExportToCsvCommand() 
	{
		$this->app->singleton('lang-export.csv', function($app)	{
			return new ExportToCsvCommand();
		});

		$this->commands('lang-export.csv');
	}

	private function registerImportFromCsvCommand() 
	{
		$this->app->singleton('lang-import.csv', function($app) {
			return new ImportFromCsvCommand();
		});

		$this->commands('lang-import.csv');
	}

}
