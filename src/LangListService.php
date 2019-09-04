<?php

namespace HighSolutions\LangImportExport;

use Lang;
use File;
use Illuminate\Support\Arr;

class LangListService 
{

	protected $dotFiles = ['routes'];

	/**
	 * Load localization file or files for specified locale.
	 * 
	 * @param string $locale
	 * @param string $group
	 * @return array
	 */
	public function loadLangList($locale, $group) 
	{
		$result = [];
		if($this->isOneGroup($group)) {
			$result[$group] = $this->getGroup($locale, $group);
			return $result;
		}

		$path = resource_path('lang/'. $locale.'/');
		$files = $this->getAllFiles($path);
		foreach($files as $file) {
			$file_path = substr($file->getRealPath(), strlen($path), -4);
			$result[$file_path] = $this->getGroup($locale, $file_path);
		}
		return $result;
	}

	/**
	 * Check if $group is one file only.
	 * 
	 * @param string $group
	 * @return bool
	 */
	private function isOneGroup($group)
	{
		return $group != '*' && $group != '';
	}

	/**
	 * Fetch localization from file.
	 * 
	 * @param string $locale
	 * @param string $group
	 * @return array
	 */
	private function getGroup($locale, $group)
	{
		$translations = Lang::getLoader()->load($locale, $group);
		return Arr::dot($translations);
	}

	/**
	 * Get list of all files from $path.
	 * 
	 * @param string $path
	 * @return array
	 */
	private function getAllFiles($path) 
	{
		return File::allFiles($path);
	}

	/**
	 * Write translated content to localization file or files.
	 * 
	 * @param string $locale
	 * @param string $group
	 * @param array $new_translations
	 * @return void
	 */
	public function writeLangList($locale, $group, $new_translations) 
	{
		if($this->isOneGroup($group)) {
			if(isset($new_translations[$group]) == false)
				return;

			return $this->writeLangFile($locale, $group, $new_translations[$group]);
		}

		foreach($new_translations as $group => $translations)
			$this->writeLangFile($locale, $group, $translations);
	}

	/**
	 * Write translated content to one file.
	 * 
	 * @param string $locale
	 * @param string $group
	 * @param array $new_translations
	 * @throws \Exception
	 * @return void
	 */
	private function writeLangFile($locale, $group, $new_translations)
	{
		$translations = $this->getTranslations($locale, $group, $new_translations);

		$header = "<?php\n\nreturn ";

		$language_file = resource_path("lang/{$locale}/{$group}.php");
		
		if ( ! file_exists(dirname($language_file)))
        	{
		    mkdir(dirname($language_file), 0777, true);
		}

		if (($fp = fopen($language_file, 'w')) !== false && is_writable($language_file))
		{
		    fputs($fp, $header . var_export($translations, true) . ";\n");
		    fclose($fp);
		} else
		{
		    throw new \Exception("Cannot open language file at {$language_file} for writing. Check the file permissions.");
		}
	}

	/**
	 * Fetch existing translations and merge with new ones.
	 * 
	 * @param string $locale
	 * @param string $group
	 * @param array $new_translations
	 * @return array
	 */
	private function getTranslations($locale, $group, $new_translations)
	{
		$translations = Lang::getLoader()->load($locale, $group);		
		foreach($new_translations as $key => $value) {
			Arr::set($translations, $key, $value);
		}

		if(in_array($group, $this->dotFiles)) {
			$translations = Arr::dot($translations);
		}

		return $translations;
	}

}
