<?php

namespace ellsif\WelCMS;

/**
 * Serviceの基底クラス。
 */
abstract class Service
{

    public function __construct()
    {
    }

    /**
     * ページの名前を取得する。
     * SomeNamePage -> someName
     *
     * @return string
     */
    protected function getName() :string
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1, -4);
        return strtolower((substr($class, 0, 1))) . substr($class, 1);
    }
}