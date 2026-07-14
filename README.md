# MT_WebSocket — PHP WebSocket Library

A lightweight, production-ready WebSocket library for PHP. Real-time bidirectional communication with minimal overhead.

## ✨ Features
- **Full-duplex** — Server push + client messages
- **Event-driven** — Clean callback API
- **Low latency** — Optimized for real-time apps
- **Simple API** — Running in minutes

## 🚀 Example
```php
$server = new WebSocketServer('0.0.0.0', 8080);
$server->on('message', function($client, $data) {
    $client->send("Echo: $data");
});
$server->start();
```

## 🎯 Use Cases
Real-time chat, live notifications, game servers, IoT streaming

*Built by Mahmudul Hasan (bdlogicalerror)*