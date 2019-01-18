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
        welLog('debug', 'View', $viewPath . ' load HtmlView start');

        $data = $result->resultData();

        if ($result->hasError() || $result->error()) {
            $data['errors'] = $result->error();
        }
        WelUtil::loadView($viewPath, $data);
        welLog('debug', 'View', $viewPath . ' load HtmlView end');
    }
}