<?php

namespace Sotbit\Reviews\Bill;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals;
use Bitrix\Main\Localization\Loc;

use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\Mail;

class Coupon
{
    public static function addCoupon($arFields, $entity)
    {
        if(!\Bitrix\Main\Engine\CurrentUser::get()->getId()) {
            return false;
        }

        $addCoupon = OptionReviews::getConfig($entity . "_ADD_COUPON", $arFields['SITE']);

        if ($addCoupon !== 'Y' || !Loader::includeModule('catalog')) {
            return false;
        }

        $idDiscount = OptionReviews::getConfig($entity . "_ID_DISCOUNT", $arFields['SITE']);
        $couponType = OptionReviews::getConfig($entity . "_COUPON_TYPE", $arFields['SITE']);
        $coupon = CatalogGenerateCoupon();
        $arCouponFields = [
            "DISCOUNT_ID" => $idDiscount,
            "ACTIVE" => "Y",
            "TYPE" => $couponType == "Y" ? 1 : ($couponType == "O" ? 2 : 4),
            "COUPON" => $coupon,
            "DATE_APPLY" => false
        ];

        if (
            Loader::includeModule('sale')
            && Internals\DiscountCouponTable::add($arCouponFields)
            && OptionReviews::getConfig($entity . "_SEND_MAIL", $arFields['SITE']) == 'Y'
        ) {
            $arFields['COUPON'] = $coupon;
            $arFields['ACTION'] = Loc::getMessage($entity . '_MAIL');

            Mail::sendMail($arFields, $idDiscount, $arFields['SITE']);
        }
    }
}
