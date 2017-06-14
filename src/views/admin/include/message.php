<?php
namespace ellsif;

// フォームのエラーメッセージを表示
$config = WelCMS\Pocket::getInstance();
$data = $config->varFormData();
$formError = $config->varFormError();
if ($data && $formError) {
  echo Form::formAlert(Validator::getErrorMessages($data, $formError));
}
