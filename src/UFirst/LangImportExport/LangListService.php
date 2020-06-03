<?php

namespace UFirst\LangImportExport;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Lang;

class LangListService {

	public function __construct(Filesystem $disk, $languageFilesPath)
	{
		$this->disk = $disk;
		$this->languageFilesPath = $languageFilesPath;
	}

	public function loadLangList($locale, $group) {
		$translations = Lang::getLoader()->load($locale, $group);
		$translations_with_prefix = Arr::dot(array($group => $translations));
		return $translations_with_prefix;
	}

	/**
     * Get all languages from the application.
     *
     * @return Collection
     */
    public function allLanguages()
    {
        $directories = Collection::make($this->disk->directories($this->languageFilesPath));
        return $directories->mapWithKeys(function ($directory) {
            $language = basename($directory);
            return [$language => $language];
        })->filter(function ($language) {
            return $language != 'vendor';
        });
	}
	
	/**
     * Get all group translations from the application.
     *
     * @return array
     */
    public function allGroup($language)
    {
        $groupPath = "{$this->languageFilesPath}".DIRECTORY_SEPARATOR."{$language}";
        if (!$this->disk->exists($groupPath)) {
            return [];
        }
        $groups = Collection::make($this->disk->allFiles($groupPath));
        return $groups->map(function ($group) {
            return $group->getBasename('.php');
        });
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
