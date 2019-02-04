# Socket Server Publish dan Subscribe 

Sebuah contoh script sederhana membuat socket server dengan metode publish dan subscribe, terinspirasi dari MQTT.

Format data yang digunakan adalah JSON dan bisa digunakan untuk IoT misalnya dengan Arduino dan board yang menggunakan chip ESP8266 dan sebagainya.

Mudah dipelajari, mudah dikembangkan, lalu-lintas data lebih kecil dibandingkan menggunakan protokol HTTP karena data yang dikirimkan berupa JSON murni tanpa header.

Sudah mendukung WebSocket, sehingga client bisa subscribe dan publish secara realtime melalui web browser / Javascript.

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
Anda memerlukan PHP untuk menjalakan script ini. Ketik seperti ini pada terminal anda.

    php socket-server

## Menghubungkan Client Terhubung
Untuk mengetes socket server ini, silahkan jalankan telnet pada windows/tab terminal terpisah:

    telnet 127.0.0.1 4444
    Trying 127.0.0.1...
    Connected to 127.0.0.1.
    Escape character is '^]'.

Setelah itu, ketiklah seperti ini baris per baris:

    {"action":"sub","topic":"demo"}
    {"action":"pub","topic":"demo","data":"Mantap brooo..."}

Anda akan mendapatkan balasan di halaman telnet seperti ini (karena baru saja subscribe pada topik 'demo').

    {"data":"Mantap brooo..."}
    
## Monitoring Pada Server
Pada terminal server akan dilihat log sebagai berikut:

    Koneksi baru dari 127.0.0.1:45580
    Jumlah client saat ini: 1 client.
    Data masuk dari 127.0.0.1:45580: {"action":"sub","topic":"demo"}
    Subscriber baru pada topik <DEMO>
    Data subscriber sekarang:
    1) demo => 1 client
    Data masuk dari 127.0.0.1:45580: {"action":"pub","topic":"demo","data":"Mantap brooo..."}
    Publish baru pada topik <DEMO>
    Selesai mengirim publish pada topik <DEMO> ke 1 subscriber
    
Log di atas memudahkan kita melakukan debug pada script dan lalu-lintas data.

## Menjalankan Script di Server 
Bila ingin membuat server socket ini menjadi online dan dapat diakses secara publik, 
maka perlu sebuah server di rumah/kantor yang online, atau dengan menyewa sebuah VPS.

Rekomendasi VPS: [DigitalOcean](https://m.do.co/c/2fa14040d118), [Vultr](https://www.vultr.com/?ref=7830794-4F)

Untuk menjalankan script secara background, silahkan buka terminal server dan ketik perintah sebagai berikut:

    nohup php socket-server.php &

Dengan `nohup`, server akan menjalankan script php tersebut secara background, script tidak akan stop walaupun sudah logout dari terminal. 

## Pengembangan
Script ini sedang dikembangkan, tutorial pada blog segera menyusul. Bila menemukan error atau ada usulan, silahkan kirimkan Issue. Bila ingin membantu pengembangan silahkan Pull Request.

## Todo
1. Handshaking pada WebSocket, decode data yang dikirim dari browser (**beres**)
2. Contoh sketch untuk Arduino, ESP8266 (NodeMCU, dsb) (**beres**)
3. Contoh program dengan Python, NodeJS, C/C++, Android
4. Menghapus client yang sudah terputus dari daftar subscriber  
5. Memproses string dari client yang dipisahkan \n 
6. Penerapan security?
