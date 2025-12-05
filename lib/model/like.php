<?php

namespace Sotbit\Reviews\Model;

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Internals\LikeTable;
use Bitrix\Main\Type\DateTime;

class Like
{
    public static function existenceCheck($arField): int
    {
        $like = LikeTable::getList([
            'filter' => [
                '=QUESTION_ID' => $arField['QUESTION_ID'] ?: 0,
                '=REVIEW_ID' => $arField['REVIEW_ID'] ?: 0,
                "IP" => $_SERVER["REMOTE_ADDR"],
                "ID_USER" => $arField['ID_USER'],
            ],
            'select' => ['ID'],
            'count_total' => 1,
            'limit' => 1,
        ])->fetch();

        return $like ? $like['ID'] : 0;
    }

    public static function create($data)
    {
        $action = 'add';
        if (($idItem = self::existenceCheck($data)) > 0) {
            $action = 'update';
            $data['ID'] = $idItem;
        }

        $result = self::$action($data);
        return $result->isSuccess() ? $result : (throw new \Exception($result->getErrorMessage()));
    }

    public static function add($field): \Bitrix\Main\ORM\Data\AddResult
    {
        return LikeTable::add($field);
    }

    public static function update($field): \Bitrix\Main\ORM\Data\UpdateResult
    {
        $id = $field['ID'];
        unset($field['ID']);

        return LikeTable::update($id, $field);
    }

    public static function recalculationSumm($fields, $originField, $data): float
    {
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $siteId = $context->getSite();

        if ($data['QUESTION_ID'] > 0) {
            $entity = "QUESTIONS";
        } elseif ($data['REVIEW_ID']) {
            $entity = "REVIEWS";
        }

        $price = [
            'LIKES' => OptionReviews::getConfig($entity."_BILL_LIKE_REVIEW", $siteId),
            'DISLIKES' => OptionReviews::getConfig($entity."_BILL_DISLIKE_REVIEW", $siteId),
        ];

        $summ =
            ($fields['LIKES'] - $originField['LIKES']) * $price['LIKES'] +
            ($fields['DISLIKES'] - $originField['DISLIKES']) * $price['DISLIKES'];

        if (Loader::includeModule('sale')) {
            if ($ar = \CSaleUserAccount::GetByUserID($data['ID_USER'], OptionReviews::getConfig($entity."_BILL_CURRENCY_REVIEW", $siteId))) {
                $NewSum = $ar["CURRENT_BUDGET"] + $summ;
                \CSaleUserAccount::Update(
                    $ar['ID'],
                    [
                        'CURRENT_BUDGET' => $NewSum,
                    ]
                );
            }
        }

        return $summ;
    }
}
