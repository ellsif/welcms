<?php
/**
 * 画像入力フォーム部品
 * riot.jsにて利用
 */
?>
<image-input>
  <file-upload-modal title={ opts.title }></file-upload-modal>
  <a onclick={ remove } href="javascript:void(0)" class="btn btn-danger">削除</a>
  <img class="uploaded" src={ opts.src } if={ opts.src }>
  <input type="hidden" value={ opts.src }>


  <style>
    image-input {
      display: block;
    }
    img {
      height: auto;
      max-width: 100%;
    }
    .uploaded {
      margin-top: 10px;
    }
  </style>

  remove(e) {
    opts.src = '';
  }
</image-input>
