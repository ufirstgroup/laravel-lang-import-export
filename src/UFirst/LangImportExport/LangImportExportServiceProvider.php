<?php

namespace UFirst\LangImportExport;

use Illuminate\Support\ServiceProvider;

use UFirst\LangImportExport\Console\ExportToCsvCommand;
use UFirst\LangImportExport\Console\ImportFromCsvCommand;
use UFirst\LangImportExport\LangListService;
class LangImportExportServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		if ($this->app->runningInConsole()) {
			$this->commands([
				ExportToCsvCommand::class,
				ImportFromCsvCommand::class
			]);
			$this->app->singleton('LangImportExportLangListService', function ($app) {
				return new LangListService();
			});
		}
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

}
