<?php
// Load environment variables (if using .env)
require 'vendor/autoload.php'; // Make sure you have vlucas/phpdotenv installed via Composer

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// PostgreSQL credentials from Render
$host = "dpg-d2mabb7diees73e61k3g-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "data_j4be";
$user = "data_j4be_user";
$password = "Bt0ZBgsg3RcPaytjpCI5WWmC0wTHm0Gs";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    // Connection successful
    // echo "Connected to PostgreSQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Cloudinary setup
\Cloudinary::config([
    "cloud_name" => $_ENV['CLOUDINARY_CLOUD_NAME'],
    "api_key"    => $_ENV['CLOUDINARY_API_KEY'],
    "api_secret" => $_ENV['CLOUDINARY_API_SECRET']
]);
?>
