<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/** @var PageNavigationComponent $component */
$component = $this->getComponent();
$this->setFrameMode(true);
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getValues();
?>
<div class="pagination-reviews-wrapper">
    <? if ($arResult["CURRENT_PAGE"] !== $arResult["END_PAGE"]) { ?>
        <div class="more-reviews r-p-4">
            <button class="btn-reviews btn-lite-reviews "
                    value="<?= $request['more'] ?>"><?= Loc::getMessage('MORE_BTN') ?></button>
        </div>
    <? } ?>
    <div class="pagination-reviews d-flex-reviews">
        <div class="navigation d-flex-reviews">
            <? if ($arResult["REVERSED_PAGES"] === true): ?>
                <? if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]): ?>
                    <? if (($arResult["CURRENT_PAGE"] + 1) == $arResult["PAGE_COUNT"]): ?>
                        <div class="navigator-prev">
                            <a href="<?= htmlspecialcharsbx($arResult["URL"]) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.75 3C5.3703 3 5.05651 3.28215 5.00685 3.64823L5 3.75V20.25C5 20.6642 5.33579 21 5.75 21C6.1297 21 6.44349 20.7178 6.49315 20.3518L6.5 20.25V3.75C6.5 3.33579 6.16421 3 5.75 3ZM18.7803 3.21967C18.5141 2.9534 18.0974 2.9292 17.8038 3.14705L17.7197 3.21967L9.46967 11.4697C9.2034 11.7359 9.1792 12.1526 9.39705 12.4462L9.46967 12.5303L17.7197 20.7803C18.0126 21.0732 18.4874 21.0732 18.7803 20.7803C19.0466 20.5141 19.0708 20.0974 18.8529 19.8038L18.7803 19.7197L11.0607 12L18.7803 4.28033C19.0732 3.98744 19.0732 3.51256 18.7803 3.21967Z"
                                          fill="#ABB5BE"/>
                                </svg>
                            </a>
                        </div>
                    <? else: ?>
                        <div class="navigator-prev">
                            <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"] + 1)) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.75 3C5.3703 3 5.05651 3.28215 5.00685 3.64823L5 3.75V20.25C5 20.6642 5.33579 21 5.75 21C6.1297 21 6.44349 20.7178 6.49315 20.3518L6.5 20.25V3.75C6.5 3.33579 6.16421 3 5.75 3ZM18.7803 3.21967C18.5141 2.9534 18.0974 2.9292 17.8038 3.14705L17.7197 3.21967L9.46967 11.4697C9.2034 11.7359 9.1792 12.1526 9.39705 12.4462L9.46967 12.5303L17.7197 20.7803C18.0126 21.0732 18.4874 21.0732 18.7803 20.7803C19.0466 20.5141 19.0708 20.0974 18.8529 19.8038L18.7803 19.7197L11.0607 12L18.7803 4.28033C19.0732 3.98744 19.0732 3.51256 18.7803 3.21967Z"
                                          fill="#ABB5BE"/>
                                </svg>
                            </a>
                        </div>
                    <? endif ?>
                    <a href="<?= htmlspecialcharsbx($arResult["URL"]) ?>">
                        <div class="itempagin btn-pagination">1</div>
                    </a>
                <? else: ?>
                    <div class="navigator-prev navigator-disable">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.75 3C5.3703 3 5.05651 3.28215 5.00685 3.64823L5 3.75V20.25C5 20.6642 5.33579 21 5.75 21C6.1297 21 6.44349 20.7178 6.49315 20.3518L6.5 20.25V3.75C6.5 3.33579 6.16421 3 5.75 3ZM18.7803 3.21967C18.5141 2.9534 18.0974 2.9292 17.8038 3.14705L17.7197 3.21967L9.46967 11.4697C9.2034 11.7359 9.1792 12.1526 9.39705 12.4462L9.46967 12.5303L17.7197 20.7803C18.0126 21.0732 18.4874 21.0732 18.7803 20.7803C19.0466 20.5141 19.0708 20.0974 18.8529 19.8038L18.7803 19.7197L11.0607 12L18.7803 4.28033C19.0732 3.98744 19.0732 3.51256 18.7803 3.21967Z"
                                  fill="#ABB5BE"/>
                        </svg>
                    </div>
                    <div class="itempagin btn-pagination active-pagination-item">1</div>

                <? endif ?>

                <?
                $page = $arResult["START_PAGE"] - 1;
                while ($page >= $arResult["END_PAGE"] + 1):
                    ?>
                    <? if ($page == $arResult["CURRENT_PAGE"]): ?>
                    <div class="itempagin btn-pagination active-pagination-item"><?= ($arResult["PAGE_COUNT"] - $page + 1) ?></div>
                <? else: ?>
                    <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($page)) ?>">
                        <div class="itempagin btn-pagination active-pagination-item"><?= ($arResult["PAGE_COUNT"] - $page + 1) ?></div>
                    </a>
                <? endif ?>
                    <? $page-- ?>
                <? endwhile ?>

                <? if ($arResult["CURRENT_PAGE"] > 1): ?>
                    <? if ($arResult["PAGE_COUNT"] > 1): ?>
                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate(1)) ?>">
                            <div class="itempagin btn-pagination active-pagination-item"><?= $arResult["PAGE_COUNT"] ?></div>
                        </a>
                    <? endif ?>
                    <div class="navigator-next">
                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"] - 1)) ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.46967 4.21967C8.17678 4.51256 8.17678 4.98744 8.46967 5.28033L15.1893 12L8.46967 18.7197C8.17678 19.0126 8.17678 19.4874 8.46967 19.7803C8.76256 20.0732 9.23744 20.0732 9.53033 19.7803L16.7803 12.5303C17.0732 12.2374 17.0732 11.7626 16.7803 11.4697L9.53033 4.21967C9.23744 3.92678 8.76256 3.92678 8.46967 4.21967Z"
                                      fill="#8F9396"/>
                            </svg>
                        </a>
                    </div>
                <? else: ?>
                    <? if ($arResult["PAGE_COUNT"] > 1): ?>
                        <div class="itempagin btn-pagination active-pagination-item"><?= $arResult["PAGE_COUNT"] ?></div>
                    <? endif ?>
                    <div class="navigator-next">
                        <a>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.46967 4.21967C8.17678 4.51256 8.17678 4.98744 8.46967 5.28033L15.1893 12L8.46967 18.7197C8.17678 19.0126 8.17678 19.4874 8.46967 19.7803C8.76256 20.0732 9.23744 20.0732 9.53033 19.7803L16.7803 12.5303C17.0732 12.2374 17.0732 11.7626 16.7803 11.4697L9.53033 4.21967C9.23744 3.92678 8.76256 3.92678 8.46967 4.21967Z"
                                      fill="#8F9396"/>
                            </svg>
                        </a>
                    </div>
                <? endif ?>

            <? else: ?>

                <? if ($arResult["CURRENT_PAGE"] > 1): ?>
                    <div class="navigator-prev">
                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate(1)) ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M5.75 3C5.3703 3 5.05651 3.28215 5.00685 3.64823L5 3.75V20.25C5 20.6642 5.33579 21 5.75 21C6.1297 21 6.44349 20.7178 6.49315 20.3518L6.5 20.25V3.75C6.5 3.33579 6.16421 3 5.75 3ZM18.7803 3.21967C18.5141 2.9534 18.0974 2.9292 17.8038 3.14705L17.7197 3.21967L9.46967 11.4697C9.2034 11.7359 9.1792 12.1526 9.39705 12.4462L9.46967 12.5303L17.7197 20.7803C18.0126 21.0732 18.4874 21.0732 18.7803 20.7803C19.0466 20.5141 19.0708 20.0974 18.8529 19.8038L18.7803 19.7197L11.0607 12L18.7803 4.28033C19.0732 3.98744 19.0732 3.51256 18.7803 3.21967Z"
                                      fill="#ABB5BE"/>
                            </svg>
                        </a>

                        <? if ($arResult["CURRENT_PAGE"] > 2): ?>
                            <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"] - 1)) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.5303 4.21967C15.8232 4.51256 15.8232 4.98744 15.5303 5.28033L8.81066 12L15.5303 18.7197C15.8232 19.0126 15.8232 19.4874 15.5303 19.7803C15.2374 20.0732 14.7626 20.0732 14.4697 19.7803L7.21967 12.5303C6.92678 12.2374 6.92678 11.7626 7.21967 11.4697L14.4697 4.21967C14.7626 3.92678 15.2374 3.92678 15.5303 4.21967Z"
                                          fill="#ABB5BE"/>
                                </svg>
                            </a>
                        <? else: ?>
                            <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"] - 1)) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.5303 4.21967C15.8232 4.51256 15.8232 4.98744 15.5303 5.28033L8.81066 12L15.5303 18.7197C15.8232 19.0126 15.8232 19.4874 15.5303 19.7803C15.2374 20.0732 14.7626 20.0732 14.4697 19.7803L7.21967 12.5303C6.92678 12.2374 6.92678 11.7626 7.21967 11.4697L14.4697 4.21967C14.7626 3.92678 15.2374 3.92678 15.5303 4.21967Z"
                                          fill="#ABB5BE"/>
                                </svg>
                            </a>
                        <? endif ?>
                    </div>
                <? else: ?>
                    <div class="itempagin btn-pagination active-pagination-item">1</div>

                <? endif ?>

                <?
                if ($arResult["END_PAGE"] > 3) {
                    if($arResult["CURRENT_PAGE"] != 1){
                        $page = $arResult["START_PAGE"];
                    }else{
                        $page = $arResult["START_PAGE"] + 1;
                    }

                    if ($arResult["CURRENT_PAGE"] == 1 && $arResult["END_PAGE"] > 1) {
                        $value = $arResult["END_PAGE"];
                    } else {
                        if($arResult["CURRENT_PAGE"] != $arResult["END_PAGE"]){
                            $value = $arResult["END_PAGE"];
                        }else{
                            $value = $arResult["END_PAGE"] - 1;
                        }
                    }



                    while ($page <= $value):
                        ?>
                        <? if ($page == $arResult["CURRENT_PAGE"]): ?>
                        <div class="itempagin btn-pagination active-pagination-item"><?= $page ?></div>
                    <? else: ?>

                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($page)) ?>">
                            <div class="itempagin btn-pagination"><?= $page ?></div>
                        </a>

                    <? endif ?>
                        <? $page++ ?>
                    <? endwhile ?>
                    <?
                } else {

                    if ($arResult["CURRENT_PAGE"] == 1) {
                        $page = $arResult["START_PAGE"] + 1;
                    } else {
                        $page = $arResult["START_PAGE"];
                    }

                    if ($arResult["CURRENT_PAGE"] == 1 && $arResult["END_PAGE"] > 1) {
                        $value = $arResult["END_PAGE"];
                    } else {
                        if($arResult["END_PAGE"] != 2){
                            if ($arResult["CURRENT_PAGE"] == 3) {
                                $value = $arResult["END_PAGE"] - 1;
                            } else {
                                $value = $arResult["END_PAGE"];
                            }
                        }


                    }
                    while ($page <= $value):
                        ?>
                        <? if ($page == $arResult["CURRENT_PAGE"]): ?>
                        <div class="itempagin btn-pagination active-pagination-item"><?= $page ?></div>
                    <? else: ?>

                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($page)) ?>">
                            <div class="itempagin btn-pagination"><?= $page ?></div>
                        </a>

                    <? endif ?>
                        <? $page++ ?>
                    <? endwhile ?>
                <? } ?>

                <? if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]): ?>
                    <div class="navigator-next">
                        <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"] + 1)) ?>">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.46967 4.21967C8.17678 4.51256 8.17678 4.98744 8.46967 5.28033L15.1893 12L8.46967 18.7197C8.17678 19.0126 8.17678 19.4874 8.46967 19.7803C8.76256 20.0732 9.23744 20.0732 9.53033 19.7803L16.7803 12.5303C17.0732 12.2374 17.0732 11.7626 16.7803 11.4697L9.53033 4.21967C9.23744 3.92678 8.76256 3.92678 8.46967 4.21967Z"
                                      fill="#8F9396"/>
                            </svg>
                        </a>
                        <? if ($arResult["PAGE_COUNT"] > 1): ?>
                            <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["PAGE_COUNT"])) ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18.25 3C18.6297 3 18.9435 3.28215 18.9932 3.64823L19 3.75V20.25C19 20.6642 18.6642 21 18.25 21C17.8703 21 17.5565 20.7178 17.5068 20.3518L17.5 20.25V3.75C17.5 3.33579 17.8358 3 18.25 3ZM5.21967 3.21967C5.48594 2.9534 5.9026 2.9292 6.19621 3.14705L6.28033 3.21967L14.5303 11.4697C14.7966 11.7359 14.8208 12.1526 14.6029 12.4462L14.5303 12.5303L6.28033 20.7803C5.98744 21.0732 5.51256 21.0732 5.21967 20.7803C4.9534 20.5141 4.9292 20.0974 5.14705 19.8038L5.21967 19.7197L12.9393 12L5.21967 4.28033C4.92678 3.98744 4.92678 3.51256 5.21967 3.21967Z"
                                          fill="#8F9396"/>
                                </svg>
                            </a>
                        <? endif ?>
                    </div>

                <? else: ?>
                    <? if ($arResult["PAGE_COUNT"] > 1): ?>
                        <? if ($arResult["CURRENT_PAGE"] == $arResult["END_PAGE"] && $arResult["PAGE_COUNT"] == 2) { ?>
                            <a href="<?= htmlspecialcharsbx($component->replaceUrlTemplate($arResult["END_PAGE"] - 1)) ?>">
                                <div class="itempagin btn-pagination"><?= $arResult["END_PAGE"] - 1 ?></div>
                            </a>
                        <? } ?>
                        <div class="itempagin btn-pagination active-pagination-item"><?= $arResult["PAGE_COUNT"] ?></div>
                    <? endif ?>

                <? endif ?>
            <? endif ?>
        </div>
        <div class="pagination-info">
            <p>
                <?
                $countPage = $arResult['PAGE_SIZE'] * $arResult['CURRENT_PAGE'];
                ?>
                <?=
                Loc::getMessage('INFO_PAGENATION', [
                    '#START#' => ($countPage - $arResult['PAGE_SIZE']) ?: 1,
                    '#FINISH#' => ($countPage > $arResult['RECORD_COUNT']) ? $arResult['RECORD_COUNT'] : $countPage,
                    '#ALL#' => $arResult['RECORD_COUNT'],
                ]);
                ?>
            </p>
        </div>
    </div>
</div>