<?php
declare(strict_types=1);

if (!function_exists('h')) {
    function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // Assume project is served from a subfolder (e.g. /web12). Detect from SCRIPT_NAME.
        $script = $_SERVER['SCRIPT_NAME'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        // If we're inside /sso or /app1 etc, go one level up to project root.
        $parts = explode('/', ltrim($dir, '/'));
        if (count($parts) >= 2) {
            $dir = '/' . $parts[0]; // /web12
        } elseif ($dir === '') {
            $dir = '';
        }

        return $scheme . '://' . $host . $dir;
    }
}

if (!function_exists('origin_url')) {
    function origin_url(): string
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}

if (!function_exists('require_post')) {
    function require_post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Method Not Allowed";
            exit;
        }
    }
}

if (!function_exists('require_get')) {
    function require_get(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            http_response_code(405);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Method Not Allowed";
            exit;
        }
    }
}

if (!function_exists('random_token')) {
    function random_token(int $bytes = 24): string
    {
        return rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('form_post')) {
    function form_post(string $url, array $fields): array
    {
        $body = http_build_query($fields);
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "Accept: application/json\r\n"
                    . "Content-Length: " . strlen($body) . "\r\n",
                'content' => $body,
                'timeout' => 10,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'Accept: application/json',
                    ],
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_TIMEOUT => 10,
                ]);
                $raw = curl_exec($ch);
                $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                curl_close($ch);
                $data = is_string($raw) ? json_decode($raw, true) : null;
                return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'raw' => $raw];
            }
            return ['ok' => false, 'status' => 0, 'data' => null, 'raw' => null];
        }

        $status = 200;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('/^HTTP\\/[0-9.]+\\s+(\\d+)/', $line, $m)) {
                    $status = (int)$m[1];
                    break;
                }
            }
        }
        $data = json_decode($raw, true);
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'raw' => $raw];
    }
}

if (!function_exists('http_get_json')) {
    function http_get_json(string $url, array $headers = []): array
    {
        $headerLines = "Accept: application/json\r\n";
        foreach ($headers as $k => $v) {
            $headerLines .= $k . ': ' . $v . "\r\n";
        }
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headerLines,
                'timeout' => 10,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                $curlHeaders = ['Accept: application/json'];
                foreach ($headers as $k => $v) {
                    $curlHeaders[] = $k . ': ' . $v;
                }
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => $curlHeaders,
                    CURLOPT_TIMEOUT => 10,
                ]);
                $raw = curl_exec($ch);
                $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                curl_close($ch);
                $data = is_string($raw) ? json_decode($raw, true) : null;
                return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'raw' => $raw];
            }
            return ['ok' => false, 'status' => 0, 'data' => null, 'raw' => null];
        }

        $status = 200;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $line) {
                if (preg_match('/^HTTP\\/[0-9.]+\\s+(\\d+)/', $line, $m)) {
                    $status = (int)$m[1];
                    break;
                }
            }
        }
        $data = json_decode($raw, true);
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'data' => $data, 'raw' => $raw];
    }
}
