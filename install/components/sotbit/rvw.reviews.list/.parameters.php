<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("iblock"))
	return;
$iModuleID='sotbit_reviews';
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"MAX_RATING" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage($iModuleID."_MAX_RATING"),
			"TYPE" => "STRING",
			"DEFAULT" => 5,
		),
        "COUNT_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage($iModuleID."_COUNT_PAGE"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),
		"DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage($iModuleID."_DATE_FORMAT"), "BASE"),
		"ID_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage($iModuleID."_ID_ELEMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$ElementID}',
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		),
	),
);
?>