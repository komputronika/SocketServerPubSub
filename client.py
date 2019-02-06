#!/usr/bin/env python3
import socket, json

HOST = '127.0.0.1'  # Alamat socket server
PORT = 4444         # Port server

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:

    s.connect((HOST, PORT))
    s.sendall('{"action":"sub","topic":"demo"}\n'.encode('UTF-8'))
    s.sendall('{"action":"pub","topic":"demo","data":"Test python"}\n'.encode('UTF-8'))

    data = s.recv(2048)

data = json.loads(data.decode('utf-8'))
print('JSON diterima:', data)
print('Data:', data["data"])
