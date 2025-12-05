<?php

namespace Sotbit\Reviews\Controller;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Engine\Controller,
    Bitrix\Main\Loader,
    Bitrix\Main\Type\DateTime;

use Bitrix\Main\Result;
use Sotbit\Reviews\Internals\BansTable,
    Sotbit\Reviews\Internals\QuestionsTable,
    Sotbit\Reviews\Internals\ReviewsTable;

Loc::loadMessages(__FILE__);


class Ban extends Controller
{
    protected array $availableEntity = [
        QuestionsTable::class,
        ReviewsTable::class,
    ];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function configureActions()
    {

        if (Loader::includeModule('sotbit.reviews')) {
            return [
                'addBan' => ['-prefilters' => [Authentication::class]],
            ];
        }
    }

    public function addBanAction($id, $entity)
    {
        if (!in_array($entity, $this->availableEntity)) {
            return (new Result)->addError(new Error('not an available entity'));
        }

        $arFields = $entity::query()
            ->addFilter('ID', $id)
            ->setSelect(['ID_USER', 'IP' => 'IP_USER'])
            ->fetch();

        if (isset($arFields['ID_USER']) || isset($arFields['IP'])) {
            $banQuery = BansTable::getList([
                'filter' => ['ID_USER' => $arFields['ID_USER'], 'IP' => $arFields['IP']],
                'select' => ['ID'],
                'count_total' => 1,
            ]);

            if ($banQuery->getCount() === 0) {
                $result = BansTable::Add($arFields);
            } elseif ($arBan = $banQuery->fetch()) {
                $result = BansTable::update($arBan['ID'], $arFields);
            }

            return $result;
        }
    }
}