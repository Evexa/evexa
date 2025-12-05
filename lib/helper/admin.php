<?
namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Admin
{
    public static function getLinkElement($idElement)
    {
        if (empty($idElement)) {
            return [];
        }

        $elementRes = \CIBlockElement::GetByID($idElement);

        if ($elementArray = $elementRes->GetNext()) {
            $result['ID'] = $elementArray['ID'];
            $result['NAME'] = '[' . $elementArray['ID'] . '] ' . $elementArray['NAME'];
            $result['LID'] = $elementArray['LID'];
            $result['IBLOCK_ID'] = $elementArray['IBLOCK_ID'];
            $result['DETAIL_PAGE_URL'] = SITE_SERVER_NAME . $elementArray['DETAIL_PAGE_URL'];
            $result['IBLOCK_SECTION_ID'] = $elementArray['IBLOCK_SECTION_ID'];
        }

        $res = \CIBlock::GetByID($result['IBLOCK_ID']);
        if ($ar_res = $res->Fetch()) {
            $result['DETAIL_PAGE_ADMIN_URL'] = 'iblock_element_edit.php?IBLOCK_ID=' . htmlspecialcharsbx($ar_res['ID']) . '&type=' . htmlspecialcharsbx($ar_res['IBLOCK_TYPE_ID']) . '&ID=' . htmlspecialcharsbx($result['ID']) . '&lang=' . htmlspecialcharsbx(LANG) . '&find_section_section=' . htmlspecialcharsbx($result['IBLOCK_SECTION_ID']);
        }

        return $result;
    }

    public static function getSeparationFields(\Bitrix\Main\HttpRequest $request)
    {
        $result = [];
        foreach ($request->getPostList() as $key => $val) {
            $pos = strpos($key, 'ADD_FIELDS_');
            if ($pos !== false) {
                $key = substr($key, 11);
                $result[$key] = $val;
            }
        }

        return $result;
    }

    public static function getFields(\Bitrix\Main\HttpRequest $request, $isNeedAddField = false)
    {
        $result = [];
        $list = $request->getPostList();

        if (empty($list)) {
            return $result;
        }

        foreach ($list as $key => $val) {
            $result[$key] = $val;
        }

        if ($isNeedAddField) {
            $result['ADD_FIELD'] = self::getSeparationFields($request);
        }
        return $result;
    }

    public static function getFieldUser($id)
    {
        $resUser = \Bitrix\Main\UserTable::getList([
            'filter' => ['ID' => $id],
            'select' => ['ID', 'LAST_NAME', 'NAME', 'LOGIN'],
            'limit' => 1
        ]);
        if ($arUser = $resUser->fetch()) {
            return [
                'NAME' => '[' . $arUser['ID'] . '] ' . $arUser['LAST_NAME'] . ' ' . $arUser['NAME'],
                'LINK' => '<a href="/bitrix/admin/user_edit.php?lang=' . LANGUAGE_ID . '&ID=' .$arUser['ID'] .'">[' . $arUser['ID'] . '] ' . $arUser['LOGIN'] . '</a>',
            ];
        }
        return [];
    }

    public static function getMailTemplate($ventName)
    {
        $rsMess = \CEventMessage::GetList($by = 'id', $order = "desc", ['ACTIVE' => 'Y', 'EVENT_NAME' => $ventName]);
        if ($isFiltered = $rsMess->fetch()) {
            return "<a target=\"_blank\" href=\"/bitrix/admin/message_edit.php?lang=ru&ID=" . $isFiltered['ID'] . "\">" . $isFiltered['SUBJECT'] . "</a>";
        }
        return Loc::getMessage('REVIEWS_NO_MAIL_NOTICE');
    }

    public static function getOrderStatus()
    {
        $orderStatus = [];
        $dbStatusList = \CSaleStatus::GetList(["SORT" => "ASC"], ["LID" => LANGUAGE_ID], false, false, ["ID", "NAME", "SORT"]);

        while ($arStatusList = $dbStatusList->Fetch()) {
            $orderStatus[$arStatusList['SORT']] = '[' . $arStatusList['ID'] . '] ' . $arStatusList['NAME'];
        }

        return $orderStatus;
    }

    public static function getDiscount()
    {
        $dbProductDiscounts = \CSaleDiscount::GetList(["SORT" => "ASC"], ["ACTIVE" => "Y"], false, false, ['ID', 'NAME']);
        while ($arProductDiscounts = $dbProductDiscounts->Fetch()) {
            $arDisc[$arProductDiscounts['ID']] = '[' . $arProductDiscounts['ID'] . '] ' . $arProductDiscounts['NAME'];
        }
        return $arDisc;
    }

    public static function getCurrency()
    {
        $lcur = \CCurrency::GetList('', '', LANGUAGE_ID);
        while ($lcurRes = $lcur->Fetch()) {
            $arCurrencies[$lcurRes['CURRENCY']] = '[' . $lcurRes['CURRENCY'] . '] ' . $lcurRes['FULL_NAME'];
        }
        return $arCurrencies;
    }

    public static function getUserGroup()
    {
        $rsGroups = \CGroup::GetList($by = "c_sort", $order = "asc", ["ACTIVE" => 'Y']);
        while ($arGroup = $rsGroups->Fetch()) {
            $groups[$arGroup['ID']] = '[' . $arGroup['ID'] . '] ' . $arGroup['NAME'];
        }

        return $groups;
    }

    public static function getSelectElement()
    {
        return [
            'ID_ELEMENT' => Loc::getMessage('REVIEWS_ID_ELEMENT_ID'),
            'XML_ID_ELEMENT' => Loc::getMessage('REVIEWS_ID_ELEMENT_XML')
        ];
    }

    public static function getCoupon()
    {
        return [
            'TYPE' => [
                'Y' => Loc::getMessage('COUPON_Y'),
                'O' => Loc::getMessage('COUPON_O'),
                'N' => Loc::getMessage('COUPON_N')
            ],
            'NO_USED' => [
                'DELETE' => Loc::getMessage('COUPON_DELETE'),
                'DEACTION' => Loc::getMessage('COUPON_DEACTION')
            ]
        ];
    }

    public static function getVariantFile()
    {
        return [
            'NO' => Loc::getMessage('NO'),
            'IMAGE' => Loc::getMessage('IMAGE_VARIANT'),
            'IMAGE_VIDEO' => Loc::getMessage('IMAGE_VIDEO_VARIANT'),
        ];
    }

    public static function getNumberField($id, $label, $value, $default = null, $description = null, $disabled = null)
    {
        if ($id == 'REVIEWS_REPEAT' || $id == 'QUESTIONS_REPEAT'){
            $value = $value ?: '0';
            $default = ($value ?: '0');
        }

        $htmlDecs = '';
        if ($description) {
            $htmlDecs = '<span class="ui-hint" data-hint="' . $description . '" data-hint-html></span>';
        }

        $html =
            '<tr id="tr_' . $id . '">
                <td width="40%" class="adm-detail-content-cell-l">
                    ' . $label . '
                </td>
                <td class="adm-detail-content-cell-r">
                    <input name="' . $id . '" type="number" value="' . ( $value ?: $default) . '" ' . $disabled . '>
                    ' . $htmlDecs . '
                </td>
            </tr>';


        return $html;
    }

    public static function getFileField($code, $label, $value = '', $readOnly = null, $description = null,)
    {
        $fieldSize = 25;

        \CAdminFileDialog::ShowScript([
            'event' => 'BX_FD_'.$code,
            'arResultDest' => ['FUNCTION_NAME' => 'BX_FD_ONRESULT_'.$code],
            'arPath' => [],
            'select' => 'F',
            'operation' => 'O',
            'showUploadTab' => true,
            'showAddToMenuTab' => false,
            'fileFilter' => '',
            'allowAllFiles' => true,
            'SaveConfig' => true
        ]);

        $htmlDecs = '';
        if ($description) {
            $htmlDecs = '<span class="ui-hint" data-hint="' . $description . '" data-hint-html></span>';
        }

        $input = '<input id="__FD_PARAM_'.$code.'" name="'.$code.'" size="'.$fieldSize.'" value="'.htmlspecialchars( $value ).'" type="text" style="float: left;" '.($readOnly=='N' ? 'readonly' : '').' />
                                    <input value="..." type="button" onclick="window.BX_FD_'.$code.'();"  style="height: 26px;margin: 0px;" '.($readOnly=='N' ? 'disabled' : '').'/>
                                    <script>
                                        setTimeout(function(){
                                            if (BX("bx_fd_input_'.strtolower( $code ).'"))
                                                BX("bx_fd_input_'.strtolower( $code ).'").onclick = window.BX_FD_'.$code.';
                                        }, 200);
                                        window.BX_FD_ONRESULT_'.$code.' = function(filename, filepath)
                                        {
                                            var oInput = BX("__FD_PARAM_'.$code.'");
                                            if (typeof filename == "object")
                                                oInput.value = filename.src;
                                            else
                                                oInput.value = (filepath + "/" + filename).replace(/\/\//ig, \'/\');
                                        }
                                    </script>';

        $html =
            '<tr id="tr_' . $code . '">
                <td width="40%" class="adm-detail-content-cell-l">
                    ' . $label . '
                </td>
                <td class="adm-detail-content-cell-r">
                    ' . $input . '
                    ' . $htmlDecs . '
                </td>
            </tr>';

        return $html;
    }

    public static function getMultiSelectField($id, $label, $arValue, $arCurrentValue, $disabled = null, $description = null)
    {
        $html = '<tr id="tr_ ' . $id . '">
            <td width="50%">' . $label . '</td>
            <td width="50%">
                <select name="' . $id . '[]" multiple ' . $disabled . '>';

        foreach ($arValue as $key => $value) {
            $selected = '';

            if (is_array($arCurrentValue) && in_array($key, $arCurrentValue)) {
                $selected = 'selected';
            }
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
        }
        $descNode = '';
        if (!empty($description)) {
            $descNode = '<span class="ui-hint" data-hint="' . $description . '" data-hint-html></span>';
        }
        $html .= '</select>' . $descNode . '</td></tr>';

        return $html;
    }
}

?>
