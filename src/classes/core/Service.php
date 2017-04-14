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

    protected function requireHelpers()
    {
        $config = Pocket::getInstance();
        require_once $config->dirSystem() . '/functions/helper.php';
        require_once $config->dirSystem() . '/classes/core/Template.php';
        require_once $config->dirSystem() . '/classes/core/HtmlTemplate.php';
        require_once $config->dirView() . '/admin/helper.php';
    }
}