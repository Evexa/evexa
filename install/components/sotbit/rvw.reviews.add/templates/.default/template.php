<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

CJSCore::Init([
    'sotbit.reviews.dropzone',
    'sotbit.reviews.common',
]);
?>
<?php
/*
 the button for adding a review entry. Opens a modal window. You can place it anywhere.

<div class="control-add">
    <input type="button" data-action="show-modal" data-target="review_add__modal"
        class="btn-reviews btn-reviews--main" value="<?= Loc::getMessage('SA_REVIEWS_BTN_ADD') ?>">
</div>
*/
?>
<div id="review_add__modal"
     class="reviews review_modal container d-none <?= $arResult['REVIEWS_UPLOAD_FILE'] != 'NO' ? '' : 'no-file' ?>"
     data-type="modal">
    <div class="review_add__modal_content">
        <div class="review_add__modal_header">
                <span class="review_add__title">
                    <?= Loc::getMessage('SA_REVIEW_ADD_FORM_TITLE'); ?>
                </span>
            <button class="review_add__close text-secondary-500" data-action="close-modal">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 1L8 8M15 15L8 8M8 8L15 1.00004M8 8L1 15" stroke="#BDBDBD" stroke-width="1.5"
                          stroke-linecap="round"/>
                </svg>
            </button>
        </div>
        <div class="review_add__modal_body" data-simplebar>
            <form action="" data-active="add">
                <div>
                    <input type="hidden" name="ID_ELEMENT" value="<?= $arParams['ID_ELEMENT'] ?>">
                    <div class="review_add__product">
                        <div class="product__img">
                            <?php if ($arResult['ELEMENT']['PREVIEW_PICTURE']['SRC'] ?: $arResult['ELEMENT']['DETAIL_PICTURE']['SRC']) { ?>
                                <img src="<?= $arResult['ELEMENT']['PREVIEW_PICTURE']['SRC'] ?: $arResult['ELEMENT']['DETAIL_PICTURE']['SRC'] ?>"
                                     alt="<?= $arResult['ELEMENT']['NAME'] ?>">
                            <?php } else { ?>
                                <img src="<?= $component->getTemplate()->GetFolder() ?>/images/no_photo.png"
                                     alt="<?= $arResult['ELEMENT']['NAME'] ?>">
                            <?php } ?>
                        </div>
                        <div class="product__name">
                            <p> <?= $arResult['ELEMENT']['NAME'] ?></p>
                            <div class="review_add__rating">
                                <div class="review_add__stars">
                                    <button class="review_add__star" type="button" data-rating="1">
                                        <span class="review_add__star-icon"></span>
                                        <span class="review_add__star-icon review_add__star-icon--filled"></span>
                                    </button>
                                    <button class="review_add__star" type="button" data-rating="2">
                                        <span class="review_add__star-icon"></span>
                                        <span class="review_add__star-icon review_add__star-icon--filled"></span>
                                    </button>
                                    <button class="review_add__star" type="button" data-rating="3">
                                        <span class="review_add__star-icon"></span>
                                        <span class="review_add__star-icon review_add__star-icon--filled"></span>
                                    </button>
                                    <button class="review_add__star" type="button" data-rating="4">
                                        <span class="review_add__star-icon"></span>
                                        <span class="review_add__star-icon review_add__star-icon--filled"></span>
                                    </button>
                                    <button class="review_add__star" type="button" data-rating="5">
                                        <span class="review_add__star-icon"></span>
                                        <span class="review_add__star-icon review_add__star-icon--filled"></span>
                                    </button>
                                </div>
                                <input type="hidden" name="RATING" value="0">
                                <div class="rating_description">
                                    <span data-rating="1"><?= Loc::getMessage('SA_REVIEW_ADD_RATING_1'); ?></span>
                                    <span data-rating="2"><?= Loc::getMessage('SA_REVIEW_ADD_RATING_2'); ?></span>
                                    <span data-rating="3"><?= Loc::getMessage('SA_REVIEW_ADD_RATING_3'); ?></span>
                                    <span data-rating="4"><?= Loc::getMessage('SA_REVIEW_ADD_RATING_4'); ?></span>
                                    <span data-rating="5"><?= Loc::getMessage('SA_REVIEW_ADD_RATING_5'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--                    <div class="form-group-reviews">-->
                    <!--                        <label for="review-dignity" class="fs-r-1 fw-medium r-p-05">-->
                    <!--                            --><?php //= Loc::getMessage('SA_REVIEW_ADD_DIGNITY'); ?>
                    <!--                        </label>-->
                    <!--                        <input id="review-dignity" class="input-text-reviews" type="text" name="DIGNITY"-->
                    <!--                               placeholder="-->
                    <?php //= Loc::getMessage('SA_REVIEW_ADD_PLACEHOLDER') ?><!--">-->
                    <!--                    </div>-->
                    <div class="form-group-reviews">
                        <div class="content__reviews__item__body--quote top-m-8 display-none-important">
                            <div class=" reviews--quote d-flex-reviews">
                                <p></p>
                                <input type="hidden" name="ID_QUOTE" value="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group-reviews">
                        <label for="review-comment" class="fs-r-1 fw-medium r-p-05">
                            <?= Loc::getMessage('SA_REVIEW_ADD_COMMENT'); ?>
                        </label>
                        <textarea data-simplebar id="review-comment" class="input-text-reviews" name="COMMENT"
                                  placeholder="<?= Loc::getMessage('SA_REVIEW_ADD_PLACEHOLDER') ?>"
                                  required maxlength="<?= $arParams['TEXTBOX_MAXLENGTH'] ?>"></textarea>
                        <p class="gray-text fs-r-1"><?=
                            Loc::getMessage('SA_REVIEW_TEXTAREA_DISCRIPTION', ['#SYMBOL_COUNT#' => $arParams['TEXTBOX_MAXLENGTH']])
                            ?>
                        </p>

                    </div>
                    <? if (!empty($arResult['REVIEWS_UPLOAD_FILE']) && $arResult['REVIEWS_UPLOAD_FILE'] !== 'NO'): ?>
                        <div class="form-group-reviews file">
                            <div class="form-group-reviews-wrapper">
                                <div class="r-p-1 file-wrap" data-simplebar>
                                    <div class="dropzone" id="dropzone_media"></div>
                                </div>
                            </div>
                            <p class="gray-text fs-r-1"><?=
                                Loc::getMessage('SA_REVIEW_DROPZONE_DISCRIPTION',
                                    [
                                        '#TYPE#' => Loc::getMessage('SA_REVIEW_DROPZONE_DISCRIPTION_TYPE_' . $arResult['REVIEWS_UPLOAD_FILE']),
                                        '#SIZE#' => Loc::getMessage('SA_REVIEW_DROPZONE_DISCRIPTION_SIZE_' . $arResult['REVIEWS_UPLOAD_FILE'], ['#SIZE_IMAGE#' => $arResult['REVIEWS_MAX_IMAGE_SIZE'], '#SIZE_VIDEO#' => $arResult['REVIEWS_MAX_VIDEO_SIZE']]),

                                    ])
                                ?>
                            </p>
                        </div>
                    <? endif; ?>

                </div>


                <input type="hidden" name="ANONYMOUS" value="N">
            </form>
        </div>
        <div class="footer_modal">
            <? if ($arResult['REVIEWS_ANONYMOUS_USER'] == 'Y' && $arResult['IS_AUTHORIZED_USER']) { ?>
                <div class="form-group-reviews">
                    <div class="control__item-filter--checkbox">
                        <input type="checkbox" class="input-checkbox-reviews" name="ANONYMOUS">
                        <p class="title-checkbox-modal"><?= Loc::getMessage('SA_REVIEW_ADD_ANONYMOUS') ?></p>
                    </div>
                </div>
            <? } ?>
            <? if ($arResult['AGREEMENT']): ?>
                <div class="form-group-reviews">
                    <div class="review_add__footer-agreement">
                        <?= Loc::getMessage('SA_REVIEW_AGREEMENT_1') ?>
                        <a href="javascript:void(0)"
                           class="text-primary"
                           data-action="show-modal"
                           data-target="policy_modal"
                        >
                            <?= Loc::getMessage('SA_REVIEW_AGREEMENT_2'); ?>
                        </a>
                    </div>
                </div>
            <? endif; ?>
            <div class="review_add__footer">
                <div class="review_add__footer-submit-wrapper">
                    <button type="submit" class="btn-reviews btn-reviews--main review_add__footer-submit">
                                    <span class="review_add__submit-text">
                                        <?= Loc::getMessage('SA_REVIEW_ADD_BUTTON_TITLE') ?>
                                    </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php
if (!$arResult['IS_AUTHORIZED_USER']) {
    $arResult['REVIEWS_ANONYMOUS'] == 'Y';
}
?>
<script>
    BX.message({
        'dropzone_message': '<?= Loc::getMessage('SA_REVIEW_DROPZONE_MESSAGE') ?>',
        'dropzone_delete': '<?= Loc::getMessage('SA_REVIEW_DROPZONE_DEL'); ?>',
        'dropzone_error_max_files': '<?= Loc::getMessage('SA_REVIEW_DROPZONE_MAX_FILES'); ?>',
        'dropzone_cancel': '<?= Loc::getMessage('SA_REVIEW_DROPZONE_CANCEL'); ?>',
        'dropzone_file_too_big': '<?= Loc::getMessage('SA_REVIEW_DROPZONE_FILE_TOO_BIG'); ?>',
        'success_title': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE'); ?>',
        'success_btn_mess': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_BTN'); ?>',
        'success_title_moderate': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_MODERATE'); ?>',
        'success_moderate_text': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_MODERATE_TEXT'); ?>',

        'success_title_quote': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_QUOTE'); ?>',
        'success_title_quote_moderate': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_QUOTE_MODERATE'); ?>',
        'success_quote_text': '<?= Loc::getMessage('SA_REVIEW_SUCCESS_TITLE_QUOTE_TEXT'); ?>',

        'auth_register': '<?= Loc::getMessage('AUTH_ERROR'); ?>',
        'title_quite': '<?= Loc::getMessage('TITLE_QUOTE'); ?>',
        'title_origin': '<?= Loc::getMessage('SA_REVIEW_ADD_FORM_TITLE'); ?>',
        'not_buy_message': '<?= Loc::getMessage('NOT_BUY_MESSAGE'); ?>',
        'universal_error': '<?= Loc::getMessage('UNIVERSAL_ERROR'); ?>',
        'repeat_message': '<?= Loc::getMessage('REPEAT_MESSAGE'); ?>',
        'error_image': '<?= Loc::getMessage('ERROR_IMAGE_COUNT'); ?>',
        'error_video': '<?= Loc::getMessage('ERROR_VIDEO_COUNT'); ?>',
        'error_image_size': '<?= Loc::getMessage('ERROR_IMAGE_SIZE'); ?>',
        'error_video_size': '<?= Loc::getMessage('ERROR_VIDEO_SIZE'); ?>',
        'error_file_type': '<?= Loc::getMessage('ERROR_FILE_TYPE'); ?>',

        'complains_yes': '<?= Loc::getMessage('TITLE_MODAL_COMPLAINTS_SELECTION_YES'); ?>',
        'complains_no': '<?= Loc::getMessage('TITLE_MODAL_COMPLAINTS_SELECTION_NO'); ?>',
    });

    document.addEventListener('DOMContentLoaded', () => {
        SA_ReviewsAdd.init({
            canAddReview: Boolean(<?=$arResult['CAN_ADD_REVIEW']?>),
            canAddReviewError: '<?=$arResult['CAN_ADD_REVIEW_ERROR']?>',
            ratingNumber: <?= $arParams['DEFAULT_RATING_ACTIVE'] ?>,
            imagesFolder: '<?= $this->__folder . '/images/' ?>',
            fileUploadUrl: '<?= $this->__folder . '/result_modifier.php' ?>',
            maxFiles: '<?= $arParams['MAX_FILES'] ?>',
            maxFileSize: '<?= $arParams['MAX_FILE_SIZE'] ?>',
            isAuthUser: '<?= $arResult['REVIEWS_ANONYMOUS'] == 'N' && !$arResult['IS_AUTHORIZED_USER'] ? 'N' : 'Y'  ?>',
            isBuyProduct: '<?= $arResult['REVIEWS_BUY'] ?>',
            isAnonymous: '<?= $arResult['REVIEWS_ANONYMOUS_USER'] ?>',
            isModerate: '<?= $arResult['REVIEWS_MODERATION'] ?>',
            canRepeat: '<?= $arResult['CAN_REPEAT'] ?? true ?>',
            modRepeat: '<?= $arResult['REVIEWS_REPEAT'] ?>',
            countRepeat: '<?= $arResult['COUNT_REPEAT'] ?>',
            configFile: '<?= json_encode($arResult['CONFIG_FILE'])?>',
            signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>',
        });
    });
</script>
