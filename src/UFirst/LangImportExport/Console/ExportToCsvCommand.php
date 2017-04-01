<?php

namespace UFirst\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \UFirst\LangImportExport\Facades\LangListService;

class ExportToCsvCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lang-export:csv';

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
			array('output', 'o', InputOption::VALUE_OPTIONAL, 'Redirect the output to this file'),
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

		$delimiter = $this->option('delimiter');
		$enclosure = $this->option('enclosure');

		$strings = LangListService::loadLangList($locale, $group);

		// Create output device and write CSV.
		$output = $this->option('output');
		if (empty($output) || !($out = fopen($output, 'w'))) {
			$out = fopen('php://output', 'w');
		}

		// Write CSV lintes
		foreach ($strings as $group => $files) {
			foreach($files as $key => $value) {
				$this->writeFile($out, $group, $key, $value, $delimiter, $enclosure);
			}
		}

		fclose($out);
	}

	private function writeFile($out, $group, $key, $value, $delimiter, $enclosure)
	{
		fputcsv($out, array($group, $key, $value), $delimiter, $enclosure);
	}

}