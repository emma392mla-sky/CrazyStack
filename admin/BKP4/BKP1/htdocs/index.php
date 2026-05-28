<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" 
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Federant&family=Literata:wght@400;700&display=swap" rel="stylesheet">
    <title>Stack Spinner: Lucky Multi-Segment Spin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Body Font: Literata for an elegant, readable feel */
body {
  font-family: 'Literata', serif;
  font-size: 18px; /* Slightly larger text often feels more luxurious */
  line-height: 1.6;
}

/* Heading Font: Federant for high impact and Art Deco style */
h1, h2, h3, .score-display { /* Apply to any element that needs to be bold/stylish */
  font-family: 'Federant', cursive; /* Federant is classified as cursive, but looks geometric/display */
  /* This ensures the headings stand out */
  text-transform: uppercase; 
  letter-spacing: 0.1em; 
}
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0c121c; /* Deep space dark background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 16px;
        }

        /* --- Game Card Styling --- */
        .game-card {
            background-color: #1a2333; /* Slightly lighter inner card */
            border: 1px solid #2e3a51;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.7), 0 0 20px rgba(71, 102, 255, 0.1); /* Subtle blue glow */
        }

        /* --- Wheel Container & Pointer --- */
        .wheel-container {
            position: relative;
            max-width: 350px; 
            margin: 0 auto;
            /* Add glow around the wheel */
            filter: drop-shadow(0 0 10px rgba(255, 204, 0, 0.2));
            transition: filter 0.3s;
        }

        canvas {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #0d121c;
            /* Inner shadow for depth */
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8), 0 0 15px rgba(255, 255, 255, 0.05);
            border: 8px solid #3d4a63; /* Strong outer border */
        }

        /* Fixed SVG Pointer Styles */
        .pointer-svg {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50px; /* Slightly larger pointer */
            height: 50px;
            transform: translate(-50%, -100%);
            z-index: 10;
            filter: drop-shadow(0 0 8px #ffcc00); /* Bright yellow pointer glow */
        }

        /* --- Button Styles --- */
        .bet-button {
            transition: all 0.2s ease-in-out;
            border: 2px solid transparent;
            font-weight: 700;
        }
        .bet-button.selected {
            border-color: #ffcc00; /* Bright yellow border when selected */
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(255, 204, 0, 0.6); /* Yellow glow */
        }
        .bet-button:active {
            transform: translateY(1px) scale(1.0);
            box-shadow: 0 0 5px rgba(255, 204, 0, 0.3);
        }

        .action-button:active {
            transform: scale(0.98);
            box-shadow: none !important;
        }
#message-box, #result-display {
    /* Existing: position: absolute; */
    left: 50%;
    transform: translateX(-50%); /* Moves the element back by half its own width */
} 
/* --- Overlay Styling (Main Container) --- */
.custom-loader-overlay {
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    position: fixed;
    z-index: 100000;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

/* --- Text Styling (The 'Loading...' part) --- */
.app-loading-text {
    font-family: 'Federant', cursive;
    color: #FFD700; /* Gold */
    font-size: 5vw;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    text-shadow: 0 0 15px rgba(255, 215, 0, 0.7);
    margin-bottom: 30px;
}

@media (min-width: 1200px) {
    .app-loading-text {
        font-size: 60px;
    }
}

/* 🆕 NEW LOADER STYLES: Gold Bouncing Dots */

/* Container for the dots */
.dot-animation-container {
    width: 100px;
    display: flex;
    justify-content: space-around;
    align-items: flex-end;
    height: 15px;
}

.loading-dot {
    width: 10px;
    height: 10px;
    background-color: #FFD700; /* Gold */
    border-radius: 50%;
    /* Use the unique animation name */
    animation: dot-bounce-key 1.4s infinite ease-in-out both;
}

/* Animation delays for sequential bouncing */
.loading-dot:nth-child(1) {
    animation-delay: -0.32s;
}

.loading-dot:nth-child(2) {
    animation-delay: -0.16s;
}

.loading-dot:nth-child(3) {
    animation-delay: 0s;
}

/* 🔑 NEW KEYFRAMES: Bouncing effect with unique name */
@keyframes dot-bounce-key {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1.0);
    }
}
/* Ensure a smooth transition for the dot and block */
        .dot {
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .block {
            transition: background-color 0.3s ease;
        }
        #music-toggle:checked + .block {
            background-color: #48bb78;
        }

        #music-toggle:checked + .block + .dot {
            transform: translateX(100%);
        }  
                                /* Full Screen Ambient Overlay */
    #spin-overlay {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background-color: transparent; 
        z-index: -1; 
        display: block;
        overflow: hidden; 
        /* VISIBILITY BOOST: Increased from 0.4 to 0.7 */
        opacity: 0.1; 
        /* For a subtle 3D rotation effect */
        perspective: 1000px; 
        /* CRUCIAL FIX: Allows clicking/touching elements underneath */
        pointer-events: none; 
    }

    /* Keyframes for Slow Warping Motion */
    @keyframes warp-drift {
        0% {
            transform: translate(0%, 0%) rotate(0deg) scale(1.0);
        }
        50% {
            /* Subtle drift and scale */
            transform: translate(10%, 10%) rotate(180deg) scale(1.1);
        }
        100% {
            transform: translate(0%, 0%) rotate(360deg) scale(1.0);
        }
    }

    /* The Pseudo-Element creates the actual animated pattern */
    #spin-overlay::before {
        content: '';
        position: absolute;
        /* Make it huge to cover the whole screen during drift */
        width: 300%; 
        height: 300%;
        top: -100%;
        left: -100%;
        
        /* Creates the abstract pattern using repeating gradients */
        background: 
            /* VISIBILITY BOOST: Repeating subtle lines */
            repeating-linear-gradient(45deg, rgba(0, 255, 255, 0.4), rgba(0, 255, 255, 0.4) 2px, transparent 2px, transparent 15px),
            /* VISIBILITY BOOST: Dark base with a stronger cyan glow */
            radial-gradient(circle, rgba(0, 255, 255, 0.3) 0%, rgba(0, 0, 0, 0.5) 70%, rgba(0, 0, 0, 0.9) 100%);
        
        /* Apply the extremely slow, infinite animation */
        animation: warp-drift 120s linear infinite; /* Runs over 2 minutes for smoothness */
        z-index: -1; 
    }
                                /* Basic Webkit Scrollbar Styling (for Chrome, Safari) */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #1a2333; /* Modal background */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #2e3a51; /* Dark gray handle */
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4a5568; /* Slightly lighter on hover */
}
    </style>
    <!-- Tailwind Configuration for custom focus ring and button hover -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-accent': '#6366f1', /* Indigo 500 */
                        'brand-yellow': '#ffcc00', /* Brighter yellow for emphasis */
                        'bg-dark': '#0c121c',
                    },
                }
            }
        }
    </script>
</head>

<body class="p-4">
    <div 
    id="noticeBox"
    class="max-w-md p-4 mx-auto rounded-xl shadow-lg border border-opacity-20 backdrop-blur-md"
    style="
        background-color: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        position: fixed; 
        bottom: 0; 
        left: 0; 
        z-index: 99999;
        height: auto;
        max-height: 120px;        /* Collapsed height */
        overflow: hidden;
        transition: max-height 0.4s ease;
        display:none;
    "
>
    <!-- Dismiss Button -->
    <button 
        onclick="document.getElementById('noticeBox').style.display='none';"
        style="
            position:absolute;
            top:8px;
            right:10px;
            color:white;
            font-size:18px;
            cursor:pointer;
            background:none;
            border:none;
        "
    >✕</button>

    <div class="flex items-center space-x-4">
        <div class="text-white">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
        </div>

        <div style="color:white;" >
            <p class="text-lg font-semibold">System Status Update</p>

            <!-- Notice Text -->
            <p id="noticeText" 
               class="text-sm text-gray-200"
               style="
                   display: -webkit-box;
                   -webkit-line-clamp: 2;
                   -webkit-box-orient: vertical;
                   overflow: hidden;
                   text-overflow: ellipsis;
               "
            >
                All services are currently under maintenance. This may cause temporary
                interruptions or performance issues. Engineers are actively working
                to restore all systems to full functionality as quickly as possible.
            </p>

            <!-- Read More Button -->
            <button 
                id="readMoreBtn"
                onclick="
                    let box = document.getElementById('noticeBox');
                    let text = document.getElementById('noticeText');
                    if (box.dataset.expanded === 'true') {
                        box.style.maxHeight = '120px';
                        text.style.webkitLineClamp = '2';
                        box.dataset.expanded = 'false';
                        this.innerText = 'Read More';
                    } else {
                        box.style.maxHeight = '50vh';
                        text.style.webkitLineClamp = 'unset';
                        box.dataset.expanded = 'true';
                        this.innerText = 'Read Less';
                    }
                "
                style="
                    margin-top:6px;
                    color:#d1d5db;
                    font-size:13px;
                    text-decoration:underline;
                    cursor:pointer;
                    background:none;
                    border:none;
                "
            >Read More</button>
        </div>
    </div>
</div>

                                
                                
                                
  <div 
    style='width:100vw;height:100vh;background:rgba(0,0,0,0.8);flex-direction:row;display:none;justify-content:center;align-items:center;position:fixed;top:0;left:0;z-index:100000;' 
    id='cashout_card0'
>
    <!-- MODAL CONTENT CONTAINER - max-h-[90vh] ensures it fits on the screen -->
    <div class='bg-[#1a2333] rounded-2xl shadow-2xl space-y-4 p-0 w-11/12 max-w-lg border border-[#2e3a51] flex flex-col max-h-[90vh]'>

        <!-- HEADER (Title and Close Button) -->
        <div class="p-5 pb-0 flex justify-between items-start flex-shrink-0">
            <p class="text-2xl text-brand-yellow font-extrabold">Terms & Conditions</p>
            <p 
                class="text-3xl text-gray-400 font-extrabold cursor-pointer hover:text-red-500 transition ml-4" 
                onclick="document.getElementById('cashout_card0').style.display='none'"
            >
                &#10005;
            </p>
        </div>

        <!-- SCROLLABLE BODY (Terms of Service) -->
        <div class="p-5 pt-0 text-sm text-gray-300 overflow-y-auto custom-scrollbar flex-grow min-h-0">
            <!-- Content is made scrollable here with overflow-y-auto -->

            <h3 class="text-lg font-bold text-gray-100 mt-2 mb-2">General Agreement</h3>
            <p>
                By using the <b>Crazy Stack</b> spinning wheel game, you agree to these Terms and Conditions (T&Cs) and the associated Game Rules. The Game is operated by <b>Crazy Stack Inc</b>, under the laws of the <b>Republic of Malawi</b>. You must be at least <b>18 years of age</b> to play. By placing a bet, you are confirming that you meet this age requirement and are legally able to enter into this agreement.
            </p>
            
            <h3 class="text-lg font-bold text-gray-100 mt-4 mb-2">Your Account and Security</h3>
            <p>
                You need to register a personal account ("Account") using your valid mobile phone number. You are permitted to open <b>only one Account</b>. It is your responsibility to keep your Account details confidential, and any bets made using your Account will be considered valid and made by you. For security, we reserve the right to perform verification checks (KYC) before processing any withdrawals, and your account may be temporarily suspended until verification is complete.
            </p>

            <h3 class="text-lg font-bold text-gray-100 mt-4 mb-2">Game Rules and Fair Play</h3>
            <p>
                The game involves betting on the outcome of a spinning wheel. All results are determined by a <b>Random Number Generator (RNG)</b> to ensure fairness; outcomes are random and independent of past results. You must place and confirm your bets before the betting period closes (announced as "No More Bets"). Specific minimum and maximum bet limits apply to each spin and are clearly displayed. If any winnings result from a <b>software error or malfunction</b>, the winnings will be voided, and we retain the right to reclaim those funds.
            </p>

            <h3 class="text-lg font-bold text-gray-100 mt-4 mb-2">Financial Transactions</h3>
            <p>
                All deposits, bets, and payouts are conducted in <b>Malawian Kwacha (MWK)</b>. All withdrawals are subject to verification. If we mistakenly credit funds to your account, we reserve the right to immediately void that transaction and reclaim the funds. You are responsible for ensuring your bets comply with the posted minimum and maximum limits.
            </p>

            <h3 class="text-lg font-bold text-gray-100 mt-4 mb-2">Misconduct and Account Closure</h3>
            <p>
                <b>Cheating, fraud, collusion</b> with other players, or the use of automated software (bots) to gain an unfair advantage is strictly forbidden. We reserve the right to permanently close or suspend your Account, without prior notice, if you violate any part of these T&Cs, especially regarding duplicate accounts or fraudulent activity. If your account is terminated for a violation, we may withhold any remaining winnings.
            </p>
            
            <h3 class="text-lg font-bold text-gray-100 mt-4 mb-2">Playing Responsibly and Liability</h3>
            <p>
                We promote responsible gaming. You can <b>self-exclude</b> yourself from the Game for a set period by contacting our Customer Support team. Please remember that gambling can be addictive, so always play responsibly and only with money you can afford to lose. Crazy Stack Inc. is not responsible for any losses that result from technical issues like system maintenance, poor internet connection, or mistakes you make when entering data.
            </p>

        </div>

        <!-- FOOTER (I Agree Button) -->
        <div class="p-5 pt-0 flex-shrink-0">
            <button 
                class="action-button w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-red-600/40 transition duration-200" 
                onclick="document.getElementById('cashout_card0').style.display='none'"
            >
                I Have Read and Understood
            </button>
        </div>

    </div>
</div>     
<div class="custom-loader-overlay" id='loader'>
  <h4 class="app-loading-text" >Loading</h4>
  
  <div class="dot-animation-container">
    <div class="loading-dot"></div>
    <div class="loading-dot"></div>
    <div class="loading-dot"></div>
  </div>
</div> 
   <div id="spin-overlay"></div>
<!-- Modal -->
<div style="width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); flex-direction: column; display:flex; justify-content: center; align-items: center; position: fixed; z-index: 90000; top: 0; left: 0; right: 0; bottom: 0;" id="game_modal">
    <div class="bg-[#1a2333] p-8 rounded-2xl shadow-2xl space-y-6 w-11/12 max-w-sm border border-[#2e3a51]">
        
        <p class="text-3xl text-gray-400 text-right -mt-4 font-extrabold cursor-pointer hover:text-red-500 transition" onclick="document.getElementById('game_modal').style.display='none'">&#10005;</p>
        
        <p class="text-2xl text-brand-yellow font-extrabold text-center">Play Music</p>
        
        <!-- Music Toggle Switch -->
        <div class="flex items-center justify-between mt-6">
            <span class="text-white font-medium">Game Music</span>
            <label for="music-toggle" class="flex items-center cursor-pointer">
                <div class="relative">
                    <input id="music-toggle" type="checkbox" class="hidden" onchange="toggleMusic()">
                    <div class="block bg-gray-400 w-14 h-8 rounded-full"></div>
                    <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                </div>
            </label>
        </div>
    </div>
</div>
                            
<!--withdraw modal-->
<div style='width:100vw;height:100vh;background:rgba(0,0,0,0.8);flex-direction:row;display:none;justify-content:center;align-items:center;position:fixed;top:0;left:0;z-index:100000;' id='cashout_card'>
    <div class='bg-[#1a2333] p-8 rounded-2xl shadow-2xl space-y-6 w-11/12 max-w-sm border border-[#2e3a51]'>
        
        <p class="text-3xl text-gray-400 text-right -mt-4 font-extrabold cursor-pointer hover:text-red-500 transition" onclick="document.getElementById('cashout_card').style.display='none'">&#10005;</p>
        
        <p class="text-2xl text-brand-yellow font-extrabold text-center">Cashout Withdrawal</p>
        
        <div class="relative w-full">
            <input type='tel' placeholder='Enter amount to withdraw' min='50' max='700000' value='50' id='cashout_amount'
                   class="w-full py-3 pl-4 pr-16 bg-[#1a2333] text-white rounded-xl border border-[#2e3a51] 
                          placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:border-brand-yellow 
                          transition outline-none text-left text-lg font-semibold">
            
            <span class="absolute inset-y-0 right-0 pr-4 flex items-center 
                         text-lg font-semibold text-gray-400 pointer-events-none">
                MWK
            </span>
        </div>
        
        <button id='take_cash' class="action-button w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-red-600/40 transition duration-200 transform hover:scale-[1.01]" onclick="cashout(document.getElementById('cashout_amount').value);">
            Withdraw Funds
        </button>
    </div>
</div>
                                
                                
                                
<!-- DEPOSIT MODAL - Modified with Currency Suffix -->
<div style='width:100vw;height:100vh;background:rgba(0,0,0,0.8);flex-direction:row;display:none;justify-content:center;align-items:center;position:fixed;top:0;left:0;z-index:100000;' id='deposit_card'>
    <div class='bg-[#1a2333] p-8 rounded-2xl shadow-2xl space-y-6 w-11/12 max-w-sm border border-[#2e3a51]'>
        <p class="text-3xl text-gray-400 text-right -mt-4 font-extrabold cursor-pointer hover:text-red-500 transition" onclick="document.getElementById('deposit_card').style.display='none'">&#10005;</p>
        <p class="text-2xl text-brand-yellow font-extrabold text-center">Account Deposit</p>
        
        <!-- WRAPPER ADDED FOR CURRENCY SUFFIX -->
        <div class="relative w-full">
            <input type='tel' placeholder='Enter amount' min='1' max='700000' value='50' id='deposit_amount'
                    class="w-full py-3 pl-4 **pr-16** bg-[#1a2333] text-white rounded-xl border border-[#2e3a51] 
                           placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:border-brand-yellow 
                           transition outline-none text-left text-lg font-semibold">
            
            <!-- CURRENCY SPAN ADDED -->
            <span class="absolute inset-y-0 right-0 pr-4 flex items-center 
                         text-lg font-semibold text-gray-400 pointer-events-none">
                MWK
            </span>
        </div>
        
        <button class="action-button w-full py-3 bg-primary-accent hover:bg-indigo-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-primary-accent/40 transition duration-200 transform hover:scale-[1.01]" onclick="trans();">
            Deposit
        </button>
    </div>
</div>
    
    <!-- LOGIN/START MODAL -->
    <div style='display:none;' id='start'>
        <div class="w-full h-full fixed inset-0 flex flex-col justify-center items-center bg-gray-900/90 z-50">
            <div class="bg-[#1a2333] p-8 rounded-2xl shadow-2xl space-y-6 w-11/12 max-w-sm border border-[#2e3a51] transform transition duration-300">
                <p class="text-xl text-gray-400 text-right w-full -mt-4 font-bold cursor-pointer hover:text-red-500 transition" onclick="start.style.display='none'">&#10005;</p>
                
                <p class="text-3xl font-extrabold text-brand-yellow text-center tracking-tight border-b border-[#2e3a51] pb-3">
                    Access Stack
                </p>
                <p class="text-lg text-gray-400 text-center -mt-4">
                    Signup & Login
                </p>
                
                <form id='user_login' onsubmit="return log(event);">
                    <input type='tel' name='phone' id='phone' placeholder='Phone e.g 0800000000' maxlength='10'
                           class="w-full p-3 bg-bg-dark text-white rounded-xl border border-[#2e3a51] placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow transition outline-none"
                           aria-label="Phone number input">
                    <br><br>
                    <input type='tel' name='pass' placeholder='Pin e.g 1234' maxlength='4'
                           class="w-full p-3 bg-bg-dark text-white rounded-xl border border-[#2e3a51] placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow transition outline-none"
                           aria-label="Pin input">

                    <button type="submit" id='logic' style='cursor:pointer;' class="action-button w-full py-3 mt-6 bg-brand-yellow hover:bg-yellow-500 text-bg-dark font-extrabold text-lg rounded-xl shadow-lg shadow-brand-yellow/50 transition duration-200 transform hover:scale-[1.01]">
                        Ready to play
                    </button>
                </form>
                <p class="text-lg text-gray-500 text-center pt-2">Forgot password</p>
                <p class="text-xs text-gray-500 text-center pt-2">
                    By logging in, you agree to our simulated <button onclick="document.getElementById('cashout_card0').style.display='flex'" class='text-blue-400'>terms</button>.
                </p>
            </div>
        </div>
    </div>
            
    <!-- MAIN GAME CONTAINER -->
    <div id="game-container" class="game-card w-full max-w-4xl rounded-2xl p-6 md:p-10 space-y-8 text-white">

        <!-- HEADER & BALANCE -->
      <div class="flex justify-between items-center py-3 border-b border-[#2e3a51] flex-wrap">
    
    <div class="flex flex-col items-center mr-4">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-brand-yellow leading-none">
            Stack
        </h1>
        <span class="text-sm sm:text-base text-red-400 font-normal leading-none mt-1" id='live'>
            Sign in to continue.
        </span>
    </div>
      <!-- EXPECTED WIN -->
                <div id='expect' class="bg-[#2c3340] text-brand-yellow p-3 rounded-lg text-center font-bold shadow-inner" style='position:fixed; z-index:70000; top: 0; left: 50%; transform: translateX(-50%);'>
                    View Prizes
                </div>
      <!-- noticeboard_msg -->                          
<div 
  id="message-box" 
  class="p-3 rounded-lg text-sm hidden transition-opacity duration-300 font-semibold border border-transparent" 
  style='
    position:fixed; 
    z-index:100000; 
    top: 0; /* Add this line to stick it to the very top */
    left: 50%; 
    transform: translateX(-50%);
  '>
    </div>
                                
<div 
  id="result-display" 
  class="text-center text-lg font-extrabold text-white pt-4 transition-colors duration-500" 
  style='
    position:fixed; 
    top: 0; 
    left: 50%; 
    transform: translateX(-50%);
  '>
    </div>
                                
    <div class="flex flex-col items-end space-y-2">
        
        <div class="flex space-x-2 sm:space-x-3">
            <button class="action-button p-1.5 sm:p-2 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-500 transition duration-150 text-sm" onclick="if(sessionStorage.getItem('logged')){sessionStorage.setItem('logged',''); location.reload();}else{start.style.display='block'}" id='logout'>
                Sign(In/Up)
            </button>
            <button id='depo' style='display:none;' class="action-button p-1.5 sm:p-2 bg-green-700 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition duration-150 text-sm" onclick="document.getElementById('deposit_card').style.display='flex'">
                Deposit
            </button>
        </div>
        
        <p class="text-sm sm:text-lg text-gray-300">
            <b id='bal' class="text-brand-yellow text-xl sm:text-xl font-extrabold ml-1">0.00</b> MWK
        </p>
    </div>
</div>
        
        <!-- GAME AREA: WHEEL + CONTROLS -->
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 space-y-8 lg:space-y-0"> 
            
            <!-- WHEEL DISPLAY -->
            <div class="flex flex-col items-center space-y-6 pt-4">
                <div class="wheel-container w-full aspect-square">
                    <canvas id="wheelCanvas"></canvas>
                    <svg class="pointer-svg" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Triangle pointer -->
                        <path d="M20 40 L0 0 H40 L20 40 Z" fill="#ffcc00"/>
                        <!-- Small reflection/highlight -->
                        <path d="M20 40 L5 20 L20 10 L35 20 Z" fill="rgba(255, 255, 255, 0.2)"/>
                    </svg>
                </div>
            </div>

            <!-- CONTROL PANEL -->
            <div id="control-panel" class="space-y-6">

                <!-- BET AMOUNT INPUT -->
                <div class="space-y-3">
                    <label for="bet-amount" class="block text-sm font-semibold text-gray-300">BET AMOUNT (MWK)</label>
                    <div class="flex rounded-xl overflow-hidden shadow-inner shadow-gray-900 w-full max-w-sm">
    
    <div class="relative flex-1">
        <input 
            type="tel" 
            id="bet-amount" 
            value="50" 
            min="50" 
            maxlength='7'
            step="50.1"
            class="w-full py-4 pl-4 pr-16 
                   focus:outline-none focus:ring-2 focus:ring-brand-yellow 
                   bg-[#2c3340] text-white text-xl font-bold">

        <span class="absolute inset-y-0 right-0 pr-4 flex items-center 
                     text-xl font-bold text-gray-400 pointer-events-none">
            MWK
        </span>
    </div>

    <button onclick="changeBet(2)" class="action-button p-4 bg-gray-700 hover:bg-gray-600 text-brand-yellow font-extrabold transition duration-150 border-l border-[#2e3a51] text-xl">
        x2
    </button>
</div>
                </div>
<!-- CATEGORY SELECTION - Now Responsive -->
<div class="space-y-3 mt-6 w-full max-w-sm mx-auto">
    <h4 class="text-lg font-bold text-gray-300">Choose Your Category:</h4>
    <div id="prediction-controls" class="grid grid-cols-3 gap-4">
        <button id='cart1' data-category="low" data-payout="2.4" 
                onclick="b=document.getElementById('bet-amount').value; selectBet('low'); document.getElementById('expect').innerHTML='Win '+(parseFloat(b)*2.4).toFixed(2)+' MWK ';window.handleSegmentSelection(0);window.scrollTo({top: document.body.scrollHeight,behavior: 'smooth'});"
                class="bet-button py-4 bg-green-800 hover:bg-green-700 text-white font-extrabold rounded-xl shadow-lg shadow-green-900/50 text-sm md:text-lg">
            LOW
        </button>
        <button id='cart2' data-category="mid" data-payout="3.6" 
                onclick="b=document.getElementById('bet-amount').value; selectBet('mid'); document.getElementById('expect').innerHTML='Win '+(parseFloat(b)*3.6).toFixed(2)+' MWK ';window.handleSegmentSelection(1);window.scrollTo({top: document.body.scrollHeight,behavior: 'smooth'});"
                class="bet-button py-4 bg-blue-800 hover:bg-blue-700 text-white font-extrabold rounded-xl shadow-lg shadow-blue-900/50 text-sm md:text-lg">
            MID
        </button>
        <button id='cart3' data-category="high" data-payout="7.2" 
                onclick="b=document.getElementById('bet-amount').value; selectBet('high'); document.getElementById('expect').innerHTML='Win '+(parseFloat(b)*7.2).toFixed(2)+' MWK ';window.handleSegmentSelection(2);window.scrollTo({top: document.body.scrollHeight,behavior: 'smooth'});"
                class="bet-button py-4 bg-red-800 hover:bg-red-700 text-white font-extrabold rounded-xl shadow-lg shadow-red-900/50 text-sm md:text-lg">
            HIGH
        </button>
    </div>
</div>

<!-- DEPOSIT MODAL - Modified with Currency Suffix -->
<div style='width:100%; height:100%; background:rgba(0, 0, 0, 0.8); flex-direction:column; display:none; justify-content:center; align-items:center; position:fixed; z-index:100000;' id='deposit_card'>
    <div class='bg-[#1a2333] p-8 rounded-2xl shadow-2xl space-y-6 w-11/12 max-w-sm border border-[#2e3a51]'>
        <p class="text-3xl text-gray-400 text-right -mt-4 font-extrabold cursor-pointer hover:text-red-500 transition" onclick="deposit_card.style.display='none'">&#10005;</p>
        <p class="text-2xl text-brand-yellow font-extrabold text-center">Account Deposit</p>
        
        <!-- WRAPPER ADDED FOR CURRENCY SUFFIX -->
        <div class="relative w-full">
            <input type='number' placeholder='Enter amount' min='1' max='700000' value='50' id='deposit_amount'
                    class="w-full py-3 pl-4 pr-16 bg-[#1a2333] text-white rounded-xl border border-[#2e3a51] 
                           placeholder-gray-500 focus:ring-2 focus:ring-brand-yellow focus:border-brand-yellow 
                           transition outline-none text-left text-lg font-semibold">
            
            <!-- CURRENCY SPAN ADDED -->
            <span class="absolute inset-y-0 right-0 pr-4 flex items-center 
                         text-lg font-semibold text-gray-400 pointer-events-none">
                MWK
            </span>
        </div>
        
        <button class="action-button w-full py-3 bg-primary-accent hover:bg-indigo-700 text-white font-bold text-lg rounded-xl shadow-lg shadow-primary-accent/40 transition duration-200 transform hover:scale-[1.01]" onclick="trans();">
            Deposit
        </button>
    </div>
</div>
                
                <!-- ACTION BUTTONS -->
                <div class="flex space-x-4">
                    <button id="spin-button" onclick="this.innerHTML=' Wait...'; handleSpin()"
                            class="action-button flex-grow py-4 bg-red-500 text-bg-dark font-extrabold text-xl rounded-xl shadow-xl shadow-brand-yellow/50 transition duration-150 disabled:bg-gray-700 disabled:text-gray-400 disabled:cursor-not-allowed" style='opacity:30%;'>
                        Wait...                    </button>
                    <button class="action-button w-1/3 py-4 bg-green-600 hover:bg-green-500 text-white font-extrabold text-lg rounded-xl shadow-lg shadow-green-900/50 transition duration-150" onclick="document.getElementById('cashout_card').style.display='flex'">
                        Cashout
                    </button>
                </div>
                
             
                 
            </div>
                               
        </div>

<audio id="def_aud" preload="auto" loop>
  <source src="aud/bg_music.m4a" type="audio/mp4">
</audio>

<button 
  onclick="document.getElementById('game_modal').style.display='flex';" 
  id='aud_btn' 
  style='
    display: inline-block; 
    /* Remove scale(2) and let font size handle the sizing */
    /* transform: scale(2); */ 
    transform-origin: center center; 
    transition: transform 0.3s ease-out;
    
    /* Add styling tweaks */
    border: none; 
    background: none; 
    cursor: pointer;
    font-size: 24px; /* Adjust size via font-size instead of scale */
    color: #6c757d; /* Gray color (Bootstrap's secondary/muted gray) */
    padding: 0; /* Remove default button padding */
  '
>
  <i class="fas fa-volume-up"></i> </button>
                                
<!-- AUDIO FOR SPIN-->
<audio id="instant-audio" preload="auto">
<source src="aud/play2.m4a" type="audio/mp4"> 
<source src="aud/play2.m4a" type="audio/mpeg">
                                
<!-- AUDIO FOR WIN-->
<audio id="instant-audio2" preload="auto">
<source src="aud/win.m4a" type="audio/mp4"> 
<source src="aud/win.mp3" type="audio/mpeg">
                                
<!-- AUDIO FOR LOSE-->
<audio id="instant-audio3" preload="auto">
<source src="aud/lose.m4a" type="audio/mp4"> 
<source src="aud/lose.mp3" type="audio/mpeg">
</audio>
            
        <!-- RESULT DISPLAY -->
        

    </div>

    <script src="https://app.malipo.mw/sdk/v1-malipo-hosted-checkout.js"></script>
    <script src="main.js"></script>

</body>
</html>
