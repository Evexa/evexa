<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

if (!CModule::IncludeModule("iblock"))
	return;

$dbUserAgreements = \Bitrix\Main\UserConsent\Internals\AgreementTable::query()->setSelect(['ID', 'NAME'])->fetchAll();
$userAgreements = [];

foreach ($dbUserAgreements as $userAgreement) {
    $userAgreements[$userAgreement['ID']] = $userAgreement['NAME'];
}

$arComponentParameters = array(
	"GROUPS" => array(),
	"PARAMETERS" => array(
		"SHOW_REVIEWS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("SHOW_REVIEWS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SHOW_QUESTIONS" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("SHOW_QUESTIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"FIRST_ACTIVE" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("FIRST_ACTIVE"),
			"TYPE" => "LIST",
			"VALUES" => array('1'=>Loc::getMessage("REVIEWS"),'3'=>Loc::getMessage("QUESTIONS")),
		),
		"MAX_RATING" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("MAX_RATING"),
			"TYPE" => "STRING",
			"DEFAULT" => 5,
		),
		"DEFAULT_RATING_ACTIVE" => array(
			"PARENT" => "BASE",
			"NAME" => Loc::getMessage("DEFAULT_RATING_ACTIVE"),
			"TYPE" => "STRING",
			"DEFAULT" => 3,
		),
        "REVIEWS_TEXTBOX_MAXLENGTH" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("REVIEWS_TEXTBOX_MAXLENGTH"),
            "TYPE" => "STRING",
            "DEFAULT" => 200,
        ),
        "QUESTIONS_TEXTBOX_MAXLENGTH" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("QUESTIONS_TEXTBOX_MAXLENGTH"),
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
