<?php

namespace ellsif;

use ellsif\Logger;
use ellsif\WelCMS\Config;

class Form
{

  private static $_count = 0;

  /**
   * fromタグを生成する。(formタグの最初にCSRF対策用のtokenをhiddenタグで埋める）
   *
   * @param string $action
   * @param array $id
   * @param array $attributes fromタグへのattribute指定
   * @param array $items formに含まれる項目の設定
   * @param array $submit submitタグの設定
   * @return string
   */
  public static function form(string $action, $id = null, $attributes = [], array $items = [], array $submit = []) :string
  {
    $validationRules = [];
    foreach($items as $name => $item) {
      if (isset($item['validation']) && is_array($item['validation'])) {
        $validationRules[$name] = $item['validation'];
      }
    }
    $html = Form::formStart($action, $attributes, $validationRules);
    if ($id !== null) {
      $_name = (is_array($id)) ? $id['name'] : 'id';
      $_value = (is_array($id)) ? $id['value'] : $id;
      $html .= Form::formInput('', $_name, ['value' => $_value, 'type' => 'hidden'], ['inputOnly' => true]);
    }

    foreach($items as $name => $item) {
      $label = $item['label'] ?? $name;
      if (isset($item['options']['riot'])) {
        $riotTag = $item['options']['riot'][0];
        $riotOpt = $item['options']['riot'][1] ?? [];
        $riotOpt['label'] = $label;
        $riotOptJson = json_encode($riotOpt);
        $innerHtml = tag($riotTag, [], '') . tag('script', [], "riot.mount('${riotTag}', ${riotOptJson})");
        $html .= Form::_formWrap($label, $innerHtml, $item['options']);
      } elseif (isset($item['options']['part'])) {
        // Web部品を使う（本来のinputはhiddenで設定される）
        $item['options']['inputOnly'] = true;
        $item['attributes']['type'] = 'hidden';
        $innerHtml = getPartHtml($item['options']['part']) . Form::formInput('', $name, $item['attributes'], $item['options']);
        $html .= Form::_formWrap($label, $innerHtml, $item['options']);
      } else if (isset($item['select']) && is_array($item['select'])) {
        $html .= Form::formSelect($label, $name, $item['select'], $item['attributes'] ?? [], $item['options'] ?? []);
      } elseif (isset($item['options']['input']['type']) && $item['options']['input']['type'] === 'textarea') {
        $html .= Form::formTextarea($label, $name, $item['attributes'] ?? [], $item['options']);
      } else {
        $html .= Form::formInput($label, $name, $item['attributes'] ?? [], $item['options'] ?? []);
      }
    }
    $tag = $submit['tag'] ?? 'button';
    $submit['attributes'] = $submit['attributes'] ?? [];
    $submit['attributes']['type'] = $submit['attributes']['type'] ?? 'submit';

    $title = $submit['title'] ?? '送信';
    $_attributes = array_merge(['class' => 'btn btn-default'], $submit['attributes']);
    $html .= tag($tag, $_attributes, $title);
    $html .= '</form>';
    return $html;
  }


  /**
   * formの開始タグを生成する。(formタグの最初にCSRF対策用のtokenをhiddenタグで埋める）
   *
   * @param string $action
   * @param array $attributes
   * @param array $validationRules
   * @return string
   */
  public static function formStart(string $action, $attributes = [], array $validationRules = []) :string
  {
    $logger = Logger::getInstance();
    $logger->log('trace', 'Helper', 'formStart called');
    $config = Config::getInstance();

    $_attributes = [
      'method' => 'post',
      'role' => 'form',
      'action' => $action,
    ];
    $_attributes = array_merge($_attributes, $attributes);

    $html = tagStart('form', $_attributes);

    $form = null;
    $token = null;
    if ($config->varFormToken()) {

      // formからPOSTされたtokenがある場合
      $token = $config->varFormToken();
      $form = Form::getReservedForm($token);
    }
    if ($form === null) {

      // tokenが無い場合、新しくformを予約
      $token = bin2hex(openssl_random_pseudo_bytes(32));
      Form::_reserveForm($token, $validationRules);
    }

    // tokenを埋める
    $html .= Form::formInput('', 'sp_token', ['value' => $token, 'type' => 'hidden'], ['inputOnly' => true]);

    return $html;
  }


  /**
   * inputタグを生成
   *
   * @param string $label
   * @param string $name
   * @param array $attributes
   * @param array $options
   * @return string
   */
  public static function formInput(string $label, string $name, array $attributes = [], array $options = []) :string
  {
    $_options = array_merge([], $options);
    $_attributes = [
      'type' => 'text',
      'value' => '',
      'class' => 'form-control',
      'name' => $name,
    ];
    $_attributes = array_merge($_attributes, $attributes);

    if ($_options['inputOnly']) {
      return tag('input', $_attributes);
    } else {
      return Form::_formWrap($label, tag('input', $_attributes), $_options);
    }
  }


  public static function formTextarea(string $label, string $name, array $attributes = [], array $options = []) :string
  {
    $_options = array_merge([], $options);
    $_attributes = [
      'class' => 'form-control',
      'name' => $name,
    ];
    $_attributes = array_merge($_attributes, $attributes);
    $_inputOptions = $options['input'];
    $_attributes['rows'] = $_inputOptions['rows'] ?? 3;
    $_value = $_inputOptions['value'] ?? '';
    if ($_options['inputOnly']) {
      return tag('textarea', $_attributes, $_value);
    } else {
      return Form::_formWrap($label, tag('textarea', $_attributes, htmlspecialchars($_value)), $_options);
    }
  }

  /**
   * selectタグを生成
   *
   * @param string $label
   * @param string $name
   * @param array $list
   * @param array $attributes
   * @param array $options
   * @return string
   */
  public static function formSelect(string $label, string $name, array $list, array $attributes = [], array $options = []) :string
  {
    $_options = array_merge([], $options);
    $_attributes = [
      'value' => '',
      'class' => 'form-control',
      'name' => $name,
    ];
    $_attributes = array_merge($_attributes, $attributes);

    $selectHtml = tagStart('select', $_attributes);
    foreach ($list as $val => $title) {
      if ($val == $_attributes['value']) {
        $selectHtml .= '<option value="' . $val . '" selected>' . $title . '</option>';
      } else {
        $selectHtml .= '<option value="' . $val . '">' . $title . '</option>';
      }
    }
    $selectHtml .= tagEnd('select');
    if ($_options['inputOnly']) {
      return $selectHtml;
    } else {
      return Form::_formWrap($label, $selectHtml, $_options);
    }
  }

  /**
   * アラートを生成
   *
   * @param $errors
   * @return string
   */
  public static function formAlert($errors, array $options = []) :string
  {
    $_options = [
      'class' => 'alert alert-danger',
    ];
    $_options = array_merge($_options, $options);

    $output = '';
    if (is_array($errors) && count($errors) > 0) {
      $output = tag('div', ['class' => $_options['class']], nl2br(implode("\n", $errors)));
    }
    return $output;
  }

  /**
   * tokenに対して予約されているformデータを取得
   *
   * @param $token
   * @return array formReservation
   */
  public static function getReservedForm($token)
  {
    $config = Config::getInstance();
    $session = $config->session();
    $dataAccess = getDataAccess();
    $forms = $dataAccess->select(
      'FormReservation', 0, -1 , '',
      [
        'session_id' => $session['id'],
        'token' => $token,
        'passed' => 0,
      ]
    );
    if (count($forms) > 0) {
      return $forms[0];
    } else {
      return null;
    }
  }

  /**
   * 投稿されたデータを編集して返す。
   */
  public static function formInputData(): array
  {
    $config = Config::getInstance();
    $formData = $config->varFormData();
    $formInputData = [];
    foreach ($formData as $name => $list) {
      if ($name === 'sp_token') continue;
      $name = toSnake($name);
      $formInputData[$name] = $list['value'] ?? '';
    }
    return $formInputData;
  }

  /**
   * Formの予約を解除する
   */
  public static function passReserve()
  {
    $dataAccess = getDataAccess();
    $config = Config::getInstance();
    $token = $config->varFormToken();
    $dataAccess->updateAll('FormReservation', ['passed' => 1], ['token' => $token]);
  }

  /**
   * 入力項目に対してラベルやエラーメッセージを追加
   *
   * @param $label
   * @param $inputHtml
   * @param $_options
   * @return string
   */
  private static function _formWrap($label, $inputHtml, $_options) :string
  {
    $class = 'form-group';
    if ($_options['error']) {
      $class .= ' has-error';
    }
    $output = tagStart('div', ['class' => $class]);
    $output .= tag('label', ['class' => 'control-label'], $label);
    $output .= $inputHtml;
    if ($_options['help']) {
      $output .= tag('p', ['class' => 'help-block'], $_options['help']);
    }
    $output .= tagEnd('div');
    return $output;
  }

  /**
   * Formを予約する。
   * 同action、session_idのFormが存在する場合はupdateとなる。
   *
   * @param $token
   * @param $validationRules
   */
  private static function _reserveForm($token, $validationRules)
  {
    $config = Config::getInstance();
    $session = $config->session();
    $dataAccess = getDataAccess();
    $reserveAction = $config->varAction() . '_' . Form::$_count;
    Form::$_count += 1;
    $form = $dataAccess->select(
      'FormReservation', 0, -1, '',
      ['session_id' => $session['id'], 'action' => $reserveAction]
    );
    if (count($form) > 0) {
      // 同action、sessionのfromが存在する場合はupdate
      $form = $form[0];
      $dataAccess->update(
        'FormReservation',
        $form['id'],
        [
          'token' => $token,
          'validation' => json_encode($validationRules),
          'action' => $reserveAction,
          'passed' => 0,
        ]
      );
    } else {
      $dataAccess->insert(
        'FormReservation',
        [
          'session_id' => $session['id'],
          'token' => $token,
          'validation' => json_encode($validationRules),
          'action' => $reserveAction,
        ]
      );
    }
  }
}