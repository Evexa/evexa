<?php

namespace Sotbit\Reviews\Event;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Sotbit\Reviews\Internals\ReviewsTable;
use Sotbit\Reviews\Internals\QuestionsTable;

Loc::loadMessages(__FILE__);

class Product extends Entity\DataManager
{
    public static function OnBeforeElementDelete($arFields)
    {
        if (!Loader::includeModule('iblock') && !Loader::includeModule('main')) {
            return;
        }

        $idEl = is_array($arFields) ? $arFields['ID'] : $arFields;

        $dbRes = \CIBlockElement::GetList([], ["ID" => $idEl], false, false, ["IBLOCK_ID", "LID"]);
        if ($arRes = $dbRes->Fetch()) {
            $site = $arRes['LID'];
        }

        ReviewsTable::deleteElement($idEl, 'REVIEWS_DELETE', $site);
        QuestionsTable::deleteElement($idEl, 'QUESTIONS_DELETE', $site);
    }
}
