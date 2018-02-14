<?php


namespace ellsif\WelCMS;


class HtmlPrinter extends Printer
{
    /**
     * HTMLを出力します。
     */
    public function print(ServiceResult $result = null)
    {
        $viewPath = welPocket()->getRouter()->getViewPath();
        $data = ['errors' => []];
        if ($result) {
            if ($result->getView($this->getName())) {
                $viewPath = $result->getView($this->getName());
            }
            $data = $result->resultData();
            if ($result->isError() && !isset($data['errors'])) {
                $data['errors'] = $result->error();
            }
        }
        WelUtil::loadView($viewPath, $data);
    }
}