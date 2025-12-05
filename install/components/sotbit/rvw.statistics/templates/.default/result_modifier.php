<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Sotbit\Reviews\Helper\OptionReviews;

$arResult['REVIEWS_SCHEMA_ORG'] = OptionReviews::getConfig(SITE_ID);

