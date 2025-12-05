<?php

namespace Sotbit\Reviews\Logic;

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Internals;

class Component
{
    public static function getCountItemFiles($productID, $productField, $rating = 0): int
    {
        $filter = [
            $productField => $productID,
            'ACTIVE' => 'Y',
            'MODERATED' => 'Y',
            '!FILES' => 'N;'
        ];

        if (!empty($rating) && $rating > 0) {
            $filter['=RATING'] = $rating;
        }

        return Internals\ReviewsTable::GetCount($filter);
    }

    public static function getArrayRating($productID, $productField, $request): array
    {
        $filter = [$productField => $productID, 'MODERATED' => 'Y'];

        if ($request['RATING'] > 0) {
            $filter['=RATING'] = $request['RATING'];
        }
        if ($request['FILES'] == 'on') {
            $filter['!FILES'] = 'N;';
        }

        return array_column(
            Internals\ReviewsTable::getList([
                'filter' => $filter,
                'select' => ['RATING'],
                'group' => ['RATING'],
                'data_doubling' => false,
            ])->fetchAll(),
            'RATING') ?? [];
    }
}
