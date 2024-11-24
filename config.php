<?php
require_once __DIR__ . '/vendor/autoload.php';

// โหลดไฟล์ .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// อ่านค่าจาก .env
return [
    'channel_id' => $_ENV['CHANNEL_ID'],
    'channel_secret' => $_ENV['CHANNEL_SECRET'],
    'redirect_uri' => $_ENV['REDIRECT_URI'],
];
