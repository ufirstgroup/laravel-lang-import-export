<?php

namespace UFirst\LangImportExport;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Lang;

class LangListService
{
    /**
     * @var string|null
     */
    private $module;

    public function __construct(Filesystem $disk, $languageFilesPath, ?string $module = null)
    {
        $this->disk = $disk;
        $this->languageFilesPath = $languageFilesPath;
        $this->module = $module;
    }

    public function setModule(string $module)
    {
        $this->module = $module;
    }

    public function loadLangList($locale, $group)
    {
        $translations = Lang::getLoader()->load($this->module ? "{$this->module}/{$locale}" : $locale, $group);
        $prefix_array = [$group => $translations];

        if($this->module) {
            $prefix_array = [$this->module => $prefix_array];
        }

        $translations_with_prefix = Arr::dot($prefix_array);

        return $translations_with_prefix;
    }

    /**
     * Get all languages from the application.
     *
     * @return Collection
     */
    public function allLanguages()
    {
        $directories = Collection::make($this->disk->directories($this->getLanguagePath()));
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
     * @return array|Collection
     */
    public function allGroup($language)
    {
        $groupPath = $this->getLanguagePath() . DIRECTORY_SEPARATOR . "{$language}";
        if (!$this->disk->exists($groupPath)) {
            return [];
        }
        $groups = Collection::make($this->disk->allFiles($groupPath));
        return $groups->map(function ($group) {
            if (empty($group->getRelativePath())) {
                return $group->getBasename('.php');
            } else {
                return $group->getRelativePath() . '/' . $group->getBasename('.php');
            }
        });
    }

    public function getLanguagePath()
    {
        return $this->module ? "{$this->languageFilesPath}/{$this->module}" : $this->languageFilesPath;
    }
}
