<?php

namespace Sotbit\Reviews\Analytic;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Sotbit\Reviews\Internals;
use Bitrix\Main\Type\DateTime;

class Metrics
{
    const ACTION = [
        'LIKE' => 3,
        'LIKES' => 3,
        'DISLIKE' => 4,
        'DISLIKES' => 4,
        'ADD_REVIEW' => 5,
        'ADD_QUESTION' => 7,
    ];

    public array $counter = [
        'CNT_REVIEWS' => 0,
        'CNT_LIKES' => 0,
        'CNT_DISLIKES' => 0,
        'MONEY_LIKES' => 0,
        'MONEY_DISLIKES' => 0,
        'MONEY_REVIEWS' => 0,
    ];

    public array $jsConfig = [
        'reviewTimeLine' => [
            'selector' => 'reviewTimeLine',
            'data' => [],
            'option' => [],
        ],

        'compareReviewCount' => [
            'selector' => 'compareReviewCount',
            'data' => [],
            'option' => [],
        ],
        'compareLikeCount' => [
            'selector' => 'compareLikeCount',
            'data' => [],
            'option' => [],
        ],
        'compareDislikeCount' => [
            'selector' => 'compareDislikeCount',
            'data' => [],
            'option' => [],
        ],
        'userCount' => [
            'selector' => 'userCount',
            'data' => [],
            'option' => [],
        ],
        'moderatedCount' => [
            'selector' => 'moderatedCount',
            'data' => [],
            'option' => [],
        ],
        'userMoney' => [
            'selector' => 'userMoney',
            'data' => [],
            'option' => [],
        ],
        'compareReviewMoney' => [
            'selector' => 'compareReviewMoney',
            'data' => [],
            'option' => [],
        ],
        'compareLikeMoney' => [
            'selector' => 'compareReviewMoney',
            'data' => [],
            'option' => [],
        ],
        'compareDislikeMoney' => [
            'selector' => 'compareDislikeMoney',
            'data' => [],
            'option' => [],
        ]
    ];

    public array $data = [];
    public array $arDateFormat = [];
    public array $dataUserName = [];
    public array $counterByTime = [];
    public array $counterByUser = [];
    public array $counterByReviewModerate = [];

    public function __construct(public array $sites, public DateTime $dateFrom, public DateTime $dateTo)
    {
        $this->arDateFormat = array_map(
            fn($item) => date("d.m.Y", $item),
            range(strtotime($dateFrom->format('d.m.Y')), strtotime($dateTo->format('d.m.Y')), 60 * 60 * 24)
        );

        $resultQuery = Internals\AnalyticTable::query()
            ->setSelect([
                'ID',
                'ACTION',
                'DATE_CREATION',
                'VALUE',
                'USER_NAME',
                'USER_ID' => 'USER.ID',
                'ELEMENT_ID' => 'REVIEWS.ID_ELEMENT',
                'MODERATED' => 'REVIEWS.MODERATED',
                'SITE_ID' => 'CATALOG_ITEM.IBLOCK.LID',
            ]);

        if (!empty($this->dateFrom)) {
            $resultQuery->where('DATE_CREATION', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $resultQuery->where('DATE_CREATION', '<=', $this->dateTo);
        }

        $resultQuery
            ->registerRuntimeField(
                new ExpressionField(
                    'USER_NAME',
                    'CONCAT("[", %s, "] ", %s, " ", %s, " (", %s,")" )',
                    ["USER.ID", "USER.NAME", "USER.LAST_NAME", "USER.LOGIN"]
                )
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'CATALOG_ITEM',
                    ElementTable::class,
                    Join::on('this.ELEMENT_ID', 'ref.ID')
                )
            );

        if (!empty($this->sites)) {
            $resultQuery->addFilter('SITE_ID', $this->sites);
        }
        $resultQuery->setDistinct(true);

        foreach ($resultQuery->fetchAll() as $item) {
            $this->data[($item['DATE_CREATION']->format('d.m.Y'))][] = $item;
            $this->dataUserName[$item['USER_ID']] = $item['USER_NAME'];
        }

        $this->calculateMetrics();
    }

    public function calculateMetrics()
    {
        foreach ($this->data as $date => $data) {
            foreach ($data as $item) {

                if ($item['VALUE'] && $item['USER_ID']) {
                    $this->counterByUser[$item['USER_ID']]['MONEY'] = $item['VALUE'] + ($this->counterByUser[$item['USER_ID']]['MONEY'] ?: 0);
                }

                switch ($item['ACTION']) {
                    case self::ACTION['ADD_REVIEW'];
                        $this->counterByTime[$date]['CNT_REVIEWS']++;
                        $this->counter['CNT_REVIEWS']++;

                        if ($item['USER_ID']) {
                            $this->counterByUser[$item['USER_ID']]['CNT_REVIEWS']++;
                        }

                        if ($item['MODERATED']) {
                            $this->counterByReviewModerate[$item['MODERATED']]++;
                        }

                        if ($item['VALUE']) {
                            $this->counter['MONEY_REVIEWS'] = $item['VALUE'] + $this->counter['MONEY_REVIEWS'];
                        }
                        break;
                    case self::ACTION['LIKE'];
                        $this->counterByTime[$date]['CNT_LIKES']++;
                        $this->counter['CNT_LIKES']++;

                        if ($item['VALUE']) {
                            $this->counter['MONEY_LIKES'] = $item['VALUE'] + $this->counter['MONEY_LIKES'];
                        }
                        break;
                    case self::ACTION['DISLIKE'];
                        $this->counterByTime[$date]['CNT_DISLIKES']++;
                        $this->counter['CNT_DISLIKES']++;

                        if ($item['VALUE']) {
                            $this->counter['MONEY_DISLIKES'] = $item['VALUE'] + $this->counter['MONEY_DISLIKES'];
                        }
                        break;
                }
            }
        }

        return $this->counterByTime;
    }
}