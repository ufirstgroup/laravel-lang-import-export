<?php

namespace UFirst\LangImportExport;

use Lang;

class LangListService {

	public function loadLangList($locale, $group) {
		$translations = Lang::getLoader()->load($locale, $group);
		$translations_with_prefix = array_dot(array($group => $translations));
		return $translations_with_prefix;
	}

	public function writeLangList($locale, $group, $new_translations) {
		$translations = Lang::getLoader()->load($locale, $group);
		foreach($new_translations as $key => $value) {
			array_set($translations, $key, $value);
		}
		$header = "<?php\n\nreturn ";

		$language_file = base_path("resources/lang/{$locale}/{$group}.php");
		if (is_writable($language_file) && ($fp = fopen($language_file, 'w')) !== FALSE) {

			fputs($fp, $header.var_export($translations[$group], TRUE).";\n");
			fclose($fp);
		} else {
			throw new \Exception("Cannot open language file at {$language_file} for writing. Check the file permissions.");
		}
	}

}
