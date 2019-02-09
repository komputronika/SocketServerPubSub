<?php
$host = "127.0.0.1";
$port = 4444;

while (true) {
    $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 120);
    if ($fp) {
        $error = false;
        while (!$error) {
            // Publish data demo untuk keperluan testing
            $demo_data["action"] = "pub";
            $demo_data["topic"]  = "demo";
            $demo_data["data"]   = array("suhu"=>rand(20,30),"kelembaban"=>rand(30,80));
            $status              = fwrite($fp, json_encode($demo_data)."\n");

            print $status;
            print "\n";
            print json_encode($demo_data);
            print "\n";

            if ($status === false) {
                $error = true;
                @fclose($fp);
            } else {
                print "Send OK\n";
            }
            sleep(3);
        }
        // Jeda sebentar, lalu ulang lagi untuk konek / loop while(true)
        sleep(5);
    }
}
