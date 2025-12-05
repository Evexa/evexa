<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("iblock"))
	return;
$IdModule='sotbit.reviews';

$dbUserAgreements = \Bitrix\Main\UserConsent\Internals\AgreementTable::query()->setSelect(['ID', 'NAME'])->fetchAll();
$userAgreements = [];

foreach ($dbUserAgreements as $userAgreement) {
	$userAgreements[$userAgreement['ID']] = $userAgreement['NAME'];
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"DEFAULT_RATING_ACTIVE" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("DEFAULT_RATING_ACTIVE"),
			"TYPE" => "STRING",
			"DEFAULT" => 3,
		),
        "COUNT_PAGE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("COUNT_PAGE"),
            "TYPE" => "STRING",
            "DEFAULT" => "10",
        ),
        "MAX_RATING" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("MAX_RATING"),
            "TYPE" => "STRING",
            "DEFAULT" => 5,
        ),
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
		"DATE_FORMAT" => CIBlockParameters::GetDateFormat(Loc::getMessage("DATE_FORMAT"), "BASE"),
		"ID_ELEMENT" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("ID_ELEMENT"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$ElementID}',
		),
		"USER_AGREEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("USER_AGREEMENT_ID"),
			"TYPE" => "LIST",
			"DEFAULT" => 1,
			"VALUES" => $userAgreements
		),
		"CACHE_TIME" => array(
			"DEFAULT" => 36000000,
		), 
	),
);
?>
