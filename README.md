# Socket Server Publish dan Subscribe 

Sebuah script untuk membuat socket server dengan metode publish dan subscribe, terinspirasi dari MQTT.

Format data yang digunakan adalah JSON dan bisa digunakan untuk IoT misalnya dengan Arduino dan board yang menggunakan chip ESP8266 dan sebagainya.

Mudah dipelajari, mudah dikembangkan, lalu-lintas data lebih kecil dibandingkan menggunakan protokol HTTP karena data yang dikirimkan berupa JSON murni tanpa header.

Sudah mendukung WebSocket, sehingga client bisa subscribe dan publish secara realtime melalui web browser / Javascript.

<img src="http://i63.tinypic.com/nzovfs.png"
     alt="JSON Socket Server"
     style="width:100%" />

## Format Data

#### Subscribe
Kirimkan string seperti di bawah ini untuk melakukan subscribe pada sebuah topik.
    
    {"action":"sub", "topic":"nama_topic"}

#### Publish
Kirimkan string seperti di bawah ini untuk mem-publish data baru ke subscriber. 

    {"action":"pub", "topic":"nama_topic", "data": "teks data"}
    
    // atau
    
    {"action":"pub", "topic":"nama_topic", "data": {"var1":val1, "var2":val2, ...} }
#### Data dari Server   
Ini adalah format data yang akan diterima oleh client pada saat ada publish baru pada topik yang diikuti.

    {"data": "teks data"}
    
    // atau
    
    {"data": {"var1":val1, "var2":val2, ...} }  

## Cara Menjalankan Server
Anda memerlukan PHP untuk menjalakan script ini. Ketik seperti ini pada terminal:

    php socket-server.php

## Menghubungkan Client ke Server
Untuk mengetes socket server ini, silahkan jalankan telnet pada windows/tab terminal terpisah:

    telnet 127.0.0.1 4444

Setelah itu, ketiklah seperti ini baris per baris:

    {"action":"sub","topic":"relay"}
    {"action":"pub","topic":"relay","data":"on"}

Bila koneksi berhasil, anda akan mendapatkan balasan di halaman telnet seperti ini (karena baru saja subscribe pada topik 'relay'):

    {"data":"on"}

## Contoh Program Client
Pada repo ini sudah juga sudah disertakan contoh-contoh program client dengan PHP, Python, NodeJS, C, Arduino, App Inventor. Contoh client dibuat sederhana agar potongan-potongan kode client tersebut mudah disisipkan pada proyek utama anda. 
    
## Monitoring Pada Server
Pada terminal server akan dilihat log sebagai berikut:

    JSON Socket Server
    Aktif pada IP:0.0.0.0, port:4444
    2019-02-06 08:25:06 Koneksi baru dari 127.0.0.1:52136
    2019-02-06 08:25:06 Data masuk dari 127.0.0.1:52136
    2019-02-06 08:25:06 Type = tcp
    2019-02-06 08:25:06 Subscriber baru pada topik <relay>
    2019-02-06 08:25:06 Data subscriber sekarang:
    2019-02-06 08:25:06 1) relay => 1 client
    2019-02-06 08:25:06 Publish baru pada topik <relay>
    2019-02-06 08:25:06 Selesai mengirim publish pada topik <relay> ke 1 subscriber
    2019-02-06 08:25:06 Sebuah client terputus, jumlah sekarang: 0 client
    
Log di atas memudahkan kita melakukan debug pada script dan untuk melihat lalu-lintas data.

Bila tidak ingin menampilkan log, atur konfigurasi seperti ini:

    $config["verbose"] = false;

## Menjalankan Script di Server 
Bila ingin membuat server socket ini menjadi online dan dapat diakses secara publik, 
maka perlu sebuah server di rumah/kantor yang selalu online, atau dengan menyewa sebuah VPS.

**Rekomendasi VPS:**

[![Time4VPS](http://i65.tinypic.com/2cfp1te.png)](https://www.time4vps.com/?affid=1643) [![Digital Ocean](http://i68.tinypic.com/122embb.png)](https://m.do.co/c/2fa14040d118) [![Vultr](http://i64.tinypic.com/2pzctts.png)](https://www.vultr.com/?ref=7830794-4F)

Untuk menjalankan script secara background, silahkan buka terminal server dan ketik perintah sebagai berikut:

    nohup php socket-server.php &

Dengan `nohup`, server akan menjalankan script php tersebut secara background, script tidak akan stop walaupun user sudah logout dari terminal. 

## Pengembangan
Bila menemukan error atau ada usulan, silahkan kirimkan Issue. Bila ingin berkontribusi silahkan fork, modifikasi dan buat Pull Request.

## Todo
1. Handshaking pada WebSocket, decode data yang dikirim dari browser (**beres**)
2. Menghapus client yang sudah terputus dari daftar subscriber (**beres**) 
3. Memproses multi-line string JSON dari client (yang dipisahkan \n) (**beres**)
4. Contoh sketch untuk Arduino, ESP8266 (NodeMCU) (**beres**)
5. Client test dengan PHP (**beres**)
6. Client test dengan Python (**beres**)
7. Client test dengan NodeJS (**beres**)
8. Client test dengan Android (**beres**)
9. Client test dengan C/C++ - Pakai library cJSON (**beres**)
10. Contoh aplikasi nyata dengan Relay, Android App, Website
11. Membuat tutorial di blog
13. Membuat video demo
14. Proteksi publish dengan token?
