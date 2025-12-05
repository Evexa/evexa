<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php
$this->setFrameMode(true);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Reviews\Helper\HelperComponent;

Loader::includeModule('sotbit.reviews');

CJSCore::Init(['sotbit.reviews.choices']);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
?>

    <form class="control-items" method="get">
        <span class="control-item control__title-sort">
            <p class="gray-text"><?= Loc::getMessage('SA_REVIEWS_TITLE_SORT') ?></p>
        </span>
        <div class="control-item control__item-sort d-flex-reviews btn-filter btn-filter--active"
             data-sort="DATE_CREATION" data-value="<?= $request['DATE_CREATION'] == 'asc' ?'asc': 'desc' ?>">
            <p class="r-m-1"><?= Loc::getMessage('SA_REVIEWS_SORT_DATE') ?></p>
            <input type="hidden" name="DATE_CREATION" value="<?= $request['DATE_CREATION'] == 'asc' ?'asc': 'desc' ?>">
            <span class="need-transfer <?= $request['DATE_CREATION'] == 'asc' ? 'transfer' : '' ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.6491 4.00685L11.7509 4C12.1306 4 12.4444 4.28215 12.494 4.64823L12.5009 4.75L12.5 17.446L15.7194 14.2248C15.9856 13.9585 16.4023 13.9341 16.696 14.1518L16.7801 14.2244C17.0465 14.4905 17.0709 14.9072 16.8531 15.2009L16.7806 15.2851L12.2843 19.7851C12.0182 20.0514 11.6015 20.0758 11.3078 19.8581L11.2237 19.7855L6.71989 15.2855C6.42688 14.9927 6.42668 14.5179 6.71945 14.2248C6.9856 13.9585 7.40226 13.9341 7.69596 14.1518L7.78011 14.2244L11 17.442L11.0009 4.75C11.0009 4.3703 11.283 4.05651 11.6491 4.00685L11.7509 4L11.6491 4.00685Z"/>
                </svg>
            </span>
        </div>
        <div class="control-item control__item-filter--select d-flex-reviews btn-filter select" data-filter="RATING"
             data-value="0">
            <p class="r-m-1"><?= Loc::getMessage('SA_REVIEWS_FILTER_RATING') ?></p>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M4.21967 8.46967C4.51256 8.17678 4.98744 8.17678 5.28033 8.46967L12 15.1893L18.7197 8.46967C19.0126 8.17678 19.4874 8.17678 19.7803 8.46967C20.0732 8.76256 20.0732 9.23744 19.7803 9.53033L12.5303 16.7803C12.2374 17.0732 11.7626 17.0732 11.4697 16.7803L4.21967 9.53033C3.92678 9.23744 3.92678 8.76256 4.21967 8.46967Z"/>
            </svg>
            <select class="select" name="RATING" id="star">

                <option value=""
                <?=($isAllStar = (isset($component->requestValue['RATING']) && empty($component->requestValue['RATING']))) ?'selected': ''?>
                ><?= Loc::getMessage('SA_REVIEWS_FILTER_RATING_VALUE_ALL') ?></option>
                <?
                if (!empty($component->requestValue['RATING'])) {
                    $currentRating = $component->requestValue['RATING'];
                }

                foreach ($arResult['RATING'] as $value) {
                    ?>
                    <option <?= $currentRating > 0 ? 'selected' : '' ?> value="<?= $value ?>">
                        <?= HelperComponent::declOfNum(
                            $value,
                            [
                                Loc::getMessage('SA_REVIEWS_FILTER_RATING_VALUE_WORD_1'),
                                Loc::getMessage('SA_REVIEWS_FILTER_RATING_VALUE_WORD_2'),
                                Loc::getMessage('SA_REVIEWS_FILTER_RATING_VALUE_WORD_3'),

                            ]
                        ); ?>
                    </option>
                <? } ?>

                <option <?= ($currentRating > 0 && !$isAllStar)? '' : 'selected' ?> value=""></option>
            </select>
        </div>
        <? if ($arResult['COUNT_ITEM_FILE'] > 0) { ?>
            <div class="control-item control__item-filter--checkbox btn-filter" data-filter="FILES"
                 data-value="<?= $component->requestValue['FILES'] == 'on' ? 'true' : 'false' ?>">
                <input type="checkbox" name="FILES" class="input-checkbox-reviews"
                       value="on" <?= $component->requestValue['FILES'] == 'on' ? 'checked' : '' ?>>
                <p class="title-checkbox">
                    <span><?= Loc::getMessage('SA_REVIEWS_FILTER_FILES') ?></span>
                    <span class="gray-text not-hover"> <?= $arResult['COUNT_ITEM_FILE'] ?></span>
                </p>
            </div>
        <? } ?>
    </form>

<script>
    SA_ReviewsFilter.init({});
</script>
