<?php
$host = "127.0.0.1";
$port = 4444;

while (true) {
	$fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 30);
	if (!$fp) {
	    echo "$errstr ($errno)\n";
	} else {
		
		$time = time();
        $error = false;
		while (!$error) {
			// Publish data demo untuk keperluan testing
		    if ( (time() - $time) >= 2) {
		        $demo_data["action"] = "pub";
		        $demo_data["topic"]  = "demo";
		        $demo_data["data"]   = "Anda subscribe pada topik demo. Waktu: ".date("H:i:s");
		        $status =  @fwrite($fp, json_encode($demo_data)."\n");
				$time = time();
		        
		        if ($status === false) {
		        	$error = true;
		        }
		    }
		}
	}
	// Jeda sebentar, lalu ulang lagi untuk konek / loop while(true)
	sleep(10);
}
?>