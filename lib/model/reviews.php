<?php

namespace Sotbit\Reviews\Model;

use Bitrix\Main\Loader;
use Sotbit\Reviews\Internals;
use Sotbit\Reviews\Internals\ReviewsTable;
use Sotbit\Reviews\Security\Security;
use Sotbit\Reviews\Helper\OptionReviews;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Engine\Response\AjaxJson;

class Reviews extends Internals\EO_Reviews
{
    public static function getObject(int $id): self
    {
        $object = ReviewsTable::getByPrimary($id)->fetchObject();
        return $object ?? throw new \Exception(Loc::getMessage('SR_REVIEWS_NOT_FOUND'));
    }

    public static function update($ID, $field, $oldField = [])
    {
        $arFields = array(
            "DATE_CREATION" => new DateTime($field['DATE_CREATION'] ?: $oldField['DATE_CREATION']),
            "DATE_CHANGE" => new DateTime(date(DateTime::getFormat()), DateTime::getFormat()),
            "RATING" => $field['RATING'],
            "TITLE" => $field['TITLE'],
            "TEXT" => $field['TEXT'],
            "ANSWER" => $field['ANSWER'],
            "ADD_FIELDS" => serialize($field['ADD_FIELD']),
            "LIKES" => $field['LIKES'],
            "DISLIKES" => $field['DISLIKES'],
            "ACTIVE" => ($field['ACTIVE'] != "Y" ? "N" : "Y"),
            "RECOMMENDATED" => ($field['RECOMMENDATED'] != "Y" ? "N" : "Y"),
            "MODERATED" => ($field['MODERATED'] != "Y" ? "N" : "Y"),
            "MODERATED_BY" => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
            "COLLECTION_NAME" => $field['COLLECTION_NAME'],
        );

        $fields['OLD_FIELDS'] = $oldField;
        $fields['SITE'] = $oldField['SITE'];
        $fields['NEW_FIELDS'] = $arFields;

        $rsEvents = \GetModuleEvents(\CSotbitReviews::iModuleID, "OnBeforeUpdateReview");

        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
            $arFields = $fields['NEW_FIELDS'];
        }

        $result = ReviewsTable::update($ID, $arFields);

        if (!$result->isSuccess()) {
            return $result;
        }
        $rsEvents = \GetModuleEvents(\CSotbitReviews::iModuleID, "OnAfterUpdateQuestion");

        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
        }

        return $result;
    }

    public static function getAddField($data)
    {
        $result = [];

        foreach ($data as $key => $val) {
            $pos = strpos($key, 'AddFields_');
            if ($pos !== false) {
                $key = substr($key, 10);
                $result[$key] = (LANG_CHARSET == 'windows-1251') ? iconv("UTF-8", "WINDOWS-1251", $val) : $val;
            }
        }

        return $result;
    }

    public static function getErrorCollection($error): ErrorCollection
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(is_array($error) ? $error : [$error]);
        return $errorCollection;
    }

    public static function  add($data, $idElement)
    {
        Loader::includeModule('iblock');
        $config = OptionReviews::getConfigsReviews(SITE_ID);
        $addFields = self::getAddField($data);

        if ($recaptcha = Security::checkRecaptha($config)) {
            return AjaxJson::createError(self::getErrorCollection($recaptcha));
        } elseif ($spam = Security::checkSpam($data, $addFields, $config)) {
            return AjaxJson::createError(self::getErrorCollection($spam));
        }

        $fieldElement = $config['REVIEWS_ID_ELEMENT'] == 'XML_ID_ELEMENT' ? 'XML_ID' : 'ID';

        $res = \Bitrix\Iblock\ElementTable::getList([
            'filter' => [$fieldElement => $idElement],
            'select' => ['ID', 'XML_ID', 'PREVIEW_PICTURE'],
            'limit' => '1'
        ])->fetch();

        if (is_array($res)) {
            $arFields['ID_ELEMENT'] = $res['ID'];
            $arFields['XML_ID_ELEMENT'] = $res['XML_ID'];
        }

        if (
            $config['REVIEWS_ANONYMOUS_USER'] == 'Y'
            && $data['ANONYMOUS'] == 'Y'
            && $config['REVIEWS_REGISTER_USERS'] != 'Y'
        ) {
            $arFields['ANONYMITY'] = true;
        }

        $arFields['ID_USER'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
        $arFields['RATING'] = $data['RATING'];
        $arFields['TEXT'] = $data['COMMENT'];
        $arFields['QUOTE'] = $data['ID_QUOTE'] ?: '';
        $arFields['ADD_FIELDS'] = serialize($addFields);
        $arFields['MODERATED'] = $config['REVIEWS_MODERATION'] == 'N' ? "Y" : 'N';
        $arFields['RECOMMENDATED'] = $data['RECOMMENDATED'] != 'Y' ? 'N' : 'Y';
        $arFields['ACTIVE'] = 'Y';
        $arFields['IP_USER'] = $_SERVER['REMOTE_ADDR'];
        $arFields['FILES'] = serialize($data['MEDIA']);
        $fields['NEW_FIELDS'] = $arFields;
        $fields['SITE'] = SITE_ID;
        $fields['NOTICE_EMAIL'] = $data['NOTICE_EMAIL'];

        $rsEvents = GetModuleEvents(\CSotbitReviews::iModuleID, "OnBeforeAddReview");
        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
            $arFields = $fields['NEW_FIELDS'];
        }

        $result = ReviewsTable::add($arFields);

        if (!$result->isSuccess()) {
            return AjaxJson::createError(self::getErrorCollection($result->getErrorMessages()));
        }
        $id = $result->getId();
        $fields['ID'] = $id;

        $rsEvents = GetModuleEvents(\CSotbitReviews::iModuleID, "OnAfterAddReview");
        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
        }
        return "SUCCESS";
    }
}
