<?php

const SESSION_KEY = "0d000721";

function padKey(string $key)
{
    return str_pad(substr($key, 0, 32), 32, "\0");
}

enum LogLevel: string
{
    case INFO = 'INFO';
    case ERROR = 'ERROR';
}

function writeLog(string $message, LogLevel $level = LogLevel::INFO)
{
    $file = dirname(__FILE__, 2) . '/app.log';
    if ($level === LogLevel::ERROR) {
        $file = dirname(__FILE__, 2) . '/error.log';
    }
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level->value] $message" . PHP_EOL;
    file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
}

function encrypt(string $data, string $key)
{
    $key = padKey($key);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt(string $data, string $key)
{
    $key = padKey($key);
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

function json_data(bool $succ, string $msg, mixed $data = null)
{
    header('Content-Type: application/json');
    return json_encode(['succ' => $succ, 'msg' => $msg, 'data' => $data]);
}

function json_err(string $msg, int $code, bool $exit = true)
{
    http_response_code($code);
    echo json_data(false, $msg, null);
    if ($exit) {
        exit();
    }
}

class JsonServ
{
    public string $method;
    public mixed $json_succ_call;
    public mixed $json_fail_call;
    public function __construct(
        string $method,
        callable $json_succ_call,
        callable $json_fail_call = null
    ) {
        $this->method = $method;
        $this->json_succ_call = $json_succ_call;
        $this->json_fail_call = $json_fail_call ?? function ($msg) {
            echo json_data(false, $msg, null);
        };
    }
}

/**
* @param JsonServ[] $services
*/
function json_service(
    array $services,
) {
    foreach ($services as $serv) {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === $serv->method) {
            // GET method
            if ($method === 'GET' || $method === 'DELETE') {
                ($serv->json_succ_call)($_GET);
                return;
            }

            $contentType = $_SERVER['CONTENT_TYPE'];
            writeLog("content type: $contentType");

            if (strpos($contentType, 'application/json') !== false) {
                $data = file_get_contents('php://input');
                $json = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    ($serv->json_succ_call)($json);
                } else {
                    ($serv->json_fail_call)(json_last_error_msg());
                }
                return;
            } elseif ($method === 'POST') {
                ($serv->json_succ_call)($_POST);
                return;
            }
        }
    }
}

function mysql_conn()
{

    $host = 'localhost';
    $db = 'website';
    $user = 'server';
    $pass = '134679852';

    return new mysqli($host, $user, $pass, $db);
}

function check_password(string $password, string $input_username)
{
    if (! (strlen($password) >= 5 && strlen($password) <= 20 && preg_match('/^[a-zA-Z0-9_]+$/', $password))) {
        writeLog("[user: $input_username]Password format error", LogLevel::ERROR);
        json_err("密码格式错误", 400);
    }
}

function check_captcha(string $input_captcha, string $input_username)
{
    if (strcasecmp($input_captcha, $_SESSION['captcha']) !== 0) {
        writeLog("[user: $input_username]Captcha mismatch", LogLevel::ERROR);
        json_err("验证码错误", 400);
    }
}

function get_username()
{
    if (isset($_COOKIE['session'])) {
        $username = decrypt($_COOKIE['session'], SESSION_KEY);
        if ($username) {
            return $username;
        }
    }

    json_err("未登录", 401);
}
