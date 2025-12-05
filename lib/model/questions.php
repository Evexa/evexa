<?php
namespace Sotbit\Reviews\Model;

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Sotbit\Reviews\Internals\QuestionsTable;
use Sotbit\Reviews\Helper\OptionReviews;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Sotbit\Reviews\Security\Security;
use Sotbit\Reviews\Internals;

class Questions extends Internals\EO_Questions
{
    public static function getObject(int $id): self
    {
        $object = QuestionsTable::getByPrimary($id)->fetchObject();
        return $object ?? throw new \Exception(Loc::getMessage('SR_QUESTIONS_NOT_FOUND'));
    }

    public static function update($ID, $field, $oldField = []): \Bitrix\Main\ORM\Data\UpdateResult
    {
        $arFields = array(
            "DATE_CREATION" => new DateTime($field['DATE_CREATION']),
            "DATE_CHANGE" => new DateTime(date(DateTime::getFormat()), DateTime::getFormat()),
            "QUESTION" => $field['QUESTION'],
            "ANSWER" => $field['ANSWER'],
            "LIKES" => $field['LIKES'],
            "DISLIKES" => $field['DISLIKES'],
            "ACTIVE" => ($field['ACTIVE'] != "Y" ? "N" : "Y"),
            "RECOMMENDATED" => ($field['RECOMMENDATED'] != "Y" ? "N" : "Y"),
            "MODERATED" => ($field['MODERATED'] != "Y" ? "N" : "Y"),
            "MODERATED_BY" => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
        );

        $fields['OLD_FIELDS'] = $oldField;
        $fields['SITE'] = $oldField['LID'];
        $fields['NEW_FIELDS'] = $arFields;
        $rsEvents = \GetModuleEvents(\CSotbitReviews::iModuleID, "OnBeforeUpdateQuestion");
        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
            $arFields = $fields['NEW_FIELDS'];
        }

        $result = QuestionsTable::update($ID, $arFields);

        if (!$result->isSuccess()) {
            return $result;
        }
        $rsEvents = \GetModuleEvents(\CSotbitReviews::iModuleID, "OnAfterUpdateQuestion");

        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
        }

        return $result;
    }

    public static function getErrorCollection($error)
    {
        $errorCollection = new ErrorCollection();
        $errorCollection->add(is_array($error) ? $error : [$error]);
        return $errorCollection;
    }

    public static function add($data, $idElement){
        Loader::includeModule('iblock');
        $config = OptionReviews::getConfigsQuestions(SITE_ID);

        if ($recaptcha = Security::checkRecaptha($config)) {
            return AjaxJson::createError(self::getErrorCollection($recaptcha));
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
            $config['QUESTIONS_ANONYMOUS_USER'] == 'Y'
            && $data['ANONYMOUS'] == 'Y'
            && $config['QUESTIONS_REGISTER_USERS'] != 'Y'
        ) {
            $arFields['ANONYMITY'] = true;
        }

        $arFields['ID_USER'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
        $arFields['QUESTION'] = $data['QUESTION'];
        $arFields['MODERATED'] = $config['QUESTIONS_MODERATION'] == 'N' ? "Y" : 'N';
        $arFields['RECOMMENDATED'] = $data['RECOMMENDATED'] != 'Y' ? 'N' : 'Y';
        $arFields['ACTIVE'] = 'Y';
        $arFields['IP_USER'] = $_SERVER['REMOTE_ADDR'];
        $fields['NEW_FIELDS'] = $arFields;
        $fields['SITE'] = SITE_ID;
        $fields['NOTICE_EMAIL'] = $data['NOTICE_EMAIL'];

        $rsEvents = GetModuleEvents(\CSotbitReviews::iModuleID, "OnBeforeAddQuestion");
        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
            $arFields = $fields['NEW_FIELDS'];
        }

        $result = QuestionsTable::add($arFields);

        if (!$result->isSuccess()) {
            return $result->getErrorMessages();
        }
        $ID = $result->getId();
        $fields['ID'] = $ID;

        $rsEvents = GetModuleEvents(\CSotbitReviews::iModuleID, "OnAfterAddQuestion");
        while ($arEvent = $rsEvents->Fetch()) {
            ExecuteModuleEventEx($arEvent, [$fields]);
        }
        return "SUCCESS";
    }
}
