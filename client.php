<?php

$host = "vps.komputronika.com";
$port = 4444;

$sub1 = '{"action":"sub","topic":"demo"}';
$sub2 = '{"action":"sub","topic":"time"}';

$fp = stream_socket_client("tcp://$host:$port", $errno, $errstr, 60);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    fwrite($fp, "$sub1\n");
    sleep(2);
    fwrite($fp, "$sub2\n");
    while (!feof($fp)) {
        echo fgets($fp, 2048);
    }
    fclose($fp);
}

?>