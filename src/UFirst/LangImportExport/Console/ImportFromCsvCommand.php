<?php

namespace UFirst\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Arr;
use Lang;
use \UFirst\LangImportExport\Facades\LangListService;
use Symfony\Component\VarExporter\VarExporter;

class ImportFromCsvCommand extends Command
{

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
	protected $description = "Imports the language files from CSV files";

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
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
	public function handle()
	{
		$file   = $this->argument('file');

		$delimiter = $this->option('delimiter');
		$enclosure = $this->option('enclosure');
		$escape    = $this->option('escape');

		// Create output device and write CSV.
		if (($input_fp = fopen($file, 'r')) === FALSE) {
			$this->error('Can\'t open the input file!');
		}

		// Write CSV lintes
		$languages = fgetcsv($input_fp, 0, $delimiter, $enclosure, $escape);
		array_shift($languages);
		$translations = [];
		$lineNumber = 1;
		while (($data = fgetcsv($input_fp, 0, $delimiter, $enclosure, $escape)) !== FALSE) {
			$lineNumber++;
			try {
				$translations[array_shift($data)] = array_combine($languages, $data);
			} catch (\Exception $e) {
				$this->error("Failed to import line ${lineNumber}. Languages" . implode(", ", $languages) . ' | Translations: ' . implode(", ", $data) . ' ' . $e->getMessage());
			}
		}
		fclose($input_fp);
		$this->writeLangList($languages, $translations);
	}

	private function getGroupsFromNewTranslations($new_translations)
	{
		$groups = [];
		foreach ($new_translations as $key => $value) {
			$group = explode('.', $key)[0];
			$groups[$group] = $group;
		}
		return $groups;
	}

	private function writeLangList($languages, $new_translations)
	{
		$groups = $this->getGroupsFromNewTranslations($new_translations);
		foreach ($languages as $locale) {
			foreach ($groups as $group) {
				$translations = LangListService::loadLangList($locale, $group);
				$override_translations = array_filter($new_translations, function ($key) use ($group) {
					return strpos($key, $group) === 0;
				}, ARRAY_FILTER_USE_KEY);
				if (count($override_translations) === 0) {
					$this->info("No translations were found for locale ${locale} within group ${group}");
					continue;
				}
				foreach ($override_translations as $key => $value) {
					if ($value[$locale]) {
                        $translations[$key] = $value[$locale];
					} else {
						Arr::forget($translations, $key);
					}
				}
                $undottedTranslations = [];
                foreach ($translations as $key => $translation) {
                    Arr::set($undottedTranslations, $key, $translation);
                }
                $translations = $undottedTranslations;
				$header = "<?php\n\nreturn ";
				$language_dir = base_path("resources/lang/{$locale}");
				if (!is_writable($language_dir)) {
					$this->error("Language directory $language_dir does not exist or is not writeable. Skipping");
					continue;
				}
				$language_file = base_path("resources/lang/{$locale}/{$group}.php");
				if (!is_writable($language_file)) {
					$this->info("Creating language file: $language_file");
					touch($language_file);
				}
				if (($fp = fopen($language_file, 'w')) !== FALSE) {
					fputs($fp, $header . VarExporter::export($translations[$group]) . ";\n");
					fclose($fp);
				} else {
					$this->error("Cannot open language file at {$language_file} for writing. Check the file permissions.");
				}
			}
		}
	}
}
