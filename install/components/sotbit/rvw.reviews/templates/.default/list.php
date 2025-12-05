<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

CJSCore::Init(['sotbit.smsauth.fslightbox']);
?>
<div class="reviews" id="reviews">
    <?php
    $APPLICATION->IncludeComponent(
        "sotbit:rvw.statistics",
        "",
        array(
            'MAX_RATING' => $arParams['MAX_RATING'],
            'ID_ELEMENT' => $arParams['ID_ELEMENT'],
            'USER_AGREEMENT_ID' => $arParams['USER_AGREEMENT_ID'],
        ),
        $component
    );
    ?>

    <div class="reviews__wrapper">
        <div class="reviews__control d-flex-reviews j-content-between-reviews r-p-4">
        <?php

       $res =  $APPLICATION->IncludeComponent(
            "sotbit:rvw.reviews.filter",
            "",
            array(
                'MAX_RATING' => $arParams['MAX_RATING'],
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
            ),
            $component
        );
        ?>
            <div class="control-add">
                <input type="button" data-action="show-modal" data-target="review_add__modal"
                       class="btn-reviews btn-reviews--main" value="<?= Loc::getMessage('SA_REVIEWS_BTN_ADD') ?>">
            </div>
        </div>
        <?php
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.reviews.list",
            "",
            array(
                'MAX_RATING' => $arParams['MAX_RATING'],
                'COUNT_PAGE' => $arParams['COUNT_PAGE'],
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                'DATE_FORMAT' => $arParams['DATE_FORMAT'],
                'AJAX_MODE' => 'Y',
            ),
            $component
        );
        ?>
    </div>
</div>
<script>
    SA_Reviews.init({
        signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>'
    });
</script>
