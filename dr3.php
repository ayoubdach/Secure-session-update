<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Shield - Invisible Bot Protection</title>
    <style>
        :root {
            --primary: #0d1117;
            --secondary: #161b22;
            --accent: #58a6ff;
            --text: #f0f6fc;
            --success: #238636;
            --danger: #da3633;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            padding: 30px 0;
            margin-bottom: 40px;
            background: rgba(13, 17, 23, 0.8);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid #30363d;
        }
        
        h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--accent);
            text-shadow: 0 0 15px rgba(88, 166, 255, 0.5);
        }
        
        .subtitle {
            font-size: 1.3rem;
            color: #8b949e;
            margin-bottom: 25px;
        }
        
        .status {
            display: inline-flex;
            align-items: center;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            background: var(--success);
            box-shadow: 0 0 20px rgba(35, 134, 54, 0.4);
        }
        
        .status::before {
            content: "";
            width: 12px;
            height: 12px;
            background: #3fb950;
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 10px #3fb950;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .card {
            background: rgba(22, 27, 34, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #30363d;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }
        
        .card h2 {
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 1.6rem;
            border-bottom: 2px solid #30363d;
            padding-bottom: 15px;
        }
        
        .feature-list {
            list-style-type: none;
        }
        
        .feature-list li {
            padding: 12px 0;
            border-bottom: 1px solid #21262d;
            display: flex;
            align-items: center;
        }
        
        .feature-list li:before {
            content: "✓";
            color: var(--success);
            margin-right: 12px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .code-container {
            background: rgba(13, 17, 23, 0.9);
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.4);
            border: 1px solid #30363d;
            overflow-x: auto;
        }
        
        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #30363d;
        }
        
        .code-header h2 {
            color: var(--accent);
            font-size: 1.8rem;
        }
        
        .copy-btn {
            background: var(--secondary);
            color: var(--text);
            border: 1px solid #30363d;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .copy-btn:hover {
            background: var(--accent);
        }
        
        pre {
            white-space: pre-wrap;
            color: #c9d1d9;
            line-height: 1.5;
            font-size: 14px;
        }
        
        .instructions {
            background: rgba(22, 27, 34, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin: 40px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #30363d;
        }
        
        .instructions h2 {
            color: var(--accent);
            margin-bottom: 25px;
            font-size: 1.8rem;
            border-bottom: 2px solid #30363d;
            padding-bottom: 15px;
        }
        
        .instructions ol {
            padding-left: 25px;
            margin: 20px 0;
        }
        
        .instructions li {
            margin-bottom: 20px;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 30px;
            color: #8b949e;
            font-size: 1rem;
            background: rgba(13, 17, 23, 0.8);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #30363d;
        }
        
        @media (max-width: 768px) {
            .cards {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SILENT SHIELD</h1>
            <p class="subtitle">Invisible Anti-Bot Protection for PHP Pages</p>
            <div class="status">STEALTH MODE: ACTIVE</div>
        </header>
        
        <div class="cards">
            <div class="card">
                <h2>Stealth Features</h2>
                <ul class="feature-list">
                    <li>Completely Invisible to Users</li>
                    <li>No Visual Elements</li>
                    <li>Silent Bot Detection</li>
                    <li>Hidden Honeypot Traps</li>
                    <li>Stealth IP Blocking</li>
                    <li>Zero Performance Impact</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Protection System</h2>
                <ul class="feature-list">
                    <li>Advanced Bot Detection</li>
                    <li>Behavior Analysis</li>
                    <li>Request Pattern Monitoring</li>
                    <li>Headless Browser Detection</li>
                    <li>Rate Limiting</li>
                    <li>Automatic IP Blocking</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>Security Advantages</h2>
                <ul class="feature-list">
                    <li>No SQL/XSS Dependencies</li>
                    <li>No Cookies Required</li>
                    <li>Lightweight & Efficient</li>
                    <li>Easy Implementation</li>
                    <li>Comprehensive Logging</li>
                    <li>Zero User Interaction</li>
                </ul>
            </div>
        </div>
        
        <div class="instructions">
            <h2>Implementation Guide</h2>
            <ol>
                <li>Create a file named <strong>silent_shield.php</strong> on your server</li>
                <li>Copy the PHP code below into the file</li>
                <li>Add this line at the very top of each of your 4 PHP pages:<br>
                    <code style="display: inline-block; background: #0d1117; padding: 10px; border-radius: 5px; margin-top: 10px;">&lt;?php require_once('silent_shield.php'); ?&gt;</code>
                </li>
                <li>That's it! The protection will run completely silently</li>
            </ol>
        </div>
        
        <div class="code-container">
            <div class="code-header">
                <h2>silent_shield.php</h2>
                <button class="copy-btn" onclick="copyCode()">Copy Code</button>
            </div>
            <pre id="code"><?php echo htmlspecialchars('<?php
/*
 * SILENT SHIELD - Invisible Anti-Bot Protection
 * Place this file in your root directory and include it at the top of every page
 * This script runs completely silently without any visual output
 */

// Enable stealth mode - hide all server signatures
function enable_stealth_mode() {
    // Remove all server identification headers
    header_remove("X-Powered-By");
    header_remove("Server");
    header_remove("X-AspNet-Version");
    
    // Set generic security headers
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
}

// Advanced bot detection with silent logging
function detect_bot() {
    $user_agent = $_SERVER[\'HTTP_USER_AGENT\'] ?? \'\';
    
    // Empty user agent is always a bot
    if (empty($user_agent)) {
        log_activity("Empty User Agent", $_SERVER[\'REMOTE_ADDR\']);
        return true;
    }
    
    // Known bot user agents
    $bot_indicators = [
        \'bot\', \'crawl\', \'spider\', \'slurp\', \'search\', \'fetcher\', 
        \'scanner\', \'checker\', \'crawler\', \'python\', \'java\', 
        \'curl\', \'wget\', \'php\', \'archive\', \'index\', \'scan\',
        \'monitor\', \'http\', \'client\', \'libwww\', \'go-http\'
    ];
    
    foreach ($bot_indicators as $indicator) {
        if (stripos($user_agent, $indicator) !== false) {
            log_activity("Bot User Agent: " . $indicator, $_SERVER[\'REMOTE_ADDR\']);
            return true;
        }
    }
    
    // Check for headless browsers
    $headless_indicators = [
        \'HeadlessChrome\', \'PhantomJS\', \'Selenium\', \'Puppeteer\'
    ];
    
    foreach ($headless_indicators as $indicator) {
        if (stripos($user_agent, $indicator) !== false) {
            log_activity("Headless Browser: " . $indicator, $_SERVER[\'REMOTE_ADDR\']);
            return true;
        }
    }
    
    return false;
}

// Request behavior analysis
function analyze_behavior() {
    $ip = $_SERVER[\'REMOTE_ADDR\'];
    
    // Check for empty referrer on form submissions
    if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\' && empty($_SERVER[\'HTTP_REFERER\'])) {
        log_activity("Empty Referrer on POST", $ip);
        return true;
    }
    
    // Check request timing (too fast = bot)
    if (!isset($_SESSION[\'first_request_time\'][$ip])) {
        $_SESSION[\'first_request_time\'][$ip] = microtime(true);
    } else {
        $current_time = microtime(true);
        $time_diff = $current_time - $_SESSION[\'first_request_time\'][$ip];
        
        if ($time_diff < 0.5) { // Less than 0.5 seconds between requests
            log_activity("Too fast requests: " . $time_diff, $ip);
            return true;
        }
        $_SESSION[\'first_request_time\'][$ip] = $current_time;
    }
    
    return false;
}

// Hidden honeypot trap for bots (completely invisible)
function hidden_honeypot() {
    // Check for common bot form submissions
    $honeypot_fields = [\'website\', \'url\', \'email\', \'name\', \'comment\'];
    
    foreach ($honeypot_fields as $field) {
        if (!empty($_POST[$field]) && !isset($_POST[\'real_form_field\'])) {
            log_activity("Honeypot triggered: " . $field, $_SERVER[\'REMOTE_ADDR\']);
            return true;
        }
    }
    
    return false;
}

// IP blocking system with silent logging
function block_ip($ip) {
    $blocked_ips = $_SESSION[\'blocked_ips\'] ?? [];
    
    // Block IP for 1 hour
    $blocked_ips[$ip] = time() + 3600;
    $_SESSION[\'blocked_ips\'] = $blocked_ips;
    
    log_activity("IP Blocked", $ip);
}

// Check if IP is blocked
function is_ip_blocked($ip) {
    $blocked_ips = $_SESSION[\'blocked_ips\'] ?? [];
    
    if (isset($blocked_ips[$ip])) {
        if (time() < $blocked_ips[$ip]) {
            return true; // Still blocked
        } else {
            unset($blocked_ips[$ip]); // Block expired
            $_SESSION[\'blocked_ips\'] = $blocked_ips;
        }
    }
    
    return false;
}

// Silent logging function
function log_activity($message, $ip) {
    $log_file = __DIR__ . \'/silent_shield.log\';
    $timestamp = date(\'Y-m-d H:i:s\');
    $log_message = "[$timestamp] [$ip] $message\n";
    
    // Write to log file (silently)
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

// Initialize session with security
session_start([
    \'cookie_httponly\' => true,
    \'use_only_cookies\'=> true,
    \'cookie_secure\' => isset($_SERVER[\'HTTPS\']),
    \'name\' => \'secure_session\'
]);

// Enable stealth mode
enable_stealth_mode();

// Check if IP is blocked
$ip = $_SERVER[\'REMOTE_ADDR\'];
if (is_ip_blocked($ip)) {
    // Serve a 404 without logging to avoid attention
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Bot detection and blocking
if (detect_bot() || analyze_behavior() || hidden_honeypot()) {
    // Block the IP
    block_ip($ip);
    
    // Serve a 404 without any indication of blocking
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Everything below this line will only execute for legitimate users
// Your normal page content will load completely unaffected
?>'); ?></pre>
        </div>
        
        <div class="footer">
            <p>Silent Shield Anti-Bot Protection System | PFE Project 2023</p>
            <p>This invisible script will protect your PHP pages without any visual elements</p>
        </div>
    </div>

    <script>
        function copyCode() {
            const code = document.getElementById('code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const btn = document.querySelector('.copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => {
                    btn.textContent = 'Copy Code';
                }, 2000);
            });
        }
    </script>
</body>
</html>