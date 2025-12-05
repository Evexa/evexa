<?php

namespace Sotbit\Reviews\Analytic;

use Bitrix\Iblock\ElementTable;
use Sotbit\Reviews\Internals\AnalyticTable;
use Sotbit\Reviews\Bill\Bill;
use Bitrix\Main\Type;

class Helper
{
    public static function getSites()
    {
        $result = ["reference", 'reference_id'];

        $query = \Bitrix\Main\SiteTable::getList([
            'select' => ['NAME', 'LID'],
            'filter' => ['ACTIVE' => 'Y'],
        ]);

        while ($arSite = $query->fetch()){
            $result['reference'][] = "[{$arSite["LID"]}] {$arSite["NAME"]}";
            $result['reference_id'][] = $arSite["LID"];
        }

        return $result;
    }

    public static function getMetrics():array
    {
        $result = [];

        return $result;
    }

    public static function updateAnalytic($fieldDelete, $fieldAdd, $idElement, $summ, $userID, $entity)
    {

        if (!empty($fieldDelete) && !empty($fieldAdd)) {
            $actionAnalyticDel = Metrics::ACTION[$fieldDelete];
            $actionAnalyticAdd = Metrics::ACTION[$fieldAdd];
            $id = self::getItemAction($idElement, $actionAnalyticDel);

            if ($id > 0) {
                AnalyticTable::update($id, ['ACTION' => $actionAnalyticAdd, 'VALUE' => $summ]);
            } else {
                $AnalyticFields = [
                    'ID_USER' => $userID,
                    'IP_USER' => $_SERVER['REMOTE_ADDR'],
                    'ID_RCQ' => $idElement,
                    'ACTION' => $actionAnalyticAdd,
                    'DATE_CREATION' => new Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
                    'VALUE' => $summ,
                    $entity . '_ID' => $idElement
                ];
                AnalyticTable::add($AnalyticFields);
            }

            return true;
        }


        if (!empty($fieldDelete)) {
            $actionAnalytic = Metrics::ACTION[$fieldDelete];
            $id = self::getItemAction($idElement, $actionAnalytic);
            AnalyticTable::delete($id);

            return true;
        }
    }

    public static function getItemAction($idElement, $actionAnalytic): int
    {
        $res = AnalyticTable::getList([
            'filter' => [
                '=ACTION' => $actionAnalytic,
                'LOGIC' => 'OR',
                ['=REVIEWS_ID' => $idElement],
                ['=QUESTIONS_ID' => $idElement],
            ],
            'select' => ['ID'],
            'limit' => 1
        ])->fetch();

        return (int)$res['ID'] ?? 0;
    }
}