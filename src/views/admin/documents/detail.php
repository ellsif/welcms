<?php
  namespace ellsif;
  $config = WelCMS\Config::getInstance();

  // ページ表示用のデータを取得
  $doc = new Document();
  $file = $docPath ?? '';
  $document = $doc->getData($config->dirSystem() . $file);

  // CSS、JSを追加
  $config->addCssAfter('system/assets/vendor/highlight/styles/tomorrow.css');
  $config->addVarFooterJsAfter('system/assets/vendor/highlight/highlight.pack.js');
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
    <?php include dirname(__FILE__, 2) . '/head.php' ?>
  </head>
  <body>
    <div id="wrapper">
      <?php include dirname(__FILE__, 2) . '/nav.php' ?>

      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">関数リファレンス</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-heading">
                ファイル： <?php echo $file ?>
              </div>
              <div class="panel-body">
                <?php if ($document) { ?>
                  <?php if ($document['class']) { ?>
                    <div class="doc-entry">
                      <h1><?php echo $document['class']['name'] ?></h1>
                      <p><?php echo $document['class']['description'] ?></p>
                      <pre class="doc-define"><?php echo $document['class']['define'] ?></pre>
                      <div class="doc-body">
                        <?php echo $document['class']['body'] ?>
                        <h2>メソッド一覧</h2>
                        <table width="100%" class="table table-striped table-bordered table-hover">
                          <?php foreach ($document['functions'] as $func): ?>
                            <?php if ($func['scope'] === 'public'): ?>
                              <tr>
                                <th><a href="#t_<?php echo $func['function'] ?>"><?php echo $func['function'] ?></a></th>
                                <td><?php echo $func['description'] ?></td>
                              </tr>
                            <?php endif; ?>
                          <?php endforeach; ?>
                        </table>
                      </div>
                    </div>
                  <?php } ?>
                  <?php foreach ($document['functions'] as $func): ?>
                    <?php if ($func['scope'] === 'public' || empty($func['scope'])): ?>
                      <div class="doc-entry">
                        <h3 id="t_<?php echo $func['function'] ?>"><?php echo $func['function'] ?></h3>
                        <p><?php echo $func['description'] ?></p>
                        <pre class="doc-define"><?php echo $func['define'] ?></pre>
                        <div class="doc-body">
                          <?php echo $func['body'] ?>
                          <?php if ($func['code']): ?>
                            <h2>ソースコード</h2>
                            <pre class="doc-code"><code class="php"><?php echo htmlspecialchars($func['code']) ?></code></pre>
                          <?php endif;?>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include dirname(__FILE__, 2) . "/foot_js.php" ?>
    <script>
      $(document).ready(function() {
        $('.doc-code,.doc-define').each(function(i, block) {
          hljs.highlightBlock(block);
        });
      });
    </script>
  </body>
</html>
