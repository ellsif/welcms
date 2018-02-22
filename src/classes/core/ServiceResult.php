<?php

namespace ellsif\WelCMS;

/**
 * Serviceの実行結果
 */
class ServiceResult
{
    private $forms;

    private $errors;

    private $resultData;

    private $viewPathList;

    /**
     * コンストラクタ
     */
    public function __construct(array $resultData = [], array $errors = [])
    {
        $this->forms = [];
        $this->resultData = $resultData;
        $this->errors = $errors;
        $this->viewPathList = [];
    }

    /**
     * Formを追加します
     */
    public function addForm(Form $form, string $name = null): ServiceResult
    {
        if (!$name) {
            $name = $form->getName();
        }
        $this->forms[$name] = $form;
        return $this;
    }

    /**
     * Formを取得します
     */
    public function getForm(string $name = null): ?Form
    {
        if ($name && isset($this->forms[$name])) {
            return $this->forms[$name];
        } elseif ($this->forms) {
            return current(array_slice($this->forms, 0, 1, true));
        }
        return null;
    }

    /**
     * エラーの有無を判定します
     */
    public function hasError(string $formName = null): bool
    {
        $form = $this->getForm($formName);
        return $form ? (count($form->getErrors()) > 0) : false;
    }

    /**
     * エラーを取得します
     */
    public function getErrors(string $name = null, string $formName = null): array
    {
        $form = $this->getForm($formName);
        $errors = $form ? $form->getErrors() : [];
        if ($name) {
            return $errors[$name] ?? [];
        }
        return $errors;
    }

    /**
     * エラーメッセージのリストを取得します
     */
    public function getErrorMessages(string $formName = null): array
    {
        $messages = [];
        foreach($this->getErrors($formName) as $name => $msgs) {
            $messages = array_merge($messages, $msgs);
        }
        return $messages;
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
     * ViewファイルのパスをSETします。
     */
    public function setView($view, $type = 'html'): ServiceResult
    {
        $this->viewPathList[$type] = $view;
        return $this;
    }

    /**
     * Serviceで設定されたViewファイルのパスをGETします。
     */
    public function getView($type): ?string
    {
        if (array_key_exists($type, $this->viewPathList)) {
            return $this->viewPathList[$type];
        } else {
            return null;
        }
    }
}