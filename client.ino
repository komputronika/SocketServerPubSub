#include <ESP8266WiFi.h>
#include <ArduinoJson.h>

const char* ssid     = "SSID";         // SSID
const char* password = "password123x"; // Password
const char* host = "172.104.49.134";   // IP address socket server
const int   port = 4444;               // Port socket server

WiFiClient client;
StaticJsonBuffer<512> jsonBuffer;

void setup() {
  Serial.begin(115200);
  Serial.print("Memulai koneksi ke WiFi: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi tersambung");  
  Serial.print("IP address anda: ");
  Serial.println(WiFi.localIP());

  if (!client.connect(host, port)) {
      Serial.println("Koneksi ke server socket gagal");
      return;
  }
  Serial.println("Tersambung dengan server");

  String url = "{\"action\":\"sub\",\"topic\":\"demo\"}\n";
  client.print( url );

}

void loop() {

    while(client.available()){
      String line = client.readStringUntil(10);
      JsonObject& root = jsonBuffer.parseObject(line);

      if (!root.success()) {
        Serial.println("Parsing JSON gagal");
        return;
      }
      const char* data = root["data"];
      Serial.print("Data masuk: ");
      Serial.println(data);
    }
}
