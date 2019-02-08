<?php

$host = "socket.komputronika.com";
$port = 4444;

$sub = '{"action":"sub","topic":"demo"}';
$pub = '{"action":"pub","topic":"demo", "data":"Mantap bro"}';

$fp = stream_socket_client("tcp://$host:$port", $errno, $errstr, 30);

fwrite($fp, "$sub\n");
fwrite($fp, "$pub\n");

while (true) {
    $changes = array($fp);
    stream_select($changes, $write, $except, 300000);
    foreach ($changes as $sock) {
        if ($sock == $fp) {
            $data = trim ( fread($sock, 2048), " \t\r\0\x0B" );
            echo "$data";
        }
    }
}
