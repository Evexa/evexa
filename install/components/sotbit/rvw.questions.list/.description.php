<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => Loc::getMessage('SR_COMPONENT_QUESTIONS_LIST_NAME'),
	"DESCRIPTION" => Loc::getMessage('SR_COMPONENT_QUESTIONS_LIST_DESCRIPTION'),
	"SORT" => 5,
	"PATH" => array(
		"ID" => "sotbit",
		"CHILD" => array(
			"ID" => "reviews",
			"NAME" => Loc::getMessage('SR_COMPONENT_MODULE_NAME'),
			"SORT" => 362,
		),
	),
);
?>
