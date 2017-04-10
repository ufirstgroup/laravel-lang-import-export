<?php

namespace HighSolutions\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use HighSolutions\LangImportExport\Facades\LangListService;

class ImportFromCsvCommand extends Command 
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'lang:import
							{locale : The locale to be imported (default - default lang of application).} 
    						{group : The name of translation file to imported (default - all files).} 
    						{--I|input= : Filename of file to be imported with translation files(optional, default - storage/app/lang-import-export.csv).} 
    						{--D|delimiter=, : Field delimiter (optional, default - ",").} 
    						{--E|enclosure=" : Field enclosure (optional, default - \'"\').} 
    						{--C|escape=\\" : Field excape (optional, default - \'\\\').}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Imports the CSV file and write content into language files.";

	/**
	 * Parameters provided to command.
	 * 
	 * @var array
	 */
	protected $parameters = [];
	
	/**
	 * Default path for file read.
	 * 
	 * @var string
	 */
	protected $defaultPath;

	/**
	 * File extension (default .csv).
	 * 
	 * @var string
	 */
	protected $ext = '.csv';

	/**
	 * Class constructor.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->defaultPath = storage_path('app'. DIRECTORY_SEPARATOR .'lang-import-export') . $this->ext;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->getParameters();	

		$this->sayItsBeginning();

		$translations = $this->getTranslations();

		$this->saveTranslations($translations);

		$this->sayItsFinish();
	}

	/**
	 * Fetch command parameters (arguments and options) and analyze them.
	 * 
	 * @return void
	 */
	private function getParameters()
	{
		$this->parameters = [
			'group' => $this->argument('group'),
			'locale' => $this->argument('locale') === false ? config('app.locale') : $this->argument('locale'),
			'input' => $this->option('input') === false ? $this->defaultPath : base_path($this->option('input')),
			'delimiter' => $this->option('delimiter'),
			'enclosure' => $this->option('enclosure'),
			'escape' => $this->option('escape'),
		];	
	}

	/**
	 * Display output that command has started and which groups are being imported.
	 * 
	 * @return void
	 */
	private function sayItsBeginning()
	{
		$this->info(PHP_EOL
			. 'Translations import of '. ($this->parameters['group'] === false ? 'all groups' : $this->parameters['group'] .' group') .' has started.');
	}

	/**
	 * Get translations from CSV file.
	 * 
	 * @return array
	 */
	private function getTranslations()
	{
		$translations = [];

		// Create output device and write CSV.
		if (($input_fp = fopen($this->parameters['input'], 'r')) === false) {
			$this->error('Can\'t open the input file!');
		}

		// Write CSV lintes
		while (($data = fgetcsv($input_fp, 0, $this->parameters['delimiter'], $this->parameters['enclosure'], $this->parameters['escape'])) !== false) {
			if(isset($translations[$data[0]]) == false)
				$translations[$data[0]] = [];

			$translations[$data[0]][$data[1]] = $data[2];
		}

		fclose($input_fp);

		return $translations;
	}

	/**
	 * Save fetched translations to file.
	 * 
	 * @return void
	 */
	private function saveTranslations($translations)
	{
		LangListService::writeLangList($locale, $group, $translations);
	}

	/**
	 * Display output that command is finished and where to find file.
	 * 
	 * @return void
	 */
	private function sayItsFinish()
	{
		$this->info('Finished! Translations imported from: '. (substr($this->parameters['output'], strlen(base_path()) + 1)) . $this->ext 
			. PHP_EOL);
	}
	
}