<?php

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
}

if (Loader::includeModule('sotbit.reviews')) {
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

    if ($request->isPost() && $request->get('action') === 'removedfile' && $request->get('fileId')) {
        \CFile::Delete($request->get('fileId'));
        return;
    }
    $arFiles = $request->getFileList()->getValues();

    foreach (['PREVIEW_PICTURE', 'DETAIL_PICTURE'] as $pictureFieldName) {
        if ($arResult['ELEMENT'][$pictureFieldName]) {
            $resizedPicture = CFile::ResizeImageGet(
                $arResult['ELEMENT'][$pictureFieldName]['ID'],
                [
                    'width' => 64,
                    'height' => 64
                ],
                false,
                [
                    [
                        "name" => "sharpen",
                        "precision" => 0
                    ]
                ]
            );

            if (is_array($resizedPicture)) {
                $arResult['ELEMENT'][$pictureFieldName]['SRC'] = $resizedPicture['src'];
                $arResult['ELEMENT'][$pictureFieldName]['HEIGHT'] = $resizedPicture['height'];
                $arResult['ELEMENT'][$pictureFieldName]['WIDTH'] = $resizedPicture['width'];
                $arResult['ELEMENT'][$pictureFieldName]['FILE_SIZE'] = $resizedPicture['size'];
            }
        }
    }

    if ($arFiles && $request->isPost()) {
        $fieldCode = array_key_first($arFiles);

        $fileID = CFile::SaveFile(array_merge($arFiles[$fieldCode], ['MODULE_ID' => 'sotbit.reviews']),
            "sotbit.reviews");

        echo \Bitrix\Main\Web\Json::encode(['file' => ['id' => $fileID]]);
        die();
    }

    $arResult['IS_AUTHORIZED_USER'] = \Bitrix\Main\Engine\CurrentUser::get()->getId() ? true : false;

    $config = OptionReviews::getConfigs(SITE_ID);
    $arResult['REVIEWS_UPLOAD_FILE'] = $config['REVIEWS_UPLOAD_FILE'];
    $arResult['REVIEWS_MAX_IMAGE_SIZE'] = $config['REVIEWS_MAX_IMAGE_SIZE'];
    $arResult['REVIEWS_MAX_VIDEO_SIZE'] = $config['REVIEWS_MAX_VIDEO_SIZE'];
    $arResult['REVIEWS_ANONYMOUS_USER'] = $config['REVIEWS_ANONYMOUS_USER'];
    $arResult['REVIEWS_ANONYMOUS'] = $config['REVIEWS_ANONYMOUS'];
    $arResult['REVIEWS_REPEAT'] = $config['REVIEWS_REPEAT'];
    $arResult['REVIEWS_MODERATION'] = $config['REVIEWS_MODERATION'];
    $arResult['CONFIG_FILE'] = OptionReviews::getConfigImageReview(SITE_ID);
}

