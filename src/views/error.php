<?php
header("HTTP/1.1 404 Not Found");
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
