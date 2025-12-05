<?php

if (\Bitrix\Main\Loader::includeModule("sotbit.reviews")) {
    CAgent::RemoveAgent("\Sotbit\Reviews\Bill\Coupon::AgentCouponsNeedAction();", CSotbitReviews::iModuleID);
}
