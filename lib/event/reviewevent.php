<?php
namespace Sotbit\Reviews\Event;

use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Bill\Coupon;
use Sotbit\Reviews\Bill\Bill;
use Sotbit\Reviews\Helper\Mail;
class ReviewEvent
{
    public static function OnAfterAddReview($arFields) {

        if(empty($arFields['SITE'])) {
            $arFields['SITE'] = SITE_ID ?: '';
        }

        // Add coupon
        if (
            OptionReviews::getConfig("REVIEWS_ADD_COUPON" , $arFields['SITE']) == 'Y'
            && OptionReviews::getConfig( "REVIEWS_MODERATION" , $arFields['SITE']) != 'Y'
        ) {
            Coupon::addCoupon($arFields, "REVIEWS");
        }

        // Add bill
        if (OptionReviews::getConfig( "REVIEWS_MODERATION" , $arFields['SITE']) != 'Y') {
            Bill::userMoney($arFields['NEW_FIELDS']['ID_USER'], 'ADD_REVIEW', $arFields['SITE'], $arFields['ID'], 'REVIEWS');
        }

        // Send notice
        $arFields['ACTION'] = 'review';
        Mail::sendNotice($arFields);
    }

    public static function OnAfterUpdateReview($arFields) {

        $rsSites = \Bitrix\Main\SiteTable::getList(['select' => ['LID']])->fetchAll();
        $isActiveCoupon = false;
        $isActiveModeration = false;

        foreach ($rsSites as $site) {
            if (
                OptionReviews::getConfig("REVIEWS_ADD_COUPON", $site['LID']) == 'Y'
                && OptionReviews::getConfig("REVIEWS_MODERATION", $site['LID']) == 'Y'
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
            // Add coupon if need
            if ($isActiveCoupon) {
                Coupon::addCoupon($arFields, 'REVIEWS');
            }

            // Add bill
            Bill::userMoney($arFields['OLD_FIELDS']['ID_USER'], 'ADD_REVIEW', $arFields['SITE'], $arFields['OLD_FIELDS']['ID'], 'REVIEWS');
        }
    }
}
