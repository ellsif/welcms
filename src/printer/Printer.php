<?php

namespace ellsif\WelCMS;
use ellsif\util\StringUtil;

/**
 * 汎用Printerクラス。
 *
 * ## 説明
 *
 */
abstract class Printer
{

    /**
     * 処理結果を出力します。
     */
    abstract public function print(ServiceResult $result);

    /**
     * Printerの名称を取得します。
     */
    public function getName(): string
    {
        $class = get_class($this);
        return lcfirst(StringUtil::rightRemove(substr($class, strrpos($class, '\\') + 1), 'Printer'));
    }

    /**
     * サブViewを読み込みます。
     */
    public function loadView(string $path, array $data = [])
    {
        $path = RoutingUtil::getViewPath($path);
        WelUtil::loadView($path, $data);
    }
}