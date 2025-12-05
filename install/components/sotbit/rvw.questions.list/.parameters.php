<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("iblock"))
	return;
$iModuleID='sotbit_reviews';
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "COUNT_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("COUNT_PAGE"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),
		"DATE_FORMAT" => CIBlockParameters::GetDateFormat(Loc::getMessage("DATE_FORMAT"), "BASE"),
		"ID_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("ID_ELEMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$ElementID}',
		),
	),
);
?>