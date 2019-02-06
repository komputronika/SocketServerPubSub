<?php
/**
 * Socket Server Publish And Subscribe Dengan Format JSON
 *
 * Sebuah contoh socket server yang menerapkan metode
 * subscribe dan publish menggunakan format data JSON.
 *
 * Terinspirasi dari MQTT, namun lebih sederhana.
 * Bisa diterapkan untuk IoT, atau dikembangkan untuk
 * chat server, messenger dsb.
 *
 * @author     Komputronika <infokomputronika@gmail.com>
 * @link       https://github.com/komputronika/SocketServerPubSub
 */

// Konfigurasi
$config["verbose"] = true; 
$config["host"]    = "0.0.0.0"; 
$config["port"]    = "4444"; 

echo "JSON Socket Server\n";

// Mulai socket server pada port yang ditentukan: 4444
$server = stream_socket_server("tcp://".$config["host"].":".$config["port"], $errno, $errorMessage);

// Bila tidak dapat membuat socket, beritahukan error
if ($server === false) 
{
    die("Tidak dapat membuat socket. Error [$errno]: $errorMessage");
} else {
    echo "Aktif pada IP:".$config["host"].", port:".$config["port"]."\n";
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
    if ( !stream_select( $read_socks, $write, $except, 300000) )
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
            notes($config, "Koneksi baru dari " . stream_socket_get_name($new_client, true));

            // Tambahkan ke data client yang aktif
            $client_socks[] = $new_client;

            // Tampilkan informasi jumlah client yang aktif
            // echo "Jumlah client saat ini: ". count($client_socks). " client.\n";
        }
        
        // Hapus socket server dari daftar baca
        unset($read_socks[ array_search($server, $read_socks) ]);
    }
    
    // Proses data yang diterima dari semua client
    foreach($read_socks as $sock)
    {
        // Default jenis socket adalah tcp
        $type = "tcp";

        // Baca data dari client
        $data = fread($sock, 1024);
        
        // Bila tidak ada data masuk
        if( empty($data) )
        {
            // Hapus dari daftar client dan tutup koneksi
            unset($client_socks[array_search($sock, $client_socks)]);
            @fclose($sock);

            // Tampilkan informasi
            notes($config, "Sebuah client terputus, jumlah sekarang: ". count($client_socks) . " client");

            // Lanjutkan ke client berikutnya
            continue;

        // Bila ada data masuk  
        } else {

            // Ubah data JSON dari client menjadi array
            $client_info = stream_socket_get_name($sock,true);

            //echo "Data masuk dari $client_info: ".$data;
            notes($config, "Data masuk dari $client_info");

            // Pecah data bila ada string JSON lebih dari 1
            $datas = explode("\n",$data);

            // Loop pada semua string JSON hasil pecahan
            foreach($datas as $data) {

                if (empty($data)) continue;

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

                } else {

                    // Asumsi bila awalan data bukan '{', bukan JSON, 
                    // maka itu adalah data encoded dari websocket
                    if (strpos($data,"{")===false) {
                        // Decode data websocket dari client
                        $data = decode($data);
                        // Set jenis clientnya adalah web = websocket
                        $type = "web";
                    }
                }

                // Ubah string data menjadi JSON
                $json = (array) @json_decode($data);
                //print_r($json);

                // Periksa apakah hasil decode benar dan menjadi array
                if (count($json)) 
                {
                    // Contoh string subscribe dari client
                    // {"action":"sub", "topic":"relay"}

                    // Bila action adalah 'sub', masukan client ke array subscriber
                    if ($json["action"] == "sub") {

                        if (!empty($json["topic"]))
                        {
                            notes($config, "Type = $type");
                            $topic = $json["topic"];
                            $client_data = array( "type" => $type, "socket" => $sock, "info" => $client_info );
                            $subscriber["$topic"][] = $client_data;
                            
                            notes($config, "Subscriber baru pada topik <$topic>");

                            // Tampilkan data subscriber
                            notes($config, "Data subscriber sekarang:");

                            $n = 1;
                            // Loop pada semua subscriber, semua topic
                            foreach($subscriber as $topic => $sub) {
                                // Tampilkan jumlah subscriber
                                notes($config, "$n) $topic => ".count($sub)." client");
                            }

                        } else {

                            notes($config, "Parameter topic belum diisi");
                        }
                    } 

                    // Contoh string data publish dari client:
                    // Data 1 dimensi =>  {"action":"pub", "topic":"relay", "data": "Hello World"}
                    // Data berdimensi banyak => {"action":"pub", "topic":"kebunku", "data": 
                    //                           { "suhu":38, "kelembaban":60 } }

                    // Bila action adalah 'pub', publish data ke semua client yang subscribe
                    if (@$json["action"] == "pub") {

                        if (!empty($json["topic"]))
                        {
                            // Ambil data topic nya                 
                            $topic = $json["topic"];
                            
                            // Buat array dengan key 'data' 
                            $broadcast_data["data"] = $json["data"];

                            // Publish ke semua subscriber dalam string JSON
                            notes($config, "Publish baru pada topik <$topic>");
                            $subscriber = publish($subscriber, $topic, json_encode($broadcast_data)."\n", $sent );
                            notes($config, "Selesai mengirim publish pada topik <$topic> ke $sent subscriber");

                        } else {

                            notes($config, "Parameter topic belum diisi");
                        }

                        // Contoh format yang diterima client / subscriber
                        // Data 1 dimensi =>  {"data": "Hello World"}
                        // Data berdimensi banyak => {"data": { "suhu":42, "kelembaban":60 } } 
                    }

                } else {

                    // Kemungkinan bukan format JSON atau format JSON salah
                    notes($config, "Data masuk bukan format JSON");
                }

            } // foreach($datas...

        } // if (empty($data...

    } // foreach($read_socks...

} // while(true...

// Tutup socket 
socket_close($server);


/**
 * Mempublish data ke semua socket pada sebuah topic
 *
 * @param  array  $socket Daftar scubsriber  
 * @param  string $topic  Topik yang akan kirim
 * @return array  Subscriber 
 */
function publish($sockets, $topic, $data, &$sent) {
    // Untuk semua element pada variable $socket
    if (@count($sockets["$topic"])<1) return;

    $n = 0;
    $sent = 0;
    foreach($sockets["$topic"] as $sock) {
        // Tulis data JSON ke resource socket 
        if ($sock["type"]=="tcp") {
            // Untuk socket normal, tidak ada header apapun
            $response = $data;
        } else {
            // Untuk WebSocket, ada header
            //$response = chr(129) . chr(strlen($data)) . $data;
            $response = encode($data);
        }

        // Kirim data ke client
        $status = @fwrite($sock["socket"], $response);

        // Bila gagal mengirim data ke client ini
        if ($status === false) {
            // Hapus client dari daftar subscriber
            unset($sockets["$topic"][$n]);
        } else { 
            // Hitung jumlah real yang terkirim
            $sent++; 
        }
        $n++;
    }

    // Return array subscriber yang sudah dibersihkan
    return $sockets;
}

/**
 * Decode data dari websocket
 *
 * @param  string $data Data yang diterima dari WebSocket
 * @return string
 */
function decode($data) {
    $bytes = $data;
    $data_length = "";
    $mask = "";
    $coded_data = "" ;
    $decoded_data = "";
    $data_length = $bytes[1] & 127;
    if($data_length === 126){
        $mask = substr($bytes, 4, 8);
        $coded_data = substr($bytes, 8);
    }else if($data_length === 127){
        $mask = substr($bytes, 10, 14);
        $coded_data = substr($bytes, 14);
    }else{
        $mask = substr($bytes, 2, 6);
        $coded_data = substr($bytes, 6);
    }
    for($i=0;$i<strlen($coded_data);$i++){
        $decoded_data .= $coded_data[$i] ^ $mask[$i%4];
    }

    return $decoded_data;
}

/**
 * Encode data untuk dikirim via websocket
 *
 * @param  string $data Data yang akan dikirimkan ke WebSocket dalam format teks JSON  
 * @return string
 */
function encode($data) {
    // 0x1 text frame (FIN + opcode)
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($data);

    if($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif($length > 125 && $length < 65536)
        $header = pack('CCS', $b1, 126, $length);
    elseif($length >= 65536)
        $header = pack('CCN', $b1, 127, $length);

    return $header.$data;
}

/**
 * Menampilkan pesan di console 
 *
 * @param  string $config Konfigurasi  
 * @param  string $str Teks yang akan ditampilkan  
 * @return void
 */
function notes($config, $str) {
    if (@$config["verbose"]) {
        echo date("Y-m-d H:i:s")." ".$str."\n";
    }
}

//--- akhir Script 