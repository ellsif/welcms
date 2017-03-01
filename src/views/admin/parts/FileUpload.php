<?php
namespace ellsif;
/**
 * ファイルアップロード用のダイアログを表示する。
 */
use ellsif\WelCMS\Router;
$url = Router::getInstance();
?>
<file-upload>
  <div class="col-sm-12">
    <div each={ items } class="files-image">
      <img onclick={ parent.selectImage } src={ url }>
    </div>
  </div>

  <style>
    .files-image {
      width: 120px;
      height: 120px;
      margin: 16px;
      float: left;
      position: relative;
      cursor: pointer;
      border: #e8e8e8 solid 1px;
      box-sizing: border-box;
    }
  </style>

  <script>
    this.on('mount', function(){
      init = opts.init || function(){
        $.get('<?php echo $url->getUrl('/admin/api/files/image') ?>', [], function(json) {
          // 初期化する
          console.log(json);
        });
      };
      init();
    });

    selectImage(e) {

    }
  </script>
</file-upload>
