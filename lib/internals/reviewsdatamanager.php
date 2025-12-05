<?php

namespace Sotbit\Reviews\Internals;


use Bitrix\Main\Entity\DataManager;
use Sotbit\Reviews\Helper\OptionReviews;

abstract class ReviewsDataManager extends DataManager
{
    public static function deleteElement(int $elementId, string $optionKey, string $site): bool
    {
        if (OptionReviews::getConfig($optionKey, $site) !== "Y") {
            return false;
        }

        $rsData = static::getList([
            'select' => ['ID'],
            'filter' => ['=ID_ELEMENT' => $elementId],
            'order' => ['ID' => 'desc']
        ]);

        while ($arRes = $rsData->Fetch()) {
            $res = static::delete($arRes['ID']);
        }

        if ($res && $res->isSuccess()) {
            return true;
        }

        return false;
    }
}
