<?php

namespace Sotbit\Reviews\Logic;

use Bitrix\Main\DB\Exception;
use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Internals;
use Bitrix\Main\Localization\Loc;

class Addition
{
    public array $config = [];
    public $entity = [
        'QUESTIONS' => Internals\QuestionsTable::class,
        'REVIEWS' => Internals\ReviewsTable::class,
    ];

    public array $result = [];
    protected int $userId;
    protected int $productId;

    public function __construct($nameObject, $userID, $productID)
    {
        $this->config = OptionReviews::getConfigs(SITE_ID);

        $this->userId = $userID;
        $this->productId = $productID;

        try {
            $this->checkBan($nameObject);
            $this->checkAuth($nameObject);
            $this->canRepeat($nameObject);
            $this->ifUserBuy($nameObject);

            $this->result['CAN_ADD'] = true;
        } catch (\Exception $e) {
            $this->result['CAN_ADD'] = false;
            $this->result['CAN_ADD_ERROR'] = $e->getMessage();
        }
    }

    public function checkBan($nameObject, $userID = 0): void
    {
        if (\Sotbit\Reviews\Model\Bans::isBanned($userID ?: $this->userId)) {
            throw new \Exception(Loc::getMessage('SR_ADD_ERROR_BAN_' . $nameObject));
        }
    }

    public function canRepeat($action, $userID = 0, $productID = 0): void
    {
        $result = [];
        $repeatTime = $this->config[$action . '_REPEAT'];

        if ($repeatTime < 0) {
            return;
        }

        $currentUserId = $userID ?: $this->userId;
        $currentProductId = $productID ?: $this->productId;

        $result['TIME_REPEAT'] = $this->getRepeat($repeatTime, $action, $currentUserId, $currentProductId);
        $result['CAN_REPEAT'] = $result['TIME_REPEAT'] === true;

        if (!$result['CAN_REPEAT']) {
            throw new \Exception(
                is_bool($result['TIME_REPEAT']) ?
                    Loc::getMessage('SR_ADD_ERROR_REPEAT_' . $action) :
                    Loc::getMessage(
                        'SR_ADD_ERROR_REPEAT_TIME_' . $action,
                        ['#DATE#' => $result['TIME_REPEAT']]
                    )
            );
        }
    }

    public function getRepeat($repeatTime, $action, $userID = 0, $productID = 0): bool|string
    {
        $fieldProduct = $this->config[$action . '_ID_ELEMENT'];
        $filter = [
            ('=' . ($fieldProduct == 'XML_ID_ELEMENT' ? 'XML_ID_ELEMENT' : 'ID_ELEMENT')) => $productID ?: $this->productId,
        ];

        $filter = array_merge($filter , ["=IP_USER" => $_SERVER['REMOTE_ADDR']]);
        if (\Bitrix\Main\Engine\CurrentUser::get()->getId()) {
            $filter = array_merge($filter ,['=ID_USER' => $userID ?: $this->userId]);
        }

        $arFields = $this->entity[$action]::getList([
            'select' => ['ID', 'DATE_CREATION'],
            'filter' => $filter,
            'order' => ['DATE_CREATION' => 'desc'],
            'limit' => 1
        ])->fetch();

        if (!$arFields) {
            return true;
        }

        if ((int)$repeatTime === 0) {
            return false;
        }

        if (!Loader::includeModule('iblock')) {
            return false;
        }

        $LastTime = \CIBlockFormatProperties::DateFormat('d.m.Y H:i:s', MakeTimeStamp($arFields['DATE_CREATION'], \CSite::GetDateFormat()));
        $ReadyTime = date('d.m.Y H:i:s', strtotime("+" . $repeatTime . " hours", strtotime($LastTime)));
        if (strtotime($ReadyTime) > strtotime(date('d.m.Y H:i:s'))) {
            return $ReadyTime;
        }

        return true;
    }

    public function checkAuth($action, $userID = 0): void
    {
        $currentUserId = $userID ?: $this->userId;

        if ($currentUserId === 0 && $this->config[$action . '_ANONYMOUS'] !== 'Y') {
            throw new \Exception(Loc::getMessage('SR_ADD_ERROR_AUTH_USER_' . $action));
        }
    }

    public function ifUserBuy($action, $userID = 0, $productID = 0): void
    {
        if ($this->config[$action . '_IF_BUY'] === 'Y') {
            return;
        }

        $currentUserId = $userID ?: $this->userId;
        $currentProductId = $productID ?: $this->productId;

        if ($currentUserId === 0) {
            throw new \Exception(Loc::getMessage('SR_ADD_ERROR_BY_' . $action));
        }

        if (
            !Loader::includeModule('sale')
            || !Loader::includeModule('catalog')
        ) {
            return;
        }

        $fieldProductConfig = $this->config[$action . '_ID_ELEMENT'];
        $isOrderPayed = $this->config[$action . '_PAYED'] === 'Y';
        $orderStatus = $this->config[$action . '_ORDER_STATUS'];
        $fieldProduct = $fieldProductConfig == 'XML_ID_ELEMENT' ? 'PRODUCT_XML_ID' : 'PRODUCT_ID';

        $arIdProduct = [];
        $res = \CCatalogSKU::getOffersList(
            $currentProductId
        );

        $arIdProduct[] = $currentProductId;
        if (is_array($res) && !empty($res)) {
            foreach ($res[$currentProductId] as $idOffer => $arOffer) {
                $arIdProduct[] = $idOffer;
            }
        }

        $filter = [
            'FUSER.USER_ID' => $currentUserId,
            $fieldProduct => $arIdProduct,
            '!=ORDER_ID' => '',
        ];

        if ($isOrderPayed) {
            $filter['ORDER.PAYED'] = 'Y';
        } elseif (!empty($orderStatus) && is_array($orderStatus)) {
            $filter['>=STATUS.SORT'] = max($orderStatus);
        }

        $resCanAdd = \Bitrix\Sale\Internals\BasketTable::getList([
                'filter' => $filter,
                'select' => ['STATUS_SORT' => 'STATUS.SORT', 'ORDER_ID'],
                'limit' => 1,
                'runtime' => [
                    new \Bitrix\Main\ORM\Fields\Relations\Reference(
                        'STATUS',
                        \Bitrix\Sale\Internals\StatusTable::class,
                        \Bitrix\Main\ORM\Query\Join::on('this.ORDER.STATUS.STATUS_ID', 'ref.ID')
                    ),
                ],
                'cache' => ['ttl' => 3600, 'cache_joins' => true],
            ])->fetch() !== false;

        if (!$resCanAdd) {
            if ($isOrderPayed) {
                throw new \Exception(Loc::getMessage('SR_ADD_ERROR_PAYED_' . $action));
            }

            throw new \Exception(Loc::getMessage('SR_ADD_ERROR_DEFAULT_' . $action));
        }
    }
}
