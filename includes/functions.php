<?php
session_start();

function load_config()
{
    static $loaded = false;

    if ($loaded) {
        return true;
    }

    $config_path = __DIR__ . '/config.php';
    if (!file_exists($config_path)) {
        return false;
    }

    require_once $config_path;
    $loaded = true;
    return true;
}

function db()
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    if (!load_config()) {
        throw new Exception('Please run the installer first.');
    }

    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
        throw new Exception('Database connection settings are missing.');
    }

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($connection->connect_error) {
        throw new Exception($connection->connect_error);
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

function ensure_installed()
{
    if (!load_config() || !defined('INSTALL_LOCK') || INSTALL_LOCK !== '1') {
        header('Location: installer.php');
        exit;
    }
}

function is_logged_in()
{
    return !empty($_SESSION['user']);
}

function require_role($role)
{
    if (!is_logged_in()) {
        header('Location: /auth/login.php');
        exit;
    }

    if ($role && ($_SESSION['user']['role'] ?? '') !== $role) {
        header('Location: /index.php');
        exit;
    }
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function password_hash_value($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function password_verify_value($password, $hash)
{
    return password_verify($password, $hash);
}

function ensure_user_database_credentials($user_id)
{
    $conn = db();
    $conn->query(
        'CREATE TABLE IF NOT EXISTS user_databases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            db_name VARCHAR(64) NOT NULL,
            db_user VARCHAR(64) NOT NULL,
            db_password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB'
    );

    $stmt = $conn->prepare('SELECT db_name, db_user, db_password FROM user_databases WHERE user_id = ? LIMIT 1');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        return $row;
    }

    $db_name = 'freenetly_u' . (int) $user_id;
    $db_user = 'freenetly_u' . (int) $user_id;
    $db_password = bin2hex(random_bytes(16));

    $conn->query("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
    $conn->query("CREATE USER IF NOT EXISTS '{$db_user}'@'localhost' IDENTIFIED BY '{$db_password}'");
    $conn->query("GRANT ALL PRIVILEGES ON `{$db_name}`.* TO '{$db_user}'@'localhost'");
    $conn->query('FLUSH PRIVILEGES');

    $insert = $conn->prepare('INSERT INTO user_databases (user_id, db_name, db_user, db_password) VALUES (?, ?, ?, ?)');
    $insert->bind_param('isss', $user_id, $db_name, $db_user, $db_password);
    $insert->execute();

    return [
        'db_name' => $db_name,
        'db_user' => $db_user,
        'db_password' => $db_password,
    ];
}

function html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
