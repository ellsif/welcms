<?php

if (!function_exists('pText')) {
    function pText($text) {
        \ellsif\WelCMS\text($text);
    }
}

if (!function_exists('pUrl')) {
    function pUrl($url) {
        \ellsif\WelCMS\url($url);
    }
}

if (!function_exists('pVal')) {
    function pVal(array $data = null, string $key, string $default = '') {
        \ellsif\WelCMS\val($data, $key, $default);
    }
}

if (!function_exists('welLoadView')) {
    function welLoadView(string $path, array $data = []) {
        \ellsif\WelCMS\welLoadView($path, $data);
    }
}