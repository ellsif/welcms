<?php
namespace ellsif\WelCMS;
?>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    <a class="navbar-brand" href="index.html"><?php echo Pocket::getInstance()->settingSiteName() ?></a>
  </div>

  <div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav navbar-collapse">
      <ul class="nav" id="side-menu">
        <li>
          <a href="<?php echo WelUtil::getUrl('admin') ?>"><i class="fa fa-dashboard fa-fw"></i> ダッシュボード</a>
        </li>
        <li>
          <p><i class="fa fa-edit fa-fw"></i> コンテンツ管理</p>
          <ul class="nav nav-second-level">
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/page') ?>">個別ページ管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/entries') ?>">記事管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/files') ?>">ファイル管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/styles') ?>">style管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/scripts') ?>">script管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/templates') ?>">テンプレート管理</a>
            </li>
          </ul>
        </li>
        <li>
          <p><i class="fa fa-wrench fa-fw"></i> 設定</p>
          <ul class="nav nav-second-level">
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/settings') ?>">CMS設定</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/groups') ?>">ユーザーグループ管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/users') ?>">ユーザーアカウント管理</a>
            </li>
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/database') ?>">データベース管理</a>
            </li>
          </ul>
        </li>
        <li>
          <p><i class="fa fa-plug fa-fw"></i> プラグイン設定</p>
          <ul class="nav nav-second-level">
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/plugin') ?>">プラグイン一覧</a>
            </li>
          </ul>
        </li>
        <li>
          <p><i class="fa fa-book fa-fw"></i> ドキュメント</p>
          <ul class="nav nav-second-level">
            <li>
              <a href="<?php echo WelUtil::getUrl('admin/documents') ?>">関数リファレンス</a>
            </li>
            <?php
            // ここは割と固定的なページを表示すると思う。
            /*
              <li>
                <a href="#">テンプレート仕様</a>
              </li>
            */
            ?>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>