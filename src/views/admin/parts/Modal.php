<?php
/**
 * モーダル系の定義。
 * やや冗長だがコピペで利用。
 * riot.jsで利用する。
 */
?>
<?php // ファイルアップロード・選択用モーダル ?>
<file-upload-modal>
  <a onclick={ openDialog } class="btn btn-default">{ opts.title }</a>
  <div class={ modal:true, fade: true, in: opts.show } tabindex="-1">
    <div onclick={ closeDialog } if={ opts.show } class="modal-backdrop fade in"></div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">{ opts.title }</h4>
        </div>
        <div class="modal-body">
          <file-upload></file-upload>
        </div>
        <div class="modal-footer">
          <button onclick={ closeDialog } type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .modal-dialog {
      z-index: 1041;
    }
  </style>

  <script>
    openDialog(e) {
      this.opts.show = true;
      this
      $('body').addClass('modal-open');
      $('.modal', this.root).show();
    }
    closeDialog(e) {
      this.opts.show = false;
      $('body').removeClass('modal-open');
      $('.modal', this.root).hide();
    }
  </script>
</file-upload-modal>

<?php // 適当なモーダル ?>
<test-modal>
  <a onclick={ openDialog } class="btn btn-default">メルマガ配信</a>
  <div class={ modal:true, fade: true, in: opts.show } tabindex="-1">
    <div onclick={ closeDialog } if={ opts.show } class="modal-backdrop fade in"></div>
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel">メルマガ配信</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>メール件名</label>
            <input type="text" value="サンマルシェから夏のキャンペーン情報！" class="form-control">
          </div>
          <div class="form-group">
            <label>配信先</label>
            <pre>test1@example.com
test2@example.com
test3@example.com</pre>
          </div>
          <div class="form-group">
            <label>メール本文</label>
            <textarea class="form-control" style="height: 350px;"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button onclick={ closeDialog } type="button" class="btn btn-primary" data-dismiss="modal">送信</button>
          <button onclick={ closeDialog } type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .modal-dialog {
      text-align: left;
      z-index: 1041;
    }
  </style>

  <script>
    openDialog(e) {
      this.opts.show = true;
      this
      $('body').addClass('modal-open');
      $('.modal', this.root).show();
    }
    closeDialog(e) {
      this.opts.show = false;
      $('body').removeClass('modal-open');
      $('.modal', this.root).hide();
    }
  </script>
</test-modal>
