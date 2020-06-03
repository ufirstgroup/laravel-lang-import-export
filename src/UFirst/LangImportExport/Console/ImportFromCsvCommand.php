<?php

namespace UFirst\LangImportExport\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Arr;
use Lang;
use \UFirst\LangImportExport\Facades\LangListService;
use Symfony\Component\VarExporter\VarExporter;

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

		$strings = array();

		// Create output device and write CSV.
		if (($input_fp = fopen($file, 'r')) === FALSE) {
			$this->error('Can\'t open the input file!');
		}

		// Write CSV lintes
		$languages = fgetcsv($input_fp, 0, $delimiter, $enclosure, $escape);
		array_shift($languages);
		$translations = [];
		while (($data = fgetcsv($input_fp, 0, $delimiter, $enclosure, $escape)) !== FALSE) {
			$translations[array_shift($data)] = array_combine($languages, $data);
		}
		fclose($input_fp);
		$this->writeLangList($languages, $translations);
	}

	private function writeLangList($languages, $new_translations) {
        foreach ($languages as $locale) {
            $groups = LangListService::allGroup($locale);
            foreach ($groups as $group) {
                $translations = LangListService::loadTranslations($locale, $group);
                $override_translations = array_filter($new_translations, function($key) use($group) {
                    return strpos($key, $group) === 0;
                }, ARRAY_FILTER_USE_KEY);
                if (count($override_translations) === 0) {
                    $this->info("No translations were found for locale ${locale} within group ${group}");
                    continue;
                }
                foreach($override_translations as $key => $value) {
					if ($value[$locale]) {
						Arr::set($translations, $key, $value[$locale]);
					} else {
						Arr::forget($translations, $key);
					}
                }
                $header = "<?php\n\nreturn ";
                $language_file = base_path("resources/lang/{$locale}/{$group}.php");
                if (is_writable($language_file) && ($fp = fopen($language_file, 'w')) !== FALSE) {
                    fputs($fp, $header.VarExporter::export($translations[$group], TRUE).";\n");
                    fclose($fp);
                } else {
                    throw new \Exception("Cannot open language file at {$language_file} for writing. Check the file permissions.");
                }
            }
        }
	}

}