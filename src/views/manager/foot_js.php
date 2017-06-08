<?php
$urlInfo = \ellsif\WelCMS\Pocket::getInstance()->varUrlInfo();
$_port = $urlInfo['port'];
if ($_port != 80) {
  $_port = ":${_port}";
} else {
  $_port = '';
}
$config = \ellsif\WelCMS\Pocket::getInstance();
?>

<!-- jQuery -->
<script src="//<?php echo $urlInfo['host'] ?><?php echo $_port ?>/assets/admin/vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="//<?php echo $urlInfo['host'] ?><?php echo $_port ?>/assets/admin/vendor/bootstrap/js/bootstrap.min.js"></script>

<script src="//<?php echo $urlInfo['host'] ?><?php echo $_port ?>/assets/admin/js/appendAdmin.js"></script>

<?php foreach($config->varFooterJsAfter() as $js): ?>
  <script src="//<?php echo $urlInfo['host'] ?><?php echo $_port ?>/<?php echo $js ?>"></script>
<?php endforeach; ?>
