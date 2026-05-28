<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stack Spinner Admin V5 Pro - Analytics Focused</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===================== THEME COLORS ===================== */
:root {
    --primary-yellow: #FFD700;
    --primary-green: #38A169;
    --primary-blue: #3B82F6;
    --primary-red: #EF4444;
    --dark-background: #1F2937;
    --card-background: #374151;
}

body {
    background-color: var(--dark-background);
    color: #E5E7EB;
    font-family: 'Inter', sans-serif;
    font-size: 14px; /* Reduced base font size */
}

.bg-card { background-color: var(--card-background); }

/* scrollbar */
.custom-scroll::-webkit-scrollbar { width: 8px; }
.custom-scroll::-webkit-scrollbar-thumb { background-color: var(--primary-yellow); border-radius: 4px; }

/* ICON BUTTONS */
.icon-btn {
    @apply text-gray-400 hover:text-yellow-400 transition duration-200;
}
.icon-btn-green:hover { color: var(--primary-green); }
.icon-btn-red:hover { color: var(--primary-red); }
.icon-btn-blue:hover { color: var(--primary-blue); }
.glow:hover { filter: drop-shadow(0 0 8px var(--primary-yellow)); }

/* CARD HOVER EFFECT */
.card-hover:hover {
    border: 1px solid var(--primary-yellow); /* Subtle border on hover */
    transform: translateY(-2px);
    box-shadow: 0 10px 15px rgba(0,0,0,0.3);
}

/* FULLSCREEN PANEL */
.fullscreen-panel {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(10,10,10,0.93);
    backdrop-filter: blur(6px);
    padding: 20px;
    z-index: 1000;
    display: none;
    overflow-y: auto; /* Ensure fullscreen panel itself is scrollable */
}

/* GRAPH MODAL - Fixed Sizing and Positioning */
.graph-modal {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(7px);
    display: none;
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 10px; /* Padding for mobile screens */
}
.graph-window {
    width: 95%; /* Max width relative to screen */
    max-width: 900px; /* Hard max width */
    max-height: 95vh; /* Prevent modal from exceeding viewport height */
    overflow-y: auto; /* Scroll if content (e.g., table) is too long */
    background: #2f2f2f;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
    border: 1px solid #4a4a4a;
}
</style>

</head>
<body class="p-4 sm:p-8">


<div id="login-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur hidden">
    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border-t-4 border-yellow-400">
        <h2 class="text-3xl font-bold text-white mb-6 flex items-center gap-2">
            <i class="fas fa-lock text-yellow-400"></i> Admin Login
        </h2>

        <div class="space-y-4">
            <div>
                <label class="block mb-1 text-sm">Username</label>
                <input type="text" id="admin-username"
                    class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-yellow-400 focus:border-yellow-400">
            </div>

            <div>
                <label class="block mb-1 text-sm">Password</label>
                <input type="password" id="admin-password"
                    class="w-full p-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-yellow-400 focus:border-yellow-400">
            </div>

            <button id="login-btn"
                class="w-full py-3 bg-yellow-400 text-black font-bold rounded-lg hover:bg-yellow-500 transition duration-300"
                onclick="admin();">
                <i class="fas fa-right-to-bracket mr-2"></i> LOGIN
            </button>

            <p id="login-error" class="text-center text-red-400 hidden text-sm">Invalid credentials</p>
        </div>
    </div>
</div>


<header class="mb-8 flex justify-between items-center border-b pb-4 border-gray-700">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-white flex items-center gap-2">
        <i class="fas fa-chart-line text-yellow-400"></i> STACK <span class="text-yellow-400">ADMIN</span>
    </h1>

    <div class="text-lg flex items-center gap-3">
        <span class="flex items-center gap-1">
            <i class="fas fa-clock text-yellow-300"></i>
            <span id="current-time" class="text-sm">--:--:--</span>
        </span>
        <span class="text-green-400 flex items-center gap-1 bg-gray-700 px-2 py-0.5 rounded-full text-xs font-medium">
            <i class="fas fa-signal"></i> Live
        </span>
    </div>
</header>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6 mb-8">

    <div class="bg-card p-4 sm:p-5 rounded-lg shadow-2xl relative transition duration-300 card-hover">
        <div class="absolute top-2 right-2 flex gap-1">
            <button class="icon-btn-green glow" onclick="showGraph('profit')" title="Weekly Net Profit Trend"><i class="fas fa-chart-bar"></i></button>
            <button class="icon-btn glow" onclick="openFullscreen('profit')" title="Fullscreen View"><i class="fas fa-expand"></i></button>
        </div>

        <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-300 mb-1">
            <i class="fas fa-sack-dollar text-green-400"></i> Net Profit / <span class="text-yellow-400">P/L Dial</span>
        </h2>
        <p id="total-revenue" class="text-xl sm:text-2xl font-extrabold text-green-400 break-words overflow-hidden">0.00</p>
        
        <div class="w-full h-16 mt-2" id="pl-dial-container">
             </div>

    </div>

    <div class="bg-card p-4 sm:p-5 rounded-lg shadow-2xl relative transition duration-300 card-hover">
        <div class="absolute top-2 right-2">
            <button class="icon-btn-blue glow" title="User Details"><i class="fas fa-users-viewfinder"></i></button>
        </div>

        <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-300 mb-1">
            <i class="fas fa-users text-blue-300"></i> Users
        </h2>

        <div class="mt-1 flex justify-between text-center text-xs">
            <div class="w-1/2">
                <p class="text-gray-400">Total</p>
                <p id="total-users" class="text-lg sm:text-xl font-bold text-blue-400">0</p>
            </div>

            <div class="w-1/2 border-l border-gray-600">
                <p class="text-gray-400">Online</p>
                <p id="online-users-count" class="text-lg sm:text-xl font-bold text-green-400">0</p>
            </div>
        </div>
    </div>

    <div class="bg-card p-4 sm:p-5 rounded-lg shadow-2xl relative transition duration-300 card-hover">
        <div class="absolute top-2 right-2 flex gap-1">
            <button class="icon-btn-green glow" onclick="showGraph('wins')" title="Weekly Wins Bar Chart"><i class="fas fa-chart-bar"></i></button>
            <button class="icon-btn-blue glow" onclick="showGraph('bets-line-comparison')" title="Win/Loss Comparison Bar Chart"><i class="fas fa-chart-column"></i></button>
        </div>
        <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-300 mb-1">
            <i class="fas fa-trophy text-green-400"></i> Total Wins
        </h2>
        <p id="total-wins" class="text-xl sm:text-2xl font-extrabold text-green-400">0</p>
    </div>

    <div class="bg-card p-4 sm:p-5 rounded-lg shadow-2xl relative transition duration-300 card-hover">
        <div class="absolute top-2 right-2">
            <button class="icon-btn-red glow" onclick="showGraph('losses')" title="Weekly Losses Bar Chart"><i class="fas fa-chart-bar"></i></button>
        </div>
        <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-300 mb-1">
            <i class="fas fa-circle-xmark text-red-400"></i> Total Losses
        </h2>
        <p id="total-losses" class="text-xl sm:text-2xl font-extrabold mt-1 text-red-400">0</p>
    </div>

    <div class="bg-card p-4 sm:p-5 rounded-lg shadow-2xl relative transition duration-300 card-hover">
        <div class="absolute top-2 right-2 flex gap-1">
            <button class="icon-btn-blue glow" onclick="showGraph('bets-pie')" title="Win/Loss Ratio Pie Chart"><i class="fas fa-chart-pie"></i></button>
        </div>
        <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-300 mb-1">
            <i class="fas fa-hand-holding-dollar text-yellow-400"></i> Min / Max Bet
        </h2>
        <p id="min-max-bet" class="text-xl sm:text-2xl font-extrabold text-yellow-400">0 / 0</p>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1 space-y-6">

        <div class="bg-card p-5 rounded-lg shadow-2xl">
            <h2 class="text-xl font-bold mb-4 border-b pb-2 border-gray-600 flex items-center gap-2">
                <i class="fas fa-chart-area text-yellow-400"></i> Analytics Dashboard
            </h2>

            <div class="grid grid-cols-2 gap-3">
                
                <button onclick="showGraph('profit')" 
                    class="py-3 px-2 bg-gray-700 text-yellow-400 rounded-lg hover:bg-yellow-500 hover:text-black transition duration-300 font-semibold text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-chart-line"></i> Weekly Profit
                </button>
                
                <button onclick="showGraph('bets-line-comparison')" 
                    class="py-3 px-2 bg-gray-700 text-yellow-400 rounded-lg hover:bg-yellow-500 hover:text-black transition duration-300 font-semibold text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-chart-column"></i> Wins vs. Losses
                </button>
                
                <button onclick="showGraph('hourly-trend')" 
                    class="py-3 px-2 bg-gray-700 text-yellow-400 rounded-lg hover:bg-yellow-500 hover:text-black transition duration-300 font-semibold text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-clock"></i> Hourly Bet Trend
                </button>
                
                <button onclick="showGraph('bets-pie')" 
                    class="py-3 px-2 bg-gray-700 text-yellow-400 rounded-lg hover:bg-yellow-500 hover:text-black transition duration-300 font-semibold text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-chart-pie"></i> Win Ratio
                </button>
            </div>
        </div>

        <div class="bg-card p-5 rounded-lg shadow-2xl">
            <h2 class="text-xl font-bold mb-4 border-b pb-2 border-gray-600 flex items-center gap-2">
                <i class="fas fa-wallet text-yellow-400"></i> Financial Operations
            </h2>

            <h3 class="text-md font-medium mt-4 mb-2 text-yellow-400 flex items-center gap-2 border-b border-dashed border-gray-600 pb-1">
                <i class="fas fa-plus-circle"></i> Manual Deposit
            </h3>

            <div class="space-y-3">
                <input id="deposit-user-input" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-yellow-400 focus:border-yellow-400" placeholder="User ID/Phone">
                <input id="deposit-amount-input" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-yellow-400 focus:border-yellow-400" placeholder="Amount (MWK)">
                <input id="deposit-trans-id" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-yellow-400 focus:border-yellow-400" placeholder="Transaction ID (Optional)">

                <button class="w-full py-2.5 bg-yellow-400 text-black font-bold rounded-lg hover:bg-yellow-500 transition duration-300 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-file-invoice-dollar"></i> PROCESS DEPOSIT
                </button>
            </div>



            <h3 class="text-md font-medium mt-6 mb-2 text-red-400 border-t pt-3 border-gray-700 flex items-center gap-2 border-b border-dashed border-gray-600 pb-1">
                <i class="fas fa-minus-circle"></i> External Withdrawal
            </h3>

            <div class="space-y-3">
                <select id="withdrawal-type" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-red-400 focus:border-red-400">
                    <option value="airtel">Airtel Money</option>
                    <option value="tnm">TNM Mpamba</option>
                    <option value="bank">Bank Transfer</option>
                </select>

                <input id="withdrawal-account" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-red-400 focus:border-red-400" placeholder="Recipient Number/Account">

                <input id="withdrawal-amount" class="w-full p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-red-400 focus:border-red-400" placeholder="Amount (MWK)">

                <button class="w-full py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition duration-300 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-paper-plane"></i> PROCESS WITHDRAWAL
                </button>
            </div>
        </div>


        <div class="bg-card p-5 rounded-lg shadow-2xl">
            <h2 class="text-xl font-bold mb-4 border-b pb-2 border-gray-600 flex items-center gap-2">
                <i class="fas fa-gears text-blue-400"></i> System Configuration
            </h2>

            <div class="mb-5 p-4 bg-gray-700 rounded-xl shadow-inner border border-gray-600">
                <label class="block text-md font-medium mb-2 flex items-center gap-2 text-blue-300">
                    <i class="fas fa-screwdriver-wrench"></i>
                    Maintenance Mode
                </label>

                <button id="maintenance-toggle"
                    class="w-full py-2.5 bg-green-500 rounded-lg font-bold hover:bg-green-600 transition duration-300 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-toggle-on"></i> SET OFFLINE
                </button>
            </div>

            <div class="p-4 bg-gray-700 rounded-xl shadow-inner border border-gray-600">
                <label class="block text-md font-medium mb-2 flex items-center gap-2 text-blue-300">
                    <i class="fas fa-gauge-high"></i> Set Min/Max Bet Limits
                </label>

                <div class="flex space-x-3 mb-3">
                    <input id="min-bet-input" class="w-1/2 p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-yellow-400 focus:border-yellow-400" placeholder="Min Amount">
                    <input id="max-bet-input" class="w-1/2 p-2.5 bg-gray-800 border border-gray-600 rounded-lg text-sm focus:ring-yellow-400 focus:border-yellow-400" placeholder="Max Amount">
                </div>

                <button id="set-bet-range-btn"
                    class="w-full py-2.5 bg-yellow-400 text-black font-bold rounded-lg hover:bg-yellow-500 transition duration-300 flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-check-circle"></i> APPLY LIMITS
                </button>
            </div>
        </div>

    </div>


    <div class="lg:col-span-2 space-y-6">


        <div class="bg-card p-5 rounded-lg shadow-2xl">
            <h2 class="text-xl font-bold mb-3 border-b pb-2 border-gray-600 flex justify-between items-center">
                <span><i class="fas fa-chart-simple mr-2 text-yellow-400"></i> Real-time Bets Log</span>

                <div class="flex gap-2">
                    <button class="icon-btn glow" onclick="openFullscreen('bets')" title="Fullscreen Log"><i class="fas fa-expand-alt"></i></button>
                </div>
            </h2>

            <div class="h-80 overflow-y-scroll custom-scroll">
                <table class="min-w-full text-xs divide-y divide-gray-700">
                    <thead class="bg-gray-700 sticky top-0 shadow-md">
                        <tr>
                            <th class="p-2 text-left">Time</th>
                            <th class="p-2 text-left">User</th>
                            <th class="p-2 text-left">Bet</th>
                            <th class="p-2 text-left">Outcome</th>
                            <th class="p-2 text-left">P/L</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody id="bets-log" class="divide-y divide-gray-800"></tbody>
                </table>
            </div>
        </div>


        <div class="bg-card p-5 rounded-lg shadow-2xl">
            <h2 class="text-xl font-bold mb-3 border-b pb-2 border-gray-600 flex justify-between items-center">
                <span><i class="fas fa-user-gear mr-2 text-blue-400"></i> User Management</span>

                <div class="flex gap-2">
                    <button class="icon-btn-blue glow" title="Search User"><i class="fas fa-magnifying-glass"></i></button>
                    <button class="icon-btn-blue glow" title="Filter Users"><i class="fas fa-filter"></i></button>
                    <button class="icon-btn-blue glow" title="User Actions"><i class="fas fa-users-cog"></i></button>
                </div>
            </h2>

            <div class="h-64 overflow-y-scroll custom-scroll">
                <table class="min-w-full text-xs divide-y divide-gray-700">
                    <thead class="bg-gray-700 sticky top-0 shadow-md">
                        <tr>
                            <th class="p-2 text-left">User ID</th>
                            <th class="p-2 text-left">Balance</th>
                            <th class="p-2 text-left">Last Login</th>
                            <th class="p-2 text-left">Account Status</th>
                            <th class="p-2 text-left">Action</th>
                        </tr>
                    </thead>

                    <tbody id="user-list" class="divide-y divide-gray-800"></tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div id="fullscreen-panel" class="fullscreen-panel">
    <button onclick="closeFullscreen()" class="absolute top-5 right-5 text-white text-3xl hover:text-red-400 transition duration-200">
        <i class="fas fa-circle-xmark"></i>
    </button>

    <div id="fullscreen-content" class="text-center text-white text-2xl mt-16 max-w-full mx-auto">
        </div>
</div>

<div id="graph-modal" class="graph-modal">
    <div class="graph-window">
        <div class="flex justify-between items-center mb-4 border-b pb-2 border-gray-700">
            <h3 id="graph-title" class="text-xl font-bold flex items-center gap-2 text-yellow-400"><i class="fas fa-chart-area"></i> Detailed Graph View</h3>
            <button onclick="closeGraph()" class="text-white text-2xl hover:text-red-400 transition duration-200" title="Close">
                <i class="fas fa-circle-xmark"></i>
            </button>
        </div>

        <div id="chart-container" style="height: 450px; max-height: 70vh;">
            <canvas id="chartjs-canvas"></canvas>
            <div id="svg-container" class="w-full h-full p-4 border border-gray-700 rounded-lg"></div>
        </div>
    </div>
</div>

<script>
const serverNowTimestamp = Date.now(); 
let serverNow = new Date(serverNowTimestamp);
const ONLINE_THRESHOLD = 300; // 5 minutes (300 seconds)

// Base MOCK data structure (used if server fails to provide custom data)
const MOCK_CHART_DATA = {
    labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    wins: [10000, 15000, 11000, 18000, 13000, 22000, 17000], 
    losses: [12000, 17000, 14000, 20000, 15000, 24000, 19000],
};

// Base data for Hourly Trend (will be dynamically calculated)
const HOURLY_LABELS = ['00h', '01h', '02h', '03h', '04h', '05h', '06h', '07h', '08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h'];


/**
 * Processes the merged bet log data to calculate hourly Bet Volume (count) and Net Profit (P/L).
 * @param {Array} mergedLog - The list of bet objects (win/lose/merged).
 * @returns {Object} {labels: Array, volume: Array, profit: Array}
 */
function processHourlyData(mergedLog) {
    // Helper: format hour in 12-hour format
    const formatHour = h => {
        const ampm = h < 12 ? "AM" : "PM";
        const hour12 = h % 12 === 0 ? 12 : h % 12;
        return `${hour12} ${ampm}`;
    };

    // Initialize 24 hours
    const hourlyData = Array(24).fill(0).map(() => ({
        volume: 0,
        totalStake: 0,
        totalWon: 0,
        totalLost: 0,
        netProfit: 0
    }));

    mergedLog.forEach(bet => {
        if (!bet.time) return;
        const hour = parseInt(bet.time.split(':')[0], 10);
        if (isNaN(hour) || hour < 0 || hour > 23) return;

        const stake = Number(bet.stake || 0);
        const won   = Number(bet.won || 0);
        const lost  = Number(bet.amount_lost || 0);

        hourlyData[hour].volume++;
        hourlyData[hour].totalStake += stake;

        if (bet.type === "win") {
            hourlyData[hour].totalWon += won;
            hourlyData[hour].netProfit += (won - stake); // usually negative for house
        } else if (bet.type === "lose") {
            hourlyData[hour].totalLost += lost;
            hourlyData[hour].netProfit += lost; // positive for house
        }
    });

    // Build human-readable labels and summary statements
    const labels = hourlyData.map((_, h) => formatHour(h));

    const summary = hourlyData.map((d, h) => {
        const avgBet = d.volume ? (d.totalStake / d.volume) : 0;
        return `At ${formatHour(h)} — Total Bets: ${d.volume}, Total Stake: ${d.totalStake.toLocaleString()}, ` +
               `Player Wins: ${d.totalWon.toLocaleString()}, Player Losses: ${d.totalLost.toLocaleString()}, ` +
               `Net Profit: ${d.netProfit.toLocaleString()}, Average Bet: ${avgBet.toFixed(2)}`;
    });

    return {
        labels, // human-readable: 12 AM, 1 AM, etc.
        volume: hourlyData.map(d => d.volume),
        profit: hourlyData.map(d => d.netProfit),
        totalStake: hourlyData.map(d => d.totalStake),
        totalWon: hourlyData.map(d => d.totalWon),
        totalLost: hourlyData.map(d => d.totalLost),
        avgBet: hourlyData.map(d => d.volume ? (d.totalStake / d.volume) : 0),
        summary
    };
}


/**
 * Processes the merged bet log data to calculate daily Wins (player win amount) and Losses (player loss amount, which is house revenue).
 * @param {Array} mergedLog - The list of bet objects (win/lose/merged).
 * @returns {Object} {labels: Array, wins: Array, losses: Array}
 */
function processWeeklyData(mergedLog) {
    const WEEK_DAYS = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    const dailyData = Array(7).fill(0).map(() => ({ wins: 0, losses: 0 }));

    mergedLog.forEach(bet => {
        if (!bet.date) return;

        const [day, month, year] = bet.date.split("-").map(n => Number(n));
        const betDate = new Date(year, month - 1, day);
        let dayIndex = betDate.getDay();
        dayIndex = dayIndex === 0 ? 6 : dayIndex - 1;

        if (bet.type === "win") {
            const stake = Number(bet.stake || 0);
            const won   = Number(bet.won || 0);
            dailyData[dayIndex].wins += (won - stake);  // NET win (negative profit)
        } else {
            dailyData[dayIndex].losses += Number(bet.amount_lost || 0);
        }
    });

    return {
        labels: WEEK_DAYS,
        wins: dailyData.map(d => d.wins),
        losses: dailyData.map(d => d.losses),
    };
}

// =========================================================
// SVG CHART DRAWING UTILITIES
// =========================================================

/**
 * Renders a single/grouped SVG histogram (Bar Chart) into a container.
 */
function drawSvgChart(containerId, chartData, labels, chartTitle) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    const WIDTH = 800;
    const HEIGHT = 400;
    const PADDING_LEFT = 60;
    const PADDING_BOTTOM = 40;
    const CHART_WIDTH = WIDTH - PADDING_LEFT;
    const CHART_HEIGHT = HEIGHT - PADDING_BOTTOM;

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", `0 0 ${WIDTH} ${HEIGHT}`);
    svg.setAttribute("preserveAspectRatio", "xMidYMid meet");
    svg.style.width = "100%";
    svg.style.height = "100%";
    
    // --- Data Processing ---
    const allData = chartData.flatMap(d => d.data);
    const hasNegative = allData.some(v => v < 0);
    const minValue = hasNegative ? Math.min(0, ...allData) : 0;
    const maxValue = Math.max(...allData);
    const range = maxValue - minValue;
    
    // Handle zero range case
    if (range === 0) {
        const value = allData[0] || 0;
        svg.innerHTML += `<text x="${WIDTH / 2}" y="${HEIGHT / 2}" text-anchor="middle" fill="#D1D5DB" font-size="20">No data or zero values.</text>`;
        container.appendChild(svg);
        document.getElementById("graph-title").innerHTML = `<i class="fas fa-chart-area"></i> ${chartTitle}`;
        return;
    }

    const getY = (value) => {
        const normalized = (value - minValue) / range;
        return CHART_HEIGHT * (1 - normalized) + PADDING_BOTTOM;
    };
    
    const zeroLineY = getY(0);
    const barWidth = (CHART_WIDTH / labels.length) / (chartData.length + 0.5); 
    
    // --- Draw Axes and Grid ---
    svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${PADDING_BOTTOM}" x2="${PADDING_LEFT}" y2="${HEIGHT}" stroke="#6B7280" stroke-width="1"/>`;
    svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${zeroLineY}" x2="${WIDTH}" y2="${zeroLineY}" stroke="#6B7280" stroke-width="1"/>`;
    
    const numTicks = 5;
    for (let i = 0; i <= numTicks; i++) {
        const value = minValue + (range / numTicks) * i;
        const y = getY(value);
        if (y >= PADDING_BOTTOM && y <= HEIGHT) {
            svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${y}" x2="${WIDTH}" y2="${y}" stroke="#4B5563" stroke-dasharray="2" stroke-width="0.5"/>`; 
            svg.innerHTML += `<text x="${PADDING_LEFT - 10}" y="${y + 4}" text-anchor="end" fill="#D1D5DB" font-size="12">${value.toLocaleString('en-MW', { minimumFractionDigits: 0 })}</text>`;
        }
    }
    
    // --- Draw Bars ---
    labels.forEach((label, i) => {
        const groupX = PADDING_LEFT + (i * (CHART_WIDTH / labels.length));
        const xCenter = groupX + (CHART_WIDTH / labels.length) / 2;

        chartData.forEach((dataSet, j) => {
            const dataValue = dataSet.data[i] || 0;
            const barX = groupX + (j * barWidth) + (barWidth / 4); 
            const barHeight = Math.abs(getY(dataValue) - zeroLineY);
            let barY = dataValue >= 0 ? getY(dataValue) : zeroLineY;

            svg.innerHTML += `
                <rect x="${barX}" y="${barY}" 
                      width="${barWidth * 0.9}" height="${barHeight}" 
                      fill="${dataSet.color}" rx="3" ry="3">
                    <title>${dataSet.title} (${label}): ${dataValue.toLocaleString()}</title>
                </rect>
            `;
        });

        // X-Axis Label
        svg.innerHTML += `
            <text x="${xCenter}" y="${HEIGHT + 15}" text-anchor="middle" fill="#D1D5DB" font-size="12">
                ${label}
            </text>
        `;
    });
    
    // --- Draw Title and Legend ---
    svg.innerHTML += `<text x="${WIDTH / 2}" y="20" text-anchor="middle" fill="#FFD700" font-size="18" font-weight="bold">${chartTitle}</text>`;
    
    let legendX = PADDING_LEFT;
    let legendY = PADDING_BOTTOM + 5;
    chartData.forEach((dataSet) => {
        svg.innerHTML += `<rect x="${legendX}" y="${legendY + 2}" width="10" height="10" fill="${dataSet.color}" rx="2"/>`;
        svg.innerHTML += `<text x="${legendX + 15}" y="${legendY + 12}" fill="#D1D5DB" font-size="12">${dataSet.title}</text>`;
        legendX += dataSet.title.length * 7 + 30; 
    });
    
    container.appendChild(svg);
    document.getElementById("graph-title").innerHTML = `<i class="fas fa-chart-area"></i> ${chartTitle}`;
}


/**
 * Renders an SVG chart with two independent Y-axes (Left and Right).
 */
function drawSvgDualAxisChart(containerId, data1, data2, labels, title1, title2, color1, color2, chartTitle) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    const WIDTH = 800;
    const HEIGHT = 400;
    const PADDING_LEFT = 60;
    const PADDING_RIGHT = 60; // Extra padding for the second axis
    const PADDING_BOTTOM = 40;
    const CHART_WIDTH = WIDTH - PADDING_LEFT - PADDING_RIGHT;
    const CHART_HEIGHT = HEIGHT - PADDING_BOTTOM;

    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", `0 0 ${WIDTH} ${HEIGHT}`);
    svg.setAttribute("preserveAspectRatio", "xMidYMid meet");
    svg.style.width = "100%";
    svg.style.height = "100%";
    
    // --- Data Processing ---
    const min1 = Math.min(0, ...data1);
    const max1 = Math.max(...data1);
    const range1 = max1 - min1;

    const min2 = Math.min(0, ...data2);
    const max2 = Math.max(...data2);
    const range2 = max2 - min2;
    
    // Handle zero range case
    if (range1 === 0 && range2 === 0) {
        svg.innerHTML += `<text x="${WIDTH / 2}" y="${HEIGHT / 2}" text-anchor="middle" fill="#D1D5DB" font-size="20">No activity recorded for this period.</text>`;
        container.appendChild(svg);
        document.getElementById("graph-title").innerHTML = `<i class="fas fa-chart-area"></i> ${chartTitle}`;
        return;
    }

    // Y-scale functions for two axes
    const getY1 = (value) => { // Left Axis (Volume)
        const normalized = (value - min1) / range1;
        return CHART_HEIGHT * (1 - normalized) + PADDING_BOTTOM;
    };

    const getY2 = (value) => { // Right Axis (Profit)
        const normalized = (value - min2) / range2;
        return CHART_HEIGHT * (1 - normalized) + PADDING_BOTTOM;
    };
    
    const barWidth = CHART_WIDTH / labels.length / 2.5; 
    const spacing = CHART_WIDTH / labels.length;

    // Zero Line (shared)
    const zeroLineY = getY1(0); // Use scale 1 for zero line reference

    // --- Draw Axes and Grid ---
    
    // X-Axis Line (Bottom or Zero Line)
    svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${zeroLineY}" x2="${WIDTH - PADDING_RIGHT}" y2="${zeroLineY}" stroke="#6B7280" stroke-width="1"/>`;
    
    // --- LEFT Y-AXIS (Volume) ---
    svg.innerHTML += `<text x="${PADDING_LEFT - 10}" y="${PADDING_BOTTOM - 10}" text-anchor="end" fill="${color1}" font-size="12">${title1}</text>`;
    const numTicks = 5;
    for (let i = 0; i <= numTicks; i++) {
        const value = min1 + (range1 / numTicks) * i;
        const y = getY1(value);
        if (y >= PADDING_BOTTOM && y <= HEIGHT) {
            svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${y}" x2="${WIDTH - PADDING_RIGHT}" y2="${y}" stroke="#4B5563" stroke-dasharray="2" stroke-width="0.5"/>`; // Grid
            svg.innerHTML += `<text x="${PADDING_LEFT - 10}" y="${y + 4}" text-anchor="end" fill="${color1}" font-size="12">${value.toLocaleString()}</text>`; // Label
        }
    }
    svg.innerHTML += `<line x1="${PADDING_LEFT}" y1="${PADDING_BOTTOM}" x2="${PADDING_LEFT}" y2="${HEIGHT}" stroke="#6B7280" stroke-width="1"/>`; // Axis Line

    // --- RIGHT Y-AXIS (Profit) ---
    svg.innerHTML += `<text x="${WIDTH - PADDING_RIGHT + 10}" y="${PADDING_BOTTOM - 10}" text-anchor="start" fill="${color2}" font-size="12">${title2}</text>`;
    for (let i = 0; i <= numTicks; i++) {
        const value = min2 + (range2 / numTicks) * i;
        const y = getY2(value);
        if (y >= PADDING_BOTTOM && y <= HEIGHT) {
            svg.innerHTML += `<text x="${WIDTH - PADDING_RIGHT + 10}" y="${y + 4}" text-anchor="start" fill="${color2}" font-size="12">${value.toLocaleString('en-MW', { minimumFractionDigits: 0 })}</text>`; // Label
        }
    }
    svg.innerHTML += `<line x1="${WIDTH - PADDING_RIGHT}" y1="${PADDING_BOTTOM}" x2="${WIDTH - PADDING_RIGHT}" y2="${HEIGHT}" stroke="#6B7280" stroke-width="1"/>`; // Axis Line

    // --- Draw Bars and Line ---
    labels.forEach((label, i) => {
        const groupX = PADDING_LEFT + (i * spacing);
        const xCenter = groupX + spacing / 2;
        const xVolume = groupX + (spacing / 2) - (barWidth / 2); // Center the bar
        
        // 1. Volume Bar (Data Set 1)
        const volValue = data1[i] || 0;
        const volBarHeight = Math.abs(getY1(volValue) - zeroLineY);
        const volBarY = volValue >= 0 ? getY1(volValue) : zeroLineY;

        svg.innerHTML += `
            <rect x="${xVolume}" y="${volBarY}" 
                  width="${barWidth}" height="${volBarHeight}" 
                  fill="${color1}80" rx="2" ry="2">
                <title>${title1} (${label}): ${volValue.toLocaleString()}</title>
            </rect>
        `;
        
        // 2. Profit Line/Points (Data Set 2)
        const profitValue = data2[i] || 0;
        const yProfit = getY2(profitValue);

        // Draw Dot for Profit
        svg.innerHTML += `
            <circle cx="${xCenter}" cy="${yProfit}" r="4" fill="${color2}" stroke="#2f2f2f" stroke-width="2">
                <title>${title2} (${label}): ${profitValue.toLocaleString('en-MW', { minimumFractionDigits: 0 })}</title>
            </circle>
        `;
        
        // Draw Connecting Line Segment
        if (i < labels.length - 1) {
            const nextProfitValue = data2[i + 1] || 0;
            const nextYProfit = getY2(nextProfitValue);
            const nextXCenter = PADDING_LEFT + ((i + 1) * spacing) + spacing / 2;
            
            svg.innerHTML += `
                <line x1="${xCenter}" y1="${yProfit}" x2="${nextXCenter}" y2="${nextYProfit}" 
                      stroke="${color2}" stroke-width="2.5" />
            `;
        }

        // X-Axis Label
        svg.innerHTML += `
            <text x="${xCenter}" y="${HEIGHT + 15}" text-anchor="middle" fill="#D1D5DB" font-size="12">
                ${label}
            </text>
        `;
    });
    
    // --- Draw Title and Legend ---
    svg.innerHTML += `<text x="${WIDTH / 2}" y="20" text-anchor="middle" fill="#FFD700" font-size="18" font-weight="bold">${chartTitle}</text>`;
    
    let legendX = PADDING_LEFT;
    let legendY = PADDING_BOTTOM + 5;
    
    // Legend for Volume
    svg.innerHTML += `<rect x="${legendX}" y="${legendY + 2}" width="10" height="10" fill="${color1}" rx="2"/>`;
    svg.innerHTML += `<text x="${legendX + 15}" y="${legendY + 12}" fill="#D1D5DB" font-size="12">${title1}</text>`;
    legendX += title1.length * 7 + 50; 

    // Legend for Profit
    svg.innerHTML += `<circle cx="${legendX + 5}" cy="${legendY + 7}" r="5" fill="${color2}" stroke="#2f2f2f" stroke-width="2"/>`;
    svg.innerHTML += `<text x="${legendX + 15}" y="${legendY + 12}" fill="#D1D5DB" font-size="12">${title2}</text>`;
    
    container.appendChild(svg);
    document.getElementById("graph-title").innerHTML = `<i class="fas fa-chart-area"></i> ${chartTitle}`;
}


// =========================================================
// CHART.JS UTILITIES (KEPT FOR DOUGHNUT/PIE CHART)
// =========================================================

let chartjsInstance = null; 
let cachedChartData = {}; 
let currentChartType = null; 

function getPieChartOptions(title) {
    const options = {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            title: { display: true, text: title, color: '#E5E7EB', font: { size: 18, weight: 'bold' } },
            legend: { position: 'bottom', labels: { color: '#E5E7EB', font: { size: 14 } } },
            tooltip: {
                bodyFont: { size: 14 }, titleFont: { size: 16, weight: 'bold' },
                backgroundColor: 'rgba(55, 65, 81, 0.9)', borderColor: '#FFD700', borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let rawTotal = (cachedChartData.totals ? Number(cachedChartData.totals.totalWon) : 0) + (cachedChartData.totals ? Number(cachedChartData.totals.totalLoss) : 0);
                        if (rawTotal > 0) {
                            let rawCount = context.dataIndex === 0 ? Number(cachedChartData.totals.totalWon) : Number(cachedChartData.totals.totalLoss);
                            return `${label}: ${rawCount.toLocaleString()}`;
                        }
                        return label;
                    }
                }
            }
        },
        layout: { padding: 10 }
    };
    return options;
}

// Main function to show the graph modal and decide rendering method
function showGraph(type){
    if(type) {
        document.getElementById("graph-modal").style.display = "flex";
        currentChartType = type;
    } else {
        type = currentChartType;
    }
    
    // Clear both containers
    document.getElementById("svg-container").innerHTML = '';
    const chartjsCanvas = document.getElementById("chartjs-canvas");
    if(chartjsInstance){ chartjsInstance.destroy(); }
    chartjsCanvas.style.display = 'none';

    if (!type) return;

    const chartsData = cachedChartData.charts || MOCK_CHART_DATA;
    const totalsData = cachedChartData.totals || {};
    
    let labels = chartsData.labels || [];
    let datasets = [];
    let title = "Data Trend";

    // --- SVG CHARTS (Bar/Histogram) ---
    if(["profit", "wins", "losses", "bets-line-comparison"].includes(type)) {
        document.getElementById("svg-container").style.display = 'block';

        if(type === "profit"){
            const wins = chartsData.wins || [];
            const losses = chartsData.losses || [];
            // Net Profit is House Revenue (Player Losses) - House Payout (Player Wins)
            const profitData = losses.map((loss, index) => Number(loss) - Number(wins[index] || 0));

            title = "Weekly Net Profit (House P/L) - MWK";
            datasets.push({ title: "Net Profit", data: profitData, color: '#FFD700' });
            
        } else if(type === "wins"){
            title = "Weekly Payout Volume (Player Wins) - MWK";
            datasets.push({ title: "Payout Volume", data: chartsData.wins || [], color: '#38A169' });

        } else if(type === "losses"){
            title = "Weekly Revenue Volume (Player Losses) - MWK";
            datasets.push({ title: "Revenue Volume", data: chartsData.losses || [], color: '#EF4444' });

        } else if(type === "bets-line-comparison"){ 
            title = "Weekly Player Wins vs Losses (MWK)";
            datasets.push({ title: "Player Wins (Payout)", data: chartsData.wins || [], color: '#38A169' });
            datasets.push({ title: "Player Losses (Revenue)", data: chartsData.losses || [], color: '#EF4444' });
        }
        
        drawSvgChart("svg-container", datasets, labels, title);

    } 
    // --- SVG DUAL AXIS CHART (Hourly Trend) ---
    else if (type === "hourly-trend") {
        document.getElementById("svg-container").style.display = 'block';
        
        const hourlyData = processHourlyData(cachedChartData.merged || []);
        
        title = "Hourly Bet Volume and Profit Trend (Last 24 Hours)";

        // Draw the dual-axis chart
        drawSvgDualAxisChart(
            "svg-container",
            hourlyData.volume,         // Data Set 1: Volume (Bar)
            hourlyData.profit,         // Data Set 2: Profit (Line)
            hourlyData.labels,         // X-Axis: Hours
            "Bet Count (Volume)",      // Title 1
            "Net P/L (MWK)",           // Title 2
            '#FFD700',                 // Color 1: Yellow
            '#3B82F6',                 // Color 2: Blue
            title
        );

    }
    // --- CHART.JS CHARTS (Doughnut/Pie) ---
    else if(type === "bets-pie"){
        chartjsCanvas.style.display = 'block';
        document.getElementById("svg-container").style.display = 'none';
        
        const ctx = chartjsCanvas.getContext("2d");
        
        const totalWon = Number(totalsData.totalWon || 0);
        const totalLoss = Number(totalsData.totalLoss || 0);
        
        const totalBets = totalWon + totalLoss;
        if (totalBets === 0) {
            // Display a message if no data exists
            document.getElementById("svg-container").style.display = 'block';
            document.getElementById("svg-container").innerHTML = '<div class="flex items-center justify-center h-full text-lg text-gray-500">No bet data available for ratio calculation.</div>';
            return;
        }
        
        let winPercent = Math.round((totalWon / totalBets) * 100);
        let lossPercent = 100 - winPercent;

        title = "Win/Loss Ratio (Total Bets Volume)";
        
        const config = {
            type:'doughnut',
            data:{
                labels:[`Wins (${winPercent}%)`,`Losses (${lossPercent}%)`],
                datasets:[{ 
                    data:[totalWon, totalLoss], 
                    backgroundColor:["#38A169","#EF4444"],
                    hoverBackgroundColor:["#48BB78","#F56565"],
                    borderColor: '#2f2f2f',
                    borderWidth: 4
                }]
            },
            options: getPieChartOptions(title)
        };
        chartjsInstance = new Chart(ctx, config);

        document.getElementById("graph-title").innerHTML = `<i class="fas fa-chart-area"></i> ${title}`;
    }
}

function closeGraph(){
    document.getElementById("graph-modal").style.display = "none";
    currentChartType = null;
}


/* ================= MISC UTILITIES (Unchanged) =================== */

/**
 * Calculates the relative time difference, using the serverNow global variable 
 * and correctly parsing the format "HH:mm:ss | YYYY-MM-DD".
 * * @param {string} dateString - The date string from the user data (e.g., "10:34:02 | 2025-11-27").
 * @returns {string} - Relative time string like 'Online', 'just now', '2 days ago', etc.
 */
function timeAgoMerged(dateString) {
    // Check if serverNow is ready before proceeding with relative time calculation
    if (serverNow === null) {
        // Fallback: If the clock hasn't synced yet, just show the formatted date/time.
        // This prevents errors in updateAdminPanel on first run.
        return dateString.split(' | ')[0]; 
    }
    
    // 1. Parse the exact format: "HH:mm:ss | YYYY-MM-DD"
    const match = dateString.match(/^(\d{2}):(\d{2}):(\d{2})\s\|\s(\d{4})-(\d{2})-(\d{2})$/);

    if (!match) {
        return dateString; 
    }

    const [
        , // Discard full match
        hour, 
        minute, 
        second, 
        year, 
        month, 
        day 
    ] = match.map(Number);

    const past = new Date(year, month - 1, day, hour, minute, second);
    
    if (isNaN(past.getTime())) {
        return "Unknown Time";
    }

    // 2. Calculation against serverNow (the client's tracked server time)
    const now = serverNow;
    const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

    // ----------------------------------------------------
    // *** CRITICAL FIX: Tolerate up to 10 seconds of "Future Time" ***
    // This handles network lag and minute server-client clock drift.
    // If user logged in between 10 seconds in the future and 60 seconds in the past, they are online.
    if (seconds > -10 && seconds < 60) {
        return seconds < 30 ? 'Online' : 'just now'; 
    }
    // ----------------------------------------------------

    // Handle remaining future time (over 10 seconds ahead—true error)
    if (seconds < 0) {
        return 'Future Time (Sync Error)';
    }

    // 3. Relative Time Logic 

    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(seconds / 3600);
    const days = Math.floor(seconds / 86400);

    // Minutes (within 60 minutes)
    if (minutes < 60) {
        return `${minutes} min${minutes !== 1 ? 's' : ''} ago`;
    } 
    
    // Hours (within 24 hours)
    else if (hours < 24) {
        return `${hours} hr${hours !== 1 ? 's' : ''} ago`;
    } 
    
    // Days (up to 30 days)
    else if (days < 30) { 
        return `${days} day${days !== 1 ? 's' : ''} ago`;
    } 
    
    // Fallback to Absolute Date (older than 30 days)
    else {
        // Return YYYY/MM/DD format
        return `${year}/${String(month).padStart(2, '0')}/${String(day).padStart(2, '0')}`;
    }
}
function updateClock() {
    const timeElement = document.getElementById('current-time');
    if (!timeElement) return; 
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    timeElement.innerText = `${hours}:${minutes}:${seconds}`;
}
setInterval(updateClock, 1000);

function reDrawChartsIfOpen() {
    if (document.getElementById("graph-modal").style.display === "flex") {
        showGraph();
    }
}

// DELETE THIS FUNCTION:
/*
function startOnlineStatusUpdate() {
    setInterval(() => {
        serverNow = new Date(serverNow.getTime() + 1000); 
        const onlineCount = document.querySelectorAll('.online-user-status').length;
        const onlineDisplay = document.getElementById("online-users-count");
        if (onlineDisplay) onlineDisplay.innerText = onlineCount;
    }, 1000);
}
*/

// Draw P/L Dial (Logic remains the same)
function drawPlDial(netProfit) {
    const container = document.getElementById('pl-dial-container');
    if (!container) return;
    const R = 45; const CX = 50; const CY = 50; const STROKE_WIDTH = 10;
    const CIRCUMFERENCE = 2 * Math.PI * R;
    const totalWon = Number(cachedChartData.totals ? cachedChartData.totals.totalWon : 0);
    const totalLoss = Number(cachedChartData.totals ? cachedChartData.totals.totalLoss : 0);
    const totalVolume = totalWon + totalLoss;
    let percentage = 0; let color = '#E5E7EB'; let statusText = 'BALANCED';
    
    if (totalVolume > 0) {
        if (netProfit > 0) {
            percentage = (totalLoss / totalVolume) * 100;
            color = '#38A169'; 
            statusText = 'PROFIT';
        } else if (netProfit < 0) {
            percentage = (totalWon / totalVolume) * 100;
            color = '#EF4444'; 
            statusText = 'LOSS';
        } else {
            percentage = 50;
            color = '#FFD700'; 
        }
    }
    
    const dashOffset = CIRCUMFERENCE - (percentage / 100) * CIRCUMFERENCE;

    container.innerHTML = `
        <svg viewBox="0 0 100 100" class="w-full h-full">
            <circle cx="${CX}" cy="${CY}" r="${R}" fill="none" stroke="#525252" stroke-width="${STROKE_WIDTH}"/>
            <circle cx="${CX}" cy="${CY}" r="${R}" fill="none" stroke="${color}" stroke-width="${STROKE_WIDTH}"
                stroke-dasharray="${CIRCUMFERENCE}" stroke-dashoffset="${dashOffset}"
                transform="rotate(-90 ${CX} ${CY})" style="transition: stroke-dashoffset 0.5s ease-out;"/>
            <text x="${CX}" y="${CY + 5}" text-anchor="middle" font-size="14" font-weight="bold" fill="${color}">
                ${statusText}
            </text>
            <text x="${CX}" y="${CY + 20}" text-anchor="middle" font-size="10" fill="#9CA3AF">
                ${Math.abs(netProfit).toLocaleString()} MWK
            </text>
        </svg>
    `;
}

// =========================================================
// AUTO UPDATE (Core Data Rendering)
// =========================================================
// =========================================================
// 1. DATA RENDERING FUNCTION (updateAdminPanel)
// =========================================================
function updateAdminPanel(data) {
    // 1. Process Time-Series Data
    const processedWeeklyData = processWeeklyData(data.merged || []);
    data.charts = {
        labels: processedWeeklyData.labels,
        wins: processedWeeklyData.wins,
        losses: processedWeeklyData.losses,
    };
    
    cachedChartData = data;

    // 2. TOP CARDS 
    const totalWins = Number(data.totals ? data.totals.totalWon : 0); // Player Wins (House Payout)
    const totalLoss = Number(data.totals ? data.totals.totalLoss : 0); // Player Losses (House Revenue)
    document.getElementById("total-wins").innerText = totalWins.toLocaleString();
    document.getElementById("total-losses").innerText = totalLoss.toLocaleString();

    // Net Profit is Losses - Wins (House Profit)
    const netRevenue = totalLoss - totalWins;
    const revenueDisplay = netRevenue.toLocaleString('en-MW', {style: 'currency', currency: 'MWK', minimumFractionDigits: 0});
    document.getElementById("total-revenue").innerText = revenueDisplay.replace('MWK', 'MWK ');
    
    drawPlDial(netRevenue);

    document.getElementById("total-users").innerText = (data.users || []).length;
    // Calculation uses timeAgoMerged against the constantly updated serverNow clock
    const activeUsers = (data.users || []).filter(u => timeAgoMerged(u.last_login).includes('Online')).length;
    const onlineDisplay = document.getElementById("online-users-count");
    if (onlineDisplay) onlineDisplay.innerText = activeUsers;

    let allBets = [
        ...(data.win || []).map(w => Number(w.stake)),
        ...(data.lose || []).map(l => Number(l.amount_lost))
    ].filter(v => !isNaN(v) && v > 0);
    
    const min = allBets.length > 0 ? Math.min(...allBets).toLocaleString() : '0';
    const max = allBets.length > 0 ? Math.max(...allBets).toLocaleString() : '0';

    document.getElementById("min-max-bet").innerText = `${min} / ${max}`;


    // 3 & 4. USER TABLE & LOG 
    const usersTable = document.getElementById("user-list");
    if (usersTable) {
        usersTable.innerHTML = "";
        (data.users || []).forEach(user => {
            const lastSeen = timeAgoMerged(user.last_login); 
            const balanceFormatted = Number(user.balance || 0).toLocaleString('en-MW', {style: 'currency', currency: 'MWK', minimumFractionDigits: 2});
            const userStatus = user.status === "Suspended" ? "Suspended" : "Active";
            let statusBadge = userStatus === "Suspended" ? '<span class="px-2 py-0.5 bg-red-800 text-red-300 rounded-full text-xs font-medium flex items-center gap-1"><i class="fas fa-ban"></i> SUSPENDED</i></span>' : '<span class="px-2 py-0.5 bg-green-800 text-green-300 rounded-full text-xs font-medium flex items-center gap-1"><i class="fas fa-check-circle"></i> ACTIVE</span>';
            let banButton = userStatus === "Suspended" ? '<button class="px-2 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700 transition duration-200 text-xs font-bold flex items-center gap-1" title="Unban User"><i class="fas fa-user-check"></i> UNBAN</button>' : '<button class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200 text-xs font-bold flex items-center gap-1" title="Ban User"><i class="fas fa-user-slash"></i> BAN</button>';

            usersTable.innerHTML += `<tr class="hover:bg-gray-700 transition duration-150"><td class="p-2 font-mono text-xs">${user.phone}</td><td class="p-2 text-yellow-300 font-medium">${balanceFormatted}</td><td class="p-2 text-xs">${lastSeen}</td><td class="p-2">${statusBadge}</td><td class="p-2">${banButton}</td></tr>`;
        });
    }

    const logTable = document.getElementById("bets-log");
    if (logTable) {
        logTable.innerHTML = "";
        (data.merged || []).sort((a, b) => {
            // Sort by combined date and time for correct order
            const timeA = new Date(`${a.date.split('-').reverse().join('-')}T${a.time}`);
            const timeB = new Date(`${b.date.split('-').reverse().join('-')}T${b.time}`);
            return timeB - timeA;
        }).forEach(row => {
            const rowType = row.type === "win" ? "win" : "lose";
            let statusColor = rowType === "win" ? "text-green-400" : "text-red-400";
            const wonAmount = Number(row.won || 0);
            const lostAmount = Number(row.amount_lost || 0);
            const stakeAmount = Number(row.stake || lostAmount);
            
            // P/L is from the HOUSE perspective 
            const plValue = rowType === "win" ? -(wonAmount) : lostAmount;
            let pl = plValue.toLocaleString();
            
            let stake = stakeAmount.toLocaleString();
            let outcomeText = rowType === "win" ? `${row.target} <i class="fas fa-check-circle text-green-500 ml-1"></i>` : `${row.target}${row.target_landed ? ` <i class="fas fa-arrow-right-long text-red-400 mx-1"></i> ${row.target_landed}` : ''}`;
            let outcomeIcon = rowType === "win" ? '<i class="fas fa-badge-check"></i>' : '<i class="fas fa-skull-crossbones"></i>';

            logTable.innerHTML += `<tr class="hover:bg-gray-700 transition duration-150"><td class="p-2 whitespace-nowrap text-gray-400 text-xs">${row.time} <span class="text-yellow-400">(${row.date})</span></td><td class="p-2 font-mono text-xs">${row.phone}</td><td class="p-2 text-yellow-300 font-medium">${stake}</td><td class="p-2 text-xs">${outcomeText}</td><td class="p-2 ${plValue > 0 ? 'text-green-400' : (plValue < 0 ? 'text-red-400' : 'text-gray-400')} font-bold">${pl}</td><td class="p-2 ${statusColor} font-bold text-sm flex items-center gap-1">${outcomeIcon} ${rowType.toUpperCase()}</td></tr>`;
        });
    }
    
    reDrawChartsIfOpen();
}

// =========================================================
// 2. CLOCK FUNCTION (FIXED)
// =========================================================
function startServerClock() {
    setInterval(() => {
        // Increment serverNow by exactly 1 second (1000 milliseconds)
        serverNow = new Date(serverNow.getTime() + 1000); 
    }, 1000);
}

// =========================================================
// 3. DATA FETCH LOOP (UPDATED FOR NET WIN CALCULATION)
// =========================================================
function startDataUpdateLoop() {
    function fetchDataAndRender() {

        fetch("sever.php")
            .then(response => response.ok ? response.json() : null)
            .then(data => {
                 if (data) {
                     updateAdminPanel(data);
                 } else {

                     // --- MOCK DATA BLOCK ---
                     const currentDate = new Date();
                     const currentHour = currentDate.getHours();
                     const d = currentDate.getDate().toString().padStart(2, '0');
                     const m = (currentDate.getMonth() + 1).toString().padStart(2, '0');
                     const y = currentDate.getFullYear();
                     const dateStr = `${d}-${m}-${y}`;
                     
                     const generateRandomBet = (hour, phone) => {
                         const stake = Math.floor(Math.random() * (2000 - 100 + 1) + 100);
                         const isWin = Math.random() > 0.45;
                         const target = Math.floor(Math.random() * 10) + 1;
                         const time = `${String(hour).padStart(2, '0')}:${String(Math.floor(Math.random()*60)).padStart(2,'0')}:${String(Math.floor(Math.random()*60)).padStart(2,'0')}`;

                         if (isWin) {
                             const won = stake * (Math.random() * 5 + 1.5);
                             return { 
                                 type: "win",
                                 time,
                                 date: dateStr,
                                 phone,
                                 stake: stake.toFixed(0),
                                 won: won.toFixed(0),
                                 target 
                             };
                         } else {
                             return { 
                                 type: "lose",
                                 time,
                                 date: dateStr,
                                 phone,
                                 stake: stake.toFixed(0),
                                 amount_lost: stake.toFixed(0),
                                 target,
                                 target_landed: Math.floor(Math.random() * 10) + 1
                             };
                         }
                     };

                     const mockUsers = [
                         { phone: "998xxxxxxx", balance: 5000, last_login: "08:19:00 | 2025-11-27", status: "Active" },
                         { phone: "999xxxxxxx", balance: 12500, last_login: "08:18:00 | 2025-11-27", status: "Active" }, 
                         { phone: "997xxxxxxx", balance: 100, last_login: "07:40:00 | 2025-11-27", status: "Active" }, 
                         { phone: "996xxxxxxx", balance: 0, last_login: "07:00:00 | 2025-11-27", status: "Suspended" },
                         { phone: "995xxxxxxx", balance: 20000, last_login: "01:00:00 | 2025-11-27", status: "Active" },
                     ];

                     const betLog = [];
                     let totalWon = 0;
                     let totalLoss = 0;

                     for (let h = 0; h <= currentHour; h++) {
                         const activityFactor = h >= 8 && h <= 22 ? 1.5 : 0.5;
                         const betCount = Math.floor(Math.random() * 10 * activityFactor);
                         
                         for (let i = 0; i < betCount; i++) {
                             const user = mockUsers[Math.floor(Math.random() * mockUsers.length)];
                             const bet = generateRandomBet(h, user.phone);
                             betLog.push(bet);

                             if (bet.type === 'win') {
                                 // NET WIN = won − stake
                                 totalWon += (Number(bet.won) - Number(bet.stake));
                             } else {
                                 // LOSS = stake lost
                                 totalLoss += Number(bet.amount_lost);
                             }
                         }
                     }

                     const mockData = {
                         status: "success",
                         totals: { 
                             totalWon: totalWon.toFixed(0), 
                             totalLoss: totalLoss.toFixed(0) 
                         },
                         users: mockUsers,
                         merged: betLog.sort((a, b) => { 
                             const tA = new Date(`${a.date}T${a.time}`);
                             const tB = new Date(`${b.date}T${b.time}`);
                             return tB - tA;
                         }),
                         charts: MOCK_CHART_DATA
                     };

                     updateAdminPanel(mockData);
                     // --- END MOCK BLOCK ---
                 }
            })
            .catch(err => {
                console.error("Failed to fetch data:", err);
                updateAdminPanel({ 
                    status: "fail", 
                    totals: { totalWon: 0, totalLoss: 0 },
                    users: [],
                    merged: []
                });
            });
    }

    fetchDataAndRender();
    setInterval(fetchDataAndRender, 10000);
}


// =========================================================
// 4. APPLICATION STARTUP (FIXED - Calls the correct clock function)
// =========================================================
document.addEventListener('DOMContentLoaded', () => {
    // Note: You must ensure 'serverNow' is initialized globally before this runs.
    
    if (document.getElementById('login-modal')) document.getElementById('login-modal').style.display = 'none'; 
    
    // START THE DEDICATED CLOCK TICKER (Fixes time calculation synchronization)
    startServerClock(); 
    
    // START THE 10-SECOND DATA FETCH LOOP
    startDataUpdateLoop(); 
    
    // Removed the problematic 'startOnlineStatusUpdate()' call
});</script>




</body>
</html>