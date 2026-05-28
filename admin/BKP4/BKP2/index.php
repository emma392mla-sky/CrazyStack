<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CrazyStack - Wheel of Fortune</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Supabase Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <style>
        /* =========================================
           RESET & VARIABLES
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; user-select: none; -webkit-tap-highlight-color: transparent; }
        
        :root {
            --bg-deep: #030712;
            --bg-surface: #0c1222;
            --card-bg: #111827;
            --border-subtle: rgba(255,255,255,0.06);
            --text-primary: #f1f5f9;
            --text-secondary: #64748b;
            --accent-gold: #fbbf24;
            --accent-gold-dim: rgba(251,191,36,0.12);
            --success: #10b981;
            --info: #3b82f6;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(ellipse at 15% 25%, rgba(59,130,246,0.07) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 75%, rgba(239,68,68,0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(251,191,36,0.03) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* =========================================
           TOP NAVIGATION BAR
           ========================================= */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: linear-gradient(to bottom, rgba(3,7,18,0.97) 0%, rgba(3,7,18,0.8) 70%, transparent 100%);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--accent-gold);
            letter-spacing: -0.3px;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #d97706 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
            color: #000;
            box-shadow: 0 4px 12px rgba(251,191,36,0.3);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .balance-widget {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            padding: 8px 16px;
            border-radius: 22px;
            backdrop-filter: blur(10px);
        }

        .balance-icon { color: var(--accent-gold); font-size: 16px; }

        .balance-info { display: flex; flex-direction: column; line-height: 1; }

        .balance-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .balance-value {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--accent-gold);
            font-family: 'SF Mono', 'Cascadia Code', monospace;
        }

        .user-chip {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: all 0.2s ease;
        }

        .user-chip:hover {
            background: var(--accent-gold-dim);
            border-color: var(--accent-gold);
            color: var(--accent-gold);
            transform: scale(1.05);
        }

        .user-chip.guest { color: var(--text-secondary); }

        /* =========================================
           MAIN STAGE & WHEEL
           ========================================= */
        .stage {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1;
            padding-top: 60px;
            padding-bottom: 280px; /* Space for control deck on mobile */
        }

        @media (min-width: 1024px) {
            .stage {
                padding-bottom: 40px;
                padding-right: 400px; /* Space for floating panel */
            }
        }

        .status-indicator {
            position: absolute;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            padding: 9px 22px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-secondary);
            backdrop-filter: blur(12px);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            z-index: 10;
        }

        @media (min-width: 1024px) { .status-indicator { top: 90px; } }

        .status-indicator.is-win {
            color: var(--success);
            border-color: rgba(16,185,129,0.35);
            box-shadow: 0 0 24px rgba(16,185,129,0.2), inset 0 0 12px rgba(16,185,129,0.08);
        }

        .status-indicator.is-loss {
            color: var(--danger);
            border-color: rgba(239,68,68,0.35);
            box-shadow: 0 0 24px rgba(239,68,68,0.2), inset 0 0 12px rgba(239,68,68,0.08);
        }

        .wheel-container {
            position: relative;
            width: min(280px, 65vw, 45vh);
            height: min(280px, 65vw, 45vh);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 1024px) {
            .wheel-container {
                width: min(380px, 38vw, 52vh);
                height: min(380px, 38vw, 52vh);
            }
        }

        .wheel-pointer {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 16px solid transparent;
            border-right: 16px solid transparent;
            border-top: 30px solid var(--accent-gold);
            filter: drop-shadow(0 4px 10px rgba(251,191,36,0.5));
            z-index: 20;
        }

        .wheel-canvas {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            transition: transform 4.2s cubic-bezier(0.17, 0.67, 0.12, 0.99);
            box-shadow: 
                0 0 80px rgba(251,191,36,0.06),
                0 30px 60px rgba(0,0,0,0.55),
                inset 0 0 40px rgba(0,0,0,0.35);
        }

        .wheel-container.spinning .wheel-canvas {
            animation: wheelPulse 0.5s ease-in-out infinite alternate;
        }

        @keyframes wheelPulse {
            from { filter: brightness(1); }
            to { filter: brightness(1.08); }
        }

        /* =========================================
           SETTINGS FAB
           ========================================= */
        .settings-fab {
            position: absolute;
            top: 75px;
            right: 20px;
            width: 46px;
            height: 46px;
            border-radius: 13px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 95;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        @media (min-width: 1024px) { .settings-fab { top: 80px; right: 28px; } }

        .settings-fab:hover {
            background: var(--accent-gold-dim);
            border-color: var(--accent-gold);
            color: var(--accent-gold);
            transform: rotate(60deg) scale(1.08);
            box-shadow: 0 6px 25px rgba(251,191,36,0.25);
        }

        .settings-popover {
            position: absolute;
            top: 130px;
            right: 20px;
            width: 220px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 9px;
            display: none;
            flex-direction: column;
            gap: 4px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.55);
            z-index: 96;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.25s ease;
        }

        @media (min-width: 1024px) { .settings-popover { right: 28px; top: 135px; } }

        .settings-popover.visible {
            display: flex;
            opacity: 1;
            transform: translateY(0);
        }

        .settings-entry {
            width: 100%;
            padding: 11px 14px;
            background: transparent;
            border: none;
            border-radius: 11px;
            color: var(--text-primary);
            font-size: 0.87rem;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.18s ease;
        }

        .settings-entry:hover { background: rgba(255,255,255,0.07); }

        .settings-entry i { width: 20px; text-align: center; opacity: 0.7; font-size: 1rem; }

        /* =========================================
           CONTROL DECK
           ========================================= */
        .control-deck {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, 
                var(--card-bg) 0%, 
                var(--card-bg) 75%,
                rgba(17,24,39,0.95) 90%,
                transparent 100%
            );
            padding: 26px 20px 32px;
            z-index: 90;
            border-top: 1px solid var(--border-subtle);
        }

        @media (min-width: 1024px) {
            .control-deck {
                position: fixed;
                right: 28px;
                left: auto;
                bottom: 28px;
                top: auto;
                width: 360px;
                background: var(--card-bg);
                border-radius: 22px;
                border: 1px solid var(--border-subtle);
                padding: 26px;
                box-shadow: 0 25px 65px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.03) inset;
            }
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 11px;
            margin-bottom: 18px;
        }

        .color-option {
            position: relative;
            padding: 15px 10px;
            border-radius: 13px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.025);
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            overflow: hidden;
        }

        .color-option .odds {
            display: block;
            font-size: 0.68rem;
            margin-top: 4px;
            opacity: 0.55;
            font-weight: 600;
        }

        .color-option:hover:not(.selected) {
            transform: translateY(-3px);
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.1);
        }

        .color-option.selected {
            color: #ffffff;
            transform: scale(1.06);
            border-width: 2px;
        }

        .option-green.selected {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: #34d399;
            box-shadow: 0 10px 30px rgba(16,185,129,0.4), 0 0 0 3px rgba(16,185,129,0.15);
        }

        .option-blue.selected {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-color: #60a5fa;
            box-shadow: 0 10px 30px rgba(59,130,246,0.4), 0 0 0 3px rgba(59,130,246,0.15);
        }

        .option-red.selected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-color: #f87171;
            box-shadow: 0 10px 30px rgba(239,68,68,0.4), 0 0 0 3px rgba(239,68,68,0.15);
        }

        .bet-row { display: flex; gap: 11px; margin-bottom: 15px; }

        .bet-input-wrap { flex: 1; position: relative; }

        .bet-field {
            width: 100%;
            padding: 15px 17px;
            background: rgba(0,0,0,0.45);
            border: 1px solid var(--border-subtle);
            border-radius: 13px;
            color: var(--accent-gold);
            font-size: 1.08rem;
            font-weight: 600;
            outline: none;
            font-family: 'SF Mono', 'Consolas', monospace;
            transition: all 0.25s ease;
        }

        .bet-field:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 4px var(--accent-gold-dim), 0 0 20px rgba(251,191,36,0.08);
            background: rgba(0,0,0,0.6);
        }

        .bet-field::placeholder {
            color: var(--text-secondary);
            font-family: inherit;
            font-weight: 400;
            opacity: 0.6;
        }

        .spin-button {
            padding: 15px 34px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #f59e0b 100%);
            border: none;
            border-radius: 13px;
            color: #000000;
            font-size: 0.96rem;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .spin-button:hover:not(:disabled) {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 30px rgba(251,191,36,0.45);
        }

        .spin-button:active:not(:disabled) { transform: translateY(0) scale(0.97); }

        .spin-button:disabled {
            background: #1e293b;
            color: var(--text-secondary);
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .action-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 11px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 13px;
            background: rgba(255,255,255,0.025);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.22s ease;
        }

        .action-btn:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-2px);
        }

        .action-btn:active { transform: translateY(0) scale(0.97); }

        .action-btn.btn-deposit { color: var(--success); }
        .action-btn.btn-deposit:hover { border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.08); }

        .action-btn.btn-withdraw { color: var(--info); }
        .action-btn.btn-withdraw:hover { border-color: rgba(59,130,246,0.3); background: rgba(59,130,246,0.08); }

        .action-btn i { font-size: 1.15rem; }

        /* =========================================
           MODAL SYSTEM
           ========================================= */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.88);
            backdrop-filter: blur(14px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 200;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-container {
            width: 100%;
            max-width: 410px;
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            border-radius: 26px;
            padding: 34px 28px;
            transform: scale(0.92) translateY(25px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
        }

        .modal-overlay.active .modal-container {
            transform: scale(1) translateY(0);
        }

        .modal-head {
            text-align: center;
            margin-bottom: 26px;
        }

        .modal-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 6px;
        }

        .modal-desc {
            font-size: 0.87rem;
            color: var(--text-secondary);
        }

        /* Tab Bar */
        .tab-bar {
            display: flex;
            background: rgba(0,0,0,0.35);
            border-radius: 13px;
            padding: 5px;
            margin-bottom: 26px;
            gap: 5px;
        }

        .tab-item {
            flex: 1;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 700;
            font-size: 0.91rem;
            cursor: pointer;
            transition: all 0.22s ease;
        }

        .tab-item.current {
            background: var(--accent-gold);
            color: #000000;
            box-shadow: 0 3px 14px rgba(251,191,36,0.35);
        }

        /* Form Views */
        .form-panel { display: block; }
        .form-panel.hidden { display: none !important; }

        .field-group { margin-bottom: 20px; }

        .field-label {
            display: block;
            font-size: 0.73rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.9px;
            margin-bottom: 9px;
        }

        .field-input {
            width: 100%;
            padding: 15px 17px;
            background: rgba(0,0,0,0.38);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            transition: all 0.22s ease;
        }

        .field-input:focus {
            border-color: var(--accent-gold);
            background: rgba(0,0,0,0.55);
            box-shadow: 0 0 0 3px var(--accent-gold-dim);
        }

        .field-input::placeholder { color: #374151; }

        .primary-action {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--accent-gold) 0%, #d97706 100%);
            border: none;
            border-radius: 13px;
            color: #000000;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.22s ease;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }

        .primary-action:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(251,191,36,0.4);
        }

        .primary-action:active:not(:disabled) { transform: scale(0.97); }
        .primary-action.disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; }

        .auth-switcher {
            text-align: center;
            margin-top: 20px;
            font-size: 0.86rem;
            color: var(--text-secondary);
        }

        .auth-switcher strong {
            color: var(--accent-gold);
            cursor: pointer;
            font-weight: 700;
            transition: color 0.2s;
        }

        .auth-switcher strong:hover { color: #fcd34d; }

        .close-modal {
            width: 100%;
            padding: 13px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 0.9rem;
            cursor: pointer;
            margin-top: 10px;
            transition: color 0.2s;
        }

        .close-modal:hover { color: var(--text-primary); }

        /* Provider Selection */
        .provider-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 13px;
            margin-bottom: 22px;
        }

        .provider-choice {
            padding: 21px 14px;
            border: 2px solid transparent;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.87rem;
            cursor: pointer;
            transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
        }

        .provider-choice.airtel-network {
            background: rgba(220,38,38,0.1);
            color: #fca5a5;
            border-color: rgba(220,38,38,0.2);
        }

        .provider-choice.tnm-network {
            background: rgba(5,150,105,0.1);
            color: #6ee7b7;
            border-color: rgba(5,150,105,0.2);
        }

        .provider-choice.chosen.airtel-network {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: #ffffff;
            border-color: #ffffff;
            box-shadow: 0 10px 32px rgba(220,38,38,0.45);
        }

        .provider-choice.chosen.tnm-network {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            border-color: #ffffff;
            box-shadow: 0 10px 32px rgba(5,150,105,0.45);
        }

        .provider-choice.disabled-state {
            opacity: 0.18;
            pointer-events: none;
            filter: grayscale(1) brightness(0.6);
        }

        /* =========================================
           TOAST NOTIFICATION
           ========================================= */
        .toast-notification {
            position: fixed;
            top: 72px;
            left: 50%;
            transform: translateX(-50%) translateY(-18px);
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            color: var(--text-primary);
            padding: 12px 26px;
            border-radius: 28px;
            font-size: 0.88rem;
            font-weight: 500;
            opacity: 0;
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 300;
            max-width: 92vw;
            text-align: center;
            box-shadow: 0 12px 35px rgba(0,0,0,0.45);
            backdrop-filter: blur(12px);
        }

        .toast-notification.visible {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .toast-notification.type-error {
            border-color: rgba(239,68,68,0.4);
            color: #fca5a5;
            background: rgba(127,29,29,0.15);
        }

        .toast-notification.type-success {
            border-color: rgba(16,185,129,0.4);
            color: #6ee7b7;
            background: rgba(6,78,59,0.15);
        }

        .toast-notification.type-info {
            border-color: rgba(59,130,246,0.4);
            color: #93c5fd;
            background: rgba(30,64,175,0.15);
        }

        /* =========================================
           CUSTOM CONFIRM MODAL (No native confirm)
           ========================================= */
        .confirm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 250;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .confirm-overlay.active {
            display: flex;
            opacity: 1;
        }

        .confirm-box {
            background: var(--card-bg);
            border: 1px solid var(--border-subtle);
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            max-width: 320px;
            transform: scale(0.9);
            transition: transform 0.2s;
        }

        .confirm-overlay.active .confirm-box {
            transform: scale(1);
        }

        .confirm-title { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 8px; }
        .confirm-msg { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 20px; }
        .confirm-actions { display: flex; gap: 10px; }
        
        .btn-confirm {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-confirm.yes { background: var(--danger); color: white; }
        .btn-confirm.no { background: #334155; color: white; }
        .btn-confirm:hover { opacity: 0.9; }

    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="brand">
            <div class="brand-mark">CS</div>
            CrazyStack
        </div>
        <div class="nav-right">
            <div class="balance-widget">
                <i class="bi bi-wallet2 balance-icon"></i>
                <div class="balance-info">
                    <span class="balance-label">Balance</span>
                    <span class="balance-value" id="displayBalance">MWK 0.00</span>
                </div>
            </div>
            <button class="user-chip guest" id="authButton" aria-label="Account" style='display:none;'>
                <i class="bi bi-person-fill"></i>
            </button>
        </div>
    </header>

    <!-- Main Stage -->
    <main class="stage" id="gameStage">
        
        <!-- Status Badge -->
        <div class="status-indicator" id="statusIndicator" style='display:none;'>Select Your Color</div>

        <!-- Settings Button -->
        <button class="settings-fab" id="fabSettingsBtn" title="Settings & Options">
            <i class="bi bi-gear-wide-connected"></i>
        </button>
        
        <!-- Settings Popover -->
        <div class="settings-popover" id="settingsPopover">
            <button class="settings-entry" id="logoutSettingEntry">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
            <button class="settings-entry" id="soundToggleEntry">
                <i class="bi bi-volume-up"></i> Sound: ON
            </button>
        </div>

        <!-- Wheel Container -->
        <div class="wheel-container" id="wheelContainer">
            <div class="wheel-pointer"></div>
            <canvas id="wheelCanvas" width="800" height="800" class="wheel-canvas"></canvas>
        </div>
    </main>

    <!-- Floating Control Deck -->
    <section class="control-deck" style='border-top:none;'>
        <div class="color-grid">
            <button class="color-option option-green" data-color="green">
                Green<span class="odds">2.4x</span>
            </button>
            <button class="color-option option-blue" data-color="blue">
                Blue<span class="odds">3.6x</span>
            </button>
            <button class="color-option option-red" data-color="red">
                Red<span class="odds">7.2x</span>
            </button>
        </div>

        <div class="bet-row">
            <div class="bet-input-wrap">
                <input type="number" class="bet-field" id="wagerInput" placeholder="Amount (min 50)" min="50"/>
            </div>
            <button class="spin-button" id="spinAction">SPIN</button>
        </div>
                    <div class="bet-row" style='display:flex;justify-content:space-between;'>
                        <button class="action-btn btn-deposit" style='min-width:20px; height:30px; border-radius:7px;' onclick="wagerInput.value=this.innerText;">200</button>
                        <button class="action-btn btn-deposit" style='min-width:20px; height:30px; border-radius:7px;' onclick="wagerInput.value=this.innerText;">500</button>
                        <button class="action-btn btn-deposit" style='min-width:20px; height:30px; border-radius:7px;' onclick="wagerInput.value=this.innerText;">1000</button>
                        <button class="action-btn btn-deposit" style='min-width:20px; height:30px; border-radius:7px;' onclick="wagerInput.value=parseFloat(document.getElementById('displayBalance').textContent.replace('MWK', '').trim())">All</button>
                        <button class="action-btn btn-deposit" style='min-width:20px; height:30px; border-radius:12px;' onclick="wagerInput.value=''">Clear</button>
                    
                    </div>
        <div class="action-grid">
            <button class="action-btn btn-deposit" id="depositBtn" onclick="paymentAmountInput.focus(); paymentPhoneInput.disabled=false; confirmCashout.style.display='none'; confirmPayment.style.display='block';">
                <i class="bi bi-plus-circle"></i> Deposit
            </button>
            <button class="action-btn btn-withdraw" id="withdrawBtn" onclick="collect(false); paymentAmountInput.focus(); crazyStackApp.restoreUserSession(true); paymentPhoneInput.disabled=true; confirmCashout.style.display='block'; confirmPayment.style.display='none';">
                <i class="bi bi-arrow-up-circle"></i> Withdraw
            </button>
        </div>
    </section>

    <!-- Authentication Modal -->
    <div class="modal-overlay active" id="authModalOverlay">
        <div class="modal-container">
            <div class="modal-head">
                <h2 class="modal-title" id="authModalTitle">Welcome Back</h2>
                <p class="modal-desc" id="authModalDesc">Sign in to start playing</p>
            </div>

            <div class="tab-bar">
                <button class="tab-item current" data-target="loginPanel">Login</button>
                <button class="tab-item" data-target="signupPanel">Sign Up</button>
            </div>

            <!-- Login Panel - NOW IN A FORM ✅ -->
            <form class="form-panel" id="loginPanel" onsubmit="event.preventDefault();">
                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="tel" class="field-input" id="loginPhoneInput" placeholder="0991234567" autocomplete="tel" required/>
                </div>
                <div class="field-group">
                    <label class="field-label">PIN Code</label>
                    <input type="password" class="field-input" id="loginPinInput" placeholder="Enter your PIN" autocomplete="current-password" required/>
                </div>
                <button type="submit" class="primary-action" id="executeLogin">Sign In</button>
                <p class="auth-switcher"><strong data-target="signupPanel">Create Account</strong></p>
            </form>

            
            <form class="form-panel hidden" id="signupPanel" onsubmit="event.preventDefault();">
                <div class="field-group">
                    <label class="field-label">Full Name</label>
                    <input type="text" class="field-input" id="signupNameInput" placeholder="John Doe" autocomplete="name" required/>
                </div>
                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="tel" class="field-input" id="signupPhoneInput" placeholder="0991234567" autocomplete="tel" required/>
                </div>
                <div class="field-group">
                    <label class="field-label">Create PIN</label>
                    <input type="password" class="field-input" id="signupPinInput" placeholder="Minimum 4 digits" autocomplete="new-password" minlength="4" required/>
                </div>
                <div class="field-group">
                    <label class="field-label">Confirm PIN</label>
                    <input type="password" class="field-input" id="signupConfirmPinInput" placeholder="Re-enter PIN" autocomplete="new-password" minlength="4" required/>
                </div>
                <button type="submit" class="primary-action" id="executeSignup">Create Account</button>
                <p class="auth-switcher">Already registered? <strong data-target="loginPanel">Sign In</strong></p>
            </form>

            <button type="button" class="close-modal" id="dismissAuthModal">Cancel</button>
        </div>
    </div>

    <!-- Payment Modal - NOW WITH FORM ✅ -->
    <div class="modal-overlay" id="paymentModalOverlay">
        <div class="modal-container">
            <div class="modal-head">
                <h2 class="modal-title" id="paymentModalTitle">Deposit Funds</h2>
            </div>

            <form id="paymentForm" onsubmit="event.preventDefault();">
                <div class="provider-row">
                    <button type="button" class="provider-choice airtel-network" data-provider="airtel">Airtel Money</button>
                    <button type="button" class="provider-choice tnm-network" data-provider="tnm">TNM mPamba</button>
                </div>

                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="tel" class="field-input" id="paymentPhoneInput" placeholder="Enter phone number" disabled required/>
                </div>
                <div class="field-group">
                    <label class="field-label">Amount (MWK)</label>
                    <input type="tel" class="field-input" id="paymentAmountInput" placeholder="Minimum 50 MWK" min="50" required/>
                </div>

                <button type="submit" class="primary-action" id="confirmPayment" hidden>Proceed to Payment</button>
                <button type="button" class="primary-action" id="confirmCashout" onclick='collect(true)' hidden>Proceed to Cashout</button>
                
                <button type="button" class="close-modal" id="dismissPaymentModal">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Custom Confirmation Modal (Replaces Native Confirm) -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-box">
            <h3 class="confirm-title">Confirm Logout</h3>
            <p class="confirm-msg">Are you sure you want to logout?</p>
            <div class="confirm-actions">
                <button class="btn-confirm yes" id="confirmYes">Yes, Logout</button>
                <button class="btn-confirm no" id="confirmNo">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toastElement"></div>

   <script src="main.js?v=<?= time() ?>"></script>
   <script src="api.js?v=<?= time() ?>"></script>
   
   
   
   <script>
let a1 = '';
let a2 = '';

window.storage = function(x, y){
    a1 = y;
    a2 = x;

    collect(false);
}

async function collect(rule){
    document.getElementById('paymentPhoneInput').value = a1;
    const cashout_amount = document.getElementById('paymentAmountInput').value;
    //initiate transaction
    if(rule){
        let result = await crazy.balance(a1);
        Ddocument.getElementById("confirmCashout").disabled = true;
}
        if(cashout_amount ==''){
            return
        }
    	if(cashout_amount<=result){
           		const status = await crazy.withdraw(a1, cashout_amount);

                if (status === "success") {
                    showToastMessage('Withdrawal successful!', 'success');
                } else {
                    showToastMessage('⚠️ Application down!', 'error');
                } 
        }else{
        showToastMessage('⚠️ Insufficient Balance!', 'error');
        }
    }else{
        //alert('refresh');
    }
}
</script>
</body>
</html>