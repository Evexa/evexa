<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php

$this->setFrameMode(true);

use Bitrix\Main\IO\Path;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

CJSCore::Init(['sotbit.reviews.swiper']);

global $APPLICATION;

?>
<div class="reviews__content_wrapper" id="sotbit-rvw-reviews">
    <?php
    if (!empty($arResult['REVIEWS'])) {
        ?>

        <?php foreach ($arResult['REVIEWS'] as $review) { ?>
        <div class="reviews__content d-flex-reviews r-p-4"
             href="#<?= $review['ID'] ?>"
            <?= $arResult['REVIEWS_SCHEMA_ORG'] == 'Y' ? 'itemscope itemtype="https://schema.org/Review"' : '' ?>
        >
          <div class="content__user r-m-4">
            <div class="user__info">
                <?php if (empty($review['PERSONAL_PHOTO_ID'] && $review['ID_USER']) || $review['ANONYMITY'] == 'Y') { ?>
                  <img class="circle" src="<?= $arResult['REVIEWS_NO_USER_IMAGE'] ?>" alt="User Without Photo">
                <?php } else { ?>
                  <img class="circle" src="<?= $review['PERSONAL_PHOTO_SRC'] ?>" alt="User Photo">
                <?php } ?>
                <?php if ($arResult['REVIEWS_COMPLAINT'] == 'Y' && ($review['ID_USER'] != $arResult["USER_ID"]) && !empty($arResult["USER_ID"])) { ?>
                  <div class="user_complaint circle" title="<?= Loc::getMessage('TITLE_BTN_COMPLAINT') ?>"
                       data-type="complaints" data-id="<?= $review['ID'] ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#FFF"
                         xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M12 6.5C12.4142 6.5 12.75 6.83579 12.75 7.25V13.5C12.75 13.9142 12.4142 14.25 12 14.25C11.5858 14.25 11.25 13.9142 11.25 13.5V7.25C11.25 6.83579 11.5858 6.5 12 6.5ZM12 17.4978C12.5523 17.4978 13 17.0501 13 16.4978C13 15.9455 12.5523 15.4978 12 15.4978C11.4477 15.4978 11 15.9455 11 16.4978C11 17.0501 11.4477 17.4978 12 17.4978ZM12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C10.3817 22 8.81782 21.6146 7.41286 20.888L3.58704 21.9553C2.92212 22.141 2.23258 21.7525 2.04691 21.0876C1.98546 20.8676 1.98549 20.6349 2.04695 20.4151L3.11461 16.5922C2.38637 15.186 2 13.6203 2 12C2 6.47715 6.47715 2 12 2ZM12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 13.4696 3.87277 14.8834 4.57303 16.1375L4.72368 16.4072L3.61096 20.3914L7.59755 19.2792L7.86709 19.4295C9.12006 20.1281 10.5322 20.5 12 20.5C16.6944 20.5 20.5 16.6944 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5Z"
                        fill="#FFF" />
                    </svg>
                  </div>
                <?php } ?>
            </div>
            <div class="user__info--counter top-m-2">
                <?php if ($arResult['USER_REVIEWS_CNT'][$review['ID_USER']] > 0 && $review['ANONYMITY'] != 'Y') { ?>
                  <span class="gray-text">
                            <?= Loc::getMessage('TITLE_COUNT_REVIEWS') ?>
                            <span class="primary-text">
                                <?= $arResult['USER_REVIEWS_CNT'][$review['ID_USER']] ?>
                            </span>
                        </span>
                <?php } ?>
            </div>
          </div>
          <div class="content__reviews__item__info">
            <div class="content__reviews__item__info--wrapper">
              <div
                class="item__info___name-user" <?= ($arResult['REVIEWS_SCHEMA_ORG'] == 'Y') && ($review['ID_USER'] > 0) ? 'itemprop="author" itemscope itemtype="https://schema.org/Person"' : '' ?>>
                  <?php if ($arResult['REVIEWS_SCHEMA_ORG'] == 'Y' && $review['ID_USER'] > 0) { ?>
                    <meta itemprop="name" content="<?= trim($review['FULL_NAME']) ?>">
                    <link itemprop="image" href="<?= $review['PERSONAL_PHOTO_SRC'] ?>">
                  <?php } ?>
                  <?

                  if ($review['ANONYMITY'] == 'Y') {
                      $name = Loc::getMessage('TITLE_USER_NO');
                  } else {
                      $name = $review['ID_USER'] > 0 ? (trim($review['FULL_NAME']) ?: Loc::getMessage('TITLE_USER_NUMBER', ['#NUMBER#' => $review['ID_USER']])) : Loc::getMessage('TITLE_USER_NO');
                  }
                  ?>

                  <?= $name ?>
              </div>
              <div class="d-flex-reviews d-flex-reviews-center">
                <div
                  class="d-flex-reviews item__info__stars r-m-1" <?= $arResult['REVIEWS_SCHEMA_ORG'] == 'Y' ? 'itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"' : '' ?>>
                    <?
                    if ($arResult['REVIEWS_SCHEMA_ORG'] == 'Y') {
                        ?>
                      <meta itemprop="worstRating" content="1">
                      <meta itemprop="bestRating" content="<?= $arParams["MAX_RATING"] ?>" />
                      <meta itemprop="ratingValue" content="<?= $review['RATING'] ?>">
                        <?
                    }


                    for ($i = 1; $i <= $arParams["MAX_RATING"]; ++$i) {
                        if ($i <= $review['RATING']) {
                            ?>
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                               xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M10.7878 3.10215C11.283 2.09877 12.7138 2.09876 13.209 3.10215L15.567 7.87987L20.8395 8.64601C21.9468 8.80691 22.3889 10.1677 21.5877 10.9487L17.7724 14.6676L18.6731 19.9189C18.8622 21.0217 17.7047 21.8627 16.7143 21.342L11.9984 18.8627L7.28252 21.342C6.29213 21.8627 5.13459 21.0217 5.32374 19.9189L6.2244 14.6676L2.40916 10.9487C1.60791 10.1677 2.05005 8.80691 3.15735 8.64601L8.42988 7.87987L10.7878 3.10215Z"
                              fill="#FF9935" />
                          </svg>
                        <?php } else { ?>
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                               xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M10.7878 3.10215C11.283 2.09877 12.7138 2.09876 13.209 3.10215L15.567 7.87987L20.8395 8.64601C21.9468 8.80691 22.3889 10.1677 21.5877 10.9487L17.7724 14.6676L18.6731 19.9189C18.8622 21.0217 17.7047 21.8627 16.7143 21.342L11.9984 18.8627L7.28252 21.342C6.29213 21.8627 5.13459 21.0217 5.32374 19.9189L6.2244 14.6676L2.40916 10.9487C1.60791 10.1677 2.05005 8.80691 3.15735 8.64601L8.42988 7.87987L10.7878 3.10215ZM11.9984 4.03854L9.74008 8.61443C9.54344 9.01288 9.16332 9.28904 8.72361 9.35294L3.67382 10.0867L7.32788 13.6486C7.64606 13.9587 7.79125 14.4055 7.71614 14.8435L6.85353 19.8729L11.3702 17.4983C11.7635 17.2915 12.2333 17.2915 12.6266 17.4983L17.1433 19.8729L16.2807 14.8435C16.2056 14.4055 16.3508 13.9587 16.6689 13.6486L20.323 10.0867L15.2732 9.35294C14.8335 9.28904 14.4534 9.01288 14.2568 8.61443L11.9984 4.03854Z"
                              fill="#CED4DA" />
                          </svg>

                            <?
                        }
                    }
                    ?>
                </div>
                <div class="item__info__date l-m-1">
                    <?php if ($arResult['REVIEWS_SCHEMA_ORG'] == 'Y') { ?>
                      <meta itemprop="datePublished" content="<?= $review['DATE_CREATION'] ?>">
                    <?php } ?>
                  <span class=" gray-text"><?= $review['DATE_CREATION'] ?></span>
                </div>
              </div>
            </div>

              <?php if ($arResult['REVIEWS_EDIT'] == 'Y' && $arResult["USER_ID"] == $review['ID_USER'] && $arResult["IS_AUTHORIZE_USER"]) { ?>
                <div class="content__reviews__item__info--edit" data-type="edit"
                     data-id="<?= $review['ID'] ?>" data-rating="<?= $review['RATING'] ?>">
                  <button class="btn-reviews-small not-p">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                      <g filter="url(#filter0_b_47_2361)">
                        <path
                          d="M28.9519 11.0481C27.5543 9.65058 25.2885 9.65064 23.8911 11.0482L11.941 22.9997C11.5347 23.4061 11.2491 23.9172 11.116 24.4762L10.0204 29.0777C9.96009 29.3311 10.0355 29.5976 10.2197 29.7817C10.4038 29.9659 10.6704 30.0413 10.9237 29.981L15.525 28.8855C16.0842 28.7523 16.5955 28.4666 17.0019 28.0601L28.952 16.1086C30.3493 14.7111 30.3493 12.4455 28.9519 11.0481ZM24.9518 12.1088C25.7634 11.2971 27.0795 11.297 27.8912 12.1088C28.7028 12.9204 28.7029 14.2363 27.8913 15.048L27 15.9395L24.0606 13.0001L24.9518 12.1088ZM23 14.0608L25.9394 17.0002L15.9412 26.9995C15.731 27.2097 15.4667 27.3574 15.1775 27.4263L11.7619 28.2395L12.5752 24.8237C12.644 24.5346 12.7917 24.2704 13.0018 24.0603L23 14.0608Z"
                          fill="#495057" />
                      </g>
                      <defs>
                        <filter id="filter0_b_47_2361" x="-40" y="-40" width="120" height="120"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                          <feFlood flood-opacity="0" result="BackgroundImageFix" />
                          <feGaussianBlur in="BackgroundImageFix" stdDeviation="20" />
                          <feComposite in2="SourceAlpha" operator="in"
                                       result="effect1_backgroundBlur_47_2361" />
                          <feBlend mode="normal" in="SourceGraphic"
                                   in2="effect1_backgroundBlur_47_2361" result="shape" />
                        </filter>
                      </defs>
                    </svg>
                  </button>
                </div>
              <?php } ?>
          </div>

            <?php if (!empty($review['QUOTE'])) { ?>
              <div class="content__reviews__item__body--quote r-p-1">
                <div class="reviews--quote d-flex-reviews l-m-6 ">
                  <p>
                      <?= $review['QUOTE'] ?>
                  </p>
                </div>
              </div>
            <?php } ?>

          <div class="content__reviews__item__body r-p-1">
            <p <?= $arResult['REVIEWS_SCHEMA_ORG'] == 'Y' ? 'itemprop="reviewBody"' : '' ?> ><?= $review['TEXT'] ?></p>
          </div>

            <?php $arFilePath = [];

            foreach ($review['FILES'] as $file) {
                if (!is_array($file)) {
                    continue;
                }
                $arFilePath[$file['ID']] = htmlspecialchars('/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'], ENT_QUOTES);
                $arFileJs[$review['ID']][$file['ID']]['src'] = $arFilePath[$file['ID']];
                $arFileJs[$review['ID']][$file['ID']]['type'] = $file['CONTENT_TYPE'];
                $arFileJs[$review['ID']][$file['ID']]['edit'] = true;
            } ?>

            <?php if ($review['FILES']) { ?>
              <div class="swiper swiper-reviews r-p-1">
                <div class="swiper-wrapper">
                  <!-- Slides -->
                    <?
                    foreach ($review['FILES'] as $file) {
                        if (!is_array($file)) {
                            continue;
                        }
                        if (CFile::IsImage($file['FILE_NAME'])) {
                            ?>
                          <div class="swiper-slide">
                            <a data-fslightbox="gallery<?= $review['ID'] ?>"
                               href="<?= $arFilePath[$file['ID']] ?>" onclick="lightbox.open()"
                               title="<?= $file['FILE_NAME'] ?>"
                            >
                              <picture>
                                <source media="(min-width:650px)"
                                        srcset="<?= $arFilePath[$file['ID']] ?>">
                                <source media="(min-width:465px)"
                                        srcset="<?= $arFilePath[$file['ID']] ?>">
                                <img src="<?= $arFilePath[$file['ID']] ?>"
                                     alt="Flowers"
                                     style="width:auto;">
                              </picture>
                            </a>
                          </div>
                        <?php } else { ?>
                          <div class="swiper-slide video">
                            <a data-fslightbox="gallery<?= $review['ID'] ?>"
                               href="<?= $arFilePath[$file['ID']] ?>"
                               title="<?= $file['FILE_NAME'] ?>"
                            >
                              <video>
                                <source src="<?= $arFilePath[$file['ID']] ?>"
                                        type="<?= $file['CONTENT_TYPE'] ?>" />
                              </video>
                            </a>
                          </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="swiper-button-prev swiper-button-lock">
                  <div class="swiper-wrap-pagination">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M7.79642 12L16.2875 3.27302C16.5764 2.97614 16.5699 2.50131 16.273 2.21246C15.9761 1.9236 15.5013 1.93011 15.2125 2.22698L6.21246 11.477C5.92918 11.7681 5.92918 12.2319 6.21246 12.523L15.2125 21.773C15.5013 22.0699 15.9761 22.0764 16.273 21.7875C16.5699 21.4987 16.5764 21.0239 16.2875 20.727L7.79642 12Z"
                        fill="#242424" />
                    </svg>
                  </div>
                </div>
                <div class="swiper-button-next swiper-button-lock">
                  <div class="swiper-wrap-pagination">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M16.2036 12L7.71246 3.27302C7.4236 2.97614 7.43011 2.50131 7.72698 2.21246C8.02386 1.9236 8.49869 1.93011 8.78754 2.22698L17.7875 11.477C18.0708 11.7681 18.0708 12.2319 17.7875 12.523L8.78754 21.773C8.49869 22.0699 8.02386 22.0764 7.72698 21.7875C7.43011 21.4987 7.4236 21.0239 7.71246 20.727L16.2036 12Z"
                        fill="#242424" />
                    </svg>
                  </div>
                </div>
              </div>
            <?php } ?>
          <div class="content__reviews__item__control d-flex-reviews">
            <div
              class="content__reviews__item__control__item btn-reviews-small <?= $arResult['LIKES'][$review['ID']] && ($review['LIKES'] > 0) ? 'btn-reviews-small--active' : '' ?>"
                <?= $review['ID_USER'] != $arResult["USER_ID"] ? 'data-type="like"' : '' ?> data-value="like"
              data-id="<?= $review['ID'] ?>">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                   xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M16.4996 5.2021C16.4996 2.76017 15.3595 1.00342 13.4932 1.00342C12.467 1.00342 12.1149 1.60478 11.747 3.00299C11.6719 3.29184 11.635 3.43248 11.596 3.57109C11.495 3.92982 11.3192 4.54058 11.069 5.4021C11.0623 5.42518 11.0524 5.44692 11.0396 5.467L8.17281 9.95266C7.49476 11.0136 6.49429 11.8291 5.31841 12.2793L4.84513 12.4605C3.5984 12.9379 2.87457 14.2416 3.1287 15.5522L3.53319 17.6383C3.77462 18.8834 4.71828 19.8743 5.9501 20.1762L13.5778 22.0457C16.109 22.6661 18.6674 21.1312 19.3113 18.6059L20.7262 13.0567C21.1697 11.3174 20.1192 9.54796 18.3799 9.10449C18.1175 9.03758 17.8478 9.00373 17.5769 9.00373H15.7536C16.2497 7.37084 16.4996 6.11106 16.4996 5.2021ZM4.60127 15.2667C4.48576 14.671 4.81477 14.0783 5.38147 13.8614L5.85475 13.6802C7.33036 13.1152 8.58585 12.0918 9.43674 10.7604L12.3035 6.27477C12.3935 6.13388 12.4629 5.98082 12.5095 5.82026C12.7608 4.95525 12.9375 4.34126 13.0399 3.97737C13.083 3.82412 13.1239 3.66867 13.1976 3.3847C13.3875 2.663 13.4809 2.50342 13.4932 2.50342C14.3609 2.50342 14.9996 3.48749 14.9996 5.2021C14.9996 6.08659 14.6738 7.53754 14.0158 9.51717C13.8544 10.0027 14.2158 10.5037 14.7275 10.5037H17.5769C17.7228 10.5037 17.868 10.522 18.0093 10.558C18.9459 10.7968 19.5115 11.7496 19.2727 12.6861L17.8578 18.2353C17.4172 19.9631 15.6668 21.0133 13.9349 20.5889L6.30718 18.7193C5.64389 18.5568 5.13577 18.0232 5.00577 17.3528L4.60127 15.2667Z"
                  fill="#495057" />
              </svg>
              <p><?= $review['LIKES'] ?: '0' ?></p>
            </div>
            <div
              class="content__reviews__item__control__item btn-reviews-small <?= $arResult['DISLIKES'][$review['ID']] && ($review['DISLIKES'] > 0) ? 'btn-reviews-small--active' : '' ?>"
                <?= $review['ID_USER'] != $arResult["USER_ID"] ? 'data-type="like"' : '' ?> data-value="dislike"
              data-id="<?= $review['ID'] ?>">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                   xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M16.4996 17.9847C16.4996 20.4266 15.3595 22.1834 13.4932 22.1834C12.5183 22.1834 12.1518 21.6406 11.8021 20.3876L11.596 19.6157C11.495 19.257 11.3192 18.6462 11.069 17.7847C11.0623 17.7616 11.0524 17.7398 11.0396 17.7198L8.17281 13.2341C7.49476 12.1732 6.49429 11.3577 5.31841 10.9074L4.84513 10.7262C3.5984 10.2489 2.87457 8.94513 3.1287 7.63456L3.53319 5.54848C3.77462 4.30339 4.71828 3.31249 5.9501 3.01057L13.5778 1.14104C16.109 0.52065 18.6674 2.05558 19.3113 4.58091L20.7262 10.1301C21.1697 11.8693 20.1192 13.6388 18.3799 14.0823C18.1175 14.1492 17.8478 14.183 17.5769 14.183H15.7536C16.2497 15.8159 16.4996 17.0757 16.4996 17.9847ZM4.60127 7.9201C4.48576 8.51581 4.81477 9.10844 5.38147 9.32541L5.85475 9.50661C7.33036 10.0716 8.58585 11.095 9.43674 12.4263L12.3035 16.912C12.3935 17.0529 12.4629 17.206 12.5095 17.3665L13.0614 19.2868L13.2731 20.0781C13.4125 20.5661 13.4827 20.6834 13.4932 20.6834C14.3609 20.6834 14.9996 19.6993 14.9996 17.9847C14.9996 17.1002 14.6738 15.6492 14.0158 13.6696C13.8544 13.1841 14.2158 12.683 14.7275 12.683H17.5769C17.7228 12.683 17.868 12.6648 18.0093 12.6288C18.9459 12.39 19.5115 11.4372 19.2727 10.5007L17.8578 4.95152C17.4172 3.22366 15.6668 2.17344 13.9349 2.59792L6.30718 4.46745C5.64389 4.63002 5.13577 5.16358 5.00577 5.83402L4.60127 7.9201Z"
                  fill="#495057" />
              </svg>
              <p><?= $review['DISLIKES'] ?: '0' ?></p>
            </div>
              <?php if ($arResult['REVIEWS_QUOTS'] == 'Y') { ?>
                <div class="content__reviews__item__control__item btn-reviews-small btn-sm-hide-text"
                     data-action="show-modal" data-target="review_add__modal"
                     data-id="<?= $review['ID'] ?>">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                       xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C10.3817 22 8.81782 21.6146 7.41286 20.888L3.58704 21.9553C2.92212 22.141 2.23258 21.7525 2.04691 21.0876C1.98546 20.8676 1.98549 20.6349 2.04695 20.4151L3.11461 16.5922C2.38637 15.186 2 13.6203 2 12C2 6.47715 6.47715 2 12 2ZM12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 13.4696 3.87277 14.8834 4.57303 16.1375L4.72368 16.4072L3.61096 20.3914L7.59755 19.2792L7.86709 19.4295C9.12006 20.1281 10.5322 20.5 12 20.5C16.6944 20.5 20.5 16.6944 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5Z"
                      fill="#495057" />
                  </svg>
                  <p><?= Loc::getMessage('TITLE_BTN_QUOTS') ?></p>
                </div>
              <?php } ?>
              <?php if ($arResult['REVIEWS_COPY'] == 'Y') { ?>
                <div class="content__reviews__item__control__item btn-reviews-small btn-sm-hide-text" data-type="copy"
                     data-value="<?= '#' . $review['ID'] ?>">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                       xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M6.74609 4.00003H10.2103C10.6245 4.00003 10.9603 4.33582 10.9603 4.75003C10.9603 5.12972 10.6781 5.44352 10.3121 5.49318L10.2103 5.50003H6.74609C5.55523 5.50003 4.58045 6.42519 4.50128 7.59598L4.49609 7.75003V17.25C4.49609 18.4409 5.42126 19.4157 6.59204 19.4948L6.74609 19.5H16.2468C17.4377 19.5 18.4125 18.5749 18.4916 17.4041L18.4968 17.25V16.7522C18.4968 16.338 18.8326 16.0022 19.2468 16.0022C19.6265 16.0022 19.9403 16.2844 19.99 16.6505L19.9968 16.7522V17.25C19.9968 19.2543 18.4245 20.8913 16.446 20.9948L16.2468 21H6.74609C4.74183 21 3.10482 19.4277 3.00129 17.4492L2.99609 17.25V7.75003C2.99609 5.74577 4.56845 4.10876 6.54694 4.00523L6.74609 4.00003H10.2103H6.74609ZM14.5001 6.51988V3.75003C14.5001 3.12606 15.2069 2.78998 15.6871 3.13983L15.7693 3.20877L21.7639 8.95877C22.0436 9.22712 22.0691 9.65814 21.8402 9.9561L21.7639 10.0412L15.7693 15.7931C15.3191 16.2251 14.5872 15.9477 14.5072 15.3589L14.5001 15.2519V12.5266L14.1566 12.5567C11.7569 12.807 9.45687 13.8879 7.24204 15.8174C6.72293 16.2696 5.9198 15.842 6.00518 15.1589C6.66997 9.83933 9.45184 6.90733 14.2007 6.53953L14.5001 6.51988V3.75003V6.51988ZM16.0001 5.50867V7.25003C16.0001 7.66424 15.6643 8.00003 15.2501 8.00003C11.3767 8.00003 8.97606 9.67616 7.93882 13.1572L7.85976 13.4358L8.21195 13.199C10.4484 11.7372 12.7978 11 15.2501 11C15.6298 11 15.9436 11.2822 15.9932 11.6483L16.0001 11.75V13.4928L20.1613 9.50012L16.0001 5.50867Z"
                      fill="#495057" />
                  </svg>
                  <p><?= Loc::getMessage('TITLE_BTN_COPY') ?></p>
                </div>
              <?php } ?>
          </div>
            <?php if (!empty($review['ANSWER'])) { ?>
              <div class="content__reviews__item__answer d-flex-reviews top-m-2">
                <div class="answer__wrapper">
                  <div class="answer__title">
                    <p><?= Loc::getMessage('TITLE_ANSWER') ?></p>
                  </div>
                  <div class="answer__body">
                    <p><?= $review['ANSWER'] ?></p>
                  </div>
                </div>
              </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?
        if ($component->nav->getPageCount() > 1) {
            $APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                "reviews",
                array(
                    "NAV_OBJECT" => $component->nav,
                    "BASE_LINK" => $APPLICATION->GetCurPage(),
                ),
                $component->getParent()
            );
        }

        ?>
    <?php } else { ?>
      <div class="content__reviews__item__answer d-flex-reviews r-p-4" style="text-align:center;">
        <div class="answer__wrapper not-itmes">
          <div class="answer__title">
            <p><?= Loc::getMessage('TITLE_COUNT_NULL') ?></p>
          </div>
          <div class="answer__body">
            <p>
                <?= Loc::getMessage('TITLE_COUNT_NULL_MESSAGE') ?>
            </p>
          </div>
        </div>
      </div>
    <?php } ?>
</div>
<?php
$messages = Loc::loadLanguageFile(__FILE__);
?>
<script>
  window.hashName = window.location.hash;

  if (window.hashName) {
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelector(`div[href="${window.hashName}"]`).scrollIntoView();
      document.querySelector(`div[href="${window.hashName}"]`).classList.add('select');
    });
  }

  BX.message(<?=CUtil::PhpToJSObject($messages)?>);

  SA_ReviewsList.init({
    signedParameters: '<?= $component->getParent()->getSignedParameters() ?>',
    arFile: '<?= json_encode($arFileJs) ?>',
  });
</script>
