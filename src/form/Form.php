<?php
namespace ellsif\WelCMS;

use ellsif\util\StringUtil;

/**
 * フォームの基底クラス
 */
abstract class Form
{
    private $tokenName;

    private $messages;

    private $errors;

    private $accepted;

    protected $token;

    protected $data;

    /**
     * コンストラクタです。
     *
     * ## 説明
     * $tokenNameに空文字列を指定した場合、トークンチェックの処理は行いません。
     */
    public function __construct(array $data = [], string $tokenName = '__token__')
    {
        $this->data = $data;
        $this->messages = [];
        $this->errors = [];
        $this->accepted = false;
        $this->tokenName = $tokenName;
        if ($this->tokenName) {
            // トークン発行
        }
    }

    protected function addMessage(string $message): Form
    {
        $this->messages[] = $message;
        return $this;
    }

    protected function setErrors(string $name, array $errors): Form
    {
        $this->errors[$name] = $errors;
        return $this;
    }

    protected function addError(string $name = null, string $message): Form
    {
        $name = $name ? $name : '__all__';
        if (!isset($this->errors[$name])) {
            $this->errors[$name] = [];
        }
        $this->errors[$name][] = $message;
        return $this;
    }

    /**
     * トークンのチェックを行います。
     */
    protected function checkToken(string $postedToken)
    {

    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function hasError(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorMessages(string $name): array
    {
        return $this->errors[$name] ?? [];
    }

    public function submit(array $data, bool $doValidation = true, bool $transaction = false): Form
    {
        $this->data = $data;

        if ($transaction) {
            // TODO トランザクション開始
        }
        if ($this->tokenName) {
            $this->checkToken($data[$this->tokenName] ?? '');
            if (!$this->isValid()) {
                return $this;
            }
        }

        if ($doValidation && !$this->processValidate($data)) {
            return $this;
        }

        $this->data = $this->processSubmit($this->data);

        if ($transaction) {
            // TODO トランザクション終了
        }
        $this->accepted = !$this->hasError();
        return $this;
    }

    /**
     * バリデーション処理のみを行います。
     */
    public function validate(array $data): bool
    {
        $this->processValidate($data);
        return $this->isValid();
    }

    /**
     * バリデーションエラーが無いことをチェックします。
     */
    public function isValid(): bool
    {
        return count($this->errors) == 0;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    /**
     * フォーム名称を取得します。
     */
    public function getName(): string
    {
        $class = get_class($this);
        return lcfirst(StringUtil::rightRemove(substr($class, strrpos($class, '\\') + 1), 'Form'));
    }

    public function getData(...$names)
    {
        if (count($names) == 0) {
            return $this->data;
        }
        $_data = $this->data;
        foreach($names as $name) {
            if (isset($_data[$name])) {
                $_data = $_data[$name];
                continue;
            } else {
                return null;
            }
        }
        return $_data;
    }

    /**
     * フォームの送信内容の受け付け処理を行います。
     */
    abstract protected function processSubmit(array $data): array;

    /**
     * フォームの送信内容のバリデーションを行います。
     */
    abstract protected function processValidate(array $data): bool;

}