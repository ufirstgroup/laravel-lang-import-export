<?php

namespace UFirst\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \UFirst\LangImportExport\Facades\LangListService;

class ImportFromCsvCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lang-import:csv';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Exports the language files to CSV files";

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('locale', InputArgument::REQUIRED, 'The locale to be exported.'),
			array('group', InputArgument::REQUIRED, 'The group (which is the name of the language file without the extension)'),
			array('file', InputArgument::REQUIRED, 'The CSV file to be imported'),
		);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('delimiter', 'd', InputOption::VALUE_OPTIONAL, 'The optional delimiter parameter sets the field delimiter (one character only).', ','),
			array('enclosure', 'c', InputOption::VALUE_OPTIONAL, 'The optional enclosure parameter sets the field enclosure (one character only).', '"'),
			array('escape',    'e', InputOption::VALUE_OPTIONAL, 'The escape character (one character only). Defaults as a backslash.', '\\'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$locale = $this->argument('locale');
		$group  = $this->argument('group');
		$file   = $this->argument('file');

		$delimiter = $this->option('delimiter');
		$enclosure = $this->option('enclosure');
		$escape    = $this->option('escape');

		$strings = array();

		// Create output device and write CSV.
		if (($input_fp = fopen($file, 'w')) === FALSE) {
			$this->error('Can\'t open the input file!');
		}

		// Write CSV lintes
		while (($data = fgetcsv($input_fp, 0, $delimiter, $enclosure, $escape)) !== FALSE) {
			$strings[$data[0]] = $data[1];
		}

		fclose($input_fp);
		LangListService::writeLangList($locale, $group, $strings);
	}
}