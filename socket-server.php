<?php
/*================================================================*\

	SOCKET SERVER PUBLISH AND SUBSCRIBE DENGAN FORMAT JSON

	Sebuah contoh socket server yang menerapkan metode
	subscribe dan publish menggunakan format data JSON.

	Terinspirasi dari MQTT, namun lebih sederhana.
	Bisa diterapkan untuk IoT, atau dikembangkan untuk
	chat server, messenger dsb.

 	Author  : Komputronika
 	Website : www.komputronika.com
 	Email   : infokomputronika@gmail.com
 	Library : Standar PHP
 	Source  : https://github.com/komputronika/SocketServerPubSub

\*================================================================*/

// Mulai socket server pada port yang ditentukan: 4444
$server = stream_socket_server("tcp://0.0.0.0:4444", $errno, $errorMessage);

// Bila tidak dapat membuat socket, beritahukan error
if ($server === false) 
{
	die("Tidak dapat membuat socket. Error [$errno]: $errorMessage");
}

// Variable untuk menyimpan semua data subscriber
// Format array nya adalah:
// $subscriber['topic1'][ 
//                         client1,
//                         client2,
//                         ... 
//                         ]
// $subscriber['topic2'][ 
//                         client11,
//                         client22,
//                         ... 
//                      ]
$subscriber = array();

// Variable untuk menyimpan data client yang terkoneksi
$client_socks = array();

// Jalankan terus-menerus, sampe klenger
while(true)
{
	// Menyimpan socket yang aktif
	$read_socks = $client_socks;
	$read_socks[] = $server;
	
	// Variable parameter, tidak digunakan isi dengan null
    $write = null;
    $except = null;
	
	// Mulai membaca lalu-lintas, gunakan timeout yang besar
	if ( !stream_select( $read_socks, $write, $except, 200000) )
	{
		// Tidak ada lalu-lintas, ulangi ke awal
		continue;
	}
	
	// Apakah ada client yang baru terkoneksi
	if(in_array($server, $read_socks))
	{
		// Bila ya, terima koneksi
		$new_client = stream_socket_accept($server);
		
		// Bila berhasil terkoneksi, lanjutkan
		if ($new_client) 
		{
			// Ini informasi IP and port client tersebut
			echo 'Koneksi baru dari ' . stream_socket_get_name($new_client, true) . "\n";

			// Tambahkan ke data client yang aktif
			$client_socks[] = $new_client;

			// Tampilkan informasi jumlah client yang aktif
			echo "Jumlah client saat ini: ". count($client_socks). " client.\n";
		}
		
		// Hapus socket server dari daftar baca
		unset($read_socks[ array_search($server, $read_socks) ]);
	}
	
	// Proses data yang diterima dari semua client
	foreach($read_socks as $sock)
	{
		// Baca data dari client
		$data = fread($sock, 1024);
		
		// Bila tidak ada data masuk
		if( empty($data) )
		{
			// Hapus dari daftar client dan tutup koneksi
			unset($client_socks[array_search($sock, $client_socks)]);
			@fclose($sock);

			// Tampilkan informasi
			echo "Sebuah client terputus, jumlah sekarang: ". count($client_socks) . " client.\n";

			// Disini harus hapus dari daftar subcriber

    		// Lanjutkan ke client berikutnya
			continue;

		// Bila ada data masuk	
		} else {

			// Ubah data JSON dari client menjadi array
			$client_info = stream_socket_get_name($sock,true);
			
			//echo "Data masuk dari $client_info: $data";
			//echo "Data masuk dari $client_info: ".unmask($data);
			echo "Data masuk dari $client_info\n";

			// Bila koneksi datang dari WebSocket 
			if (strpos($data, "HTTP")!==false ) {

				// Baca data header
				preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $data, $matches);
				$key = base64_encode(pack(
				    'H*',
				    sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
				));

				// Buat response handshaking untuk browser
				$headers = "HTTP/1.1 101 Switching Protocols\r\n";
				$headers.= "Upgrade: websocket\r\n";
				$headers.= "Connection: Upgrade\r\n";
				$headers.= "Sec-WebSocket-Version: 13\r\n";
				$headers.= "Sec-WebSocket-Accept: $key\r\n\r\n";

				// Kirim response handshaking ke browser
				fwrite($sock,$headers);

				// Ini adalah topic yang disubscribe
				// Format url => ws://host:port/topic
				// Misal => ws://127.0.0.1/demo

				// Temukan nama topic dari header
				preg_match('/GET \/(\w*)/', $data, $found);
				$topic = $found[1];

				// Karena ini adalah WebSocket, tentukan jenisnya adalah 'web'
				$client_data = array( "type" => "web" , "socket" => $sock, "info" => $client_info );
				$subscriber["$topic"][] = $client_data;

			}

			// Ubah string data menjadi JSON
			$json = (array) @json_decode($data);

			// Periksa apakah hasil decode benar dan menjadi array
			if (count($json)) 
			{
				// Contoh string subscribe dari client
				// {"action":"sub"}

				// Bila action adalah 'sub', masukan client ke array subscriber
				if ($json["action"] == "sub") {

					if (!empty($json["topic"]))
					{
						$topic = $json["topic"];
						$client_data = array( "type" => "tcp", "socket" => $sock, "info" => $client_info );
						$subscriber["$topic"][] = $client_data;
						echo "Subscriber baru pada topik <".strtoupper($topic).">\n";

						// Tampilkan data subscriber
						echo "Data subscriber sekarang:\n";

						$n = 1;
						// Loop pada semua subscriber, semua topic
						foreach($subscriber as $topic => $sub) {
							// Loop pada semua subscriber, topic ini
							/*foreach ($sub as $s) {
								echo "$n) $topic => ".$s["info"]."\n";
								$n++;
							}*/
			    			echo "$n) $topic => ".count($sub)." client\n";
						}

					} else {

						echo "Parameter topic belum diisi\n";
					}
				} 

				// Contoh string data publish dari client:
				// Data 1 dimensi =>  {"action":"pub", "data": "Hello World"}
				// Data berdimensi banyak => {"action":"pub", "data": { "suhu":42, "kelembaban":60 } }

				// Bila action adalah 'pub', publish data ke semua client yang subscribe
				if ($json["action"] == "pub") {

					if (!empty($json["topic"]))
					{
						// Ambil data topic nya					
						$topic = $json["topic"];
						
						// Buat array dengan key 'data' 
						$broadcast_data["data"] = $json["data"];

						// Publish ke semua subscriber dalam string JSON
						echo "Publish baru pada topik <".strtoupper($topic).">\n";
						publish($subscriber, $topic, json_encode($broadcast_data)."\n" );
					
					} else {

						echo "Parameter topic belum diisi\n";
					}

					// Contoh format yang diterima client / subscriber
					// Data 1 dimensi =>  {"data": "Hello World"}
					// Data berdimensi banyak => {"data": { "suhu":42, "kelembaban":60 } } 
				}

			} else {

				// Kemungkinan bukan format JSON atau format JSON salah
				echo "Data masuk bukan format JSON\n";
			}
		}
	}
}

// Tutup socket (tidak perlu, hanya formalitas)
socket_close($server);

//--------------------------------------------
// Fungsi untuk mempublish ke semua socket
// Parameter:
// $socket: Array yang berisi target socket masing-masing berisi 
//          dua key, yaitu 'socket' dan 'info'. 
// $topic:  String topic yang menjadi target publish
// $data:   String JSON yang akan dipublish
//--------------------------------------------
function publish($sockets, $topic, $data) {
	// Untuk semua element pada variable $socket
	$n = 0;
	foreach($sockets["$topic"] as $sock) {
		// Tulis data JSON ke resource socket 
		if ($sock["type"]=="tcp") {
			// Untuk socket normal, tidak ada header apapun
            $response = $data;
	    } else {
	    	// Untuk WebSocket, ada header
			$response = chr(129) . chr(strlen($data)) . $data;
	    }

	    // Kirim data ke client
        @fwrite($sock["socket"], $response);

		$n++;
	}
	echo "Selesai mengirim publish pada topik <".strtoupper($topic)."> ke $n subscriber\n";
}

function unmask($payload) {
	$length = ord($payload[1]) & 127;

	if($length == 126) {
		$masks = substr($payload, 4, 4);
		$data = substr($payload, 8);
	}
	elseif($length == 127) {
		$masks = substr($payload, 10, 4);
		$data = substr($payload, 14);
	}
	else {
		$masks = substr($payload, 2, 4);
		$data = substr($payload, 6);
	}

	$text = '';
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}