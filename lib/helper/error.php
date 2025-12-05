<?

namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Engine\Response\AjaxJson,
    Bitrix\Main\Error as BitrixError,
    Bitrix\Main\ErrorCollection;

class Error
{
    public static function getAjaxError($error)
    {
        $arError = [];
        $errorCollection = new ErrorCollection();

        $error = is_array($error) ? $error : [$error];

        foreach ($error as $itemError) {
            $arError[] = ($itemError instanceof BitrixError)
                ? $itemError
                : new BitrixError($itemError);
        }

        $errorCollection->add($arError);

        return AjaxJson::createError($errorCollection);
    }
}
