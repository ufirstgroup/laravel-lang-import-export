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
							{locale? : The locale to be imported (default - default lang of application).} 
    						{group? : The name of translation file to imported (default - all files).} 
    						{input? : Filename of file to be imported with translation files(optional, default - storage/app/lang-import-export.csv).} 
    						{--D|delimiter=, : Field delimiter (optional, default - ",").} 
    						{--E|enclosure=" : Field enclosure (optional, default - \'"\').} 
    						{--C|escape=" : Field escape (optional, default - \'"\').}
    						{--X|excel : Set file encoding from Excel (optional, default - UTF-8).}';

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
			'locale' => $this->argument('locale') === null ? config('app.locale') : $this->argument('locale'),
			'group' => $this->argument('group'),
			'input' => $this->argument('input') === null ? $this->defaultPath : base_path($this->argument('input')),
			'delimiter' => $this->option('delimiter'),
			'enclosure' => $this->option('enclosure'),
			'escape' => $this->option('escape'),
			'excel' => $this->option('excel') !== false,
		];	

		if(substr($this->parameters['input'], -4) != $this->ext)
			$this->parameters['input'] .= $this->ext;
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
		$input = $this->openFile();

		$translations = $this->readFile($input);

		$this->closeFile($input);

		return $translations;
	}

	/**
	 * Opens file to read content.
	 * 
	 * @return FileInputPointer
	 */
	private function openFile()
	{
		if (($input = fopen($this->parameters['input'], 'r')) === false) {
			$this->error('Can\'t open the input file!');
		}

		return $input;
	}

	/**
	 * Read content of file.
	 * 
	 * @param FilePointer $input
	 * @throws \Exception
	 * @return array
	 */
	private function readFile($input)
	{
		if($this->parameters['excel'])
			$this->adjustFromExcel();

		$translations = [];
		while (($data = fgetcsv($input, 0, $this->parameters['delimiter'], $this->parameters['enclosure'], $this->parameters['escape'])) !== false) {
			if(isset($translations[$data[0]]) == false)
				$translations[$data[0]] = [];

			if(sizeof($data) != 3)
				throw new \Exception("Wrong format of file. Try launch command with -X option if you use Excel for editing file.");

			$translations[$data[0]][$data[1]] = $data[2];
		}

		return $translations;
	}

	/**
	 * Adjust file to Excel format.
	 * 
	 * @return void
	 */
	private function adjustFromExcel()
	{
		$data = file_get_contents($this->parameters['input']);
		file_put_contents($this->parameters['input'], mb_convert_encoding($data, 'UTF-8', 'UTF-16'));		
	}

	/**
	 * Close file.
	 * 
	 * @return void
	 */
	private function closeFile($input)
	{
		fclose($input);
	}

	/**
	 * Save fetched translations to file.
	 * 
	 * @return void
	 */
	private function saveTranslations($translations)
	{
		LangListService::writeLangList($this->parameters['locale'], $this->parameters['group'], $translations);
	}

	/**
	 * Display output that command is finished and where to find file.
	 * 
	 * @return void
	 */
	private function sayItsFinish()
	{
		$this->info('Finished! Translations imported from: '. (substr($this->parameters['input'], strlen(base_path()) + 1))
			. PHP_EOL);
	}
	
}