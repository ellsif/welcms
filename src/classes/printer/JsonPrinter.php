<?php


namespace ellsif\WelCMS;


class JsonPrinter extends Printer
{
    /**
     * jsonを表示する。
     */
    public function print(ServiceResult $result = null)
    {
        header("Content-Type: application/json; charset=utf-8");
        if ($result->isError()) {
            http_response_code(500);
        }
        if ($result->getView('json')) {
            WelUtil::loadView($result->getView('json'), $result->resultData());
        } else {
            echo json_encode(["result" =>$result->resultData()]);
        }
    }
}