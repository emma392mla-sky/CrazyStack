<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrazyStack Command Center</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg-dark: #0a0e1a;
            --bg-card: #131827;
            --bg-elevated: #1a2035;
            --border: #2a3548;
            --text: #e8ecf4;
            --text-muted: #7b889f;
            --gold: #fbbf24;
            --gold-dim: rgba(251,191,36,0.15);
            --success: #10b981;
            --danger: #ef4444;
            --info: #3b82f6;
            --warning: #f59e0b;
            --purple: #8b5cf6;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            color: var(--text);
        }

        .app-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 1920px;
            margin: 0 auto;
        }

        /* ===== HEADER ===== */
        .top-bar {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-dark) 100%);
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--gold), #d97706);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 900;
            color: #000;
            box-shadow: 0 4px 20px rgba(251,191,36,0.3);
        }

        .brand-text h1 {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--gold);
            letter-spacing: -0.5px;
        }

        .brand-text span {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .db-status-mini {
            display: flex;
            gap: 18px;
        }

        .db-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: pulse-dot 2s infinite;
        }

        .status-dot.on { background: var(--success); box-shadow: 0 0 8px var(--success); }
        .status-dot.off { background: var(--danger); animation: none; }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.15); }
        }

        .refresh-btn {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .refresh-btn:hover {
            background: var(--gold);
            color: #000;
            border-color: var(--gold);
        }

        .refresh-btn.loading i { animation: spin 0.8s linear infinite; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ===== TAB NAVIGATION ===== */
        .tab-nav {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex;
            gap: 4px;
            flex-shrink: 0;
            overflow-x: auto;
        }

        .tab-btn {
            padding: 14px 22px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover { 
            color: var(--text); 
            background: rgba(255,255,255,0.03); 
        }

        .tab-btn.active {
            color: var(--gold);
            background: rgba(251,191,36,0.08);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: var(--gold);
            box-shadow: 0 -2px 10px rgba(251,191,36,0.4);
        }

        /* ===== MAIN CONTENT AREA ===== */
        .main-content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .tab-panel {
            display: none;
            flex: 1;
            overflow: hidden;
            flex-direction: column;
            padding: 20px 24px;
        }

        .tab-panel.active { 
            display: flex; 
        }

        /* ===== STATS ROW ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 18px;
            flex-shrink: 0;
        }

        .stat-card {
            background: linear-gradient(145deg, var(--bg-card), var(--bg-elevated));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            opacity: 0.6;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--gold);
            font-family: 'SF Mono', monospace;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-top: 6px;
        }

        /* ===== TABLE CONTAINER ===== */
        .table-wrapper {
            flex: 1;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .table-toolbar {
            background: rgba(0,0,0,0.25);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .table-title {
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-title i { color: var(--gold); }

        .record-count {
            font-size: 0.78rem;
            color: var(--text-muted);
            background: var(--bg-elevated);
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
        }

        .toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-box {
            background: var(--bg-dark);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 7px 13px;
            border-radius: 7px;
            outline: none;
            font-size: 0.82rem;
            width: 180px;
            transition: border-color 0.2s;
        }

        .search-box:focus { border-color: var(--gold); }

        .filter-dropdown {
            background: var(--bg-dark);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 7px 12px;
            border-radius: 7px;
            outline: none;
            font-size: 0.82rem;
            cursor: pointer;
        }

        /* ===== DATA TABLE ===== */
        .data-table-container {
            flex: 1;
            overflow: auto;
        }

        .data-table-container::-webkit-scrollbar { width: 6px; height: 6px; }
        .data-table-container::-webkit-scrollbar-track { background: transparent; }
        .data-table-container::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            padding: 12px 14px;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        td {
            padding: 11px 14px;
            font-size: 0.84rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        tr { transition: background 0.15s; }
        tr:hover { background: rgba(255,255,255,0.03); }

        /* ===== BADGES & TAGS ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-won { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-lost { background: rgba(239,68,68,0.15); color: var(--danger); }
        .badge-deposit { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-withdraw { background: rgba(59,130,246,0.15); color: var(--info); }
        .badge-success { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-failed { background: rgba(239,68,68,0.15); color: var(--danger); }
        .badge-pending { background: rgba(245,158,11,0.15); color: var(--warning); }

        .amount { font-family: 'SF Mono', monospace; font-weight: 700; font-size: 0.86rem; }
        .amount-pos { color: var(--success); }
        .amount-neg { color: var(--danger); }
        .amount-gold { color: var(--gold); }

        .phone-link {
            color: var(--info);
            font-weight: 700;
            cursor: pointer;
        }

        .phone-link:hover { text-decoration: underline; }

        /* ===== RESULT DISPLAY ===== */
        .result-display {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
        }

        .color-choice {
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.76rem;
        }

        .color-green { background: rgba(16,185,129,0.2); color: #10b981; }
        .color-blue { background: rgba(59,130,246,0.2); color: #3b82f6; }
        .color-red { background: rgba(239,68,68,0.2); color: #ef4444; }
        .color-gray { background: rgba(148,163,184,0.2); color: #94a3b8; }

        .result-arrow {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* ===== USER AVATAR ===== */
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--gold), #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.82rem;
            color: #000;
            flex-shrink: 0;
        }

        .user-name { font-weight: 600; }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 300px;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.25;
            margin-bottom: 14px;
        }

        .empty-state p { font-size: 0.92rem; font-weight: 500; }

        /* ===== LOADING OVERLAY ===== */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(10,14,26,0.92);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 14px;
        }

        .loading-overlay.show { display: flex; }

        .spinner-lg {
            width: 45px;
            height: 45px;
            border: 3px solid var(--border);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* ===== QUICK ACTIONS PANEL ===== */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
            flex-shrink: 0;
        }

        .action-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-card:hover {
            border-color: var(--gold);
            background: var(--bg-elevated);
            transform: translateY(-2px);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .action-info h4 {
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .action-info p {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1400px) {
            .stats-row { grid-template-columns: repeat(3, 1fr); }
            .quick-actions { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 1024px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .tab-btn { padding: 12px 16px; font-size: 0.82rem; }
            .search-box { width: 150px; }
        }
    </style>
</head>
<body>

<div class="app-container">

    <!-- ===== TOP BAR ===== -->
    <header class="top-bar">
        <div class="logo-section">
            <div class="logo-icon">CS</div>
            <div class="brand-text">
                <h1>CrazyStack Command Center</h1>
                <span>Multi-Database Administration Console</span>
            </div>
        </div>

        <div class="header-controls">
            <div class="db-status-mini">
                <div class="db-indicator">
                    <div class="status-dot" id="mainDbDot"></div>
                    <span>Main DB</span>
                </div>
                <div class="db-indicator">
                    <div class="status-dot" id="paymentsDbDot"></div>
                    <span>Payments</span>
                </div>
            </div>

            <button class="refresh-btn" onclick="refreshData()" id="refreshBtn">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Refresh</span>
            </button>

            <div style="width: 1px; height: 28px; background: var(--border);"></div>

            <div style="font-size: 0.78rem; color: var(--text-muted);">
                <i class="bi bi-clock"></i>
                <span id="lastUpdate">--:--:--</span>
            </div>
        </div>
    </header>

    <!-- ===== TAB NAVIGATION ===== -->
    <nav class="tab-nav">
        <button class="tab-btn active" data-tab="dashboard" onclick="switchTab('dashboard')">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </button>
        <button class="tab-btn" data-tab="bets" onclick="switchTab('bets')">
            <i class="bi bi-dice-5"></i> Bets History
        </button>
        <button class="tab-btn" data-tab="transactions" onclick="switchTab('transactions')">
            <i class="bi bi-credit-card"></i> Transactions
        </button>
        <button class="tab-btn" data-tab="users" onclick="switchTab('users')">
            <i class="bi bi-people"></i> Users
        </button>
        <button class="tab-btn" data-tab="analytics" onclick="switchTab('analytics')">
            <i class="bi bi-graph-up"></i> Analytics
        </button>
        <button class="tab-btn" data-tab="tools" onclick="switchTab('tools')">
            <i class="bi bi-tools"></i> Tools
        </button>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content">

        <!-- ===== DASHBOARD TAB ===== -->
        <section class="tab-panel active" id="dashboardPanel">
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card" onclick="switchTab('transactions')">
                    <div class="action-icon" style="background: rgba(16,185,129,0.15); color: var(--success);">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="action-info">
                        <h4>Total Revenue</h4>
                        <p id="quickRevenue">MWK 0</p>
                    </div>
                </div>

                <div class="action-card" onclick="switchTab('users')">
                    <div class="action-icon" style="background: rgba(59,130,246,0.15); color: var(--info);">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="action-info">
                        <h4>Active Users</h4>
                        <p id="quickUsers">0</p>
                    </div>
                </div>

                <div class="action-card" onclick="switchTab('bets')">
                    <div class="action-icon" style="background: rgba(139,92,246,0.15); color: var(--purple);">
                        <i class="bi bi-dice-5-fill"></i>
                    </div>
                    <div class="action-info">
                        <h4>Bets Today</h4>
                        <p id="quickBets">0</p>
                    </div>
                </div>

                <div class="action-card" onclick="switchTab('analytics')">
                    <div class="action-icon" style="background: rgba(245,158,11,0.15); color: var(--warning);">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <div class="action-info">
                        <h4>Win Rate</h4>
                        <p id="quickWinRate">0%</p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: var(--gold-dim); color: var(--gold);">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statRevenue">MWK 0</div>
                    <div class="stat-label">Total Revenue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: var(--success);">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statUsers">0</div>
                    <div class="stat-label">Registered Users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(139,92,246,0.15); color: var(--purple);">
                            <i class="bi bi-dice-5"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statBets">0</div>
                    <div class="stat-label">Total Bets</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: var(--info);">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statTxns">0</div>
                    <div class="stat-label">Transactions</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: var(--danger);">
                            <i class="bi bi-graph-down"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statWithdrawals">MWK 0</div>
                    <div class="stat-label">Total Withdrawals</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: var(--warning);">
                            <i class="bi bi-percent"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="statWinRate">0%</div>
                    <div class="stat-label">Player Win Rate</div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="table-wrapper">
                <div class="loading-overlay" id="dashLoading"><div class="spinner-lg"></div></div>
                
                <div class="table-toolbar">
                    <div class="toolbar-left">
                        <div class="table-title"><i class="bi bi-activity"></i> Recent Activity Feed</div>
                        <span class="record-count" id="activityCount">0 records</span>
                    </div>
                </div>

                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 140px;">Time</th>
                                <th style="width: 80px;">Type</th>
                                <th style="width: 130px;">User</th>
                                <th>Action Details</th>
                                <th style="width: 120px;">Amount</th>
                                <th style="width: 90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="activityBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><p>Loading activity...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== BETS HISTORY TAB ===== -->
        <section class="tab-panel" id="betsPanel">
            <div class="table-wrapper">
                <div class="loading-overlay" id="betsLoading"><div class="spinner-lg"></div></div>
                
                <div class="table-toolbar">
                    <div class="toolbar-left">
                        <div class="table-title"><i class="bi bi-dice-5-fill"></i> Betting History</div>
                        <span class="record-count" id="betsCount">0 bets</span>
                    </div>
                    <div class="toolbar-right">
                        <input type="text" class="search-box" placeholder="Search phone..." id="betSearch" oninput="renderBets()">
                        <select class="filter-dropdown" id="betFilter" onchange="renderBets()">
                            <option value="">All Results</option>
                            <option value="won">Won Only</option>
                            <option value="lost">Lost Only</option>
                        </select>
                    </div>
                </div>

                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th style="width: 140px;">Timestamp</th>
                                <th style="width: 120px;">Phone</th>
                                <th style="width: 110px;">Bet Amount</th>
                                <th style="width: 200px;">Result Breakdown</th>
                                <th style="width: 90px;">Outcome</th>
                                <th>Visual Result</th>
                            </tr>
                        </thead>
                        <tbody id="betsBody">
                            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No betting records</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== TRANSACTIONS TAB ===== -->
        <section class="tab-panel" id="transactionsPanel">
            <div class="table-wrapper">
                <div class="loading-overlay" id="txnsLoading"><div class="spinner-lg"></div></div>
                
                <div class="table-toolbar">
                    <div class="toolbar-left">
                        <div class="table-title"><i class="bi bi-credit-card-2-front"></i> Payment Transactions</div>
                        <span class="record-count" id="txnsCount">0 transactions</span>
                    </div>
                    <div class="toolbar-right">
                        <input type="text" class="search-box" placeholder="Search phone or ID..." id="txnSearch" oninput="renderTxns()">
                        <select class="filter-dropdown" id="txnTypeFilter" onchange="renderTxns()">
                            <option value="">All Types</option>
                            <option value="deposit">Deposits</option>
                            <option value="withdraw">Withdrawals</option>
                        </select>
                        <select class="filter-dropdown" id="txnStatusFilter" onchange="renderTxns()">
                            <option value="">All Status</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 160px;">Transaction ID</th>
                                <th style="width: 140px;">Date/Time</th>
                                <th style="width: 120px;">Phone</th>
                                <th style="width: 90px;">Type</th>
                                <th style="width: 80px;">Provider</th>
                                <th style="width: 120px;">Amount</th>
                                <th style="width: 90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="txnsBody">
                            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No transactions</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== USERS TAB ===== -->
        <section class="tab-panel" id="usersPanel">
            <div class="table-wrapper">
                <div class="loading-overlay" id="usersLoading"><div class="spinner-lg"></div></div>
                
                <div class="table-toolbar">
                    <div class="toolbar-left">
                        <div class="table-title"><i class="bi bi-people-fill"></i> Registered Users</div>
                        <span class="record-count" id="usersCount">0 users</span>
                    </div>
                    <div class="toolbar-right">
                        <input type="text" class="search-box" placeholder="Search name or phone..." id="userSearch" oninput="renderUsers()">
                        <select class="filter-dropdown" id="userSort" onchange="renderUsers()">
                            <option value="newest">Newest First</option>
                            <option value="balance">Highest Balance</option>
                            <option value="active">Most Active</option>
                        </select>
                    </div>
                </div>

                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 220px;">User Profile</th>
                                <th style="width: 120px;">Phone</th>
                                <th style="width: 120px;">Balance</th>
                                <th style="width: 80px;">Bets</th>
                                <th style="width: 120px;">Deposits</th>
                                <th style="width: 120px;">Withdrawals</th>
                                <th style="width: 140px;">Last Active</th>
                            </tr>
                        </thead>
                        <tbody id="usersBody">
                            <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No users found</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== ANALYTICS TAB ===== -->
        <section class="tab-panel" id="analyticsPanel">
            <div class="stats-row" style="margin-bottom: 0;">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: var(--gold-dim); color: var(--gold);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="avgBetSize">MWK 0</div>
                    <div class="stat-label">Avg Bet Size</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: var(--success);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="totalWins">0</div>
                    <div class="stat-label">Total Wins</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: var(--danger);">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="totalLosses">0</div>
                    <div class="stat-label">Total Losses</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: var(--info);">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="houseEdge">MWK 0</div>
                    <div class="stat-label">House Profit</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(245,158,11,0.15); color: var(--warning);">
                            <i class="bi bi-star"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="topBettor">N/A</div>
                    <div class="stat-label">Top Bettor</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon" style="background: rgba(139,92,246,0.15); color: var(--purple);">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="stat-value" id="peakHour">N/A</div>
                    <div class="stat-label">Peak Hour</div>
                </div>
            </div>

            <div class="table-wrapper" style="margin-top: 18px;">
                <div class="table-toolbar">
                    <div class="table-title"><i class="bi bi-bar-chart"></i> Color Distribution Analysis</div>
                </div>
                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th style="width: 100px;">Times Chosen</th>
                                <th style="width: 100px;">Times Won</th>
                                <th style="width: 100px;">Win Rate %</th>
                                <th style="width: 120px;">Total Wagered</th>
                                <th style="width: 120px;">Total Won</th>
                            </tr>
                        </thead>
                        <tbody id="colorAnalysisBody">
                            <tr><td colspan="6"><div class="empty-state"><i class="bi bi-pie-chart"></i><p>Loading analysis...</p></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ===== TOOLS TAB ===== -->
        <section class="tab-panel" id="toolsPanel">
            <div class="quick-actions">
                <div class="action-card" onclick="exportData('csv')">
                    <div class="action-icon" style="background: rgba(16,185,129,0.15); color: var(--success);">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </div>
                    <div class="action-info">
                        <h4>Export CSV</h4>
                        <p>Download all data as spreadsheet</p>
                    </div>
                </div>

                <div class="action-card" onclick="exportData('json')">
                    <div class="action-icon" style="background: rgba(59,130,246,0.15); color: var(--info);">
                        <i class="bi bi-filetype-json"></i>
                    </div>
                    <div class="action-info">
                        <h4>Export JSON</h4>
                        <p>Raw data export for developers</p>
                    </div>
                </div>

                <div class="action-card" onclick="clearCache()">
                    <div class="action-icon" style="background: rgba(239,68,68,0.15); color: var(--danger);">
                        <i class="bi bi-trash"></i>
                    </div>
                    <div class="action-info">
                        <h4>Clear Cache</h4>
                        <p>Force reload all data fresh</p>
                    </div>
                </div>

                <div class="action-card" onclick="toggleAutoRefresh()">
                    <div class="action-icon" style="background: rgba(245,158,11,0.15); color: var(--warning);">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="action-info">
                        <h4>Auto-Refresh</h4>
                        <p id="autoRefreshStatus">Currently: OFF (30s)</p>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <div class="table-toolbar">
                    <div class="table-title"><i class="bi bi-terminal"></i> System Information & Diagnostics</div>
                </div>
                <div class="data-table-container">
                    <table>
                        <tbody id="systemInfoBody">
                            <tr>
                                <td style="width: 200px; font-weight: 700;">Main Database URL</td>
                                <td style="font-family: monospace; font-size: 0.82rem; color: var(--text-muted);">https://awnzbiatwnfmryerfxwg.supabase.co</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Payments Database URL</td>
                                <td style="font-family: monospace; font-size: 0.82rem; color: var(--text-muted);">https://vfntorjzpselgbhkjetz.supabase.co</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Tables Monitored</td>
                                <td><code>bets_history</code>, <code>payments</code>, <code>users</code>, <code>withdrawals</code></td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Last Successful Load</td>
                                <td id="lastLoadTime">Never</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Data Freshness</td>
                                <td id="dataFreshness">--</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Auto-Refresh Interval</td>
                                <td id="refreshInterval">30 seconds</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
// =============================================
// CONFIGURATION
// =============================================
const CONFIG = {
    mainDb: {
        url: "https://awnzbiatwnfmryerfxwg.supabase.co",
        key: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg"
    },
    paymentsDb: {
        url: "https://vfntorjzpselgbhkjetz.supabase.co",
        key: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZmbnRvcmp6cHNlbGdiaGtqZXR6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzYyNTk4ODAsImV4cCI6MjA5MTgzNTg4MH0._AkGUZJ-D5nsLEfcD1xzbEBEz2KLJdzo3pxuZMLTb4A"
    }
};

const mainClient = supabase.createClient(CONFIG.mainDb.url, CONFIG.mainDb.key);
const paymentsClient = supabase.createClient(CONFIG.paymentsDb.url, CONFIG.paymentsDb.key);

// Data stores
let allBets = [];
let allTransactions = [];
let allWithdrawals = []; // NEW: Store withdrawal records
let allUsers = [];

let autoRefreshEnabled = false;
let autoRefreshTimer = null;

// =============================================
// INITIALIZATION
// =============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('%c🎮 CrazyStack Command Center Online', 'color: #fbbf24; font-size: 18px; font-weight: bold;');
    
    refreshData();
});

// =============================================
// DATA LOADING
// =============================================
async function refreshData() {
    const btn = document.getElementById('refreshBtn');
    btn.classList.add('loading');
    
    showLoading(true);

    try {
        const [betsResult, txnsResult, withdrawalsResult, usersResult] = await Promise.all([
            loadBets(),
            loadTransactions(),
            loadWithdrawals(), // NEW: Load withdrawals
            loadUsers()
        ]);

        updateAllStats();
        renderActivityFeed();
        renderBets();
        renderTxns();
        renderWithdrawals(); // NEW: Render withdrawals
        renderUsers();
        renderAnalytics();

        updateTimestamp();
        
        console.log('✅ All data refreshed successfully');

    } catch (error) {
        console.error('❌ Refresh error:', error);
    } finally {
        showLoading(false);
        btn.classList.remove('loading');
    }
}

async function loadBets() {
    const dot = document.getElementById('mainDbDot');
    try {
        const { data, error, count } = await mainClient
            .from('bets_history')
            .select('*', { count: 'exact' })
            .order('time', { ascending: false })
            .limit(1000);

        if (error) throw error;

        allBets = data || [];
        dot.className = 'status-dot on';
        
        return { success: true };
    } catch (error) {
        dot.className = 'status-dot off';
        console.error('Bets error:', error.message);
        return { success: false };
    }
}

async function loadTransactions() {
    const dot = document.getElementById('paymentsDbDot');
    try {
        const { data, error, count } = await paymentsClient
            .from('payments')
            .select('*', { count: 'exact' })
            .order('created_at', { ascending: false })
            .limit(1000);

        if (error) throw error;

        allTransactions = data || [];
        dot.className = 'status-dot on';
        
        return { success: true };
    } catch (error) {
        dot.className = 'status-dot off';
        console.error('Transactions error:', error.message);
        return { success: false };
    }
}

// =============================================
// ✅✅✅ NEW: LOAD WITHDRAWALS FROM WITHDRAWALS TABLE
// =============================================
async function loadWithdrawals() {
    try {
        const { data, error, count } = await paymentsClient
            .from('withdrawals')
            .select('*', { count: 'exact' })
            .order('created_at', { ascending: false })
            .limit(1000);

        if (error) throw error;

        allWithdrawals = data || [];
        console.log(`📊 Loaded ${allWithdrawals.length} withdrawal records`);
        
        return { success: true };
    } catch (error) {
        console.error('❌ Withdrawals loading error:', error.message);
        return { success: false };
    }
}

async function loadUsers() {
    try {
        const { data, error, count } = await mainClient
            .from('users')
            .select('*', { count: 'exact' })
            .order('created_at', { ascending: false });

        if (error) throw error;

        allUsers = data || [];
        return { success: true };
    } catch (error) {
        console.error('Users error:', error.message);
        return { success: false };
    }
}

// =============================================
// STATISTICS
// =============================================
function updateAllStats() {
    // Basic counts
    document.getElementById('statUsers').textContent = allUsers.length.toLocaleString();
    document.getElementById('statBets').textContent = allBets.length.toLocaleString();
    document.getElementById('statTxns').textContent = allTransactions.length.toLocaleString();

    // Quick stats
    document.getElementById('quickRevenue').textContent = `MWK ${formatNum(calcTotalDeposits())}`;
    document.getElementById('quickUsers').textContent = `${allUsers.length} users`;
    document.getElementById('quickBets').textContent = `${allBets.length} bets`;
    document.getElementById('quickWinRate').textContent = `${calcWinRate()}%`;

    // Financial
    const revenue = calcTotalDeposits();
    const withdrawals = calcTotalWithdrawalsFromTable(); // NEW: Calculate from withdrawals table
    
    document.getElementById('statRevenue').textContent = `MWK ${formatNum(revenue)}`;
    document.getElementById('statWithdrawals').textContent = `MWK ${formatNum(withdrawals)}`;
    document.getElementById('statWinRate').textContent = `${calcWinRate()}%`;

    // Record counts
    document.getElementById('activityCount').textContent = `${Math.min(allBets.length + allTransactions.length + allWithdrawals.length, 50)} records`;
    document.getElementById('betsCount').textContent = `${allBets.length} bets`;
    document.getElementById('txnsCount').textContent = `${allTransactions.length} transactions`;
    document.getElementById('usersCount').textContent = `${allUsers.length} users`;

    // Analytics
    updateAnalytics();
}

// =============================================
// ✅✅✅ NEW: CALCULATE TOTAL FROM WITHDRAWALS TABLE
// =============================================
function calcTotalWithdrawalsFromTable() {
    return allWithdrawals
        .filter(w => w.status === 'success' || w.status === 'completed')
        .reduce((sum, w) => sum + (parseFloat(w.amount) || 0), 0);
}

function updateAnalytics() {
    // Average bet size
    const avgBet = allBets.length > 0 
        ? allBets.reduce((sum, b) => sum + (parseFloat(b.amount) || 0), 0) / allBets.length 
        : 0;
    document.getElementById('avgBetSize').textContent = `MWK ${formatNum(avgBet)}`;

    // Total wins/losses
    const wins = allBets.filter(b => b.status === 'won').length;
    const losses = allBets.filter(b => b.status === 'lost').length;
    document.getElementById('totalWins').textContent = wins;
    document.getElementById('totalLosses').textContent = losses;

    // House profit (approximate)
    const totalWagered = allBets.reduce((sum, b) => sum + (parseFloat(b.amount) || 0), 0);
    const totalPaidOut = allBets
        .filter(b => b.status === 'won')
        .reduce((sum, b) => {
            const parts = (b.results || '').split('-Landed-');
            const choice = parts[0]?.trim()?.toUpperCase() || '';
            const multipliers = { GREEN: 1.01, BLUE: 1.35, RED: 2.6, GRAY: 0 };
            return sum + ((parseFloat(b.amount) || 0) * (multipliers[choice] || 1));
        }, 0);
    
    const houseProfit = totalWagered - totalPaidOut;
    document.getElementById('houseEdge').textContent = `MWK ${formatNum(houseProfit)}`;

    // Top bettor
    if (allBets.length > 0) {
        const betCounts = {};
        allBets.forEach(b => {
            betCounts[b.phone] = (betCounts[b.phone] || 0) + 1;
        });
        const topPhone = Object.entries(betCounts).sort((a,b) => b[1] - a[1])[0];
        document.getElementById('topBettor').textContent = topPhone ? `${topPhone[0].slice(-4)} (${topPhone[1]} bets)` : 'N/A';
    }

    // Peak hour
    if (allBets.length > 0) {
        const hourCounts = {};
        allBets.forEach(b => {
            if (b.time) {
                const hour = new Date(b.time).getHours();
                hourCounts[hour] = (hourCounts[hour] || 0) + 1;
            }
        });
        const peakHour = Object.entries(hourCounts).sort((a,b) => b[1] - a[1])[0];
        document.getElementById('peakHour').textContent = peakHour ? `${peakHour[0]}:00` : 'N/A';
    }

    // Color analysis
    renderColorAnalysis();
}

function renderColorAnalysis() {
    const tbody = document.getElementById('colorAnalysisBody');
    const colors = ['GREEN', 'BLUE', 'RED', 'GRAY'];
    
    let html = '';
    colors.forEach(color => {
        const colorBets = allBets.filter(b => (b.results || '').toUpperCase().startsWith(color));
        const chosen = colorBets.length;
        const won = colorBets.filter(b => b.status === 'won').length;
        const winRate = chosen > 0 ? ((won / chosen) * 100).toFixed(1) : 0;
        const wagered = colorBets.reduce((sum, b) => sum + (parseFloat(b.amount) || 0), 0);
        
        const multipliers = { GREEN: 1.01, BLUE: 1.35, RED: 2.6, GRAY: 0 };
        const wonAmount = colorBets
            .filter(b => b.status === 'won')
            .reduce((sum, b) => sum + ((parseFloat(b.amount) || 0) * (multipliers[color] || 1)), 0);

        html += `
            <tr>
                <td>
                    <span class="color-choice color-${color.toLowerCase()}">${color}</span>
                </td>
                <td style="font-family: monospace; font-weight: 700;">${chosen}</td>
                <td style="font-family: monospace; font-weight: 700; color: var(--success);">${won}</td>
                <td style="font-family: monospace; font-weight: 700; color: ${parseFloat(winRate) > 30 ? 'var(--warning)' : 'var(--success)'};">${winRate}%</td>
                <td class="amount">MWK ${formatNum(wagered)}</td>
                <td class="amount amount-pos">MWK ${formatNum(wonAmount)}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html || '<tr><td colspan="6"><div class="empty-state"><i class="bi bi-pie-chart"></i><p>No data for analysis</p></div></td>';
}

// =============================================
// RENDER FUNCTIONS
// =============================================
function renderActivityFeed() {
    const tbody = document.getElementById('activityBody');
    
    let activities = [];

    // Bets
    allBets.slice(0, 25).forEach(bet => {
        const parts = (bet.results || '').split('-Landed-');
        activities.push({
            time: bet.time,
            type: 'bet',
            icon: '🎰',
            user: bet.phone,
            details: `${parts[0]?.trim() || ''} → ${parts[1]?.trim() || ''}`,
            amount: bet.amount,
            status: bet.status
        });
    });

    // Transactions
    allTransactions.slice(0, 25).forEach(txn => {
        activities.push({
            time: txn.created_at,
            type: txn.type,
            icon: txn.type === 'deposit' ? '💰' : '💸',
            user: txn.phone || txn.userId,
            details: txn.provider?.toUpperCase() || '',
            amount: txn.amount,
            status: txn.status
        });
    });

    // NEW: Withdrawals from dedicated table
    allWithdrawals.slice(0, 25).forEach(withdrawal => {
        activities.push({
            time: withdrawal.created_at,
            type: 'withdrawal',
            icon: '🏧',
            user: withdrawal.phone?.toString(),
            details: 'Withdrawal Request',
            amount: withdrawal.amount,
            status: withdrawal.status
        });
    });

    activities.sort((a, b) => new Date(b.time) - new Date(a.time));
    activities = activities.slice(0, 50);

    if (activities.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><p>No recent activity</p></div></td>';
        return;
    }

    tbody.innerHTML = activities.map(a => `
        <tr>
            <td>${formatDate(a.time)}</td>
            <td><span style="font-size: 1.1em;">${a.icon}</span></td>
            <td><span class="phone-link">${a.user}</span></td>
            <td>${a.details}</td>
            <td class="amount ${a.type === 'deposit' ? 'amount-pos' : 'amount-neg'}">${a.type === 'deposit' ? '+' : '-'}MWK ${(parseFloat(a.amount) || 0).toFixed(2)}</td>
            <td><span class="badge badge-${a.status}">${a.status}</span></td>
        </tr>
    `).join('');
}

function renderBets() {
    const tbody = document.getElementById('betsBody');
    const search = document.getElementById('betSearch')?.value.toLowerCase() || '';
    const filter = document.getElementById('betFilter')?.value || '';

    let filtered = allBets.filter(bet => {
        const matchPhone = !search || (bet.phone && bet.phone.includes(search));
        const matchStatus = !filter || bet.status === filter;
        return matchPhone && matchStatus;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No matching bets</p></div></td>';
        return;
    }

    tbody.innerHTML = filtered.map(bet => {
        const parts = (bet.results || '').split('-Landed-');
        const choice = parts[0]?.trim() || 'N/A';
        const landed = parts[1]?.trim() || 'N/A';
        const isWon = bet.status === 'won';

        return `
            <tr>
                <td style="font-family: monospace; font-weight: 700;">#${bet.id}</td>
                <td>${formatDate(bet.time)}</td>
                <td><span class="phone-link">${bet.phone}</span></td>
                <td class="amount amount-neg">MWK ${(parseFloat(bet.amount) || 0).toFixed(2)}</td>
                <td style="font-family: monospace; font-size: 0.82rem;">${bet.results}</td>
                <td><span class="badge ${isWon ? 'badge-won' : 'badge-lost'}">${isWon ? 'WON' : 'LOST'}</span></td>
                <td>
                    <div class="result-display">
                        <span class="color-choice color-${choice.toLowerCase()}">${choice}</span>
                        <span class="result-arrow">landed on</span>
                        <span class="color-choice color-${landed.toLowerCase()}">${landed}</span>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderTxns() {
    const tbody = document.getElementById('txnsBody');
    const search = document.getElementById('txnSearch')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('txnTypeFilter')?.value || '';
    const statusFilter = document.getElementById('txnStatusFilter')?.value || '';

    let filtered = allTransactions.filter(txn => {
        const matchSearch = !search || 
            (txn.phone && txn.phone.includes(search)) ||
            (txn.charge_id && txn.charge_id.includes(search));
        const matchType = !typeFilter || txn.type === typeFilter;
        const matchStatus = !statusFilter || txn.status === statusFilter;
        return matchSearch && matchType && matchStatus;
    });

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No matching transactions</p></div></td>';
        return;
    }

    tbody.innerHTML = filtered.map(txn => `
        <tr>
            <td style="font-family: monospace; font-size: 0.78rem;">${String(txn.id || txn.charge_id || '').substring(0, 18)}...</td>
            <td>${formatDate(txn.created_at)}</td>
            <td><span class="phone-link">${txn.phone || txn.userId || 'N/A'}</span></td>
            <td><span class="badge ${txn.type === 'deposit' ? 'badge-deposit' : 'badge-withdraw'}">${txn.type || 'N/A'}</span></td>
            <td>${txn.provider?.toUpperCase() || '-'}</td>
            <td class="amount ${txn.type === 'deposit' ? 'amount-pos' : 'amount-neg'}">${txn.type === 'deposit' ? '+' : '-'}MWK ${(parseFloat(txn.amount) || 0).toFixed(2)}</td>
            <td><span class="badge badge-${txn.status}">${txn.status || 'pending'}</span></td>
        </tr>
    `).join('');
}

// =============================================
// ✅✅✅ NEW: RENDER WITHDRAWALS TABLE
// =============================================
function renderWithdrawals() {
    // You can add a separate tab or section for withdrawals if needed
    // For now, they're included in the activity feed
    console.log(`📋 Rendering ${allWithdrawals.length} withdrawal records`);
}

function renderUsers() {
    const tbody = document.getElementById('usersBody');
    const search = document.getElementById('userSearch')?.value.toLowerCase() || '';
    const sort = document.getElementById('userSort')?.value || 'newest';

    let filtered = allUsers.filter(user => {
        return !search || 
            (user.phone && user.phone.includes(search)) ||
            (user.name && user.name.toLowerCase().includes(search));
    });

    // Sort
    switch(sort) {
        case 'balance':
            filtered.sort((a, b) => (parseFloat(b.balance) || 0) - (parseFloat(a.balance) || 0));
            break;
        case 'active':
            filtered.sort((a, b) => {
                const aCount = allBets.filter(bet => bet.phone === a.phone).length;
                const bCount = allBets.filter(bet => bet.phone === b.phone).length;
                return bCount - aCount;
            });
            break;
        default:
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No users found</p></div></td>';
        return;
    }

    tbody.innerHTML = filtered.map(user => {
        const userBets = allBets.filter(b => b.phone === user.phone);
        const userDeposits = allTransactions.filter(t => t.phone === user.phone && t.type === 'deposit' && t.status === 'success');
        const userWithdrawals = allWithdrawals.filter(w => w.phone == user.phone && (w.status === 'success' || w.status === 'completed')); // NEW: Use withdrawals table

        return `
            <tr>
                <td>
                    <div class="user-cell">
                        <div class="avatar-sm">${(user.name || 'U').charAt(0).toUpperCase()}</div>
                        <span class="user-name">${user.name || 'Unknown'}</span>
                    </div>
                </td>
                <td><span class="phone-link">${user.phone}</span></td>
                <td class="amount amount-gold">MWK ${(parseFloat(user.balance) || 0).toFixed(2)}</td>
                <td style="font-weight: 700;">${userBets.length}</td>
                <td class="amount amount-pos">+MWK ${formatNum(userDeposits.reduce((s,t) => s + (parseFloat(t.amount)||0), 0))}</td>
                <td class="amount amount-neg">-MWK ${formatNum(userWithdrawals.reduce((s,w) => s + (parseFloat(w.amount)||0), 0))}</td>
                <td style="color: var(--text-muted); font-size: 0.82rem;">${formatDate(user.updated_at || user.created_at)}</td>
            </tr>
        `;
    }).join('');
}

function renderAnalytics() {
    // Already handled in updateAnalytics()
}

// =============================================
// TAB SWITCHING
// =============================================
function switchTab(tabName) {
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });

    // Update panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    document.getElementById(tabName + 'Panel').classList.add('active');
}

// =============================================
// UTILITIES
// =============================================
function showLoading(show) {
    ['dashLoading', 'betsLoading', 'txnsLoading', 'usersLoading'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('show', show);
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '--';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return 'Invalid';
    
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatNum(num) {
    if (!num || isNaN(num)) return '0.00';
    return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

function updateTimestamp() {
    const now = new Date();
    document.getElementById('lastUpdate').textContent = now.toLocaleTimeString();
    document.getElementById('lastLoadTime').textContent = now.toLocaleString();
    document.getElementById('dataFreshness').textContent = 'Just now';
}

// Calculation helpers
function calcTotalDeposits() {
    return allTransactions
        .filter(t => t.status === 'success' && t.type === 'deposit')
        .reduce((s, t) => s + (parseFloat(t.amount) || 0), 0);
}

function calcTotalWithdrawals() {
    return allTransactions
        .filter(t => t.status === 'success' && t.type === 'withdraw')
        .reduce((s, t) => s + (parseFloat(t.amount) || 0), 0);
}

function calcWinRate() {
    if (allBets.length === 0) return 0;
    const won = allBets.filter(b => b.status === 'won').length;
    return ((won / allBets.length) * 100).toFixed(1);
}

// =============================================
// TOOLS
// =============================================
function exportData(format) {
    alert(`Exporting data as ${format.toUpperCase()}...\n\nThis will download all visible data.`);
    // Implement actual export logic here
}

function clearCache() {
    if (confirm('Clear all cached data and reload?')) {
        location.reload();
    }
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    
    if (autoRefreshEnabled) {
        autoRefreshTimer = setInterval(refreshData, 30000);
        document.getElementById('autoRefreshStatus').textContent = 'Currently: ON (30s)';
        document.getElementById('refreshInterval').textContent = '30 seconds (ACTIVE)';
    } else {
        clearInterval(autoRefreshTimer);
        document.getElementById('autoRefreshStatus').textContent = 'Currently: OFF';
        document.getElementById('refreshInterval').textContent = '30 seconds (disabled)';
    }
}

// =============================================
// ✅✅✅ NEW: INSERT WITHDRAWAL RECORD INTO WITHDRAWALS TABLE
// =============================================
async function insertWithdrawalRecord(phone, amount, status = 'pending') {
    try {
        console.log(`💾 Inserting withdrawal record: Phone=${phone}, Amount=${amount}, Status=${status}`);
        
        const { data, error } = await paymentsClient
            .from('withdrawals')
            .insert([
                {
                    phone: parseInt(phone),
                    amount: parseFloat(amount),
                    status: status
                }
            ])
            .select();

        if (error) {
            console.error('❌ Failed to insert withdrawal record:', error);
            return { success: false, error: error.message };
        }

        console.log('✅ Withdrawal record inserted successfully:', data);
        return { success: true, data: data };

    } catch (err) {
        console.error('❌ Exception inserting withdrawal:', err);
        return { success: false, error: err.message };
    }
}

// Make this function globally available for the game app
window.insertWithdrawalRecord = insertWithdrawalRecord;

</script>

<script>
//=============================================
// SUPABASE CONFIGURATION
//=============================================
const SUPABASE_PROJECT_URL = "https://awnzbiatwnfmryerfxwg.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";

const supabaseClient = supabase.createClient(SUPABASE_PROJECT_URL, SUPABASE_ANON_KEY);

const PAYMENTS_DB_URL = "https://vfntorjzpselgbhkjetz.supabase.co";
const PAYMENTS_DB_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZmbnRvcmp6cHNlbGdiaGtqZXR6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzYyNTk4ODAsImV4cCI6MjA5MTgzNTg4MH0._AkGUZJ-D5nsLEfcD1xzbEBEz2KLJdzo3pxuZMLTb4A";
const paymentsDbClient = supabase.createClient(PAYMENTS_DB_URL, PAYMENTS_DB_KEY);

function createAuthenticatedClient(sessionToken) {
    return supabase.createClient(SUPABASE_PROJECT_URL, SUPABASE_ANON_KEY, {
        headers: { 'x-session-token': sessionToken }
    });
}

// Session configuration - MUST be defined before use
const SESSION_CONFIG = {
    STORAGE_KEY: 'crazyStack_user_session'
};

//=============================================
// GAME CONFIGURATION
//=============================================
const WHEEL_CONFIG = {
    segments: [
        { name: 'Green', key: 'green', hexColor: '#10b981', multiplier: 1.01, slots: 3 },
        { name: 'Blue', key: 'blue', hexColor: '#3b82f6', multiplier: 1.35, slots: 2 },
        { name: 'Red', key: 'red', hexColor: '#ef4444', multiplier: 2.6, slots: 1 },
        { name: 'Gray', key: 'gray', hexColor: '#64748b', multiplier: 0, slots: 2 }
    ],
    minimumBet: 50,
    totalSegments: 8
};

let currentActiveTab = 'login';
let isTabTransitioning = false;

//=============================================
// DUPLICATE PREVENTION SYSTEM
//=============================================
class DuplicatePreventionSystem {
    constructor() { 
        this.processedTransactions = new Set(); 
        this.activePollingSessions = new Map(); 
        this.isProcessingPayment = false; 
    }
    
    isTransactionProcessed(id) { return this.processedTransactions.has(id); }
    
    markTransactionProcessed(id) { 
        this.processedTransactions.add(id); 
        this.persistProcessedTransactions(); 
    }
    
    hasActivePollingSession(id) { return this.activePollingSessions.has(id); }
    
    registerPollingSession(id, intervalId) { this.activePollingSessions.set(id, intervalId); }
    
    unregisterPollingSession(id) { 
        if (this.activePollingSessions.has(id)) { 
            clearInterval(this.activePollingSessions.get(id)); 
            this.activePollingSessions.delete(id); 
        } 
    }
    
    acquireProcessingLock() { 
        if (this.isProcessingPayment) return false; 
        this.isProcessingPayment = true; 
        return true; 
    }
    
    releaseProcessingLock() { this.isProcessingPayment = false; }
    
    persistProcessedTransactions() { 
        try { 
            sessionStorage.setItem('crazyStack_processed_txns', JSON.stringify(Array.from(this.processedTransactions))); 
        } catch(e) {} 
    }
    
    loadPersistedTransactions() { 
        try { 
            const stored = sessionStorage.getItem('crazyStack_processed_txns'); 
            if(stored) this.processedTransactions = new Set(JSON.parse(stored)); 
        } catch(e) {} 
    }
    
    clearAll() { 
        this.processedTransactions.clear(); 
        this.activePollingSessions.forEach(id => clearInterval(id)); 
        this.activePollingSessions.clear(); 
        this.isProcessingPayment = false; 
        sessionStorage.removeItem('crazyStack_processed_txns'); 
    }
}
const duplicatePrevention = new DuplicatePreventionSystem();

//=============================================
// THEME MANAGER
//=============================================
class ThemeManager {
    constructor() { this.currentTheme = 'dark'; this.init(); }
    
    init() { this.applyTheme(localStorage.getItem('crazyStack_theme') || 'dark'); }
    
    applyTheme(name) {
        this.currentTheme = name; 
        document.documentElement.setAttribute('data-theme', name); 
        localStorage.setItem('crazyStack_theme', name);
        this.updateToggleUI(name === 'light');
        
        const icon = document.getElementById('themeIconSvg');
        if(icon) {
            icon.innerHTML = name === 'light' 
                ? '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>' 
                : '<circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>';
        }
    }
    
    toggleTheme() { this.applyTheme(this.currentTheme === 'dark' ? 'light' : 'dark'); }
    
    updateToggleUI(isLight) { 
        const cb = document.getElementById('themeToggleCheckbox'); 
        if(cb) cb.checked = isLight; 
        
        const lb = document.getElementById('themeStatusLabel'); 
        if(lb) lb.textContent = isLight ? 'LIGHT' : 'DARK'; 
    }
}
const themeManager = new ThemeManager();

//=============================================
// AUDIO ENGINE
//=============================================
class SoundEngine {
    constructor() { 
        this.isEnabled = true; 
        this.audioContext = null; 
        this.tickTimer = null; 
        this.bgMusicEnabled = false; 
        this.bgMusicElement = null; 
        this.bgMusicVolume = 0.3; 
        this.initContext(); 
        this.initBackgroundMusic(); 
    }
    
    initContext() { try { this.audioContext = new (window.AudioContext || window.webkitAudioContext)(); } catch(e) {} }
    
    initBackgroundMusic() {
        this.bgMusicElement = new Audio('aud/bg_music.m4a'); 
        this.bgMusicElement.loop = true; 
        this.bgMusicElement.volume = this.bgMusicVolume; 
        this.bgMusicElement.preload = 'auto';
        
        this.bgMusicElement.addEventListener('error', () => { 
            this.bgMusicEnabled = false; 
            this.updateMusicToggleUI(false); 
        }, { once: true });
        
        if(localStorage.getItem('crazyStack_bgmusic') === 'true') { 
            this.bgMusicEnabled = true; 
            this.playBackgroundMusic(); 
        }
        
        const sv = localStorage.getItem('crazyStack_music_volume'); 
        if(sv !== null) { 
            this.bgMusicVolume = parseFloat(sv); 
            this.bgMusicElement.volume = this.bgMusicVolume; 
        }
        
        if(localStorage.getItem('crazyStack_sfx') === 'false') this.isEnabled = false;
    }
    
    playTone(freq = 850, dur = 0.1, type = 'sine') { 
        if(!this.isEnabled || !this.audioContext) return; 
        try { 
            if(this.audioContext.state === 'suspended') this.audioContext.resume(); 
            
            const o = this.audioContext.createOscillator(), 
                  g = this.audioContext.createGain(); 
                  
            o.connect(g); 
            g.connect(this.audioContext.destination); 
            o.frequency.value = freq; 
            o.type = type; 
            
            const n = this.audioContext.currentTime; 
            g.gain.setValueAtTime(0.15, n); 
            g.gain.exponentialRampToValueAtTime(0.001, n + dur); 
            o.start(n); 
            o.stop(n + dur); 
        } catch(e) {} 
    }
    
    clickSound() { this.playTone(920, 0.06); }
    
    successSound() { 
        this.playTone(523, 0.12); 
        setTimeout(() => this.playTone(659, 0.12), 120); 
        setTimeout(() => this.playTone(784, 0.18), 240); 
    }
    
    errorSound() { this.playTone(220, 0.25, 'square'); }
    
    playBikeClick(intensity = 1) { 
        if(!this.isEnabled || !this.audioContext) return; 
        try { 
            if(this.audioContext.state === 'suspended') this.audioContext.resume(); 
            
            const ctx = this.audioContext, 
                  now = ctx.currentTime, 
                  bs = ctx.sampleRate * 0.02, 
                  buf = ctx.createBuffer(1, bs, ctx.sampleRate), 
                  d = buf.getChannelData(0);
                  
            for(let i=0;i<bs;i++) d[i]=(Math.random()*2-1)*Math.exp(-i/(bs*0.3));
            
            const ns=ctx.createBufferSource();
            ns.buffer=buf;
            
            const f=ctx.createBiquadFilter();
            f.type='bandpass';
            f.frequency.value=2500+Math.random()*1500;
            f.Q.value=2+Math.random();
            
            const g=ctx.createGain();
            g.gain.setValueAtTime(0.12*intensity,now);
            g.gain.exponentialRampToValueAtTime(0.001,now+0.025);
            
            ns.connect(f);
            f.connect(g);
            g.connect(ctx.destination);
            ns.start(now);
            ns.stop(now+0.03); 
        } catch(e) {} 
    }
    
    startBicycleSpin(onComplete) { 
        this.stopBicycleSpin(); 
        let t=0; 
        const m=80; 
        
        const d=()=>{
            if(t>=m){
                if(onComplete) onComplete();
                return; 
            } 
            
            this.playBikeClick(Math.max(0.2, 1-Math.pow(t/m,1.5))); 
            t++; 
            
            let dl;
            if(t<20) dl=70+t*1.5;
            else if(t<45) dl=100*Math.pow(1.06,t-20);
            else if(t<70) dl=280*Math.pow(1.04,t-45);
            else dl=450+t*15;
            
            if(dl>700) dl=700;
            this.tickTimer=setTimeout(d,dl);
        }; 
        
        d(); 
    }
    
    stopBicycleSpin() { 
        if(this.tickTimer){
            clearTimeout(this.tickTimer);
            this.tickTimer=null;
        } 
    }
    
    playStopSound() { 
        if(!this.isEnabled||!this.audioContext)return; 
        try{
            const c=this.audioContext,n=c.currentTime;
            [0,80].forEach(d=>{
                const o=c.createOscillator(),
                      g=c.createGain(),
                      f=c.createBiquadFilter();
                      
                o.type='square';
                o.frequency.value=180;
                f.type='lowpass';
                f.frequency.value=800;
                g.gain.setValueAtTime(0.15,n+d/1000);
                g.gain.exponentialRampToValueAtTime(0.001,n+(d+100)/1000);
                o.connect(f);
                f.connect(g);
                g.connect(c.destination);
                o.start(n+d/1000);
                o.stop(n+(d+120)/1000);
            });
        }catch(e){} 
    }
    
    toggleSound() { 
        this.isEnabled=!this.isEnabled; 
        localStorage.setItem('crazyStack_sfx',this.isEnabled.toString()); 
        if(this.isEnabled)this.clickSound(); 
        this.updateSfxToggleUI(this.isEnabled); 
    }
    
    updateSfxToggleUI(isOn) { 
        const c=document.getElementById('sfxToggleCheckbox');
        if(c) c.checked=isOn; 
        
        const l=document.getElementById('sfxStatusLabel');
        if(l) l.textContent=isOn?'ON':'OFF'; 
    }
    
    playBackgroundMusic() { 
        if(!this.bgMusicElement)return; 
        
        const p=this.bgMusicElement.play(); 
        if(p!==undefined){
            p.then(()=>{
                this.bgMusicEnabled=true;
                this.updateMusicToggleUI(true);
            }).catch(()=>{
                this.bgMusicEnabled=false;
                this.updateMusicToggleUI(false);
            });
        } 
    }
    
    stopBackgroundMusic() { 
        if(!this.bgMusicElement)return; 
        this.bgMusicElement.pause(); 
        this.bgMusicElement.currentTime=0; 
        this.bgMusicEnabled=false; 
        this.updateMusicToggleUI(false); 
    }
    
    toggleBackgroundMusic() { 
        if(this.bgMusicEnabled){
            this.stopBackgroundMusic();
            localStorage.setItem('crazyStack_bgmusic','false');
        }else{
            this.playBackgroundMusic();
            localStorage.setItem('crazyStack_bgmusic','true');
        } 
    }
    
    setMusicVolume(v) { 
        this.bgMusicVolume=Math.max(0,Math.min(1,v)); 
        if(this.bgMusicElement) this.bgMusicElement.volume=this.bgMusicVolume; 
        localStorage.setItem('crazyStack_music_volume',this.bgMusicVolume.toString()); 
    }
    
    updateMusicToggleUI(isOn) { 
        const c=document.getElementById('musicToggleCheckbox');
        if(c) c.checked=isOn; 
        
        const l=document.getElementById('musicStatusLabel');
        if(l) l.textContent=isOn?'ON':'OFF'; 
        
        const vc=document.getElementById('musicVolumeControl');
        if(vc) vc.classList.toggle('visible',isOn); 
    }
}
const soundEngine = new SoundEngine();

//=============================================
// UI HELPERS
//=============================================
function showToastMessage(message, toastType = '') {
    const t = document.getElementById('toastElement'); 
    if(!t) return; 
    
    t.textContent = message; 
    t.className = `toast-notification visible ${toastType ? 'type-' + toastType : ''}`;
    
    if(toastType === 'success') soundEngine.successSound(); 
    else if(toastType === 'error') soundEngine.errorSound();
    
    setTimeout(() => { t.className = 'toast-notification'; }, 3800);
}

function showCustomConfirm(message, onYes) {
    const o=document.getElementById('confirmOverlay'),
          t=o.querySelector('.confirm-title'),
          m=o.querySelector('.confirm-msg'),
          yb=document.getElementById('confirmYes'),
          nb=document.getElementById('confirmNo');
          
    t.textContent='Confirm Action';
    m.textContent=message||'Are you sure?';
    o.classList.add('active');
    soundEngine.clickSound();
    
    const ny=yb.cloneNode(true),
          nn=nb.cloneNode(true);
          
    yb.parentNode.replaceChild(ny,yb);
    nb.parentNode.replaceChild(nn,nb);
    
    ny.addEventListener('click',()=>{
        o.classList.remove('active');
        if(onYes) onYes();
    });
    
    nn.addEventListener('click',()=>{ o.classList.remove('active'); });
}

function switchToTab(targetTabId) {
    soundEngine.clickSound(); 
    if(isTabTransitioning || targetTabId === currentActiveTab) return; 
    
    isTabTransitioning = true;
    
    document.querySelectorAll('.tab-item').forEach(tab => tab.classList.toggle('current', tab.dataset.target === targetTabId));
    document.getElementById('loginPanel').classList.toggle('hidden', targetTabId !== 'loginPanel');
    document.getElementById('signupPanel').classList.toggle('hidden', targetTabId !== 'signupPanel');
    
    const at=document.getElementById('authModalTitle'),
          ad=document.getElementById('authModalDesc');
          
    if(targetTabId==='loginPanel'){
        at.textContent='Welcome Back';
        ad.textContent='Sign in to start playing';
    }else{
        at.textContent='Create Account';
        ad.textContent='Join CrazyStack today';
    }
    
    setTimeout(()=>{ isTabTransitioning=false; currentActiveTab=targetTabId; },180);
}

//=============================================
// ✅✅✅ MAIN APPLICATION CLASS - BALANCE BUGS FIXED
//=============================================
class CrazyStackApp {
    constructor() {
        // Initialize ALL properties FIRST before any method calls
        this.currentBalance = 0;
        this.selectedColor = null;
        this.isWheelSpinning = false;
        this.wheelRotationAngle = 0;
        this.isUserAuthenticated = false;
        this.playerName = '';
        this.playerPhone = '';
        this.selectedPaymentProvider = null;
        this.currentTransactionType = null;
        this.sessionToken = null;
        this.sessionMonitorInterval = null;
        this.isRestoringSession = false;
        this.balanceFetchInProgress = false;
        this.balanceRetryCount = 0;
        this.maxBalanceRetries = 3;
        
        // Canvas setup
        this.canvasElement = document.getElementById('wheelCanvas');
        this.canvasContext = this.canvasElement ? this.canvasElement.getContext('2d') : null;
        
        // Initialize app
        this.initializeApplication();
    }

    generateSessionToken() {
        const array = new Uint8Array(32); 
        crypto.getRandomValues(array);
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    }

    getDbClient() {
        if (this.sessionToken) return createAuthenticatedClient(this.sessionToken);
        return supabaseClient;
    }

    initializeApplication() {
        duplicatePrevention.loadPersistedTransactions();
        this.renderWheelGraphic(); 
        this.attachAllEventListeners(); 
        this.setupGlobalClickSounds();
        // Don't call restoreUserSession here - wait for window load
    }

    setupGlobalClickSounds() {
        document.addEventListener('click', (e) => { 
            if(e.target.closest('button') && !e.target.closest('#settingsPopover')) soundEngine.clickSound(); 
        });
    }

    // =============================================
    // ✅✅✅ BALANCE MANAGEMENT SYSTEM - COMPLETELY REWRITTEN
    // =============================================
    
    /**
     * Force refresh balance from database with retry logic
     * This is the ONLY method that should update balance from DB
     */
    async forceRefreshBalance(source = 'unknown', retryCount = 0) {
        // Prevent concurrent fetches
        if (this.balanceFetchInProgress) {
            console.log(`⏳ Balance fetch already in progress, waiting...`);
            // Wait for current fetch to complete
            await new Promise(resolve => setTimeout(resolve, 500));
            if (this.balanceFetchInProgress) {
                return this.currentBalance;
            }
        }
        
        if (!this.playerPhone) {
            console.warn(`[${source}] ❌ Cannot fetch balance: no phone number`);
            return this.currentBalance;
        }
        
        this.balanceFetchInProgress = true;
        this.balanceRetryCount = retryCount;
        
        try {
            console.log(`🔄 [${source}] Fetching balance from DB (attempt ${retryCount + 1}/${this.maxBalanceRetries + 1})...`);
            console.log(`📱 Phone: ${this.playerPhone}`);
            
            // Show loading state on balance display
            this.showBalanceLoading();
            
            const result = await supabaseClient.rpc('get_balance_by_phone', { 
                p_phone: this.playerPhone 
            });
            
            const { data: dbBalance, error } = result;
            
            console.log(`📊 [${source}] DB Response:`, { data: dbBalance, error });
            
            if (error) {
                console.error(`❌ [${source}] Database Error:`, error);
                
                // Retry logic
                if (retryCount < this.maxBalanceRetries) {
                    console.log(`🔁 Retrying in 1 second... (${retryCount + 1}/${this.maxBalanceRetries})`);
                    this.balanceFetchInProgress = false;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    return this.forceRefreshBalance(`${source}-retry`, retryCount + 1);
                }
                
                throw error;
            }
            
            if (dbBalance === null || dbBalance === undefined) {
                console.warn(`⚠️ [${source}] No balance returned from DB`);
                
                // Retry logic
                if (retryCount < this.maxBalanceRetries) {
                    console.log(`🔁 Retrying in 1 second... (${retryCount + 1}/${this.maxBalanceRetries})`);
                    this.balanceFetchInProgress = false;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    return this.forceRefreshBalance(`${source}-retry`, retryCount + 1);
                }
                
                throw new Error('No balance data returned');
            }
            
            // Parse and validate balance
            const newBalance = parseFloat(dbBalance);
            
            if (isNaN(newBalance)) {
                throw new Error(`Invalid balance value: ${dbBalance}`);
            }
            
            // ✅ SUCCESS: Update balance
            console.log(`✅ [${source}] Balance fetched successfully: MWK ${newBalance.toFixed(2)}`);
            
            this.currentBalance = newBalance;
            this.updateBalanceDisplay();
            this.saveUserSession(this.playerPhone, this.playerName, this.currentBalance);
            
            return this.currentBalance;
            
        } catch (error) {
            console.error(`❌ [${source}] Failed to fetch balance:`, error.message);
            
            // Don't change existing balance on error, just log it
            return this.currentBalance;
            
        } finally {
            this.balanceFetchInProgress = false;
            this.hideBalanceLoading();
        }
    }
    
    /**
     * Show loading state on balance display
     */
    showBalanceLoading() {
        const el = document.getElementById('displayBalance');
        if (el) {
            el.innerHTML = '<span style="opacity: 0.7;">Loading...</span>';
        }
    }
    
    /**
     * Hide loading state on balance display
     */
    hideBalanceLoading() {
        this.updateBalanceDisplay();
    }
    
    /**
     * Update balance in memory and on screen immediately
     */
    setBalanceImmediate(newBalance, source = 'manual') {
        const parsed = parseFloat(newBalance) || 0;
        this.currentBalance = parsed;
        this.updateBalanceDisplay();
        console.log(`💰 [${source}] Balance set immediately: MWK ${parsed.toFixed(2)}`);
    }

    // =============================================
    // SESSION MANAGEMENT - FIXED
    // =============================================
    
    async restoreUserSession(hood) {
        // Prevent double restoration
        if(this.isRestoringSession) {
            console.log('⏳ Session restoration already in progress');
            return;
        }
        
        this.isRestoringSession = true;
        
        try {
            const savedSession = sessionStorage.getItem(SESSION_CONFIG.STORAGE_KEY);
            
            if(!savedSession) { 
                this.showLoginModal(); 
                return; 
            }
            
            let sessionData;
            try {
                sessionData = JSON.parse(savedSession);
            } catch(e) {
                this.clearUserSession();
                this.showLoginModal();
                return;
            }
            
            console.log('📦 Saved session data:', sessionData);
            
            if(!sessionData || !sessionData.authenticated || !sessionData.playerPhone) { 
                console.log('❌ Invalid session data - missing required fields');
                this.clearUserSession(); 
                this.showLoginModal(); 
                return; 
            }
            
            // Get session token
            this.sessionToken = sessionStorage.getItem('crazyStack_session_token');
            
            if(!this.sessionToken) { 
                console.log('❌ No session token found');
                this.clearUserSession(); 
                this.showLoginModal(); 
                showToastMessage('Session invalid, please log in again.', 'error'); 
                return; 
            }

            // Set user info from saved session
            this.isUserAuthenticated = true; 
            this.playerName = sessionData.playerName || ''; 
            this.playerPhone = sessionData.playerPhone;
            
            
            console.log(`👤 User: ${this.playerName} (${this.playerPhone})`);
            console.log(`🔑 Token: ${this.sessionToken.substring(0, 20)}...`);
            
            // ✅ CRITICAL FIX: Force fresh balance from DB
            console.log('💰 Fetching FRESH balance from database...');
            
            await this.forceRefreshBalance('session-restore');
            
            // Update authentication UI
            this.updateAuthenticationUI(); 
            this.hideModal('authModalOverlay');
            
            // Show welcome message with ACTUAL balance
            const msg = hood 
                ? `Ready! Your balance: MWK ${this.currentBalance.toFixed(2)}`
                : `Welcome back, ${this.playerName}! Balance: MWK ${this.currentBalance.toFixed(2)}`;
            showToastMessage(msg, 'success');
            document.getElementById('paymentPhoneInput').value=this.playerPhone;
            // Start session monitoring
            this.startSessionMonitoring();
            
            console.log('✅ Session restored successfully!');
            console.log(`💰 Final Balance: MWK ${this.currentBalance.toFixed(2)}`);
            console.log('🔄 =======================================\n');
            
        } catch(error) { 
            console.error('❌ Session restoration failed:', error);
            console.error('Error details:', error.stack);
            this.clearUserSession(); 
            this.showLoginModal(); 
        } finally {
            this.isRestoringSession = false;
        }
    }
phone(){
document.getElementById('paymentPhoneInput').value=this.playerPhone;
}

    startSessionMonitoring() {
        this.stopSessionMonitoring();
        
        if(this.isUserAuthenticated && this.playerPhone && this.sessionToken) {
            console.log('👁️ Starting session monitoring (checks every 5 minutes)');
            
            this.sessionMonitorInterval = setInterval(async () => {
                try {
                    const {data, error} = await supabaseClient.from('users')
                        .select('session_token')
                        .eq('phone', this.playerPhone)
                        .eq('session_token', this.sessionToken)
                        .single();
                        
                    if(error) {
                        console.error('Session check error:', error);
                        return;
                    }
                    
                    if(!data) {
                        console.log('⏰ Session expired on server - forcing logout');
                        this.forceLogoutDueToTimeout();
                    }
                } catch(e) {
                    console.error('Session monitor exception:', e);
                }
            }, 5 * 60 * 1000); // Every 5 minutes
        }
    }

    stopSessionMonitoring() { 
        if(this.sessionMonitorInterval) { 
            clearInterval(this.sessionMonitorInterval); 
            this.sessionMonitorInterval = null; 
        } 
    }
    
    saveUserSession(phone, name, balance) { 
        try {
            const sessionData = { 
                authenticated: true, 
                playerPhone: phone, 
                playerName: name, 
                balance: parseFloat(balance) || 0,
                timestamp: Date.now() 
            };
            
            sessionStorage.setItem(SESSION_CONFIG.STORAGE_KEY, JSON.stringify(sessionData));
            console.log('💾 Session saved to storage:', sessionData);
        } catch(e) {
            console.error('❌ Failed to save session:', e);
        }
    }
    
    clearUserSession() { 
        console.log('🗑️ Clearing all session data');
        sessionStorage.removeItem(SESSION_CONFIG.STORAGE_KEY); 
        sessionStorage.removeItem('crazyStack_session_token'); 
        duplicatePrevention.clearAll(); 
    }

    showLoginModal() { 
        this.hideModal('authModalOverlay'); 
        setTimeout(() => { 
            isTabTransitioning = false; 
            currentActiveTab = 'login'; 
            switchToTab('loginPanel'); 
            this.showModal('authModalOverlay'); 
        }, 100); 
    }
    
    forceLogoutDueToTimeout() {
        console.log('⚠️ FORCE LOGOUT: Session timeout');
        
        this.stopSessionMonitoring();
        
        // Clear token on server
        if(this.playerPhone && this.sessionToken) {
            supabaseClient.from('users')
                .update({session_token: null})
                .eq('phone', this.playerPhone)
                .eq('session_token', this.sessionToken)
                .then(({error}) => {
                    if(error) console.error('Failed to clear server token:', error);
                    else console.log('✅ Server token cleared');
                })
                .catch(e => console.error('Server token clear exception:', e));
        }
        
        // Reset all state
        this.setBalanceImmediate(0, 'logout');
        this.clearUserSession(); 
        this.isUserAuthenticated = false; 
        this.playerName = ''; 
        this.playerPhone = '';
        this.sessionToken = null;
        
        // Update UI
        this.updateAuthenticationUI(); 
        this.showModal('authModalOverlay');
        
        isTabTransitioning = false; 
        currentActiveTab = 'login'; 
        switchToTab('loginPanel');
        
        const at=document.getElementById('authModalTitle'),
              ad=document.getElementById('authModalDesc');
              
        if(at) at.textContent='Session Expired'; 
        if(ad) ad.textContent='You were logged out due to inactivity. Please sign in again.';
        
        showToastMessage('Session expired due to inactivity', 'error'); 
        soundEngine.errorSound();
    }

    attachAllEventListeners() {
        // Color selection
        document.querySelectorAll('.color-option').forEach(b => 
            b.addEventListener('click', () => this.selectBettingColor(b.dataset.color))
        );
        
        // Spin button
        document.getElementById('spinAction')?.addEventListener('click', () => this.executeSpin());
        
        // Auth button
        document.getElementById('authButton')?.addEventListener('click', () => this.handleAuthenticationToggle());
        
        // Tab switching
        document.querySelectorAll('.tab-item').forEach(b => 
            b.addEventListener('click', () => switchToTab(b.dataset.target))
        );
        
        // Login/Signup forms
        document.getElementById('executeLogin')?.addEventListener('click', () => this.processLogin());
        document.getElementById('executeSignup')?.addEventListener('click', () => this.processSignup());
        
        // Modal controls
        document.getElementById('dismissAuthModal')?.addEventListener('click', () => this.hideModal('authModalOverlay'));
        document.querySelectorAll('.auth-switcher strong').forEach(l => 
            l.addEventListener('click', () => switchToTab(l.dataset.target))
        );
        
        // Payment buttons
        document.getElementById('depositBtn')?.addEventListener('click', () => this.openPaymentModal('deposit'));
        document.getElementById('withdrawBtn')?.addEventListener('click', () => this.openPaymentModal('withdraw'));
        document.getElementById('confirmPayment')?.addEventListener('click', (e) => this.processPaymentTransaction(e));
        document.getElementById('dismissPaymentModal')?.addEventListener('click', () => this.hideModal('paymentModalOverlay'));
        
        // Payment provider selection
        document.querySelectorAll('.provider-choice').forEach(b => 
            b.addEventListener('click', () => this.selectPaymentProvider(b.dataset.provider))
        );
        
        document.getElementById('paymentPhoneInput')?.addEventListener('input', () => this.autoDetectNetworkProvider());
        
        // Settings
        document.getElementById('fabSettingsBtn')?.addEventListener('click', () => this.toggleSettingsPopover());
        document.getElementById('themeSettingEntry')?.addEventListener('click', (e) => { 
            e.preventDefault(); 
            themeManager.toggleTheme(); 
        });
        
        document.getElementById('themeToggleCheckbox')?.addEventListener('change', () => 
            themeManager.applyTheme(document.getElementById('themeToggleCheckbox').checked ? 'light' : 'dark')
        );
        
        document.getElementById('soundToggleEntry')?.addEventListener('click', (e) => { 
            e.preventDefault(); 
            soundEngine.toggleSound(); 
        });
        
        document.getElementById('sfxToggleCheckbox')?.addEventListener('change', () => { 
            if(document.getElementById('sfxToggleCheckbox').checked !== soundEngine.isEnabled) soundEngine.toggleSound(); 
        });
        
        document.getElementById('musicToggleEntry')?.addEventListener('click', (e) => { 
            e.preventDefault(); 
            soundEngine.toggleBackgroundMusic(); 
        });
        
        document.getElementById('musicToggleCheckbox')?.addEventListener('change', () => { 
            if(document.getElementById('musicToggleCheckbox').checked !== soundEngine.bgMusicEnabled) soundEngine.toggleBackgroundMusic(); 
        });
        
        document.getElementById('musicVolumeSlider')?.addEventListener('input', (e) => 
            soundEngine.setMusicVolume(parseInt(e.target.value) / 100)
        );
        
        document.getElementById('logoutSettingEntry')?.addEventListener('click', () => { 
            this.toggleSettingsPopover(false); 
            showCustomConfirm('Are you sure you want to logout?', () => this.executeLogout()); 
        });
        
        // Close settings popover when clicking outside
        document.addEventListener('click', (e) => { 
            const p=document.getElementById('settingsPopover'),
                  f=document.getElementById('fabSettingsBtn');
                  
            if(p && p.classList.contains('visible') && !p.contains(e.target) && !f.contains(e.target)) {
                this.toggleSettingsPopover(false);
            } 
        });
        
        // Resize handler for canvas
        window.addEventListener('resize', () => this.renderWheelGraphic());
    }

    updateBalanceDisplay() { 
        const el = document.getElementById('displayBalance'); 
        if(el) {
            el.textContent = `MWK ${this.currentBalance.toFixed(2)}`;
            
            // Add subtle animation to draw attention
            el.style.transition = 'transform 0.15s ease-out';
            el.style.transform = 'scale(1.02)';
            setTimeout(() => {
                el.style.transform = 'scale(1)';
            }, 150);
        }
    }
    
    updateAuthenticationUI() {
        const b = document.getElementById('authButton'); 
        if(!b) return;
        
        if(this.isUserAuthenticated) { 
            b.innerHTML = '<i class="bi bi-person-check-fill"></i>'; 
            b.classList.remove('guest'); 
            b.title = `${this.playerName} - Click to logout`; 
            //b.style.display = 'flex';
        } else { 
            b.innerHTML = '<i class="bi bi-person-fill"></i>'; 
            b.classList.add('guest'); 
            b.title = 'Click to login'; 
            b.style.display = 'none'; 
        }
    }

    renderWheelGraphic() {
        if(!this.canvasContext) return;
        
        const ctx=this.canvasContext,
              cX=400,
              cY=400,
              r=385,
              tS=WHEEL_CONFIG.totalSegments,
              aS=(Math.PI*2)/tS;
              
        ctx.clearRect(0,0,800,800);
        ctx.save();
        ctx.translate(cX,cY);
        ctx.rotate(-Math.PI/2);
        ctx.translate(-cX,-cY);
        
        let sA=0;
        WHEEL_CONFIG.segments.forEach(seg=>{
            for(let i=0;i<seg.slots;i++){
                ctx.beginPath();
                ctx.moveTo(cX,cY);
                ctx.arc(cX,cY,r,sA,sA+aS);
                ctx.fillStyle=seg.hexColor;
                ctx.fill();
                ctx.strokeStyle='rgba(0,0,0,0.18)';
                ctx.lineWidth=2;
                ctx.stroke();
                
                ctx.save();
                ctx.translate(cX,cY);
                ctx.rotate(sA+aS/2);
                ctx.textAlign='right';
                ctx.fillStyle=seg.key==='gray'?'#a1a1aa':'#ffffff';
                ctx.font='bold 46px Inter, sans-serif';
                ctx.shadowColor='rgba(0,0,0,0.45)';
                ctx.shadowBlur=5;
                ctx.fillText(seg.name,r-50,14);
                ctx.font='30px Inter, sans-serif';
                ctx.fillStyle='rgba(255,255,255,0.78)';
                ctx.fillText(seg.multiplier+'x',r-50,56);
                ctx.restore();
                sA+=aS;
            }
        });
        
        ctx.restore();
        ctx.beginPath();
        ctx.arc(cX,cY,r,0,Math.PI*2);
        ctx.lineWidth=26;
        ctx.strokeStyle='#cbd5e1';
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(cX,cY,r-14,0,Math.PI*2);
        ctx.lineWidth=4;
        ctx.strokeStyle='#030712';
        ctx.stroke();
        
        const hG=ctx.createRadialGradient(cX,cY,10,cX,cY,90);
        hG.addColorStop(0,'#334155');
        hG.addColorStop(1,'#0c1222');
        
        ctx.beginPath();
        ctx.arc(cX,cY,90,0,Math.PI*2);
        ctx.fillStyle=hG;
        ctx.fill();
        ctx.lineWidth=6;
        ctx.strokeStyle='#fbbf24';
        ctx.stroke();
        
        ctx.fillStyle='#fbbf24';
        ctx.font='bold 36px Inter, sans-serif';
        ctx.textAlign='center';
        ctx.textBaseline='middle';
        ctx.fillText('CS',cX,cY);
    }

    selectBettingColor(colorKey) {
        if(this.isWheelSpinning) return; 
        
        this.selectedColor = colorKey;
        
        document.querySelectorAll('.color-option').forEach(b => b.classList.remove('selected'));
        
        const sb = document.querySelector(`.option-${colorKey}`); 
        if(sb) sb.classList.add('selected');
        
        const si = document.getElementById('statusIndicator');
        const seg = WHEEL_CONFIG.segments.find(s => s.key === colorKey);
        const multi = seg ? seg.multiplier : '';
        
        si.style.cssText = 'padding:10px 24px;background:var(--card-bg);border:1px solid var(--border-subtle);border-radius:30px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-primary);backdrop-filter:blur(12px);white-space:nowrap;z-index:10;transition:all 0.3s ease;';
        si.textContent = `${colorKey.toUpperCase()} • ${multi}x`; 
        si.className = 'status-indicator';
    }

    async updateDatabaseBalance(amount, phone) {
        try {
            const nA = parseFloat(amount); 
            if(isNaN(nA)) throw new Error('Invalid amount');
            
            console.log(`📊 Updating DB balance: ${nA > 0 ? '+' : ''}${nA} for ${phone}`);
            
            const { data: nB, error: uEr } = await supabaseClient.rpc('update_balance_by_phone', {
                p_phone: phone,
                p_amount: nA
            });
            
            if(uEr) throw uEr; 
            
            const newBalance = parseFloat(nB) || 0;
            console.log(`✅ DB updated. New balance: MWK ${newBalance.toFixed(2)}`);
            
            return newBalance;
        } catch(error) { 
            console.error('❌ DB Update Error:', error); 
            throw error; 
        }
    }

    executeSpin() {
        if(this.isWheelSpinning) return;
        
        if(!this.isUserAuthenticated) { 
            showToastMessage('Login first', 'error'); 
            this.showLoginModal(); 
            return; 
        }
        
        const wI = document.getElementById('wagerInput'), 
              bA = parseFloat(wI?.value || 0);
              
        if(!this.selectedColor) { 
            showToastMessage('Pick a color first', 'error'); 
            return; 
        }
        
        if(isNaN(bA) || bA < WHEEL_CONFIG.minimumBet) { 
            showToastMessage(`Minimum: MWK ${WHEEL_CONFIG.minimumBet}`, 'error'); 
            return; 
        }
        
        if(bA > this.currentBalance) { 
            showToastMessage('Not enough balance', 'error'); 
            return; 
        }

        // Deduct bet amount IMMEDIATELY from displayed balance
        this.setBalanceImmediate(this.currentBalance - bA, 'bet-deduct');
        this.isWheelSpinning = true;
        
        const btn = document.getElementById('spinAction'); 
        if(btn) { 
            btn.disabled = true; 
            btn.textContent = 'Spinning...'; 
        }
        
        document.querySelectorAll('.color-option').forEach(b => { 
            b.style.pointerEvents = 'none'; 
            b.style.opacity = '0.5'; 
        });
        
        const status = document.getElementById('statusIndicator'); 
        if(status) { 
            status.style.cssText = 'padding:10px 24px;background:var(--card-bg);border:1px solid var(--border-subtle);border-radius:30px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--accent-gold);backdrop-filter:blur(12px);white-space:nowrap;z-index:10;transition:all 0.3s ease;';
            status.textContent = 'Spinning...'; 
        }
        
        try { soundEngine.startBicycleSpin(() => {}); } catch(e){}

        supabaseClient.rpc('get_random_number')
            .then(({ data, error }) => {
                if(error) throw error; 
                
                const num = parseInt(data);
                if(isNaN(num)) throw new Error('Not a number'); 
                if(num < 0 || num > 7) throw new Error('Out of range: ' + num);
                
                this.spinWheel(num, bA);
            })
            .catch(err => {
                soundEngine.stopBicycleSpin(); 
                // Refund immediately
                this.setBalanceImmediate(this.currentBalance + bA, 'bet-refund'); 
                this.resetSpinUI();
                
                if(status) { 
                    status.style.cssText='padding:10px 24px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);border-radius:30px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#ef4444;backdrop-filter:blur(12px);white-space:nowrap;z-index:10;'; 
                    status.textContent='Error: ' + err.message; 
                }
                
                showToastMessage('Error: ' + err.message, 'error'); 
                try { soundEngine.errorSound(); } catch(e){}
            });
    }

    spinWheel(targetIndex, betAmount) {
        const canvas=this.canvasElement;
        
        if(!canvas) return this.processResult(targetIndex, betAmount);
        
        const tS=WHEEL_CONFIG.totalSegments,
              dPS=360/tS,
              tCA=(targetIndex*dPS)+(dPS/2),
              fP=360-tCA,
              fS=10*360,
              rJ=(Math.random()*16)-8,
              cAM=this.wheelRotationAngle%360,
              a=(fP-cAM+360)%360,
              tR=this.wheelRotationAngle+fS+a+rJ;
              
        canvas.style.transition='transform 8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        canvas.style.transform=`rotate(${tR}deg)`; 
        this.wheelRotationAngle=tR;
        
        setTimeout(()=>{
            if(this.isWheelSpinning) {
                soundEngine.stopBicycleSpin();
                soundEngine.playStopSound();
                this.processResult(targetIndex, betAmount);
            }
        }, 8200);
    }

    processResult(winnerIndex, betAmount) {
        let counter = 0, winningSegment = null;

        for (const seg of WHEEL_CONFIG.segments) {
            for (let s = 0; s < seg.slots; s++) {
                if (counter === winnerIndex) winningSegment = seg;
                counter++;
            }
            if (winningSegment) break;
        }

        if (!winningSegment) {
            this.handleSpinError(new Error('Invalid segment'), betAmount);
            return;
        }

        this.resetSpinUI();
        const status = document.getElementById('statusIndicator');

        let choice = (this.selectedColor || "").toUpperCase();
        let landed = (winningSegment.name || "").toUpperCase();
        let state = `${choice} -Landed- ${landed}`;
        let isWin = (choice === landed && landed !== "GRAY");
        let msg = isWin ? "won" : "lost";

        if (isWin) {
            const pA = betAmount * winningSegment.multiplier;

            // Update DB first
            this.updateDatabaseBalance(pA, this.playerPhone)
                .then(async (nB) => {
                    // Then refresh from DB to get accurate balance
                    await this.forceRefreshBalance('win');
                    
                    this.showWinDisplay(status, pA, winningSegment);
                    this.launchConfetti(70);
                    showToastMessage(
                        `🎉 You won MWK ${pA.toFixed(2)}! New Balance: MWK ${this.currentBalance.toFixed(2)}`, 
                        'success'
                    );
                    
                    try { soundEngine.successSound(); } catch (e) {}

                    return supabaseClient.from('bets_history').insert({
                        phone: this.playerPhone,
                        amount: betAmount,
                        results: state,
                        status: msg
                    }).select();
                })
                .then(({ data, error }) => {
                    if (error) console.error('❌ Insert failed:', error);
                    else console.log('✅ Bet saved:', data);
                })
                .catch(err => {
                    console.error('Win processing error:', err);
                });

        } else {
            // Loss - confirm deduction with DB
            this.updateDatabaseBalance(-betAmount, this.playerPhone)
                .then(async (nB) => {
                    // Refresh to sync with DB
                    await this.forceRefreshBalance('loss');
                    
                    this.showLossDisplay(status, winningSegment, betAmount);
                    showToastMessage(
                        `${winningSegment.name} landed! Balance: MWK ${this.currentBalance.toFixed(2)}`, 
                        'info'
                    );
                    
                    try { soundEngine.errorSound(); } catch (e) {}

                    return supabaseClient.from('bets_history').insert({
                        phone: this.playerPhone,
                        amount: betAmount,
                        results: state,
                        status: msg
                    }).select();
                })
                .then(({ data, error }) => {
                    if (error) console.error('❌ Insert failed:', error);
                    else console.log('✅ Bet saved:', data);
                })
                .catch(err => {
                    console.error('Loss processing error:', err);
                });
        }
    }

    showWinDisplay(s,p,seg){
        if(!s)return;
        s.innerHTML=`<i class="bi bi-trophy-fill" style="color:#fbbf24;margin-right:6px;"></i> WIN <span style="color:#10b981;">+MWK ${p.toFixed(2)}</span>`;
        s.style.cssText='padding:10px 24px;border-radius:30px;text-align:center;background:rgba(16,185,129,0.1);color:#10b981;border:1px solid rgba(16,185,129,0.3);backdrop-filter:blur(20px);box-shadow:0 8px 32px rgba(16,185,129,0.15);font-size:0.9rem;font-weight:800;letter-spacing:0.5px;white-space:nowrap;z-index:10;';
    }

    showLossDisplay(s,seg,l){
        if(!s)return;
        s.innerHTML=`<i class="bi bi-x-circle-fill" style="color:#ef4444;margin-right:6px;"></i> ${seg.name.toUpperCase()} <span style="color:rgba(239,68,68,0.6);">-MWK ${l.toFixed(2)}</span>`;
        s.style.cssText='padding:10px 24px;border-radius:30px;text-align:center;background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);backdrop-filter:blur(20px);box-shadow:0 8px 32px rgba(239,68,68,0.1);font-size:0.9rem;font-weight:800;letter-spacing:0.5px;white-space:nowrap;z-index:10;';
    }

    resetSpinUI() { 
        this.isWheelSpinning=false;
        
        const b=document.getElementById('spinAction');
        if(b){
            b.disabled=false;
            b.textContent='SPIN'; 
        } 
        
        document.querySelectorAll('.color-option').forEach(b=>{
            b.style.pointerEvents='auto';
            b.style.opacity='1'; 
        }); 
    }
    
    handleSpinError(error, refund) { 
        soundEngine.stopBicycleSpin(); 
        
        if(refund>0){
            this.setBalanceImmediate(this.currentBalance + refund, 'error-refund'); 
        } 
        
        this.resetSpinUI(); 
        
        const s=document.getElementById('statusIndicator');
        if(s){
            s.textContent='Error: '+error.message;
            s.style.cssText='padding:10px 24px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.4);border-radius:30px;font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#ef4444;backdrop-filter:blur(12px);white-space:nowrap;z-index:10;'; 
        } 
        
        showToastMessage('Error: '+error.message,'error'); 
        try{soundEngine.errorSound();}catch(e){}
    }

    launchConfetti(pCount=50) {
        document.querySelectorAll('.confetti-box').forEach(e=>e.remove());
        
        const b=document.createElement('div');
        b.className='confetti-box';
        b.style.cssText='position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;pointer-events:none;z-index:99999;overflow:hidden;';
        document.body.appendChild(b);
        
        const pal=['#fbbf24','#10b981','#3b82f6','#ef4444','#8b5cf6','#ec4899'];
        
        for(let i=0;i<pCount;i++){
            const p=document.createElement('div'),
                  sz=Math.random()*9+5,
                  col=pal[i%pal.length],
                  lp=Math.random()*100,
                  ft=Math.random()*2+2.2,
                  dl=Math.random()*0.5,
                  rs=Math.random()*360,
                  ic=Math.random()>0.5;
                  
            p.style.cssText=`position:absolute;top:-15px;left:${lp}%;width:${sz}px;height:${sz}px;background:${col};${ic?'border-radius:50%':'border-radius:2px'}opacity:${Math.random()*0.4+0.6};transform:rotate(${rs}deg);animation:confettiFall${i} ${ft}s cubic-bezier(0.25,0.46,0.45,0.94) ${dl}s forwards;`;
            b.appendChild(p);
            
            const st=document.createElement('style');
            st.textContent=`@keyframes confettiFall${i}{0%{transform:translateY(0) rotate(${rs}deg) scale(1);opacity:1;}85%{opacity:1;}100%{transform:translateY(100vh) rotate(${rs+720}deg) scale(0);opacity:0;}}`;
            document.head.appendChild(st);
        }
        
        setTimeout(()=>{if(b.parentNode)b.remove();},4000);
    }

    handleAuthenticationToggle() { 
        if(this.isUserAuthenticated){
            showCustomConfirm('Are you sure you want to logout?',()=>this.executeLogout());
            return;
        } 
        
        const m=document.getElementById('authModalOverlay');
        if(m.classList.contains('active')) this.hideModal('authModalOverlay');
        else{
            isTabTransitioning=false;
            currentActiveTab='login';
            switchToTab('loginPanel');
            this.showModal('authModalOverlay');
        } 
    }
    
    showModal(id){const m=document.getElementById(id);if(m)m.classList.add('active');}
    hideModal(id){const m=document.getElementById(id);if(m)m.classList.remove('active');}

    // =============================================
    // ✅ LOGIN - Fixed to always show correct balance
    // =============================================
    async processLogin() {
        const pI=document.getElementById('loginPhoneInput')?.value.trim(),
              pnI=document.getElementById('loginPinInput')?.value.trim();
              
        if(!pI||pI.length<9){
            showToastMessage('Enter valid phone number (9+ digits)','error');
            return;
        }
        
        if(!pnI||pnI.length<4){
            showToastMessage('Enter valid PIN (4+ digits)','error');
            return;
        }
        
        const lB=document.getElementById('executeLogin');
        lB.disabled=true;
        lB.innerHTML='<div class="loader4"></div>';
        
        try {
            const newToken = this.generateSessionToken();
            
            console.log('🔐 =======================================');
            console.log('🔐 ATTEMPTING LOGIN');
            console.log('🔐 Phone:', pI);
            console.log('🔐 =======================================');
            
            const { data: userData, error: rpcError } = await supabaseClient.rpc('login_user', {
                p_phone: pI,
                p_password: pnI,
                p_new_token: newToken
            });

            if (rpcError || !userData || userData.length === 0) {
                console.error('❌ Login failed:', rpcError);
                showToastMessage(rpcError?.message || 'Invalid phone number or PIN', 'error');
            } else {
                const user = userData[0];
                
                console.log('✅ Login successful!');
                console.log('👤 User data:', user);
                console.log('💰 Balance from login response:', user.balance);
                
                // Set session data
                this.sessionToken = newToken;
                sessionStorage.setItem('crazyStack_session_token', newToken);
                
                this.isUserAuthenticated = true;
                this.playerName = user.name || pI;
                this.playerPhone = pI;
                
                // Use balance from login response as initial value
                const loginBalance = parseFloat(user.balance) || 0;
                this.setBalanceImmediate(loginBalance, 'login-response');
                
                // Save session
                this.saveUserSession(pI, this.playerName, this.currentBalance);
                
                // Update UI
                this.updateAuthenticationUI();
                this.hideModal('authModalOverlay');
                
                showToastMessage(
                    `Welcome back, ${this.playerName}! Balance: MWK ${this.currentBalance.toFixed(2)}`,
                    'success'
                );
                
                this.startSessionMonitoring();
                
                // Verify balance with DB in background (non-blocking)
                setTimeout(async () => {
                    console.log('🔄 Verifying login balance with database...');
                    await this.forceRefreshBalance('login-verify');
                    
                    // If different from what we showed, notify user
                    console.log(`✅ Verified balance: MWK ${this.currentBalance.toFixed(2)}`);
                }, 1500);
            }
        } catch(error) { 
            console.error('❌ Login error:',error); 
            showToastMessage(`Login Error: ${error.message}`,'error'); 
        } finally { 
            lB.disabled=false;
            lB.textContent='Sign In'; 
        }
    }

    async processSignup() {
        const nI=document.getElementById('signupNameInput')?.value.trim(),
              pI=document.getElementById('signupPhoneInput')?.value.trim(),
              pnI=document.getElementById('signupPinInput')?.value.trim(),
              cpI=document.getElementById('signupConfirmPinInput')?.value.trim();
              
        if(!nI) return showToastMessage('Please enter your full name','error');
        if(!pI||pI.length<9) return showToastMessage('Enter valid phone number','error');
        if(!pnI||pnI.length<4) return showToastMessage('PIN must be at least 4 digits','error');
        if(pnI!==cpI) return showToastMessage('PIN codes do not match','error');
        
        const sB=document.getElementById('executeSignup');
        sB.disabled=true;
        sB.textContent='Creating...';
        
        try {
            const newToken = this.generateSessionToken();
            
            console.log('📝 Creating account...');
            
            const { data: success, error: rpcError } = await supabaseClient.rpc('signup_new_user', {
                p_phone: pI,
                p_password: pnI,
                p_name: nI,
                p_token: newToken
            });

            if (rpcError) {
                console.error('❌ Signup failed:', rpcError);
                showToastMessage(rpcError.message, 'error');
            } else {
                console.log('✅ Account created!');
                
                this.sessionToken = newToken;
                sessionStorage.setItem('crazyStack_session_token', newToken);
                
                this.isUserAuthenticated=true;
                this.playerName=nI;
                this.playerPhone=pI;
                
                // New accounts start at 0
                this.setBalanceImmediate(0, 'signup');
                
                this.saveUserSession(pI, nI, 0);
                this.updateAuthenticationUI();
                this.hideModal('authModalOverlay');
                
                showToastMessage(`Account created! Welcome, ${nI}!`,'success');
                this.startSessionMonitoring();
            }
        } catch(error) { 
            console.error('❌ Signup error:',error); 
            showToastMessage(`Signup Error: ${error.message}`,'error'); 
        } finally { 
            sB.disabled=false;
            sB.textContent='Create Account'; 
        }
    }

    async executeLogout() {
        console.log('🚪 Logging out...');
        
        if(this.playerPhone && this.sessionToken) { 
            await supabaseClient.from('users')
                .update({session_token: null})
                .eq('phone', this.playerPhone)
                .eq('session_token', this.sessionToken); 
        }
        
        this.stopSessionMonitoring();
        this.setBalanceImmediate(0, 'logout');
        this.clearUserSession();
        this.isUserAuthenticated=false;
        this.playerName='';
        this.playerPhone='';
        this.sessionToken=null;
        
        this.updateAuthenticationUI();
        this.showModal('authModalOverlay');
        
        isTabTransitioning=false;
        currentActiveTab='login';
        switchToTab('loginPanel');
        
        showToastMessage('Logged out successfully','info');
    }

    openPaymentModal(type) {
        if(!this.isUserAuthenticated){
            showToastMessage('Please login first!','error');
            this.showLoginModal();
            return;
        }
        
        document.getElementById('paymentModalTitle').textContent=type==='deposit'?'Deposit Funds':'Withdraw Funds';
        this.selectedPaymentProvider=null;
        document.querySelectorAll('.provider-choice').forEach(b=>b.classList.remove('chosen','disabled-state'));
        //document.getElementById('paymentAmountInput').value='';
        this.currentTransactionType=type;
        this.showModal('paymentModalOverlay');
    }

    selectPaymentProvider(p){
        this.selectedPaymentProvider=p;
        document.querySelectorAll('.provider-choice').forEach(b=>{
            b.classList.remove('chosen','disabled-state');
            if(b.dataset.provider===p) b.classList.add('chosen');
            else b.classList.add('disabled-state');
        });
    }
    
    autoDetectNetworkProvider(){
        const r=document.getElementById('paymentPhoneInput')?.value.replace(/\D/g,'');
        if(r.length<2)return;
        
        let d=null;
        if(r.startsWith('2659')||r.startsWith('09')) d='airtel';
        else if(r.startsWith('2658')||r.startsWith('08')) d='tnm';
        
        if(d) this.selectPaymentProvider(d);
    }

    // =============================================
    // ✅✅✅ PAYMENT PROCESSING - INSTANT BALANCE REFRESH & WITHDRAWAL RECORDING
    // =============================================
    processPaymentTransaction(event) {
        event.preventDefault();
        
        if(!duplicatePrevention.acquireProcessingLock()){
            showToastMessage('Payment already in progress. Please wait...','warning');
            return;
        }
        
        const pN = document.getElementById('paymentPhoneInput')?.value.trim(),
              aV = document.getElementById('paymentAmountInput')?.value,
              pB = document.getElementById('confirmPayment'),
              oT = pB?.textContent;
        
        if(!this.selectedPaymentProvider || !pN || pN.length < 9 || !aV || parseFloat(aV) < 50){
            showToastMessage('Please fill all fields correctly','error');
            duplicatePrevention.releaseProcessingLock();
            return;
        }
        
        const amount = parseFloat(aV);
        const requestData = {
            type: this.currentTransactionType,
            provider: this.selectedPaymentProvider,
            mobile: pN,
            amount: amount,
            userId: this.playerPhone
        };

        console.log('💳 Processing payment:', requestData);

        pB.innerHTML='<div class="loader4"></div>';
        pB.disabled = true;

        fetch('mobile_pay.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        })
        .then(r => r.text().then(t => {
            try { return JSON.parse(t); } 
            catch(e) { throw new Error("Server unavailable"); }
        }))
        .then(rD => {
            pB.textContent = oT;
            pB.disabled = false;
            
            if(rD.status !== 'success'){
                console.error('❌ Payment failed:', rD);
                showToastMessage(rD.message || 'Transaction failed','error');
                duplicatePrevention.releaseProcessingLock();
                return;
            }
            
            console.log('✅ Payment initiated:', rD);
            
            const tR = rD.transaction?.charge_id,
                  tA = rD.transaction?.amount || amount,
                  cP = rD.phone || pN;
            
            if(duplicatePrevention.isTransactionProcessed(tR)){
                duplicatePrevention.releaseProcessingLock();
                return;
            }
            
            showToastMessage('USSD sent, Enter PIN to confirm','success');
            pB.innerHTML='<div class="loader5"></div>';
            
            let pA=0;
            const mP=25;
            const app=this;
            
            const pI = setInterval(async()=>{
                pA++;
                
                try{
                    const {data:pRec}=await paymentsDbClient.from('payments')
                        .select('status,amount,charge_id')
                        .eq('charge_id',tR)
                        .maybeSingle();
                        
                    if(!pRec){ 
                        if(pA>=mP){
                            console.log('⏰ Payment timeout');
                            clearInterval(pI);
                            pB.textContent=oT;
                            pB.disabled=false;
                            duplicatePrevention.releaseProcessingLock();
                            showToastMessage('Transaction timed out. Contact support.','error');
                            return;
                        }
                        return;
                    }
                    
                    if(pRec.status==='success'){
                        console.log('✅✅✅ PAYMENT SUCCESSFUL!', pRec);
                        
                        if(duplicatePrevention.isTransactionProcessed(tR)){ 
                            clearInterval(pI); 
                            return; 
                        }
                        
                        duplicatePrevention.markTransactionProcessed(tR);
                        clearInterval(pI);
                        pB.textContent = 'Complete';
                        
                        // =============================================
                        // ✅✅✅ CRITICAL: Force refresh balance IMMEDIATELY
                        // =============================================
                        
                        const paymentAmount = parseFloat(pRec.amount) || tA;
                        
                        console.log(`💰 Payment amount: ${paymentAmount}`);
                        console.log(`📊 Transaction type: ${app.currentTransactionType}`);
                        console.log(`💳 Current balance before refresh: ${app.currentBalance}`);
                        
                        // Show processing message
                        showToastMessage('✅ Payment received! Updating balance...', 'success');
                        
                        // =============================================
                        // ✅✅✅ NEW: INSERT INTO WITHDRAWALS TABLE IF WITHDRAWAL
                        // =============================================
                        if (app.currentTransactionType === 'withdraw') {
                            console.log('🏧 Recording withdrawal to withdrawals table...');
                            
                            // Insert into withdrawals table
                            const withdrawResult = await window.insertWithdrawalRecord(
                                app.playerPhone,
                                paymentAmount,
                                'success'
                            );
                            
                            if (withdrawResult.success) {
                                console.log('✅ Withdrawal recorded successfully!');
                            } else {
                                console.error('❌ Failed to record withdrawal:', withdrawResult.error);
                            }
                        }
                        
                        // Force refresh from DB - THIS IS THE KEY FIX!
                        await app.forceRefreshBalance('payment-success');
                        
                        // Build success message based on transaction type
                        let successMsg;
                        if(app.currentTransactionType === 'deposit') {
                            successMsg = `💰 Deposit received!\n+MWK ${paymentAmount.toFixed(2)}\nNew Balance: MWK ${app.currentBalance.toFixed(2)}`;
                        } else {
                            successMsg = `💸 Withdrawal processed!\n-MWK ${paymentAmount.toFixed(2)}\nNew Balance: MWK ${app.currentBalance.toFixed(2)}`;
                        }
                        
                        showToastMessage(successMsg, 'success');
                        
                        console.log(`✅✅✅ FINAL BALANCE: MWK ${app.currentBalance.toFixed(2)}`);
                        
                        // Close modal after showing success
                        setTimeout(() => {
                            app.hideModal('paymentModalOverlay');
                        }, 2500);
                        
                        duplicatePrevention.releaseProcessingLock();
                        
                        setTimeout(() => { 
                            pB.textContent = oT; 
                            pB.disabled = false; 
                        }, 3500);
                        
                    } else if(pRec.status==='failed'){
                        console.log('❌ Payment failed');
                        clearInterval(pI);
                        pB.textContent='Failed';
                        showToastMessage('Payment failed or cancelled','error');
                        duplicatePrevention.releaseProcessingLock();
                        setTimeout(() => { 
                            pB.textContent = oT; 
                            pB.disabled = false; 
                        }, 3500);
                    } else { 
                        if(pA%5===0) {
                            console.log(`⏳ Still waiting... (${pA}/${mP})`);
                            showToastMessage(`Still waiting for confirmation... (${pA}/${mP})`,'info'); 
                        } 
                        pB.textContent=`Checking... (${pA})`; 
                    }
                }catch(e){ 
                    console.error('❌ Polling error:',e); 
                }
            }, 1200);
            
            duplicatePrevention.registerPollingSession(tR, pI);
            
        }).catch(fE => {
            console.error('❌ Fetch error:', fE);
            pB.textContent = oT;
            pB.disabled = false;
            duplicatePrevention.releaseProcessingLock();
            showToastMessage(
                fE.message.includes("Server unavailable") 
                    ? 'Payment Backend Error: Ensure mobile_pay.php is running.' 
                    : 'Connection error - Try again',
                'error'
            );
        });
    }

    toggleSettingsPopover(state=null){
        const p=document.getElementById('settingsPopover'),
              s=state!==null?state:!p.classList.contains('visible');
        p.classList.toggle('visible',s);
    }
}

//=============================================
// INITIALIZATION - FIXED
//=============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎮 =======================================');
    console.log('🎮 INITIALIZING CRAZYSTACK APPLICATION');
    console.log('🎮 =======================================');
    
    window.crazyStackApp = new CrazyStackApp();
    
    console.log('✅ Application initialized');
    
    setTimeout(()=>{
        soundEngine.updateSfxToggleUI(soundEngine.isEnabled);
        soundEngine.updateMusicToggleUI(soundEngine.bgMusicEnabled);
        themeManager.updateToggleUI(themeManager.currentTheme==='light');
        
        const vs=document.getElementById('musicVolumeSlider');
        if(vs) vs.value=Math.round(soundEngine.bgMusicVolume*100);
    },100);
    
    if(location.hostname!=="localhost"){
        // Disable console logs in production (except our important ones)
        const originalLog = console.log;
        console.log = (...args) => {
            if (typeof args[0] === 'string' && (
                args[0].includes('🔄') || 
                args[0].includes('✅') || 
                args[0].includes('❌') ||
                args[0].includes('💰') ||
                args[0].includes('🔐') ||
                args[0].includes('📊')
            )) {
                originalLog.apply(console, args);
            }
        };
    }
});

// Restore session when page fully loads
window.addEventListener("load", () => {
    console.log('📄 Page fully loaded');
    console.log('🔄 Restoring user session...\n');
    
    // Small delay to ensure DOM is ready
    setTimeout(() => {
        if(window.crazyStackApp) {
            crazyStackApp.restoreUserSession(true);
        } else {
            console.error('❌ CrazyStackApp not initialized!');
        }
    }, 500);
});
</script>

</body>
</html>