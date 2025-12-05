<?
namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\UserTable;

class Mail
{
    public static function sendNotice($arFields): bool
    {
        if (empty($arFields['SITE']) || empty($arFields['ACTION'])) {
            return false;
        }
        
        self::mergeField($arFields);
        self::getActionMessage($arFields);
        self::getNoticeEmail($arFields);
        self::getFieldProduct($arFields, $arFields['ID_ELEMENT']);
        self::getFieldSite($arFields, $arFields['SITE']);
        
        Event::send([
            "EVENT_NAME" => "SOTBIT_REVIEWS_ADD_NOTICE_MAILING_EVENT_SEND",
            "LID" => $arFields['SITE'],
            "C_FIELDS" => $arFields
        ]);

        return true;
    }

    public static function sendMail($arFields, $idDiscount, $site): bool
    {
        if (
            empty($arFields['COUPON'])
            || empty($arFields['NEW_FIELDS']['ID_USER'])
            || empty($arFields['NEW_FIELDS']['ID_ELEMENT'])
            || empty($site)
        ) {
            return false;
        }
        
        self::mergeField($arFields);
        self::getUserEmail($arFields);
        self::getFieldProduct($arFields, $arFields['ID_ELEMENT']);
        self::getFieldSite($arFields, $arFields['SITE']);
        self::getDiscountName($arFields, $idDiscount);

        Event::send([
            "EVENT_NAME" => "SOTBIT_REVIEWS_ADD_MAILING_EVENT_SEND",
            "LID" => $arFields['SITE'],
            "C_FIELDS" => $arFields
        ]);

        return true;
    }
    public static function getDiscountName(&$arFields,$idDiscount): void
    {
         if (!Loader::includeModule('catalog')) {
             return;
         }

         $dbProductDiscounts = \CCatalogDiscount::GetList(["SORT" => "ASC"], ["ACTIVE" => "Y", '=ID' => $idDiscount], false, false, ['NAME']);

         if ($arProductDiscounts = $dbProductDiscounts->Fetch()) {
             $arFields['DISCOUNT_NAME'] = $arProductDiscounts['NAME'];
         }
    }

    public static function getUserEmail(&$arFields): void
    {
        $arUser = UserTable::getById($arFields['ID_USER']);
        if ($arItem = $arUser->fetch()) {
            $arFields['EMAIL_TO'] = $arItem['EMAIL'];
        }
    }
    public static function getNoticeEmail(&$arFields): void
    {
        if (empty($arFields['NOTICE_EMAIL'])) {
            $arFields['NOTICE_EMAIL'] = Option::get("main", "email_from");
        }
    }
    public static function getActionMessage(&$arFields): void
    {
        $arFields['ACTION'] = Loc::getMessage('MAIL_' . $arFields['ACTION']);
    }

    public static function mergeField(&$arFields): void
    {
        if (!empty($arFields['NEW_FIELDS']) && is_array($arFields['NEW_FIELDS'])) {
            $arFields = array_merge($arFields, $arFields['NEW_FIELDS']);
        }

        if (!empty($arFields['OLD_FIELDS']) && is_array($arFields['OLD_FIELDS'])) {
            $arFields = array_merge($arFields, $arFields['OLD_FIELDS']);
        }
    }
    public static function getFieldSite(&$arFields, $id): void
    {
        $rsSites = \Bitrix\Main\SiteTable::getById($id);
        if ($arSite = $rsSites->fetch()) {
            $arFields['SITE_NAME'] = $arSite['SITE_NAME'];
            $arFields['SERVER_NAME'] = $arSite['SERVER_NAME'];
        }
    }

    public static function getFieldProduct(&$arFields, $id): void
    {
        if (!Loader::includeModule('iblock')) {
            return;
        }

        $res = \CIBlockElement::GetByID($id);
        if ($arRes = $res->Fetch()) {
            $arFields['ELEMENT_NAME'] = $arRes['NAME'];
            $arFields['ELEMENT_LINK'] = $arRes['DETAIL_PAGE_URL'];
        }
    }
}

?>
