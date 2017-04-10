<?php

namespace HighSolutions\LangImportExport;

use Lang;
use File;

class LangListService 
{

	public function loadLangList($locale, $group) 
	{
		$result = [];
		if($group != '*') {
			$result[$group] = $this->getGroup($locale, $group);
			return $result;
		}

		$path = resource_path('lang/'. $locale.'/');
		$files = File::allFiles($path);
		foreach($files as $file) {
			$file_path = substr($file->getRealPath(), strlen($path), -4);
			$result[$file_path] = $this->getGroup($locale, $file_path);
		}
		return $result;
	}

	private function getGroup($locale, $group)
	{
		$translations = Lang::getLoader()->load($locale, $group);
		return array_dot($translations);
	}

	public function writeLangList($locale, $group, $new_translations) 
	{
		if($group != '*')
			return $this->writeLangFile($locale, $group, $new_translations);

		foreach($new_translations as $group => $translations)
			$this->writeLangFile($locale, $group, $translations);
	}

	private function writeLangFile($locale, $group, $new_translations)
	{
		$translations = Lang::getLoader()->load($locale, $group);
		foreach($new_translations as $key => $value) {
			array_set($translations, $key, $value);
		}
		$header = "<?php\n\nreturn ";

		$language_file = resource_path("lang/{$locale}/{$group}.php");
		if (is_writable($language_file) && ($fp = fopen($language_file, 'w')) !== FALSE) {
			fputs($fp, $header . var_export($translations, TRUE).";\n");
			fclose($fp);
		} else {
			throw new \Exception("Cannot open language file at {$language_file} for writing. Check the file permissions.");
		}
	}

}
