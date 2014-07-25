<?php

use \UFirst\LangImportExport\LangListService;

App::singleton('LangImportExportLangListService', function()
{
	return new LangListService;
});