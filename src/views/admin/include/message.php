<?php
namespace ellsif;

// フォームのエラーメッセージを表示
$config = WelCMS\Pocket::getInstance();
$data = $config->varFormData();
$formError = $config->varFormError();
if ($data && $formError) {
  echo Form::formAlert(Validator::getErrorMessages($data, $formError));
}

// お知らせメッセージを表示
echo Form::formAlert($config->varAlertError(), ['class' => 'alert alert-danger']);
echo Form::formAlert($config->varAlertWarning(), ['class' => 'alert alert-warning']);
echo Form::formAlert($config->varAlertInfo(), ['class' => 'alert alert-info']);
echo Form::formAlert($config->varAlertSuccess(), ['class' => 'alert alert-success']);
