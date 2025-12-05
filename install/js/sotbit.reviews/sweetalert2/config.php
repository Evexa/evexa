<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}
return [
    'css' => './dist/sweetalert2.min.css',
    'js' => './dist/sweetalert2.min.js',
    'lang' => './lang/' . LANGUAGE_ID . '/lang.php',
    'rel' => ['sotbit.b2c.animate'],
    'skip_core' => true,
];
