<?php

return [
    'host' => env('MQTT_HOST', 'localhost'),
    'port' => env('MQTT_PORT', 1883),
    'username' => env('MQTT_USERNAME', ''),
    'password' => env('MQTT_PASSWORD', ''),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel_mqtt_client'),
    'keep_alive' => env('MQTT_KEEP_ALIVE', 60),
    'connect_timeout' => env('MQTT_CONNECT_TIMEOUT', 3),
    'use_tls' => env('MQTT_USE_TLS', false),
    'tls_self_signed_allowed' => env('MQTT_TLS_SELF_SIGNED_ALLOWED', true),
]; 