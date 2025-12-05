<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

$this->setFrameMode(true);
$instanceId = 'sotbit-rvw-rating-' . $this->randString();
?>

<button id="<?= $instanceId ?>" class="sotbit-rvw-rating" type="button">
    <div class="sotbit-rvw-rating__item" title="<?= Loc::getMessage('SR_COMPONENT_TEMPLATE_RATING_RATING') ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10.7878 3.10215C11.283 2.09877 12.7138 2.09876 13.209 3.10215L15.567 7.87987L20.8395 8.64601C21.9468 8.80691 22.3889 10.1677 21.5877 10.9487L17.7724 14.6676L18.6731 19.9189C18.8622 21.0217 17.7047 21.8627 16.7143 21.342L11.9984 18.8627L7.28252 21.342C6.29213 21.8627 5.13459 21.0217 5.32374 19.9189L6.2244 14.6676L2.40916 10.9487C1.60791 10.1677 2.05005 8.80691 3.15735 8.64601L8.42988 7.87987L10.7878 3.10215Z"
                  fill="#FF9935">
        </svg>
        <span class="sotbit-rvw-rating__text"><?php
            $frame = $this->createFrame()->begin('0.0');
            echo $arResult['RATING'];
            $frame->end();
            unset($frame);
            ?></span>
    </div>

    <div class="sotbit-rvw-rating__item" title="<?= Loc::getMessage('SR_COMPONENT_TEMPLATE_RATING_REVIEWS_COUNT') ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C10.3817 22 8.81782 21.6146 7.41286 20.888L3.58704 21.9553C2.92212 22.141 2.23258 21.7525 2.04691 21.0876C1.98546 20.8676 1.98549 20.6349 2.04695 20.4151L3.11461 16.5922C2.38637 15.186 2 13.6203 2 12C2 6.47715 6.47715 2 12 2ZM12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 13.4696 3.87277 14.8834 4.57303 16.1375L4.72368 16.4072L3.61096 20.3914L7.59755 19.2792L7.86709 19.4295C9.12006 20.1281 10.5322 20.5 12 20.5C16.6944 20.5 20.5 16.6944 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5Z"
                  fill="#2C2C2C">
        </svg>
        <span class="sotbit-rvw-rating__text"><?php
            $frame = $this->createFrame()->begin('0');
            echo $arResult['REVIEWS_COUNT'];
            $frame->end();
            unset($frame);
            ?></span>
    </div>
</button>

<script>
  BX.ready(() => {
    sotbitReviewsInitializeRatingEvents('<?= $instanceId ?>');
  });
</script>
