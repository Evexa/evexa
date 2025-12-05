<?php

namespace Sotbit\Reviews\Bill;

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Bitrix\Main\Type;
use Sotbit\Reviews\Internals\AnalyticTable;

class Bill
{
    public static function userMoney(
        $idUser,
        $action,
        $site,
        $idRCQ,
        $entity = ''
    ): void
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        $sum = 0;

        switch ($action) {
            case 'LIKE';
                $sum = OptionReviews::getConfig($entity . "_BILL_LIKE_REVIEW", $site);
                break;
            case 'DISLIKE';
                $sum = OptionReviews::getConfig($entity . "_BILL_DISLIKE_REVIEW", $site);
                break;
            case 'ADD_REVIEW';
            case 'ADD_QUESTION';
                $sum = OptionReviews::getConfig($entity . "_BILL_ADD_REVIEW", $site);
                break;
        }

        $currency = OptionReviews::getConfig($entity . "_BILL_CURRENCY_REVIEW", $site);
        $groups = OptionReviews::getConfig($entity . "_BILL_GROUP", $site);

        if ($sum > 0 && $idUser > 0 && !empty($currency)) {

            // Check group
            $InGroup = false;
            if (is_array($groups) && count($groups) > 0) {
                $UserGroups = \CUser::GetUserGroup($idUser);

                foreach ($groups as $Group) {
                    if (in_array($Group, $UserGroups)) {
                        $InGroup = true;
                        break;
                    }
                }
            } else {
                $InGroup = true;
            }

            if ($InGroup) {
                // Check if bill exist
                if (!\CSaleUserAccount::GetByUserID($idUser, $currency)) {
                    // Create bill if not exist - need for unlock
                    $arFields = [
                        "USER_ID" => $idUser,
                        "CURRENCY" => $currency,
                        "CURRENT_BUDGET" => 0,
                        'LOCKED' => 'N'
                    ];
                    \CSaleUserAccount::Add($arFields);
                }

                if ($ar = \CSaleUserAccount::GetByUserID($idUser, $currency)) {
                    $NewSum = $ar["CURRENT_BUDGET"] + $sum;

                    \CSaleUserAccount::Update($ar['ID'], ['CURRENT_BUDGET' => $NewSum]);
                }
            }
        }

        $AnalyticFields = [
            'ID_USER' => $idUser,
            'IP_USER' => $_SERVER['REMOTE_ADDR'],
            'ID_RCQ' => $idRCQ,
            'ACTION' => \Sotbit\Reviews\Analytic\Metrics::ACTION[$action],
            'VALUE' => $sum,
            $entity . '_ID' => $idRCQ
        ];

        AnalyticTable::add($AnalyticFields);
    }
}
