<?php

// 最初にBUILT-IN WEB SERVERを起動
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    WEB_SERVER_DOCROOT
);

// Execute the command and store the process ID
$output = array();
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
        '%s - Web server started on %s:%d with PID %d',
        date('r'),
        WEB_SERVER_HOST,
        WEB_SERVER_PORT,
        $pid
    ) . PHP_EOL;

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});


// サーバが起動するまで待つ
do {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $f = curl_exec($ch);
    curl_close($ch);
    sleep(1);
} while($f);

require_once '../vendor/autoload.php';