<?php
$host = "socket.komputronika.com";
$port = 4444;

while (true) {
    $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 120);
    if ($fp) {
        $time  = time();
        $error = false;
        while (!$error) {
            // Publish data demo untuk keperluan testing
            $demo_data["action"] = "pub";
            $demo_data["topic"]  = "time";
            $demo_data["data"]   = "Waktu sekarang: " . date("H:i:s");
            $status              = @fwrite($fp, json_encode($demo_data) . "\n");
            $time                = time();

            if ($status === false) {
                $error = true;
                @fclose($fp);
            }
            sleep(3);
        }
        // Jeda sebentar, lalu ulang lagi untuk konek / loop while(true)
        sleep(5);
    }
}
