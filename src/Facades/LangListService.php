<?php
namespace HighSolutions\LangImportExport\Facades;

use Illuminate\Support\Facades\Facade;

class LangListService extends Facade
{

	protected static function getFacadeAccessor() 
	{
		return 'LangImportExportLangListService';
	}
	
}