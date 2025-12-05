<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\UserConsent\Internals\AgreementTable;

$dbUserAgreements = AgreementTable::query()->setSelect(['ID', 'NAME'])->fetchAll();
$userAgreements = [];

foreach ($dbUserAgreements as $userAgreement) {
    $userAgreements[$userAgreement['ID']] = $userAgreement['NAME'];
}

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "USER_AGREEMENT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage('USER_AGREEMENT_ID'),
            "TYPE" => "LIST",
            "DEFAULT" => 1,
            "VALUES" => $userAgreements
        ),
    ),
);
