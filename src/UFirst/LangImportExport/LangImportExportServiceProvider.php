<?php

namespace UFirst\LangImportExport;

use Illuminate\Support\ServiceProvider;

use UFirst\LangImportExport\Console\ExportToCsvCommand;
use UFirst\LangImportExport\Console\ImportFromCsvCommand;

class LangImportExportServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerExportToCsvCommand();
		$this->registerImportFromCsvCommand();
		require __DIR__.'/../../bindings.php';
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

	private function registerExportToCsvCommand() {
		$this->app['lang-export.csv'] = $this->app->share(function($app)
		{
			return new ExportToCsvCommand();
		});

		$this->commands('lang-export.csv');
	}

	private function registerImportFromCsvCommand() {
		$this->app['lang-import.csv'] = $this->app->share(function($app)
		{
			return new ImportFromCsvCommand();
		});

		$this->commands('lang-import.csv');
	}

}
