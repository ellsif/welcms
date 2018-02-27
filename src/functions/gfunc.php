<?php

if (!function_exists('text')) {
    function text($text) {
        \ellsif\WelCMS\text($text);
    }
}

if (!function_exists('link')) {
    function link($url) {
        \ellsif\WelCMS\link($url);
    }
}

if (!function_exists('welLoadView')) {
    function welLoadView(string $path, array $data = []) {
        \ellsif\WelCMS\welLoadView($path, $data);
    }
}