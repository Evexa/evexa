<?php

namespace Sotbit\Reviews\Model;

use Sotbit\Reviews\Internals;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\Query;

class Bans extends Internals\EO_Bans
{
    protected Reviews $review;
    protected int $userId;
    public function createByReview(int $reviewId, int $userId = 0): void
    {
        $this->userId = $userId ?: (int)\Bitrix\Main\Engine\CurrentUser::get()->getId();
        if (!$this->userId) {
            throw new \Exception(Loc::getMessage('SR_BANS_CREATE_ERROR_USER'));
        }

        $this->fillReview($reviewId);
        $this->set('ID_USER', $this->review->get('ID_USER'));
        $this->set('IP', $this->review->get('IP_USER'));
        $this->set('TYPE', self::getTypeList()['COMPLAINT']);
        $this->setReasonByTemplate();
        $this->setActive('N');

        $saveResult = $this->save();
        if (!$saveResult->isSuccess()) {
            throw new \Exception($saveResult->getErrorMessage());
        }
    }

    protected function fillReview(int $id): void
    {
        $this->review = Reviews::getObject($id);
    }

    public static function getTypeList(): array
    {
        return [
            'COMPLAINT' => Loc::getMessage('SR_BANS_COMPLAINT_TYPE')
        ];
    }

    protected function setReasonByTemplate(): void
    {
        $this->setReason(Loc::getMessage('SR_BANS_REASON_TEMPLATE', [
            '#ID_USER#' => $this->userId,
            '#ID_USER_COMPLAINT#' => $this->review->get('ID_USER'),
            '#REVIEWS#' => $this->review->getText()
        ]));
    }

    public static function isBanned(int $userId): bool
    {
        return Internals\BansTable::query()
            ->addSelect('ID')
            ->where('ACTIVE', 'Y')
            ->where('DATE_TO', '>', new \Bitrix\Main\Type\DateTime())
            ->where(
                Query::filter()
                    ->logic('or')
                    ->where('IP', $_SERVER["REMOTE_ADDR"])
                    ->where(
                        Query::filter()
                        ->logic('and')
                        ->where('ID_USER', $userId)
                        ->whereNot('ID_USER', 0)
                    )
            )->fetch() !== false;
    }
}
