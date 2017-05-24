<?php

namespace ellsif\WelCMS;

/**
 * Serviceの実行結果
 */
class ServiceResult
{
    private $errors = [];
    private $resultData = [];
    private $viewPath = [];

    /**
     * エラー
     */
    public function isError(): bool
    {
        return (is_array($this->errors) && count($this->errors) > 0);
    }

    /**
     * エラーのgetter/setter。
     */
    public function error(...$err): array
    {
        if (count($err) > 0) {
            if (is_array($err[0])) {
                $this->errors = $err[0];
            } else {
                $this->errors[] = $err[0];
            }
        }
        return $this->errors;
    }

    /**
     * Service実行結果のgetter/setter。
     */
    public function resultData(...$resultData): array
    {
        if (count($resultData) > 0) {
            $this->resultData = $resultData[0];
        }
        return $this->resultData;
    }

    /**
     * Viewファイルパスのgetter/setter。
     */
    public function setView($view, $type = 'html')
    {
        $this->viewPath[$type] = $view;
    }

    public function getView($type)
    {
        $pocket = Pocket::getInstance();
        if (array_key_exists($type, $this->viewPath)) {
            return $this->viewPath[$type];
        } else {
            // デフォルトを使う
            return null;
        }
    }
}