<?php


namespace ellsif\WelCMS;


class HtmlPrinter extends Printer
{
    /**
     * HTMLを出力します。
     */
    public function print(ServiceResult $result)
    {
        if ($v = $result->getView($this->getName())) {
            $viewPath = $v;
        } else {
            $viewPath = welPocket()->getRouter()->getViewPath();
        }

        if ($result->hasError()) {
            $data = ['errors' => $result->error()];
        } else {
            $data = $result->resultData();
        }

        WelUtil::loadView($viewPath, $data);
    }

    /**
     * サブViewを読み込みます。
     */
    public function loadView(string $path)
    {
        RoutingUtil::getViewPath($path);
    }
}