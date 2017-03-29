var smAdmin = {};

// 画面上部にアラートを表示
smAdmin.alert = function(message, type, keep, $toElem) {
  type = type || 'info';
  $toElem = $toElem || $('.panel-body:first');
  keep = keep || false;
  var $elem = $('<div class="alert alert-' + type + '">' + message + '</div>');
  $toElem.prepend($elem);
  if (!keep) {
    setTimeout(function(){
      $elem.animate({opacity: 0}, {duration: 1000, complete: function(){$elem.remove()}});
    }, 3000);
  }
};

smAdmin.getSubmitData = function($elem) {
  var data = $elem.data();
  var result = {};
  $elem.find('input').each(function(){
    result[$(this).attr('name')] = $(this).val();
  });
  return result;
};

smAdmin.ajax = function($elem, type, url, data, options) {
  if (!$elem.is('.disabled')) {
    data = data || smAdmin.getSubmitData($elem);
    var success = options['success'] || null;
    var $alertElem = $('.panel-body:first');
    if ($elem.closest('.modal-dialog').length > 0) {
      $alertElem = $elem.closest('.modal-dialog').find('.modal-body');
    }
    $.ajax({
      type: type,
      url: url,
      data: data,
      dataType: 'json',
      success: function(data){
        $elem.removeClass('disabled');
        if (data.result && data.result['success']) {
          if (typeof(success) === 'function') {
            success($elem, data);
          }
        } else {
          var msg = 'APIの呼び出しに失敗しました。';
          if (data.result) {
            msg = data.result['message'];
          }
          smAdmin.alert(msg, 'danger', false, $alertElem);
        }
      },
      error: function() {
        $elem.removeClass('disabled');
        smAdmin.alert('Ajaxの呼び出しに失敗しました。', 'danger', false, $alertElem);
      }
    });
    $elem.addClass('disabled');
  }
};

$(function(){

  /**
   * .js-ajax : ajax通信を行う。
   *
   * ## 説明
   * js-ajaxクラスが指定された要素に対してクリックイベントが追加されます。
   * サーバからの戻りはjson形式である必要があります。<br>
   * 送信するパラメータは.js-ajax以下のinputタグが対象になります。
   *
   * ## 属性
   * data属性として下記が利用可能です。
   * - action: 通信先のURL（完全または相対）
   * - type: 通信の種類（デフォルトはGET）
   * - success: 通信成功時のコールバック関数を返す関数名（TBD）
   * - error: 通信失敗時のコールバック関数を返す関数名（未実装）
   *
   * ## 例
   * 下記の場合、id=1をパラメータとしてsubmit.phpにGETリクエストを行います。
   *
   * ### HTML
   *     <div class="js-ajax" data-action="submit.php" data-success="getCallback">
   *       <input type="hidden" name="id" value="1">
   *       送信
   *     </div>
   *
   * ### JS
   * コールバック関数は第一引数にイベントを設定した要素、第二引数にサーバから返却されたデータを取ります。
   *     var getCallback = function(){
   *         return function($elem, data){ $elem.text(data.result['something']) };
   *     };
   *
   * ## JSON
   * サーバが返却するJSONには下記の項目を含める必要があります。
   *     { result: { success: true } }
   * エラー時はmessageを指定します。
   *     { result: { success: false, message: 'idが指定されていません。' } }
   */
  $('.js-ajax').each(function(){
    var $self = $(this);
    var url = $(this).data('action');
    var type = $(this).data('type') || 'GET';
    var success = eval($(this).data('success') + '()');
    $self.on('click', function() {
      smAdmin.ajax($self, type, url, null, {success: success});
    });
  });

  /**
   * .js-submit : formのsubmitをajax通信で行う。
   *
   * ## 説明
   * js-submitクラスが指定された要素の属するformをajaxで送信します。
   * サーバからの戻りはjson形式である必要があります。
   *
   * ## 属性
   * data属性として下記が利用可能です。
   * - success: 通信成功時のコールバック関数を返す関数名（TBD）
   * - error: 通信失敗時のコールバック関数を返す関数名（未実装）
   *
   * ## 例
   *
   * ## JSON
   * サーバが返却するJSONの形式はjs-ajaxと同様です。
   */
  $('.js-submit').each(function(){
    var $self = $(this);
    var $form = $self.closest('form');
    var url = $form.attr('action');
    var type = $form.attr('method') || 'POST';
    var success = null;
    if ($(this).data('success')) {
      success = Function('return ' + $(this).data('success'));
    }
    $self.on('click', function(e) {
      e.preventDefault();
      var data = $form.serialize();
      smAdmin.ajax($self, type, url, data, {success: success});
    });
  });
});