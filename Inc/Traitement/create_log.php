<?php 
// create log file
$logFile = 'log.txt';
$logMessage = date('Y-m-d H:i:s') . ' - ' . $_SERVER['REMOTE_ADDR'] . ' - ' . $_SERVER['REQUEST_URI'] . ' - ' . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;
file_put_contents($logFile, $logMessage, FILE_APPEND);
;?>