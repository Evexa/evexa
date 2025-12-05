<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;

?>
<div class="reviews__content_wrapper" id="sotbit-rvw-questions">
    <?php if (!empty($arResult['QUESTIONS'])) {?>
        <?php foreach ($arResult['QUESTIONS'] as $questions) { ?>
            <div class="reviews__content d-flex-reviews r-p-4"
                 href="#<?= $questions['ID'] ?>"
                <?= $arResult['QUESTIONS_SCHEMA_ORG'] == 'Y' ? ' itemscope itemprop="mainEntity" itemtype="https://schema.org/Question"' : '' ?>
            >
                <div class="content__user r-m-4">
                    <div class="user__info">
                        <?php if (empty($questions['PERSONAL_PHOTO_ID'] && $questions['ID_USER']) || $questions['ANONYMITY'] == 'Y') { ?>
                            <img class="circle" src="<?= $arResult['QUESTIONS_NO_USER_IMAGE'] ?>">
                        <?php } else { ?>
                            <img class="circle" src="<?= $questions['PERSONAL_PHOTO_SRC'] ?>">
                        <?php } ?>
                    </div>
                </div>
                <div class="content__reviews__item__info">
                    <div class="content__reviews__item__info--wrapper">
                        <div class="item__info___name-user" >
                            <?
                            if ($questions['ANONYMITY'] == 'Y') {
                                $name = Loc::getMessage('TITLE_USER_NO');
                            } elseif(!empty(trim($questions['FULL_NAME']))) {
                                $name = $questions['ID_USER'] > 0 ? trim($questions['FULL_NAME']) : Loc::getMessage('TITLE_USER_NO');
                            }elseif($questions['ID_USER'] > 0){
                                $name = Loc::getMessage('TITLE_USER_NUMBER', ['#NUMBER#' => $questions['ID_USER']]);
                            }else{
                                $name =  Loc::getMessage('TITLE_USER_NO');
                            }

                            echo $name;
                            ?>
                        </div>
                        <div class="d-flex-reviews">
                            <div class="item__info__date">
                                <span class=" gray-text"><?= $questions['DATE_CREATION'] ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($arResult['QUESTIONS_EDIT'] == 'Y' && \Bitrix\Main\Engine\CurrentUser::get()->getId() == $questions['ID_USER'] && $arResult['IS_AUTHORIZE_USER']) { ?>
                        <div class="content__reviews__item__info--edit" data-type="edit"
                             data-id="<?= $questions['ID'] ?>" data-rating="<?= $questions['RATING'] ?>">
                            <button class="btn-reviews-small not-p">
                                <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <g filter="url(#filter0_b_47_2361)">
                                        <path d="M28.9519 11.0481C27.5543 9.65058 25.2885 9.65064 23.8911 11.0482L11.941 22.9997C11.5347 23.4061 11.2491 23.9172 11.116 24.4762L10.0204 29.0777C9.96009 29.3311 10.0355 29.5976 10.2197 29.7817C10.4038 29.9659 10.6704 30.0413 10.9237 29.981L15.525 28.8855C16.0842 28.7523 16.5955 28.4666 17.0019 28.0601L28.952 16.1086C30.3493 14.7111 30.3493 12.4455 28.9519 11.0481ZM24.9518 12.1088C25.7634 11.2971 27.0795 11.297 27.8912 12.1088C28.7028 12.9204 28.7029 14.2363 27.8913 15.048L27 15.9395L24.0606 13.0001L24.9518 12.1088ZM23 14.0608L25.9394 17.0002L15.9412 26.9995C15.731 27.2097 15.4667 27.3574 15.1775 27.4263L11.7619 28.2395L12.5752 24.8237C12.644 24.5346 12.7917 24.2704 13.0018 24.0603L23 14.0608Z"
                                              fill="#495057"/>
                                    </g>
                                    <defs>
                                        <filter id="filter0_b_47_2361" x="-40" y="-40" width="120" height="120"
                                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                                            <feGaussianBlur in="BackgroundImageFix" stdDeviation="20"/>
                                            <feComposite in2="SourceAlpha" operator="in"
                                                         result="effect1_backgroundBlur_47_2361"/>
                                            <feBlend mode="normal" in="SourceGraphic"
                                                     in2="effect1_backgroundBlur_47_2361" result="shape"/>
                                        </filter>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                    <?php } ?>
                </div>

                <div class="content__reviews__item__body r-p-1">
                    <p <?= $arResult['QUESTIONS_SCHEMA_ORG'] == 'Y' ? 'itemprop="name"' : '' ?> ><?= $questions['QUESTION'] ?></p>
                </div>
                <div class="content__reviews__item__control d-flex-reviews">
                    <div class="content__reviews__item__control__item btn-reviews-small <?= $arResult['LIKES'][$questions['ID']] && ($questions['LIKES'] > 0) ? 'btn-reviews-small--active' : '' ?>"
                        <?= $questions['ID_USER'] != $arResult['USER_ID'] ? 'data-type="like"' : '' ?> data-value="like"
                         data-id="<?= $questions['ID'] ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.4996 5.2021C16.4996 2.76017 15.3595 1.00342 13.4932 1.00342C12.467 1.00342 12.1149 1.60478 11.747 3.00299C11.6719 3.29184 11.635 3.43248 11.596 3.57109C11.495 3.92982 11.3192 4.54058 11.069 5.4021C11.0623 5.42518 11.0524 5.44692 11.0396 5.467L8.17281 9.95266C7.49476 11.0136 6.49429 11.8291 5.31841 12.2793L4.84513 12.4605C3.5984 12.9379 2.87457 14.2416 3.1287 15.5522L3.53319 17.6383C3.77462 18.8834 4.71828 19.8743 5.9501 20.1762L13.5778 22.0457C16.109 22.6661 18.6674 21.1312 19.3113 18.6059L20.7262 13.0567C21.1697 11.3174 20.1192 9.54796 18.3799 9.10449C18.1175 9.03758 17.8478 9.00373 17.5769 9.00373H15.7536C16.2497 7.37084 16.4996 6.11106 16.4996 5.2021ZM4.60127 15.2667C4.48576 14.671 4.81477 14.0783 5.38147 13.8614L5.85475 13.6802C7.33036 13.1152 8.58585 12.0918 9.43674 10.7604L12.3035 6.27477C12.3935 6.13388 12.4629 5.98082 12.5095 5.82026C12.7608 4.95525 12.9375 4.34126 13.0399 3.97737C13.083 3.82412 13.1239 3.66867 13.1976 3.3847C13.3875 2.663 13.4809 2.50342 13.4932 2.50342C14.3609 2.50342 14.9996 3.48749 14.9996 5.2021C14.9996 6.08659 14.6738 7.53754 14.0158 9.51717C13.8544 10.0027 14.2158 10.5037 14.7275 10.5037H17.5769C17.7228 10.5037 17.868 10.522 18.0093 10.558C18.9459 10.7968 19.5115 11.7496 19.2727 12.6861L17.8578 18.2353C17.4172 19.9631 15.6668 21.0133 13.9349 20.5889L6.30718 18.7193C5.64389 18.5568 5.13577 18.0232 5.00577 17.3528L4.60127 15.2667Z"
                                  fill="#495057"/>
                        </svg>
                        <p><?= $questions['LIKES'] ?: '0' ?></p>
                    </div>
                    <div class="content__reviews__item__control__item btn-reviews-small <?= $arResult['DISLIKES'][$questions['ID']] && ($questions['DISLIKES'] > 0) ? 'btn-reviews-small--active' : '' ?>"
                        <?= $questions['ID_USER'] != $arResult['USER_ID'] ? 'data-type="like"' : '' ?> data-value="dislike"
                         data-id="<?= $questions['ID'] ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.4996 17.9847C16.4996 20.4266 15.3595 22.1834 13.4932 22.1834C12.5183 22.1834 12.1518 21.6406 11.8021 20.3876L11.596 19.6157C11.495 19.257 11.3192 18.6462 11.069 17.7847C11.0623 17.7616 11.0524 17.7398 11.0396 17.7198L8.17281 13.2341C7.49476 12.1732 6.49429 11.3577 5.31841 10.9074L4.84513 10.7262C3.5984 10.2489 2.87457 8.94513 3.1287 7.63456L3.53319 5.54848C3.77462 4.30339 4.71828 3.31249 5.9501 3.01057L13.5778 1.14104C16.109 0.52065 18.6674 2.05558 19.3113 4.58091L20.7262 10.1301C21.1697 11.8693 20.1192 13.6388 18.3799 14.0823C18.1175 14.1492 17.8478 14.183 17.5769 14.183H15.7536C16.2497 15.8159 16.4996 17.0757 16.4996 17.9847ZM4.60127 7.9201C4.48576 8.51581 4.81477 9.10844 5.38147 9.32541L5.85475 9.50661C7.33036 10.0716 8.58585 11.095 9.43674 12.4263L12.3035 16.912C12.3935 17.0529 12.4629 17.206 12.5095 17.3665L13.0614 19.2868L13.2731 20.0781C13.4125 20.5661 13.4827 20.6834 13.4932 20.6834C14.3609 20.6834 14.9996 19.6993 14.9996 17.9847C14.9996 17.1002 14.6738 15.6492 14.0158 13.6696C13.8544 13.1841 14.2158 12.683 14.7275 12.683H17.5769C17.7228 12.683 17.868 12.6648 18.0093 12.6288C18.9459 12.39 19.5115 11.4372 19.2727 10.5007L17.8578 4.95152C17.4172 3.22366 15.6668 2.17344 13.9349 2.59792L6.30718 4.46745C5.64389 4.63002 5.13577 5.16358 5.00577 5.83402L4.60127 7.9201Z"
                                  fill="#495057"/>
                        </svg>
                        <p><?= $questions['DISLIKES'] ?: '0' ?></p>
                    </div>
                </div>
                <?php if (!empty($questions['ANSWER'])) { ?>
                    <div class="content__reviews__item__answer d-flex-reviews top-m-2" <?= $arResult['QUESTIONS_SCHEMA_ORG'] == 'Y' ? 'itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"' : '' ?>>
                        <div class="answer__wrapper">
                            <div class="answer__title">
                                <p><?= Loc::getMessage('TITLE_ANSWER') ?></p>
                            </div>
                            <div class="answer__body" >
                                <p <?= $arResult['QUESTIONS_SCHEMA_ORG'] == 'Y' ? 'itemprop="text"' : '' ?>><?= $questions['ANSWER'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?
        if($component->nav->getPageCount() > 1) {
            $APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                "reviews",
                array(
                    "NAV_OBJECT" => $component->nav,
                    "BASE_LINK" => $APPLICATION->GetCurPageParam('', array('mode', 'c', 'action')),
                ),
                $component->getParent()
            );
        }
        ?>

    <?php } ?>
</div>
<?php
$messages = Loc::loadLanguageFile(__FILE__);
?>
<script>
    BX.message(<?=CUtil::PhpToJSObject($messages)?>);
    SA_QuestionsList.init({
        signedParameters: '<?= $component->getParent()->getSignedParameters() ?>',
        arFile: '<?= json_encode($arFileJs) ?>',
    });
</script>
