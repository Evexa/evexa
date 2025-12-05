<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php

$this->setFrameMode(true);

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Sotbit\Reviews\Helper\HelperComponent;

CJSCore::Init(['sotbit.reviews.swiper', 'sotbit.reviews.sweetalert2']);

Loader::includeModule('sotbit.reviews');
?>
<div class="wrapper__statistic" id="reviews-statistics">
    <div class="wrapper__statistic__info r-p-4" <?= $arResult['REVIEWS_SCHEMA_ORG'] ? 'itemscope itemtype="https://schema.org/AggregateRating"' : '' ?>>
        <div class="statistic__info-estimation">
            <div class="statistic__info-estimation__title-wrap">
                <span class="statistic__info-estimation__title" <?= $arResult['REVIEWS_SCHEMA_ORG'] ? ' itemprop="ratingValue"' : '' ?> ><?= $arResult['AVERAGE_RATING'] ?></span>
            </div>
            <div>
                <span  <?= $arResult['REVIEWS_SCHEMA_ORG'] ? 'itemprop="ratingCount"' : '' ?> style="display:none"><?=$arResult['COUNT']?></span>
                <span class="gray-text">
                    <?= HelperComponent::declOfNum(
                        $arResult['COUNT'],
                        [
                            Loc::getMessage('SA_REVIEW_TITLE_COUNT_REVIEW_1'),
                            Loc::getMessage('SA_REVIEW_TITLE_COUNT_REVIEW_2'),
                            Loc::getMessage('SA_REVIEW_TITLE_COUNT_REVIEW_3'),

                        ]
                    ); ?>
                </span>
            </div>
        </div>
        <div class="statistic__info-stars r-m-3">

            <? for ($i = $arParams["MAX_RATING"]; $i >= 1; --$i) { ?>
                <div class="statistic__info-stars--item">
                    <? for ($j = $i; $j >= 1; --$j) { ?>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.28307 2.31924C7.57652 1.72464 8.4244 1.72464 8.71785 2.31924L10.2622 5.44844L13.7155 5.95023C14.3717 6.04557 14.6337 6.85195 14.1588 7.31478L11.66 9.75052L12.2499 13.1898C12.362 13.8434 11.6761 14.3417 11.0892 14.0332L8.00046 12.4094L4.91176 14.0332C4.32485 14.3417 3.63891 13.8434 3.751 13.1898L4.34089 9.75052L1.84207 7.31478C1.36726 6.85195 1.62927 6.04557 2.28545 5.95023L5.73872 5.44844L7.28307 2.31924Z"
                                  fill="#FF9935"/>
                        </svg>
                    <? } ?>
                </div>
            <? } ?>
        </div>

        <div class="statistic__info-progress  r-m-3">
            <? for ($i = $arParams["MAX_RATING"]; $i >= 1; --$i) { ?>
                <div class="statistic__info-progress-block">
                    <div class="progress">
                        <progress max="100" value="<?= $arResult['AVERAGE_RATING_ITEM'][$i] ?: 0 ?>"></progress>
                        <div class="progress-value"
                             style="width: <?= $arResult['AVERAGE_RATING_ITEM'][$i] ?: 0 ?>%;"></div>
                        <div class="progress-bg">
                            <div class="progress-bar"></div>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
        <div class="statistic__info-count-reviews">
            <? for ($i = $arParams["MAX_RATING"]; $i >= 1; --$i) { ?>
                <div class="count-reviews-item" <?= $arResult['REVIEWS_SCHEMA_ORG'] ? 'itemprop="bestRating"' : '' ?>><?= $arResult['RATING'][$i] ?: 0 ?></div>
            <? } ?>
        </div>
    </div>
    <? if (!empty($arResult['FILES'])) { ?>
        <div class="swiper swiper-statistic media-reviews r-p-4">
            <div class="swiper-wrapper">
                <? foreach ($arResult['FILES'] as $file) {
                    if (\CFile::IsImage($file['FILE_NAME'])) {
                        ?>
                        <div class="swiper-slide">
                            <a data-fslightbox="galleryAll" href="<?= $file['SRC'] ?>" title="<?=$file['FILE_NAME']?>">
                                <picture>
                                    <source media="(min-width:650px)" srcset="<?= $file['SRC'] ?>">
                                    <source media="(min-width:465px)" srcset="<?= $file['SRC'] ?>">
                                    <img src="<?= $file['SRC'] ?>" alt="Flowers" style="width:auto;">
                                </picture>
                            </a>
                        </div>
                        <?
                    } else {
                        ?>
                        <div class="swiper-slide video">
                            <a data-fslightbox="galleryAll" href="<?= $file['SRC'] ?>" title="<?=$file['FILE_NAME']?>">
                                <video>
                                    <source src="<?= $file['SRC'] ?>"
                                            type="<?= $file['CONTENT_TYPE'] ?>"/>
                                </video>
                            </a>
                        </div>
                        <?
                    }
                }
                ?>
            </div>
            <div class="swiper-button-prev">
                <div class="swiper-wrap-pagination">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.79642 12L16.2875 3.27302C16.5764 2.97614 16.5699 2.50131 16.273 2.21246C15.9761 1.9236 15.5013 1.93011 15.2125 2.22698L6.21246 11.477C5.92918 11.7681 5.92918 12.2319 6.21246 12.523L15.2125 21.773C15.5013 22.0699 15.9761 22.0764 16.273 21.7875C16.5699 21.4987 16.5764 21.0239 16.2875 20.727L7.79642 12Z"
                              fill="#242424"/>
                    </svg>
                </div>
            </div>
            <div class="swiper-button-next">
                <div class="swiper-wrap-pagination">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M16.2036 12L7.71246 3.27302C7.4236 2.97614 7.43011 2.50131 7.72698 2.21246C8.02386 1.9236 8.49869 1.93011 8.78754 2.22698L17.7875 11.477C18.0708 11.7681 18.0708 12.2319 17.7875 12.523L8.78754 21.773C8.49869 22.0699 8.02386 22.0764 7.72698 21.7875C7.43011 21.4987 7.4236 21.0239 7.71246 20.727L16.2036 12Z"
                              fill="#242424"/>
                    </svg>
                </div>
            </div>
        </div>
    <? }

    ?>

<script>
    BX.ready(
        function () {
            swiperReviews = new Swiper('.swiper-statistic', {
                slidesPerView: 'auto',
                pagination: {
                    el: '.swiper-pagination',
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                }
            });
        }
    );
</script>
<?php
$APPLICATION->IncludeComponent(
    'bitrix:main.userconsent.view',
    'reviews',
    array(
        'ID' => $arParams['USER_AGREEMENT_ID'],
        'SECURITY_CODE' => $arResult['USER_AGREEMENT_SECURITY_CODE'],
        'REPLACE' => array(
            'button_caption' => '',
            'fields' => []
        ),
        'LABEL' => Loc::getMessage('SA_REVIEW_USER_CONSENT_VIEW_LABEL_TEXT')
    ),
    $component->getParent()
);
?>
</div>
