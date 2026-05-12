<?php

declare(strict_types=1);

date_default_timezone_set('Indian/Mauritius');

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'smart_tiles_db';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Database connection failed. Please import database/schema.sql in phpMyAdmin.');
}

mysqli_set_charset($conn, 'utf8mb4');

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$firstSegment = explode('/', trim($scriptDir, '/'))[0] ?? '';

if (!defined('BASE_URL')) {
    define('BASE_URL', $firstSegment !== '' ? '/' . $firstSegment : '');
}

