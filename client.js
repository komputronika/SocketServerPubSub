var net = require('net');

var client = new net.Socket();
client.connect(4444, '127.0.0.1', function() {
    
    sub = '{"action":"sub","topic":"demo"}\n';  
    pub = '{"action":"pub","topic":"demo","data":"Test NodeJS"}\n';

    console.log('Subscriber ke server');
    console.log(sub);
    client.write(sub);


    console.log('Publish ke server');
    console.log(pub);
    client.write(pub);
});

client.on('data', function(data) {
    console.log('Data diterima: ' + data);
    client.destroy(); 
});

client.on('close', function() {
    console.log('Koneksi terputus');
});