<?php

namespace Sotbit\Reviews\Security;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;

class Security
{
    public static function recaptha2($key, $SecretKey): string
    {
        $recaptcha = $key;
        $url = "https://www.google.com/recaptcha/api/siteverify?secret=" . $SecretKey . "&response=" . $recaptcha . "&remoteip=" . $_SERVER['REMOTE_ADDR'];
        $return = '';
        $status = 1;
        if (!empty($recaptcha)) {
            $curl = curl_init();
            if (!$curl) {
                $status = 2;
            } else {
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
                $curlData = curl_exec($curl);
                curl_close($curl);
                $curlData = json_decode($curlData, true);
                if ($curlData['success']) {
                    $status = 0;
                }
            }
        }

        if ($status === 1) {
            $return = Loc::getMessage("WRONG_RECAPTCHA");
        } elseif ($status === 2) {
            $return = Loc::getMessage("CURL_ERROR");
        }

        return $return;
    }

    public static function checkRecaptha($config): Error|false
    {
        $secretKey = $config['REVIEWS_RECAPTCHA2_SECRET_KEY'];

        if (!empty($secretKey) && isset($request['g-recaptcha-response'])) {
            $recaptch2Return = self::recaptha2($request['g-recaptcha-response'], $secretKey);
        }

        if ($recaptch2Return != "") {
            return new Error($recaptch2Return);
        }

       return false;
    }

    public static function checkSpam($data, $addFields, $config): Error|false
    {
        $uriString = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getRequestUri();
        
        $akismetKey = $config['REVIEWS_AKISMET_API_KEY'];
        if (empty($akismetKey)) {
            return false;
        }

        $akismetAuthName = $config['REVIEWS_AKISMET_API_LOGIN'];
        $akismetWebsiteUrl = SITE_SERVER_NAME;
        $oAkismet = new \Akismet($akismetWebsiteUrl, $akismetKey);
        if (!$oAkismet->isKeyValid()) {
            return false;
        }

        $oAkismet->setCommentAuthor($akismetAuthName);
        $oAkismet->setPermalink($uriString);
        $oAkismet->setCommentContent($data['COMMENT'] ?: $data['QUESTION']);
        if ($oAkismet->isCommentSpam()) {
            return new Error('spam');
        }

        foreach ($addFields as $addField) {
            $oAkismet->setCommentContent($addField);
            if ($oAkismet->isCommentSpam()) {
                return new Error('spam');
            }
        }
    }
}
