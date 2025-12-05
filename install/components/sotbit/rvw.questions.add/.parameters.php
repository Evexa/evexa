<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("iblock"))
	return;
$IdModule='sotbit.reviews';
$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
        "TEXTBOX_MAXLENGTH" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("TEXTBOX_MAXLENGTH"),
            "TYPE" => "STRING",
            "DEFAULT" => 200,
        ),
		"NOTICE_EMAIL" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("NOTICE_EMAIL"),
			"TYPE" => "STRING",
		),
		"ID_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("ID_ELEMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$ElementID}',
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		), 
	),
);
?>