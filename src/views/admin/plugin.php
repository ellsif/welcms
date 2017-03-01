<?php
  namespace ellsif;
  use ellsif\WelCMS\Router;
  $url = Router::getInstance();
  $plugins = $plugins ?? [];
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__) . '/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <?php include dirname(__FILE__) . '/nav.php' ?>

      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">プラグイン管理</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                プラグイン一覧
              </div>
              <div class="panel-body">
                <?php include dirname(__FILE__) . '/include/message.php' ?>
                <?php if (count($plugins) > 0) { ?>
                  <table width="100%" class="table table-striped table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>プラグイン名</th>
                        <th>作者</th>
                        <th>状態</th>
                        <th>バージョン</th>
                        <th>操作</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach($plugins as $key => $pluginData) { ?>
                        <?php if ($pluginData['status']) { ?>
                          <?php
                            $plugin = $pluginData['plugin']['current'] ?? $pluginData['plugin']['latest'];
                            $version = $pluginData['plugin']['current'] ? $pluginData['plugin']['current']['version'] : '';
                          ?>
                          <tr class="js-group">
                            <td><?php echo $plugin['name'] ?? '' ?></td>
                            <td><?php echo $plugin['author'] ?? '' ?></td>
                            <td class="js-change-status"><?php echo intval($plugin['active']) === 1 ? '有効' : '無効' ?></td>
                            <td><?php
                              $selectVersions = ['' => '選択してください'];
                              foreach($pluginData['plugin']['versions'] as $_version) {
                                $selectVersions[$_version] = $_version;
                              }
                              echo Form::formSelect(
                                'バージョン選択',
                                "version[${key}]",
                                $selectVersions,
                                ['value' => $version, 'class' => 'form-control js-change-version'],
                                ['inputOnly' => true]
                              );
                            ?></td>
                            <td>
                              <?php
                                $_active = $plugin['active'] ?? 0;
                                $_disableClass = $_active != 1 ? 'btn-danger disabled' : 'btn-danger';
                              ?>
                              <div data-type="POST" data-action="<?php echo Router::getUrl('/admin/plugin.json') ?>" data-success="_btn_callback" class="js-ajax js-ajax-submit btn btn-primary btn-sm">
                                <input type="hidden" name="id" value="<?php echo $plugin['id'] ?>">
                                <input type="hidden" name="name" value="<?php echo $plugin['name'] ?>">
                                <input type="hidden" name="version" value="<?php echo $version ?>">
                                <input type="hidden" name="active" value="1">
                                適用
                              </div>
                              <div data-type="POST" data-id="<?php echo $plugin['id'] ?>" data-name="<?php echo $plugin['name'] ?>" data-action="<?php echo Router::getUrl('/admin/plugin.json') ?>" data-success="_btn_callback" class="js-ajax btn <?php echo $_disableClass ?> btn-sm">無効</div>
                            </td>
                          </tr>
                        <?php } else { ?>
                          <?php $plugin = $pluginData['plugin']['current'] ?? $pluginData['plugin']['latest']; ?>
                          <tr>
                            <td><?php echo $plugin['name'] ?? '' ?></td>
                            <td colspan="4"><?php echo $pluginData['message'] ?? 'プラグインにエラーがあります。' ?></td>
                          </tr>
                        <?php } ?>
                      <?php } ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <div class="alert alert-info">プラグインがありません。</div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__) . "/foot_js.php" ?>
    <script>
      // 有効無効切替成功時
      var _btn_callback = function() {
        return function($elem, result){
          if (result.data && result.data.active == 1) {
            $elem.next('.btn').removeClass('disabled');
            $elem.closest('.js-group').find('.js-change-status').text('有効');
          } else {
            $elem.prev('.btn').removeClass('disabled');
            $elem.closest('.js-group').find('.js-change-status').text('無効');
          }
          smAdmin.alert(result.result.message);
        };
      };
      $(function(){
        <?php // バージョン変更時に適用ボタンのdataを書き換える ?>
        $('.js-change-version').on('change', function(){
          var elem = $(this).closest('.js-group').find('input[name="version"]');
          elem.val($(this).val());
        });
      });
    </script>
  </body>
</html>
