<?php

namespace UFirst\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use \UFirst\LangImportExport\Facades\LangListService;

class ExportToCsvCommand extends Command
{

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
		return [];
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
			array('locale', 'l', InputOption::VALUE_OPTIONAL, 'The locale to be exported'),
			array('group', 'g', InputOption::VALUE_OPTIONAL, 'The group (which is the name of the language file without the extension)'),
		);
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$delimiter = $this->option('delimiter');
		$enclosure = $this->option('enclosure');
		$groupOption  = $this->option('group');
		$locale = $this->option('locale');
		$languages = LangListService::allLanguages()->all();
		if ($locale && !in_array($locale, $languages)) {
			$this->error("Locale ${locale} does not exist");
			return;
		}
		$languages = $locale ? [$locale => $locale] : $languages;
		$translations = [];
		foreach ($languages as $language) {
			$groups = LangListService::allGroup($language)->all();
			if ($groupOption) {
				if (in_array($groupOption, $groups)) {
					$groups = [$groupOption];
				} else {
					$this->error("Group ${groupOption} does not exist for locale ${language}. Skipping");
					continue;
				}
			}

			foreach ($groups as $group) {
				$strings = LangListService::loadLangList($language, $group);
				$strings = array_map(function ($value) use ($language) {
					return [$language => $value];
				}, $strings);
				$translations = array_merge_recursive($translations, $strings);
			}
		}
		//normalize
		$defaultLanguValues = array_map(function () {
			return "";
		}, $languages);
		$translations = array_map(function ($translation) use ($defaultLanguValues) {
			return array_merge($defaultLanguValues, $translation);
		}, $translations);
		// Create output device and write CSV.
		$output = $this->option('output');
		if (empty($output) || !($out = fopen($output, 'w'))) {
			$out = fopen('php://output', 'w');
		}
		// Write CSV lintes
		fputcsv($out, array_merge(["key"], $languages));
		foreach ($translations as $key => $values) {
			try {
				fputcsv($out, array_merge([$key], $values), $delimiter, $enclosure);
			} catch (\Exception $e) {
				$this->error("Failed to write ${key} with error: " . $e->getMessage());
			}
		}
		fclose($out);
	}
}
