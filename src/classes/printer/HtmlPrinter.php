<?php


namespace ellsif\WelCMS;


class HtmlPrinter extends Printer
{
    /**
     * HTMLを出力します。
     */
    public function print(ServiceResult $result = null)
    {
        if ($result && $result->getView('html')) {
            $viewPath = $result->getView('html');
        } else {
            $viewPath = Router::getViewPath(null);
        }
        $data = $result ? $result->resultData() : [];
        if ($result->isError() && !isset($data['errors'])) {
            $data['errors'] = $result->error();
        }
        WelUtil::loadView($viewPath, $data);
    }
}