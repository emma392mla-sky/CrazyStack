<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BetPay Admin Dashboard</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Supabase Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#4F46E5', surface: '#F3F4F6' }
                }
            }
        }
    </script>

    <style>
        .page-section { display: none; }
        .page-section.active { display: block; }
        
        .nav-item { transition: all 0.2s ease; }
        .nav-item:hover { padding-left: 1.5rem; background: rgba(255,255,255,0.1); }
        .nav-item.active-nav { background: rgba(255,255,255,0.2); border-left: 4px solid white; }
        
        .mob-item.active-mob { color: #4F46E5; border-top: 3px solid #4F46E5; }
        
        .table-container { max-height: 65vh; overflow-y: auto; }

        /* Custom Toast Notifications */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; }
        .toast {
            padding: 12px 20px; border-radius: 8px; color: white; font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateX(120%);
            animation: slideIn 0.4s forwards, slideOut 0.4s 2.6s forwards;
        }
        .toast-success { background: #10B981; }
        .toast-error { background: #EF4444; }
        .toast-info { background: #3B82F6; }
        .toast-warning { background: #F59E0B; color: #fff; }

        @keyframes slideIn { to { transform: translateX(0); } }
        @keyframes slideOut { to { transform: translateX(120%); opacity: 0; } }

        /* Custom Modals */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: auto; }
        .modal-box { background: white; padding: 24px; border-radius: 12px; width: 90%; max-width: 400px; transform: scale(0.9); transition: transform 0.3s ease; }
        .modal-overlay.active .modal-box { transform: scale(1); }

        /* Notice pulse indicator */
        @keyframes noticePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(79, 70, 229, 0); }
        }
        .notice-active-dot {
            width: 10px; height: 10px; background: #4F46E5; border-radius: 50%;
            animation: noticePulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Confirm Delete Modal -->
    <div id="confirm-modal" class="modal-overlay">
        <div class="modal-box text-center">
            <h3 class="text-xl font-bold mb-4" id="confirm-title">Are you sure?</h3>
            <p class="text-gray-600 mb-6" id="confirm-msg">This action cannot be undone.</p>
            <div class="flex gap-3 justify-center">
                <button id="confirm-no" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
                <button id="confirm-yes" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">Delete</button>
            </div>
        </div>
    </div>

    <!-- Auto Logout Modal -->
    <div id="logout-modal" class="modal-overlay">
        <div class="modal-box text-center">
            <i class="bi bi-shield-lock text-4xl text-brand mb-4 block"></i>
            <h3 class="text-xl font-bold mb-2" id="logout-title">Security Check</h3>
            <p class="text-gray-600 mb-4" id="logout-msg">Are you still there?</p>
            <p class="text-sm text-gray-500 mb-6">Logging out in <span id="logout-countdown" class="font-bold text-red-500">60</span> seconds</p>
            <button id="logout-stay-btn" class="px-6 py-2 bg-brand text-white rounded-lg hover:bg-indigo-700 font-semibold">Stay Logged In</button>
        </div>
    </div>

    <!-- LOGIN SCREEN -->
    <div id="loginScreen" class="fixed inset-0 bg-gray-900 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-96 text-center">
            <i class="bi bi-shield-check text-5xl text-brand mb-4 block"></i>
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Admin Login</h2>
            <input type="password" id="loginPassword" placeholder="Enter Password" class="w-full p-3 mb-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand">
            <button onclick="login()" class="w-full bg-brand text-white p-3 rounded-lg font-bold hover:bg-indigo-700"><i class="bi bi-box-arrow-in-right mr-2"></i>Login</button>
        </div>
    </div>

    <!-- MAIN APP LAYOUT -->
    <div id="appLayout" class="hidden h-screen flex">
        
        <!-- DESKTOP SIDEBAR -->
        <aside class="hidden md:flex flex-col w-64 bg-gray-900 text-white h-full flex-shrink-0">
            <div class="p-6 text-2xl font-bold border-b border-gray-700 flex items-center gap-2">
                <i class="bi bi-controller"></i> Admin
            </div>
            <nav class="flex-1 py-4 flex flex-col space-y-1">
                <button onclick="switchTab('dashboard')" class="nav-item active-nav text-left p-3 w-full flex items-center gap-2" data-tab="dashboard"><i class="bi bi-graph-up"></i> Dashboard</button>
                <button onclick="switchTab('bets')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="bets"><i class="bi bi-dice-5"></i> Bet History</button>
                <button onclick="switchTab('deposits')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="deposits"><i class="bi bi-cash-stack"></i> Deposits</button>
                <button onclick="switchTab('withdrawals')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="withdrawals"><i class="bi bi-wallet2"></i> Withdrawals</button>
                <button onclick="switchTab('monitor')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="monitor"><i class="bi bi-broadcast"></i> Live Monitor</button>
                <button onclick="switchTab('users')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="users"><i class="bi bi-people"></i> Users</button>
                <button onclick="switchTab('actions')" class="nav-item text-left p-3 w-full flex items-center gap-2" data-tab="actions"><i class="bi bi-sliders"></i> Quick Actions</button>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <button onclick="logout()" class="w-full p-2 bg-red-600 rounded flex items-center justify-center gap-2 hover:bg-red-700"><i class="bi bi-box-arrow-left"></i> Logout</button>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 overflow-y-auto pb-20 md:pb-0">
            
            <!-- Mobile Header with Logout -->
            <div class="md:hidden bg-white p-3 shadow-sm flex justify-between items-center sticky top-0 z-30">
                <h2 class="font-bold text-lg text-brand flex items-center gap-2"><i class="bi bi-controller"></i> Admin</h2>
                <button onclick="logout()" class="text-red-500 text-sm font-bold flex items-center gap-1"><i class="bi bi-box-arrow-left"></i> Logout</button>
            </div>

            <!-- 1. DASHBOARD -->
            <section id="tab-dashboard" class="page-section active p-6 space-y-6">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2"><i class="bi bi-graph-up"></i> Dashboard Overview</h1>
                
                <!-- Active Notice Banner on Dashboard -->
                <!-- Dynamic styles applied via JS based on notice type -->
                <div id="dash-notice-banner" class="hidden rounded-xl p-4 flex items-start gap-3 border transition-colors">
                    <div class="flex-shrink-0 mt-1"><div class="notice-active-dot"></div></div>
                    <div class="flex-1">
                        <p id="dash-notice-type-label" class="text-xs font-bold uppercase tracking-wide mb-1">Active Notice</p>
                        <p id="dash-notice-text" class="font-medium text-gray-900"></p>
                        <p id="dash-notice-time" class="text-xs mt-1 opacity-70"></p>
                    </div>
                    <button onclick="switchTab('actions')" class="opacity-70 hover:opacity-100 text-sm font-semibold flex-shrink-0"><i class="bi bi-pencil-square"></i> Edit</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Revenue Card -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex items-center gap-4">
                        <i class="bi bi-currency-dollar text-3xl text-green-500"></i>
                        <div>
                            <h3 class="text-gray-500 text-sm">Revenue (Profit)</h3>
                            <p id="stat-profit" class="text-xl font-bold text-green-600">MWK 0</p>
                        </div>
                    </div>
                    <!-- Losses Card -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-500 flex items-center gap-4">
                        <i class="bi bi-graph-down-arrow text-3xl text-red-500"></i>
                        <div>
                            <h3 class="text-gray-500 text-sm">Payouts (Losses)</h3>
                            <p id="stat-losses" class="text-xl font-bold text-red-600">MWK 0</p>
                        </div>
                    </div>
                    <!-- Wins Card -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500 flex items-center gap-4">
                        <i class="bi bi-trophy text-3xl text-blue-500"></i>
                        <div>
                            <h3 class="text-gray-500 text-sm">Bets Won</h3>
                            <p id="stat-wins" class="text-xl font-bold text-blue-600">0</p>
                        </div>
                    </div>
                    <!-- Online Users Card -->
                    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500 flex items-center gap-4">
                        <i class="bi bi-wifi text-3xl text-purple-500"></i>
                        <div>
                            <h3 class="text-gray-500 text-sm">Online Users</h3>
                            <p id="stat-online" class="text-xl font-bold text-purple-600">0</p>
                        </div>
                    </div>
                    <!-- NEW WALLET BALANCE CARD -->
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-4 rounded-xl shadow-lg text-white flex items-center justify-between relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-indigo-100 text-sm font-medium">Wallet Balance</h3>
                            <p id="stat-wallet-balance" class="text-2xl font-bold mt-1">Loading...</p>
                            <p id="stat-wallet-currency" class="text-xs text-indigo-200 opacity-80">MWK</p>
                        </div>
                        <button onclick="fetchWalletBalance(true)" class="bg-white/20 hover:bg-white/30 text-white p-2 rounded-lg transition backdrop-blur-sm z-10" title="Refresh Balance">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        <!-- Decorative Background Circle -->
                        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full z-0"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm">
                        <h3 class="font-bold mb-4 flex items-center gap-2"><i class="bi bi-pie-chart"></i> Network Distribution</h3>
                        <div class="h-64"><canvas id="networkChart"></canvas></div>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow-sm">
                        <h3 class="font-bold mb-4 flex items-center gap-2"><i class="bi bi-lightning"></i> Profit Prediction</h3>
                        <p class="text-sm text-gray-500 mb-2" id="prediction-text">Calculating...</p>
                        <div class="h-64"><canvas id="predictionChart"></canvas></div>
                    </div>
                </div>
            </section>

            <!-- 4. BET HISTORY -->
            <section id="tab-bets" class="page-section p-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-dice-5"></i> Bet History</h1>
                <div class="bg-white rounded-xl shadow-sm p-4 table-container">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-3 border">Phone</th>
                                <th class="p-3 border">Amount</th>
                                <th class="p-3 border">Result</th>
                                <th class="p-3 border">Time</th>
                            </tr>
                        </thead>
                        <tbody id="bets-table"></tbody>
                    </table>
                </div>
            </section>

            <!-- 5. DEPOSIT RECORDS -->
            <section id="tab-deposits" class="page-section p-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-cash-stack"></i> Deposit Records</h1>
                <div class="bg-white rounded-xl shadow-sm p-4 table-container">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-3 border">Phone</th>
                                <th class="p-3 border">Amount</th>
                                <th class="p-3 border">Network</th>
                                <th class="p-3 border">Status</th>
                                <th class="p-3 border">Date</th>
                            </tr>
                        </thead>
                        <tbody id="deposits-table"></tbody>
                    </table>
                </div>
            </section>

            <!-- 6. WITHDRAWAL RECORDS -->
            <section id="tab-withdrawals" class="page-section p-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-wallet2"></i> Withdrawal Records</h1>
                <div class="bg-white rounded-xl shadow-sm p-4 table-container">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-3 border">Phone</th>
                                <th class="p-3 border">Amount</th>
                                <th class="p-3 border">Network</th>
                                <th class="p-3 border">Status</th>
                                <th class="p-3 border">Date</th>
                            </tr>
                        </thead>
                        <tbody id="withdrawals-table"></tbody>
                    </table>
                </div>
            </section>

            <!-- 7. LIVE MONITOR -->
            <section id="tab-monitor" class="page-section p-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-broadcast"></i> Live Monitor <span class="ml-2 h-3 w-3 bg-red-500 rounded-full animate-pulse"></span></h1>
                <p class="text-gray-500 mb-4">Auto-refreshes every 10 seconds.</p>
                <div class="bg-white rounded-xl shadow-sm p-4 table-container">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="p-3 border">Event Type</th>
                                <th class="p-3 border">Phone</th>
                                <th class="p-3 border">Amount</th>
                                <th class="p-3 border">Time</th>
                            </tr>
                        </thead>
                        <tbody id="live-table"></tbody>
                    </table>
                </div>
            </section>

            <!-- 8. USER MANAGEMENT -->
            <section id="tab-users" class="page-section p-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-people"></i> User Management</h1>
                <div class="flex gap-2 mb-4">
                    <input type="text" id="search-phone" placeholder="Search by Phone (e.g., 0991234567)" class="flex-1 p-2 border rounded">
                    <button onclick="searchUser()" class="bg-brand text-white px-4 rounded hover:bg-indigo-700 flex items-center gap-1"><i class="bi bi-search"></i> Search</button>
                </div>
                
                <div id="user-profile" class="hidden bg-white p-6 rounded-xl shadow-sm mb-6">
                    <h3 class="text-xl font-bold mb-2" id="prof-name">-</h3>
                    <p class="text-gray-600">Phone: <span id="prof-phone">-</span></p>
                    <p class="text-gray-600">Balance: <span id="prof-balance">MWK 0</span></p>
                    <p class="text-gray-600 mb-2">Status: <span id="prof-status" class="font-bold">-</span></p>
                    <p class="text-gray-600 mb-4">Last Seen: <span id="prof-lastseen">-</span></p>
                    
                    <div class="flex gap-2 flex-wrap">
                        <button onclick="toggleBlock('blocked')" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 flex items-center gap-1"><i class="bi bi-slash-circle"></i> Block</button>
                        <button onclick="toggleBlock('active')" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 flex items-center gap-1"><i class="bi bi-check-circle"></i> Activate</button>
                        <button onclick="deleteUser()" class="bg-gray-800 text-white px-3 py-1 rounded text-sm hover:bg-gray-900 flex items-center gap-1"><i class="bi bi-trash"></i> Delete Account</button>
                    </div>
                </div>
            </section>

            <!-- 9 & 10. QUICK ACTIONS -->
            <section id="tab-actions" class="page-section p-6 space-y-6">
                <h1 class="text-3xl font-bold mb-4 flex items-center gap-2"><i class="bi bi-sliders"></i> Quick Actions</h1>
                
                <!-- BROADCAST NOTICE — Enhanced with Type Selection -->
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg flex items-center gap-2"><i class="bi bi-megaphone"></i> Broadcast Notice</h3>
                        <span id="notice-status-badge" class="hidden text-xs font-bold px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 flex items-center gap-1.5">
                            <span class="notice-active-dot" style="width:7px;height:7px;"></span> LIVE
                        </span>
                    </div>

                    <!-- Current Notice Preview (Colored dynamically) -->
                    <div id="current-notice-preview" class="hidden mb-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Currently Active Notice</p>
                        <div id="current-notice-box" class="border rounded-lg p-4 relative transition-colors">
                            <p id="current-notice-text" class="text-gray-800 pr-8 font-medium"></p>
                            <p id="current-notice-meta" class="text-xs mt-2 opacity-70"></p>
                            <button onclick="clearNotice()" class="absolute top-2 right-2 opacity-50 hover:opacity-100 hover:text-red-500 transition-all" title="Remove notice">
                                <i class="bi bi-x-circle text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div id="no-notice-indicator" class="mb-4">
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center">
                            <i class="bi bi-chat-square-text text-2xl text-gray-300 block mb-1"></i>
                            <p class="text-gray-400 text-sm">No active notice. Type below to broadcast one.</p>
                        </div>
                    </div>

                    <!-- Notice History -->
                    <div id="notice-history-section" class="hidden mb-4">
                        <button onclick="toggleNoticeHistory()" class="text-xs text-brand hover:underline flex items-center gap-1 mb-2">
                            <i class="bi bi-clock-history"></i> <span id="notice-history-toggle-text">Show Notice History</span>
                        </button>
                        <div id="notice-history-list" class="hidden max-h-40 overflow-y-auto space-y-1"></div>
                    </div>

                    <div class="space-y-3">
                        <!-- Type Selector -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notice Type</label>
                            <select id="notice-type" class="w-full border border-gray-300 p-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand bg-white">
                                <option value="info">🔵 Info (Blue)</option>
                                <option value="warning">⚠️ Warning (Yellow/Orange)</option>
                                <option value="success">✅ Success (Green)</option>
                                <option value="error">⛔ Error (Red)</option>
                            </select>
                        </div>

                        <!-- Message Input -->
                        <textarea id="notice-msg" rows="3" class="w-full border border-gray-300 p-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent resize-none" placeholder="Type message for all users..."></textarea>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            <button onclick="sendNotice()" class="bg-brand text-white px-5 py-2.5 rounded-lg hover:bg-indigo-700 font-semibold flex items-center gap-2 transition-colors">
                                <i class="bi bi-send"></i> Update Notice
                            </button>
                            <button id="clear-notice-btn" onclick="clearNotice()" class="hidden bg-gray-100 text-gray-500 px-4 py-2.5 rounded-lg hover:bg-red-50 hover:text-red-600 font-semibold flex items-center gap-2 transition-colors">
                                <i class="bi bi-x-circle"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><i class="bi bi-pencil-square"></i> Manual Balance Update</h3>
                    <input type="text" id="man-phone" placeholder="User Phone" class="w-full border p-2 rounded mb-2">
                    <input type="number" id="man-amount" placeholder="Amount (+ to Add, - to Remove)" class="w-full border p-2 rounded mb-2">
                    <button onclick="manualBalanceUpdate()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-1"><i class="bi bi-check2-square"></i> Apply Change</button>
                </div>
            </section>

        </main>

        <!-- MOBILE BOTTOM TABS -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t flex z-40">
            <button onclick="switchTab('dashboard')" class="mob-item active-mob flex-1 p-2 text-center text-xs flex flex-col items-center" data-tab="dashboard"><i class="bi bi-graph-up text-lg"></i> Home</button>
            <button onclick="switchTab('bets')" class="mob-item flex-1 p-2 text-center text-xs flex flex-col items-center" data-tab="bets"><i class="bi bi-dice-5 text-lg"></i> Bets</button>
            <button onclick="switchTab('monitor')" class="mob-item flex-1 p-2 text-center text-xs flex flex-col items-center" data-tab="monitor"><i class="bi bi-broadcast text-lg"></i> Live</button>
            <button onclick="switchTab('users')" class="mob-item flex-1 p-2 text-center text-xs flex flex-col items-center" data-tab="users"><i class="bi bi-people text-lg"></i> Users</button>
            <button onclick="switchTab('actions')" class="mob-item flex-1 p-2 text-center text-xs flex flex-col items-center" data-tab="actions"><i class="bi bi-sliders text-lg"></i> Actions</button>
        </nav>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // ==========================================
        // 1. SUPABASE SETUP
        // ==========================================
        const SUPABASE_URL = "https://awnzbiatwnfmryerfxwg.supabase.co";
        const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";
        const db = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

        let currentUserPhone = null; 
        let liveInterval = null;     
        let inactivityTimer = null;
        let countdownTimer = null;
        let countdownSeconds = 60;
        let currentNoticeId = null; 

        // ==========================================
        // 2. CUSTOM UI FEEDBACK
        // ==========================================
        
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerText = message;
            container.appendChild(toast); 
            setTimeout(() => toast.remove(), 3000);
        }

        function showConfirm(title, msg, onConfirm) {
            document.getElementById('confirm-title').innerText = title;
            document.getElementById('confirm-msg').innerText = msg;
            document.getElementById('confirm-modal').classList.add('active');
            
            document.getElementById('confirm-yes').onclick = async () => {
                await onConfirm();
                document.getElementById('confirm-modal').classList.remove('active');
            };
            document.getElementById('confirm-no').onclick = () => {
                document.getElementById('confirm-modal').classList.remove('active');
            };
        }

        // ==========================================
        // 3. INACTIVITY & AUTO LOGOUT
        // ==========================================

        function startInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(showAutoLogoutModal, 300000); 
        }

        function showAutoLogoutModal() {
            const hour = new Date().getHours();
            const isNight = hour >= 20 || hour < 6; 

            const titleEl = document.getElementById('logout-title');
            const msgEl = document.getElementById('logout-msg');
            const stayBtn = document.getElementById('logout-stay-btn');

            if (isNight) {
                titleEl.innerText = "Are you sleeping?";
                msgEl.innerText = "For security reasons, I'll log you out.";
                stayBtn.innerText = "Still Awake";
                stayBtn.className = "px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold";
            } else {
                titleEl.innerText = "Looks like you're busy";
                msgEl.innerText = "For security reasons, I'll log you out.";
                stayBtn.innerText = "Not Busy";
                stayBtn.className = "px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold";
            }

            document.getElementById('logout-modal').classList.add('active');
            countdownSeconds = 60;
            document.getElementById('logout-countdown').innerText = countdownSeconds;

            countdownTimer = setInterval(() => {
                countdownSeconds--;
                document.getElementById('logout-countdown').innerText = countdownSeconds;
                if (countdownSeconds <= 0) {
                    clearInterval(countdownTimer);
                    logout();
                }
            }, 1000);

            stayBtn.onclick = () => {
                clearInterval(countdownTimer);
                document.getElementById('logout-modal').classList.remove('active');
                startInactivityTimer(); 
                showToast('Timer reset!', 'info');
            };
        }

        ['mousemove', 'keydown', 'click', 'scroll'].forEach(evt => {
            document.addEventListener(evt, () => {
                if (!document.getElementById('logout-modal').classList.contains('active')) {
                    startInactivityTimer();
                }
            });
        });

        function logout() {
            clearTimeout(inactivityTimer);
            clearInterval(countdownTimer);
            clearInterval(liveInterval);
            
            document.getElementById('logout-modal').classList.remove('active');
            document.getElementById('appLayout').classList.add('hidden');
            document.getElementById('appLayout').classList.remove('flex');
            document.getElementById('loginScreen').classList.remove('hidden');
            document.getElementById('loginPassword').value = '';
            
            showToast('Logged out successfully.', 'info');
        }

        // ==========================================
        // 4. NAVIGATION & LOGIN
        // ==========================================
        
        async function login() {
            const pass = document.getElementById('loginPassword').value;
            if(!pass) return showToast('Please enter a password', 'error');
            
            const { data, error } = await db.from('admins').select('*').eq('password', pass).single();
            
            if (error) {
                return showToast('Invalid Password or Database Error', 'error');
            }

            if (data) {
                document.getElementById('loginScreen').classList.add('hidden');
                document.getElementById('appLayout').classList.remove('hidden');
                document.getElementById('appLayout').classList.add('flex');
                showToast('Login Successful!', 'success');
                startInactivityTimer(); 
                initDashboard(); 
            }
        }

        // Allow Enter key to login
        document.getElementById('loginPassword').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') login();
        });

        function switchTab(tabName) {
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            document.getElementById(`tab-${tabName}`).classList.add('active');

            document.querySelectorAll('.nav-item').forEach(el => {
                el.classList.remove('active-nav');
                if(el.getAttribute('data-tab') === tabName) el.classList.add('active-nav');
            });

            document.querySelectorAll('.mob-item').forEach(el => {
                el.classList.remove('active-mob');
                if(el.getAttribute('data-tab') === tabName) el.classList.add('active-mob');
            });

            if(tabName === 'monitor') {
                loadLiveMonitor();
                liveInterval = setInterval(loadLiveMonitor, 10000); 
            } else {
                clearInterval(liveInterval);
            }

            if(tabName === 'actions') {
                loadCurrentNotice();
            }
        }

        // Helper to get Tailwind color classes based on notice type
        function getNoticeStyles(type) {
            switch(type) {
                case 'error': return { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-900', badge: 'text-red-700', bgBadge: 'bg-red-100', icon: 'text-red-500' };
                case 'success': return { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-900', badge: 'text-green-700', bgBadge: 'bg-green-100', icon: 'text-green-500' };
                case 'warning': return { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-900', badge: 'text-yellow-700', bgBadge: 'bg-yellow-100', icon: 'text-yellow-500' };
                case 'info': 
                default: return { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-900', badge: 'text-blue-700', bgBadge: 'bg-blue-100', icon: 'text-blue-500' };
            }
        }

        // ==========================================
        // 5. NOTICE MANAGEMENT (FULL INTEGRATION)
        // ==========================================

        async function loadCurrentNotice() {
            const { data, error } = await db
                .from('notice')
                .select('*')
                .order('created_at', { ascending: false })
                .limit(1)
                .single();

            if (error || !data || !data.message) {
                currentNoticeId = null;
                setNoticeUIEmpty();
                return;
            }

            currentNoticeId = data.id;
            // If type is missing from older records, default to 'info'
            const type = data.type || 'info';
            setNoticeUIActive(data.message, data.created_at, type);
        }

        function setNoticeUIEmpty() {
            document.getElementById('current-notice-preview').classList.add('hidden');
            document.getElementById('no-notice-indicator').classList.remove('hidden');
            document.getElementById('notice-status-badge').classList.add('hidden');
            document.getElementById('clear-notice-btn').classList.add('hidden');
            document.getElementById('dash-notice-banner').classList.add('hidden');
            document.getElementById('notice-msg').value = '';
            document.getElementById('notice-type').value = 'info';
        }

        function setNoticeUIActive(message, createdAt, type) {
            const styles = getNoticeStyles(type);

            // 1. Update Actions Tab Preview
            const previewBox = document.getElementById('current-notice-preview');
            const boxContent = document.getElementById('current-notice-box');
            const text = document.getElementById('current-notice-text');
            const meta = document.getElementById('current-notice-meta');
            
            previewBox.classList.remove('hidden');
            document.getElementById('no-notice-indicator').classList.add('hidden');
            document.getElementById('notice-status-badge').classList.remove('hidden');
            document.getElementById('clear-notice-btn').classList.remove('hidden');

            // Apply dynamic classes
            boxContent.className = `border rounded-lg p-4 relative transition-colors ${styles.bg} ${styles.border}`;
            text.className = `pr-8 font-medium ${styles.text}`;
            meta.className = `text-xs mt-2 opacity-70 ${styles.badge}`;
            
            text.innerText = message;
            meta.innerText = `${type.toUpperCase()} — Posted: ${new Date(createdAt).toLocaleString()}`;

            // 2. Update Dashboard Banner
            const banner = document.getElementById('dash-notice-banner');
            const dot = banner.querySelector('.notice-active-dot');
            
            banner.className = `rounded-xl p-4 flex items-start gap-3 border transition-colors ${styles.bg} ${styles.border}`;
            dot.style.background = type === 'error' ? '#EF4444' : type === 'success' ? '#10B981' : type === 'warning' ? '#F59E0B' : '#4F46E5';
            
            document.getElementById('dash-notice-type-label').innerText = `${type.toUpperCase()} — Active Notice`;
            document.getElementById('dash-notice-type-label').className = `text-xs font-bold uppercase tracking-wide mb-1 ${styles.badge}`;
            document.getElementById('dash-notice-text').innerText = message;
            document.getElementById('dash-notice-text').className = `font-medium ${styles.text}`;
            document.getElementById('dash-notice-time').innerText = `Posted: ${new Date(createdAt).toLocaleString()}`;

            banner.classList.remove('hidden');

            // 3. Update Form Inputs
            document.getElementById('notice-msg').value = message;
            document.getElementById('notice-type').value = type;
        }

        async function sendNotice() {
            const msg = document.getElementById('notice-msg').value.trim();
            const type = document.getElementById('notice-type').value; // Get selected type

            if(!msg) return showToast('Please type a message', 'info');

            const { data, error } = await db
                .from('notice')
                .insert({ message: msg, type: type })
                .select()
                .single();

            if(error) {
                console.error('Notice insert error:', error);
                return showToast('Failed to post notice.', 'error');
            }

            showToast(`Notice (${type.toUpperCase()}) is now live!`, 'success');
            
            currentNoticeId = data.id;
            setNoticeUIActive(data.message, data.created_at, data.type);
        }

        async function clearNotice() {
            if(!currentNoticeId) return;

            showConfirm(
                'Remove Notice?',
                'This will hide the notice from all users immediately.',
                async () => {
                    const { error } = await db
                        .from('notice')
                        .update({ message: null, type: null }) // Clear both
                        .eq('id', currentNoticeId);

                    if(error) {
                        return showToast('Failed to clear notice.', 'error');
                    }

                    showToast('Notice removed successfully.', 'success');
                    setNoticeUIEmpty();
                    currentNoticeId = null;
                }
            );
        }

        let noticeHistoryVisible = false;
        async function toggleNoticeHistory() {
            const listEl = document.getElementById('notice-history-list');
            const toggleText = document.getElementById('notice-history-toggle-text');

            if (noticeHistoryVisible) {
                listEl.classList.add('hidden');
                toggleText.innerText = 'Show Notice History';
                noticeHistoryVisible = false;
                return;
            }

            const { data, error } = await db
                .from('notice')
                .select('*')
                .not('message', 'is', null)
                .order('created_at', { ascending: false })
                .limit(20);

            if (error || !data || data.length === 0) {
                listEl.innerHTML = '<p class="text-gray-400 text-sm">No notice history found.</p>';
            } else {
                listEl.innerHTML = data.map(n => {
                    const styles = getNoticeStyles(n.type || 'info');
                    return `
                    <div class="flex items-start gap-2 p-2 rounded hover:bg-gray-50 text-sm ${n.id === currentNoticeId ? styles.bg + ' border ' + styles.border : ''}">
                        <i class="bi ${n.type === 'error' ? 'bi-exclamation-circle' : n.type === 'success' ? 'bi-check-circle' : 'bi-info-circle'} ${styles.icon} mt-0.5 flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-700 break-words">${n.message}</p>
                            <p class="text-xs text-gray-400">${(n.type || 'info').toUpperCase()} • ${new Date(n.created_at).toLocaleString()}</p>
                        </div>
                        ${n.id === currentNoticeId ? `<span class="text-[10px] font-bold ${styles.badge} ${styles.bgBadge} px-1.5 py-0.5 rounded flex-shrink-0">ACTIVE</span>` : ''}
                    </div>
                `}).join('');
            }

            listEl.classList.remove('hidden');
            toggleText.innerText = 'Hide Notice History';
            noticeHistoryVisible = true;
        }

        // ==========================================
        // 6. DASHBOARD DATA & CHARTS
        // ==========================================
        
        async function initDashboard() {
            loadCurrentNotice();
            fetchWalletBalance(); // NEW: Fetch Balance on init

            const { data: bets, error: betsError } = await db.from('bets_history').select('*').order('time', { ascending: false });
            if (betsError) return showToast('Error loading bets: ' + betsError.message, 'error');
            
            let profit = 0, losses = 0, wins = 0;
            if (bets) {
                bets.forEach(bet => {
                    if(bet.status === 'lost') profit += parseFloat(bet.amount); 
                    if(bet.status === 'won') {
                        losses += parseFloat(bet.amount); 
                        wins++;
                    }
                });
            }
            document.getElementById('stat-profit').innerText = `MWK ${profit.toFixed(2)}`;
            document.getElementById('stat-losses').innerText = `MWK ${losses.toFixed(2)}`;
            document.getElementById('stat-wins').innerText = wins;

            const twoMinsAgo = new Date(Date.now() - 120000).toISOString();
            const { data: recentBets } = await db.from('bets_history').select('phone').gt('time', twoMinsAgo);
            const uniqueOnline = recentBets ? new Set(recentBets.map(b => b.phone)).size : 0;
            document.getElementById('stat-online').innerText = uniqueOnline;

            const { data: users, error: usersError } = await db.from('users').select('phone');
            if (usersError) return showToast('Error loading users: ' + usersError.message, 'error');
            
            let tnm = 0, airtel = 0;
            if (users) {
                users.forEach(u => {
                    if(u.phone) {
                        const p = u.phone.replace('+265', '').trim();
                        if(p.startsWith('099') || p.startsWith('088')) tnm++;
                        else if(p.startsWith('097') || p.startsWith('098')) airtel++;
                    }
                });
            }
            renderNetworkChart(tnm, airtel);
            renderPredictionChart(bets);

            loadBetsTable(bets);
            loadDeposits();
            loadWithdrawals();

            const { count } = await db.from('notice').select('*', { count: 'exact', head: true }).not('message', 'is', null);
            if (count && count > 0) {
                document.getElementById('notice-history-section').classList.remove('hidden');
            }
        }

        // ==========================================
        // 7. FETCH WALLET BALANCE FROM PHP
        // ==========================================
        
        async function fetchWalletBalance(manual = false) {
            const balanceEl = document.getElementById('stat-wallet-balance');
            const currencyEl = document.getElementById('stat-wallet-currency');

            if(manual) {
                balanceEl.innerText = 'Updating...';
            }

            try {
                // Fetch from your local balance.php file
                const response = await fetch('balance.php');
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();

                if (data.status === 'success') {
                    // Format the number nicely (e.g. 10,000.50)
                    balanceEl.innerText = parseFloat(data.main_balance).toLocaleString('en-MW', { minimumFractionDigits: 2 });
                    currencyEl.innerText = data.currency;
                    if(manual) showToast('Wallet balance updated', 'success');
                } else {
                    balanceEl.innerText = 'Error';
                    console.error('API Error:', data.message);
                    if(manual) showToast('API Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                balanceEl.innerText = 'Offline';
                if(manual) showToast('Failed to connect to balance.php', 'error');
            }
        }

        function renderNetworkChart(tnm, airtel) {
            const ctx = document.getElementById('networkChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['TNM Users', 'Airtel Users'],
                    datasets: [{ data: [tnm, airtel], backgroundColor: ['#F59E0B', '#EF4444'] }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        function renderPredictionChart(bets) {
            const dailyData = {};
            if (bets) {
                bets.forEach(bet => {
                    const day = new Date(bet.time).toLocaleDateString();
                    if(!dailyData[day]) dailyData[day] = 0;
                    dailyData[day] += bet.status === 'lost' ? parseFloat(bet.amount) : -parseFloat(bet.amount);
                });
            }

            const labels = Object.keys(dailyData).slice(-7); 
            const data = Object.values(dailyData).slice(-7);

            const last3 = data.slice(-3);
            const avgPred = last3.reduce((a,b) => a+b, 0) / (last3.length || 1);
            
            document.getElementById('prediction-text').innerText = `Expected tomorrow: MWK ${avgPred.toFixed(2)}`;
            
            labels.push('Tomorrow (Pred)');
            data.push(avgPred);

            const ctx = document.getElementById('predictionChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Profit',
                        data: data,
                        borderColor: '#4F46E5',
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // ==========================================
        // 8. TABLE LOADERS
        // ==========================================

        async function loadBetsTable(bets = null) {
            if(!bets) {
                const res = await db.from('bets_history').select('*').order('time', { ascending: false });
                bets = res.data;
            }
            const tbody = document.getElementById('bets-table');
            if (!bets) return;
            tbody.innerHTML = bets.map(b => `
                <tr class="hover:bg-gray-50">
                    <td class="p-3 border">${b.phone}</td>
                    <td class="p-3 border">MWK ${b.amount}</td>
                    <td class="p-3 border ${b.status==='won'?'text-green-600 font-bold':'text-red-600'}">${b.status.toUpperCase()}</td>
                    <td class="p-3 border">${new Date(b.time).toLocaleString()}</td>
                </tr>
            `).join('');
        }

        async function loadDeposits() {
            const { data, error } = await db.from('phone_mappings').select('*').eq('transaction_type', 'deposit').order('created_at', { ascending: false }).limit(50);
            if (error) return showToast('Deposit Load Error: ' + error.message, 'error');
            const tbody = document.getElementById('deposits-table');
            if (!data) return;
            tbody.innerHTML = data.map(d => `
                <tr class="hover:bg-gray-50">
                    <td class="p-3 border">${d.correct_phone_local || '-'}</td>
                    <td class="p-3 border">MWK ${d.amount || 0}</td>
                    <td class="p-3 border"><span class="px-2 py-1 rounded-full text-xs font-bold ${d.network === 'TNM' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${d.network || '-'}</span></td>
                    <td class="p-3 border text-blue-600">${d.status || '-'}</td>
                    <td class="p-3 border">${new Date(d.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        }

        async function loadWithdrawals() {
            const { data, error } = await db.from('phone_mappings').select('*').eq('transaction_type', 'withdraw').order('created_at', { ascending: false }).limit(50);
            if (error) return showToast('Withdrawal Load Error: ' + error.message, 'error');
            const tbody = document.getElementById('withdrawals-table');
            if (!data) return;
            tbody.innerHTML = data.map(w => `
                <tr class="hover:bg-gray-50">
                    <td class="p-3 border">${w.correct_phone_local || '-'}</td>
                    <td class="p-3 border">MWK ${w.amount || 0}</td>
                    <td class="p-3 border"><span class="px-2 py-1 rounded-full text-xs font-bold ${w.network === 'TNM' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">${w.network || '-'}</span></td>
                    <td class="p-3 border text-orange-600">${w.status || '-'}</td>
                    <td class="p-3 border">${new Date(w.created_at).toLocaleString()}</td>
                </tr>
            `).join('');
        }

        async function loadLiveMonitor() {
            const fiveMinAgo = new Date(Date.now() - 300000).toISOString();
            const { data: bets } = await db.from('bets_history').select('*').gt('time', fiveMinAgo).order('time', { ascending: false });
            const { data: deps } = await db.from('phone_mappings').select('*').in('transaction_type', ['deposit', 'withdraw']).gt('created_at', fiveMinAgo).order('created_at', { ascending: false });

            const tbody = document.getElementById('live-table');
            let rows = '';

            if (deps) {
                deps.forEach(d => {
                    const isDeposit = d.transaction_type === 'deposit';
                    rows += `<tr class="${isDeposit ? 'bg-blue-50' : 'bg-orange-50'}">
                        <td class="p-3 border font-bold ${isDeposit ? 'text-blue-700' : 'text-orange-700'}">${d.transaction_type.toUpperCase()}</td>
                        <td class="p-3 border">${d.correct_phone_local}</td>
                        <td class="p-3 border">MWK ${d.amount}</td>
                        <td class="p-3 border">${new Date(d.created_at).toLocaleTimeString()}</td>
                    </tr>`;
                });
            }

            if (bets) {
                bets.forEach(b => {
                    rows += `<tr class="bg-green-50"><td class="p-3 border font-bold text-green-700">BET (${b.status.toUpperCase()})</td><td class="p-3 border">${b.phone}</td><td class="p-3 border">MWK ${b.amount}</td><td class="p-3 border">${new Date(b.time).toLocaleTimeString()}</td></tr>`;
                });
            }

            tbody.innerHTML = rows || '<tr><td colspan="4" class="p-4 text-center text-gray-500">No recent activity</td></tr>';
        }

        // ==========================================
        // 9. USER MANAGEMENT & ACTIONS
        // ==========================================

        async function searchUser() {
            const phone = document.getElementById('search-phone').value.trim();
            if(!phone) return showToast('Please enter a phone number', 'info');

            currentUserPhone = phone;
            const { data: user, error } = await db.from('users').select('*').eq('phone', phone).single();
            
            if(error) return showToast('User not found or RLS blocks read!', 'error');

            const { data: lastBet } = await db.from('bets_history').select('time').eq('phone', phone).order('time', { ascending: false }).limit(1).single();
            const lastSeen = lastBet ? new Date(lastBet.time).toLocaleString() : 'Never';

            document.getElementById('user-profile').classList.remove('hidden');
            document.getElementById('prof-name').innerText = user.name || 'Unknown';
            document.getElementById('prof-phone').innerText = user.phone;
            document.getElementById('prof-balance').innerText = `MWK ${user.balance || 0}`;
            
            const statusEl = document.getElementById('prof-status');
            statusEl.innerText = user.block_status;
            statusEl.className = `font-bold ${user.block_status === 'active' ? 'text-green-600' : 'text-red-600'}`;
            
            document.getElementById('prof-lastseen').innerText = lastSeen;
        }

        async function toggleBlock(status) {
            if(!currentUserPhone) return;
            const { error } = await db.from('users').update({ block_status: status }).eq('phone', currentUserPhone);
            if(!error) {
                showToast(`User has been ${status === 'active' ? 'Activated' : 'Blocked'}!`, 'success');
                searchUser(); 
            } else showToast('Error updating: Check RLS permissions', 'error');
        }

        function deleteUser() {
            if(!currentUserPhone) return;
            showConfirm(
                'Delete User Account?', 
                `This will permanently delete ${currentUserPhone} and all their bet records.`, 
                async () => {
                    await db.from('bets_history').delete().eq('phone', currentUserPhone);
                    await db.from('users').delete().eq('phone', currentUserPhone);
                    
                    showToast('User deleted successfully.', 'success');
                    document.getElementById('user-profile').classList.add('hidden');
                    currentUserPhone = null;
                }
            );
        }

        async function manualBalanceUpdate() {
            const phone = document.getElementById('man-phone').value.trim();
            const amount = parseFloat(document.getElementById('man-amount').value);
            
            if(!phone || isNaN(amount)) return showToast('Valid Phone and Amount required', 'error');

            const { data: user } = await db.from('users').select('balance').eq('phone', phone).single();
            if(!user) return showToast('User not found', 'error');

            const newBalance = (parseFloat(user.balance) || 0) + amount;
            
            const { error } = await db.from('users').update({ balance: newBalance, updated_at: new Date().toISOString() }).eq('phone', phone);
            
            if(!error) {
                showToast(`Balance updated! New balance: MWK ${newBalance.toFixed(2)}`, 'success');
                document.getElementById('man-phone').value = '';
                document.getElementById('man-amount').value = '';
            } else showToast('Error updating balance. Check RLS.', 'error');
        }

    </script>
</body>
</html>