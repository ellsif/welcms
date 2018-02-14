<?php
$errors = $errors ?? [];
?><!DOCTYPE html>
<html lang="ja-JP">
  <head>
  </head>
  <body>
    <div id="wrapper">
      <div id="page-wrapper">
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">System Error</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="panel panel-default">
              <div class="panel-body">
                <?php foreach($errors as $error): ?>
                  <p><?php echo htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php endforeach; ?>
                <?php if (isset($e)) : ?>
                  <p><?php echo $e->getMessage(); ?></p>
                <?php endif ?>
                <pre><?php if (isset($e)) echo $e->getTraceAsString(); ?></pre>

                <?php foreach(\ellsif\WelCMS\welPocket()->getLogger()->getHistory() as $log) : ?>
                  <p><?php echo $log; ?></p>
                <?php endforeach ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
