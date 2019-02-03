<?php

$host = "127.0.0.1";
$port = 4444;

$sub = '{"action":"sub","topic":"demo"}';
$pub = '{"action":"pub","topic":"demo", "data":"Mantap bro"}';

$fp = stream_socket_client("tcp://$host:$port", $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "$sub\n");
    sleep(2);
    fwrite($fp, "$pub\n");
    while (!feof($fp)) {
        echo fgets($fp, 1024);
    }
    fclose($fp);
}

?>