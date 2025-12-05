<?php

namespace Sotbit\Reviews\Event;

use Bitrix\Main\Config\Option;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Bill\Coupon;
use Sotbit\Reviews\Bill\Bill;

class QuestionEvent
{
    public static function OnAfterAddQuestion($arFields)
    {
        // Add bill
        if (OptionReviews::getConfig("QUESTIONS_MODERATION", $arFields['SITE']) != 'Y') {
            Bill::userMoney(
                $arFields['NEW_FIELDS']['ID_USER'],
                'ADD_QUESTION',
                $arFields['SITE'],
                $arFields['NEW_FIELDS']['ID']
            );
        }

        // Coupon
        if (
            OptionReviews::getConfig("QUESTIONS_ADD_COUPON", $arFields['SITE']) == 'Y'
            && OptionReviews::getConfig("QUESTIONS_MODERATION", $arFields['SITE']) != 'Y'
        ) {
            Coupon::addCoupon($arFields, "QUESTIONS");
        }

        // Send notice
        $arFields['ACTION'] = 'question';
        \Sotbit\Reviews\Helper\Mail::sendNotice($arFields);
    }

    public static function OnAfterUpdateQuestion($arFields)
    {
        // Add coupon if need

        $rsSites = \Bitrix\Main\SiteTable::getList(['select' => ['LID']])->fetchAll();
        $isActiveCoupon = false;
        $isActiveModeration = false;

        foreach ($rsSites as $site) {
            if (
                OptionReviews::getConfig("QUESTIONS_ADD_COUPON", $site['LID']) == 'Y'
                && OptionReviews::getConfig("QUESTIONS_MODERATION", $site['LID']) == 'Y'
            ) {
                $arFields['SITE'] = $site['LID'];
                $isActiveCoupon = true;
                $isActiveModeration = true;
                break;
            }
        }

        $moderationChanged = (
            $isActiveModeration
            && !empty($arFields['OLD_FIELDS']['MODERATED'])
            && $arFields['OLD_FIELDS']['MODERATED'] == 'N'
            && empty($arFields['OLD_FIELDS']['MODERATED_BY'])
            && !empty($arFields['NEW_FIELDS']['MODERATED'])
            && $arFields['NEW_FIELDS']['MODERATED'] == 'Y'
            && !empty($arFields['NEW_FIELDS']['MODERATED_BY'])
            && $arFields['NEW_FIELDS']['MODERATED_BY'] > 0
        );

        if ($moderationChanged) {
            if ($isActiveCoupon) {
                Coupon::addCoupon($arFields, 'QUESTIONS');
            }

            // Add bill
            Bill::userMoney(
                $arFields['OLD_FIELDS']['ID_USER'],
                'ADD_QUESTION',
                $arFields['SITE'],
                $arFields['OLD_FIELDS']['ID'],
                'QUESTIONS'
            );
        }
    }
}
