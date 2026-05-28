<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires"="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>CrazyStack - Wheel of Fortune</title>
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Supabase Client (Project B Only) -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <style>
        /* =========================================
       COLOR GRID & BUTTONS (DISABLED LOOK)
       ========================================= */
    /*login progress*/
    .loader5  {
  width: 45px;
  aspect-ratio: 1;
  --c: no-repeat linear-gradient(#000 0 0);
  background: 
    var(--c) 0%   50%,
    var(--c) 50%  50%,
    var(--c) 100% 50%;
  background-size: 20% 100%;
  animation: l1 1s infinite linear;
}
@keyframes l1 {
  0%  {background-size: 20% 100%,20% 100%,20% 100%}
  33% {background-size: 20% 10% ,20% 100%,20% 100%}
  50% {background-size: 20% 100%,20% 10% ,20% 100%}
  66% {background-size: 20% 100%,20% 100%,20% 10% }
  100%{background-size: 20% 100%,20% 100%,20% 100%}
}
    .loader4 {
  width: 50px;
  padding: 8px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: #25b09b;
  --_m: 
    conic-gradient(#0000 10%,#000),
    linear-gradient(#000 0 0) content-box;
  -webkit-mask: var(--_m);
          mask: var(--_m);
  -webkit-mask-composite: source-out;
          mask-composite: subtract;
  animation: l3 1s infinite linear;
}
@keyframes l3 {to{transform: rotate(1turn)}}
    .color-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 11px;
        margin-bottom: 18px;
    }

    /* Base Button Style (Dimmed/Disabled Look) */
    .color-option {
        position: relative;
        padding: 15px 10px;
        border-radius: 13px;
        border: 2px solid transparent;
        background-color: transparent !important;
        
        font-weight: 700;
        font-size: 0.82rem;
        cursor: pointer;
        text-align: center;
        overflow: hidden;
        
        /* Make it look disabled/dimmed by default */
        opacity: 0.4 !important;
        filter: grayscale(0.5); /* Further dim the colors */
        
        transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* --- Green Button --- */
    .option-green {
        background-color: #047857 !important; /* Dark Emerald */
        color: #ecfdf5 !important;
        border-color: #065f46 !important;
    }

    /* --- Blue Button --- */
    .option-blue {
        background-color: #1d4ed8 !important; /* Royal Blue */
        color: #dbeafe !important;
        border-color: #1e40af !important;
    }

    /* --- Red Button --- */
    .option-red {
        background-color: #b91c1c !important; /* Dark Red */
        color: #fee2e2 !important;
        border-color: #991b1b !important;
    }

    .color-option .odds {
        display: block;
        font-size: 0.68rem;
        margin-top: 4px;
        font-weight: 600;
    }

    /* =========================================
       SELECTED STATE (Enabled/Active Look)
       ========================================= */
    .color-option.selected {
        /* FORCE FULL BRIGHTNESS */
        opacity: 1 !important;
        filter: grayscale(0) !important;
        
        transform: scale(1.06);
        border-width: 2px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.4) !important;
    }

    /* Green Selected */
    .option-green.selected {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        border-color: #34d399 !important;
        box-shadow: 0 10px 30px rgba(16,185,129,0.4), 0 0 0 3px rgba(16,185,129,0.15) !important;
    }

    /* Blue Selected */
    .option-blue.selected {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: #ffffff !important;
        border-color: #60a5fa !important;
        box-shadow: 0 10px 30px rgba(59,130,246,0.4), 0 0 0 3px rgba(59,130,246,0.15) !important;
    }

    /* Red Selected */
    .option-red.selected {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: #ffffff !important;
        border-color: #f87171 !important;
        box-shadow: 0 10px 30px rgba(239,68,68,0.4), 0 0 0 3px rgba(239,68,68,0.15) !important;
    }
        /* =========================================
       BOTTOM STATUS INDICATOR (UPDATED)
       ========================================= */
    .status-indicator {
        /* Force relative positioning to override any JS absolute positioning */
        position: relative !important;
        top: auto !important;
        left: auto !important;
        transform: none !important;
        margin: 0 !important;

        /* New Bottom Panel Layout */
        width: 100%;
        display: none; /* Hidden by default */
        padding: 12px 15px !important;
        border-radius: 10px;
        background: rgba(0,0,0,0.3);
        border: 1px solid var(--border-subtle);
        text-align: center;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: var(--text-secondary);
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 15px !important;
    }

    /* Light theme adjustment */
    [data-theme="light"] .status-indicator {
        background: rgba(0,0,0,0.05);
        border-color: rgba(0,0,0,0.08);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    /* Win State Style */
    .status-indicator.is-win {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.4);
        color: var(--success);
        box-shadow: 0 0 25px rgba(16, 185, 129, 0.15);
        text-shadow: 0 0 10px rgba(16, 185, 129, 0.3);
    }

    /* Loss State Style */
    .status-indicator.is-loss {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.4);
        color: var(--danger);
        box-shadow: 0 0 25px rgba(239, 68, 68, 0.15);
    }
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
        
        /* Theme variables */
        --bg-primary: #030712;
        --bg-secondary: #111827;
        --bg-tertiary: #1f2937;
        --input-bg: #0a0f1e;
        --popover-bg: #111827;
        --hover-bg: rgba(255,255,255,0.06);
    }

    /* Light theme variables */
    [data-theme="light"] {
        --bg-deep: #f8fafc;
        --bg-surface: #ffffff;
        --card-bg: #ffffff;
        --border-subtle: rgba(0,0,0,0.08);
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --bg-primary: #f8fafc;
        --bg-secondary: #ffffff;
        --bg-tertiary: #e2e8f0;
        --input-bg: #ffffff;
        --popover-bg: #ffffff;
        --hover-bg: rgba(0,0,0,0.04);
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: var(--bg-deep);
        color: var(--text-primary);
        height: 100vh;
        overflow: hidden;
        position: relative;
        transition: background 0.35s ease, color 0.35s ease;
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

/* loaders */
.loader1 {
  height: 30px;
  aspect-ratio: 3;
  --c:no-repeat linear-gradient(#514b82 0 0);
  background:
    var(--c) calc(0*100%/5) 50%,
    var(--c) calc(1*100%/5) 50%,
    var(--c) calc(2*100%/5) 50%,
    var(--c) calc(3*100%/5) 50%,
    var(--c) calc(4*100%/5) 50%,
    var(--c) calc(5*100%/5) 50%;
  background-size:calc(100%/6 + 1px) 50%;
  animation: l19 1s infinite cubic-bezier(0.5,170,0.5,-170);
}
@keyframes l19 {
    14.28% {background-position: calc(0*100%/5) 51%, calc(1*100%/5) 50%, calc(2*100%/5) 50%, calc(3*100%/5) 50%, calc(4*100%/5) 50%, calc(5*100%/5) 50%}
    28.57% {background-position: calc(0*100%/5) 50%, calc(1*100%/5) 51%, calc(2*100%/5) 50%, calc(3*100%/5) 50%, calc(4*100%/5) 50%, calc(5*100%/5) 50%}
    42.85% {background-position: calc(0*100%/5) 50%, calc(1*100%/5) 50%, calc(2*100%/5) 51%, calc(3*100%/5) 50%, calc(4*100%/5) 50%, calc(5*100%/5) 50%}
    57.14% {background-position: calc(0*100%/5) 50%, calc(1*100%/5) 50%, calc(2*100%/5) 50%, calc(3*100%/5) 51%, calc(4*100%/5) 50%, calc(5*100%/5) 50%}
    71.42% {background-position: calc(0*100%/5) 50%, calc(1*100%/5) 50%, calc(2*100%/5) 50%, calc(3*100%/5) 50%, calc(4*100%/5) 51%, calc(5*100%/5) 50%}
    85.71% {background-position: calc(0*100%/5) 50%, calc(1*100%/5) 50%, calc(2*100%/5) 50%, calc(3*100%/5) 50%, calc(4*100%/5) 50%, calc(5*100%/5) 51%}
}

.loader2 {
  width: 50px;
  padding: 8px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: #25b09b;
  --_m: 
    conic-gradient(#0000 10%,#000),
    linear-gradient(#000 0 0) content-box;
  -webkit-mask: var(_m);
          mask: var(_m);
  -webkit-mask-composite: source-out;
          mask-composite: subtract;
  animation: l3 1s infinite linear;
}
@keyframes l3 {to{transform: rotate(1turn)}}

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
        transition: background 0.35s ease;
    }

    [data-theme="light"] .navbar {
        background: linear-gradient(to bottom, rgba(248,250,252,0.95) 0%, rgba(248,250,252,0.8) 70%, transparent 100%);
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
        transition: background 0.35s ease, border-color 0.35s ease;
    }

    [data-theme="light"] .balance-widget {
        background: var(--bg-secondary);
        border-color: rgba(0,0,0,0.08);
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
        padding-bottom: 280px;
    }

    @media (min-width: 1024px) {
        .stage {
            padding-bottom: 40px;
            padding-right: 400px;
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
        transition: transform 0.5s ease;
        transform-origin: center center;
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
        0 0 80px rgba(251,191,36,0.06),      /* glow - already even ✓ */
        0 0 60px rgba(0,0,0,0.55),           /* CHANGED: removed 30px offset */
        inset 0 0 40px rgba(0,0,0,0.35);     /* inset - already even ✓ */
}

    .wheel-container.spinning .wheel-canvas {
        animation: wheelPulse 0.5s ease-in-out infinite alternate;
    }

    @keyframes wheelPulse {
        from { filter: brightness(1); }
        to { filter: brightness(1.08); }
    }

    /* =========================================
       SETTINGS FAB & POPOVER (UPDATED - LEFT ALIGNED)
       ========================================= */
    .settings-fab {
        position: absolute;
        top: 75px;
        /* CHANGED: Moved from right: 20px to left: 20px */
        left: 20px;
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

    @media (min-width: 1024px) { .settings-fab { top: 80px; left: 28px; } }

    .settings-fab:hover {
        background: var(--accent-gold-dim);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
        transform: rotate(-60deg) scale(1.08);
        box-shadow: 0 6px 25px rgba(251,191,36,0.25);
    }

    .settings-popover {
        position: absolute;
        top: 130px;
        /* CHANGED: Moved from right: 20px to left: 20px */
        left: 20px;
        width: 260px;
        background: var(--popover-bg);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 8px;
        display: none;
        flex-direction: column;
        gap: 3px;
        box-shadow: 0 15px 45px rgba(0,0,0,0.55);
        z-index: 9999999999;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.25s ease;
    }

    @media (min-width: 1024px) { .settings-popover { left: 28px; top: 135px; } }

    .settings-popover.visible {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    .settings-entry {
        width: 100%;
        padding: 12px 14px;
        background: transparent;
        border: none;
        border-radius: 12px;
        color: var(--text-primary);
        font-size: 0.87rem;
        font-weight: 600;
        cursor: pointer;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.18s ease;
        gap: 10px;
    }

    .settings-entry:hover { 
        background: var(--hover-bg); 
    }

    .settings-entry .entry-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-entry .entry-left i { 
        width: 20px; 
        text-align: center; 
        opacity: 0.65; 
        font-size: 1rem; 
    }

    .settings-entry .entry-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .settings-entry .entry-label {
        font-size: 0.87rem;
        font-weight: 600;
    }

    .settings-entry .status-text {
        font-size: 0.78rem;
        color: var(--text-muted, var(--text-secondary));
        min-width: 38px;
        text-align: right;
    }

    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        width: 46px;
        height: 25px;
        flex-shrink: 0;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #374151;
        transition: 0.3s;
        border-radius: 25px;
    }

    [data-theme="light"] .toggle-slider {
        background-color: #d1d5db;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 19px;
        width: 19px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: #10b981;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(22px);
    }

    /* Settings Divider */
    .settings-divider {
        height: 1px;
        background: var(--border-subtle);
        margin: 5px 12px;
    }

    /* Logout Entry Special Style */
    #logoutSettingEntry {
        color: var(--danger) !important;
    }

    #logoutSettingEntry:hover {
        background: rgba(239,68,68,0.1) !important;
    }

    #logoutSettingEntry i {
        color: var(--danger) !important;
        opacity: 0.8 !important;
    }

    /* Music Volume Control */
    .music-volume-row {
        display: none;
        padding: 10px 14px 14px;
        gap: 10px;
        align-items: center;
    }

    .music-volume-row.visible {
        display: flex;
    }

    .music-volume-row label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        white-space: nowrap;
        font-weight: 500;
    }

    .music-volume-row input[type="range"] {
        flex: 1;
        height: 4px;
        -webkit-appearance: none;
        appearance: none;
        background: #374151;
        border-radius: 2px;
        outline: none;
    }

    [data-theme="light"] .music-volume-row input[type="range"] {
        background: #d1d5db;
    }

    .music-volume-row input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        background: #10b981;
        border-radius: 50%;
        cursor: pointer;
        border: none;
    }

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
        transition: background 0.35s ease;
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

    [data-theme="light"] .bet-field {
        background: rgba(0,0,0,0.06);
        border-color: rgba(0,0,0,0.12);
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

    [data-theme="light"] .field-input {
        background: rgba(0,0,0,0.05);
        border-color: rgba(0,0,0,0.12);
        color: #0f172a;
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
        background: rgba(127,29,27,0.15);
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
       CUSTOM CONFIRM MODAL
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

    /* =========================================
       SESSION WARNING BANNER
       ========================================= */
    .session-warning-banner {
        position: fixed;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #000000;
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.9rem;
        z-index: 350;
        box-shadow: 0 8px 32px rgba(245, 158, 11, 0.5);
        animation: pulse-warning 1s ease-in-out infinite;
        display: flex;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
    }

    @keyframes pulse-warning {
        0%, 100% { 
            opacity: 1; 
            transform: translateX(-50%) scale(1); 
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.5);
        }
        50% { 
            opacity: 0.85; 
            transform: translateX(-50%) scale(1.03); 
            box-shadow: 0 12px 40px rgba(245, 158, 11, 0.7);
        }
    }

    .session-warning-banner button {
        margin-left: 12px;
        background: #000000;
        color: #fbbf24;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 900;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .session-warning-banner button:hover {
        background: #1f2937;
        transform: scale(1.05);
    }

    .session-timer {
        font-size: 0.7rem;
        color: var(--text-secondary);
        font-family: 'SF Mono', monospace;
        opacity: 0.7;
    }

    /* =========================================
       DEMO CONTAINER - NEW!
       ========================================= */
    .demo-container {
        position: fixed;
        inset: 0;
        background: #fff;
        backdrop-filter: blur(20px);
        z-index: 450;
        display: none;
        overflow-y: auto;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .demo-container.active {
        display: block;
        opacity: 1;
    }

    .demo-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 16px;
        background: transparent;
    }

    .demo-close-btn {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 460;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--card-bg);
        border: 1px solid var(--border-subtle);
        color: var(--text-primary);
        font-size: 20px;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }

    .demo-close-btn.visible {
        display: flex;
    }

    .demo-close-btn:hover {
        background: var(--danger);
        border-color: var(--danger);
        color: white;
        transform: rotate(90deg);
    }

    /* =========================================
   WHATSAPP FLOATING BUTTON - TOP RIGHT
   ========================================= */
.whatsapp-float {
    position: fixed;
    top: 28px;                                    /* ✅ TOP POSITION */
    right: 28px;                                  /* ✅ RIGHT POSITION */
    width: 58px;
    height: 58px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 28px;
    text-decoration: none;
    z-index: 999;
    box-shadow: 0 6px 24px rgba(37, 211, 102, 0.45), 0 0 0 3px rgba(37, 211, 102, 0.15);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: whatsapp-pulse 2s ease-in-out infinite;
}

.whatsapp-float:hover {
    transform: scale(1.12) translateY(-4px);
    box-shadow: 0 10px 35px rgba(37, 211, 102, 0.6), 0 0 0 5px rgba(37, 211, 102, 0.2);
    animation: none;
}

.whatsapp-float:active {
    transform: scale(0.95);
}

@keyframes whatsapp-pulse {
    0%, 100% {
        box-shadow: 0 6px 24px rgba(37, 211, 102, 0.45), 0 0 0 3px rgba(37, 211, 102, 0.15);
    }
    50% {
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6), 0 0 0 6px rgba(37, 211, 102, 0.25);
    }
}

/* WhatsApp Tooltip - appears BELOW the button */
.whatsapp-float::before {
    content: 'Customer Care';
    position: absolute;
    top: 68px;                                   /* ✅ BELOW BUTTON */
    right: 0;                                    /* ✅ RIGHT-ALIGNED */
    transform: translateY(0);
    background: var(--card-bg);
    color: var(--text-primary);
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 1px solid var(--border-subtle);
    pointer-events: none;
}

.whatsapp-float:hover::before {
    opacity: 1;
    visibility: visible;
    top: 72px;                                  /* ✅ SLIDES DOWN */
}

/* Mobile adjustment - positioned below navbar */
@media (max-width: 767px) {
    .whatsapp-float {
        top: 80px;                              /* ✅ MOBILE: Below navbar */
        right: 20px;                            /* ✅ MOBILE: Right side */
        width: 54px;
        height: 54px;
        font-size: 26px;
    }

    .whatsapp-float::before {
        display: none;                          /* Hide tooltip on mobile */
    }
}

/* Desktop layout */
@media (min-width: 1024px) {
    .whatsapp-float {
        top: 28px;                              /* ✅ DESKTOP: Top-right */
        right: 28px;
    }
}
    /* Desktop layout adjustment when control deck is on the right */
    @media (min-width: 1024px) {
        .whatsapp-float {
            bottom: 28px;
            right: 28px;
        }
    }

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
        
        <!-- Settings Popover - WITH DEMO TOGGLE -->
        <div class="settings-popover" id="settingsPopover">
            
            <!-- Theme Toggle -->
            <button class="settings-entry" id="themeSettingEntry" type="button">
                <span class="entry-left">
                    <i class="bi bi-sun" id="themeIconSvg"></i>
                    <span class="entry-label">Theme</span>
                </span>
                <span class="entry-right">
                    <span class="status-text" id="themeStatusLabel">DARK</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="themeToggleCheckbox">
                        <span class="toggle-slider"></span>
                    </label>
                </span>
            </button>

            <!-- ✨ NEW: Demo Mode Toggle -->
            <button class="settings-entry" id="demoModeToggleEntry" type="button" style='display:none;'>
                <span class="entry-left">
                    <i class="bi bi-play-circle"></i>
                    <span class="entry-label">Demo</span>
                </span>
                <span class="entry-right">
                    <span class="status-text" id="demoModeStatusLabel">OFF</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="demoModeCheckbox">
                        <span class="toggle-slider"></span>
                    </label>
                </span>
            </button>

            <div class="settings-divider"></div>

            <!-- Sound Effects Toggle -->
            <button class="settings-entry" id="soundToggleEntry" type="button">
                <span class="entry-left">
                    <i class="bi bi-volume-up"></i>
                    <span class="entry-label">Sound FX</span>
                </span>
                <span class="entry-right">
                    <span class="status-text" id="sfxStatusLabel">ON</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="sfxToggleCheckbox" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </span>
            </button>

            <div class="settings-divider"></div>

            <!-- Background Music Toggle -->
            <button class="settings-entry" id="musicToggleEntry" type="button">
                <span class="entry-left">
                    <i class="bi bi-music-note-beamed"></i>
                    <span class="entry-label">Music</span>
                </span>
                <span class="entry-right">
                    <span class="status-text" id="musicStatusLabel">OFF</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="musicToggleCheckbox">
                        <span class="toggle-slider"></span>
                    </label>
                </span>
            </button>

            <!-- Music Volume Slider (shows when music is ON) -->
            <div class="music-volume-row" id="musicVolumeControl">
                <label>Volume</label>
                <input type="range" id="musicVolumeSlider" min="0" max="100" value="30">
            </div>

            <div class="settings-divider"></div>

            <!-- Logout Button -->
            <button class="settings-entry" id="logoutSettingEntry" type="button">
                <span class="entry-left">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="entry-label">Logout</span>
                </span>
            </button>

        </div>

        <!-- Wheel Container -->
        <div class="wheel-container" id="wheelContainer">
            <div class="wheel-pointer"></div>
            <canvas id="wheelCanvas" width="800" height="800" class="wheel-canvas"></canvas>
        </div>
    </main>

    <!-- ✨ NEW: Demo Container (loads demo.html here) -->
    <div class="demo-container" id="demoContainer">
        <!-- demo.html content will be loaded here via JavaScript -->
    </div>

    <!-- Demo Close Button -->
    <button class="demo-close-btn" id="demoCloseBtn" title="Exit Demo">
        <i class="bi bi-x-lg"></i>
    </button>

    <!-- Floating Control Deck -->
    <section class="control-deck" style='border-top:none;'>
        <div class="color-grid">
            <button class="color-option option-green" data-color="green">
                Green<span class="odds">1.01x</span>
            </button>
            <button class="color-option option-blue" data-color="blue">
                Blue<span class="odds">1.35x</span>
            </button>
            <button class="color-option option-red" data-color="red">
                Red<span class="odds">2.6x</span>
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
            <button class="action-btn btn-deposit" id="depositBtn" onclick="group();">
                <i class="bi bi-plus-circle"></i> Deposit
            </button>
           
            <button class="action-btn btn-withdraw" id="withdrawBtn" onclick="openWithdrawModalFixed()">
                <i class="bi bi-arrow-up-circle"></i> Withdraw
            </button>
        </div>
    </section>

    <!-- Authentication Modal -->
    <div class="modal-overlay" id="authModalOverlay">
        <div class="modal-container">
            <div class="modal-head">
                <h2 class="modal-title" id="authModalTitle">Welcome Back</h2>
                <p class="modal-desc" id="authModalDesc">Sign in to start playing</p>
            </div>

            <div class="tab-bar">
                <button class="tab-item current" data-target="loginPanel">Login</button>
                <button class="tab-item" data-target="signupPanel">Sign Up</button>
            </div>

            <!-- Login Panel -->
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

            <!-- Signup Panel -->
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

    <!-- Payment Modal -->
    <div class="modal-overlay" id="paymentModalOverlay">
        <div class="modal-container">
            <div class="modal-head">
                <h2 class="modal-title" id="paymentModalTitle">Deposit Funds</h2>
            </div>

            <form id="paymentForm" onsubmit="event.preventDefault();">
                <div class="provider-row">
                    <button type="button" class="provider-choice airtel-network" data-provider="airtel" id='airtel'>Airtel Money</button>
                    <button type="button" class="provider-choice tnm-network" data-provider="tnm" id='tnm'>TNM mPamba</button>
                </div>

                <div class="field-group">
                    <label class="field-label">Phone Number</label>
                    <input type="tel" class="field-input" id="paymentPhoneInput" placeholder="Enter phone number" required/>
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

    <!-- Custom Confirmation Modal -->
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

    <!-- Session Warning Banner Container -->
    <div id="sessionWarningContainer"></div>

    <!-- =========================================
         WHATSAPP CUSTOMER CARE BUTTON - NEW!
         ========================================= -->
    <a href="https://wa.me/2659805326903" target="_blank" rel="noopener noreferrer" class="whatsapp-float" title="Chat with Customer Care on WhatsApp" aria-label="Contact Customer Care via WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- 
    ============================================================
    SCRIPTS - UNIFIED PROJECT B ARCHITECTURE
    ============================================================
    ✅ main.js - Uses SINGLE Project B client (users + payments tables)
    ✅ withdraw_api.js - Withdrawal API integration
    NO MORE: paymentsDbClient or separate payment database!
    -->
    
    <!-- ✅✅✅ FIXED: Script loading with error detection -->
    <script src="API/main.js?v=<?= time() ?>" 
            onload="console.log('✅ main.js loaded successfully')" 
            onerror="console.error('❌ FAILED to load main.js')"></script>
            
    <script src="API/withdraw_api.js?v=<?= time() ?>" 
            onload="console.log('✅ withdraw_api.js loaded - crazy object ready')" 
            onerror="handleWithdrawAPILoadError()"></script>
    
    <!-- Error handler for failed script load -->
    <script>
    function handleWithdrawAPILoadError() {
        console.error('🚨🚨🚨 CRITICAL ERROR: withdraw_api.js FAILED TO LOAD!');
        console.error('   This will cause "crazy is not defined" error');
        console.error('');
        console.error('   POSSIBLE CAUSES:');
        console.error('   1. File does not exist at: API/withdraw_api.js');
        console.error('   2. Server returned 404/500 error');
        console.error('   3. Network connectivity issues');
        console.error('   4. JavaScript syntax error in the file');
        console.error('');
        console.error('   IMMEDIATE FIX:');
        console.error('   1. Check that API/withdraw_api.js exists on server');
        console.error('   2. Verify file permissions (should be 644)');
        console.error('   3. Test URL directly in browser');
        
        // Show user-friendly message
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (typeof crazy === 'undefined') {
                    const toast = document.getElementById('toastElement');
                    if (toast) {
                        toast.textContent = '⚠️ System partially loaded. Some features may be unavailable.';
                        toast.className = 'toast-notification type-error visible';
                        setTimeout(() => toast.classList.remove('visible'), 5000);
                    }
                }
            }, 2000);
        });
    }
    </script>
    
    <!-- SESSION MANAGEMENT SCRIPT -->
    <script>
    // =============================================
    // SESSION CONFIGURATION
    // =============================================
    const SESSION_CONFIG = {
        TIMEOUT_DURATION: 5 * 60 * 1000,
        WARNING_DURATION: 30 * 1000,
        STORAGE_KEY: 'crazyStackSession',
        CHECK_INTERVAL: 1000
    };

    // =============================================
    // SESSION MANAGER CLASS
    // =============================================
    class SessionManager {
        constructor(appInstance) {
            this.app = appInstance;
            this.timeoutTimer = null;
            this.warningTimer = null;
            this.checkInterval = null;
            this.lastActivityTime = Date.now();
            this.isActive = false;
            this.warningShown = false;
            this.warningElement = null;

            console.log('Session Manager initialized');
            this.setupActivityListeners();
        }

        start() {
            if (this.isActive) return;
            
            console.log(`Session started - Auto-logout after ${SESSION_CONFIG.TIMEOUT_DURATION / 1000 / 60} minutes`);
            this.isActive = true;
            this.lastActivityTime = Date.now();
            this.startTimers();
            this.startActivityCheck();
        }

        stop() {
            console.log('Session stopped');
            this.isActive = false;
            this.clearAllTimers();
            this.removeWarningBanner();
        }

        startTimers() {
            this.clearAllTimers();

            if (!this.isActive || !this.app.isUserAuthenticated) return;

            const timeUntilWarning = SESSION_CONFIG.TIMEOUT_DURATION - SESSION_CONFIG.WARNING_DURATION;
            
            this.warningTimer = setTimeout(() => {
                if (this.isActive && this.app.isUserAuthenticated) {
                    this.showWarningBanner();
                }
            }, timeUntilWarning);

            this.timeoutTimer = setTimeout(() => {
                if (this.isActive && this.app.isUserAuthenticated) {
                    this.handleTimeout();
                }
            }, SESSION_CONFIG.TIMEOUT_DURATION);
        }

        clearAllTimers() {
            if (this.warningTimer) {
                clearTimeout(this.warningTimer);
                this.warningTimer = null;
            }
            if (this.timeoutTimer) {
                clearTimeout(this.timeoutTimer);
                this.timeoutTimer = null;
            }
        }

        startActivityCheck() {
            if (this.checkInterval) clearInterval(this.checkInterval);
            
            this.checkInterval = setInterval(() => {
                if (!this.isActive || !this.app.isUserAuthenticated) {
                    this.stop();
                    return;
                }

                const elapsed = Date.now() - this.lastActivityTime;
                const remaining = Math.max(0, SESSION_CONFIG.TIMEOUT_DURATION - elapsed);

                if (Math.floor(elapsed / 1000) % 30 === 0 && elapsed > 0) {
                    console.log(`Session active - ${Math.ceil(remaining / 1000)}s remaining`);
                }

            }, SESSION_CONFIG.CHECK_INTERVAL);
        }

        resetTimers() {
            if (!this.isActive) return;
            
            this.lastActivityTime = Date.now();
            this.warningShown = false;
            this.removeWarningBanner();
            this.startTimers();
            
            console.log('Session timers reset');
        }

        setupActivityListeners() {
            const activityEvents = [
                'mousedown', 'mousemove', 'keypress', 'scroll', 
                'touchstart', 'click', 'keydown'
            ];

            activityEvents.forEach(eventName => {
                document.addEventListener(eventName, () => {
                    if (this.isActive && this.app.isUserAuthenticated) {
                        this.resetTimers();
                    }
                }, { passive: true });
            });

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && this.isActive && this.app.isUserAuthenticated) {
                    const timeAway = Date.now() - this.lastActivityTime;
                    
                    if (timeAway >= SESSION_CONFIG.TIMEOUT_DURATION) {
                        console.log(`User was away for ${Math.floor(timeAway / 1000)}s - forcing logout`);
                        this.handleTimeout();
                    } else if (timeAway > 0) {
                        console.log(`User returned after ${Math.floor(timeAway / 1000)}s away`);
                        this.resetTimers();
                    }
                }
            });

            console.log('Activity listeners attached');
        }

        showWarningBanner() {
            if (this.warningShown || !this.app.isUserAuthenticated) return;
            
            this.warningShown = true;

            this.removeWarningBanner();

            this.warningElement = document.createElement('div');
            this.warningElement.className = 'session-warning-banner';
            this.warningElement.id = 'sessionWarning';
            this.warningElement.innerHTML = `
                <span>Session expiring in ${SESSION_CONFIG.WARNING_DURATION / 1000} seconds...</span>
                <button onclick="window.crazyStackApp.sessionManager.extendSession()">
                    Stay Logged In
                </button>
            `;

            document.getElementById('sessionWarningContainer').appendChild(this.warningElement);

            try {
                if (typeof soundEngine !== 'undefined') {
                    soundEngine.errorSound();
                }
            } catch(e) {}
        }

        removeWarningBanner() {
            if (this.warningElement && this.warningElement.parentNode) {
                this.warningElement.remove();
                this.warningElement = null;
            }
            
            const existing = document.getElementById('sessionWarning');
            if (existing) existing.remove();
            
            this.warningShown = false;
        }

        extendSession() {
            console.log('Session extended by user action');
            this.resetTimers();
            this.removeWarningBanner();
            
            try {
                if (typeof showToastMessage === 'function') {
                    showToastMessage('Session extended! You have 5 more minutes.', 'success');
                }
            } catch(e) {}
        }

        handleTimeout() {
            if (!this.app.isUserAuthenticated) return;

            console.log('SESSION TIMEOUT! Forcing logout...');

            this.stop();
            this.removeWarningBanner();

            if (this.app.forceLogoutDueToTimeout) {
                this.app.forceLogoutDueToTimeout();
            }
        }

        getRemainingTime() {
            const elapsed = Date.now() - this.lastActivityTime;
            return Math.max(0, Math.ceil((SESSION_CONFIG.TIMEOUT_DURATION - elapsed) / 1000));
        }

        destroy() {
            this.stop();
            this.removeWarningBanner();
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
                this.checkInterval = null;
            }
        }
    }

    window.SessionManager = SessionManager;
    window.SESSION_CONFIG = SESSION_CONFIG;

    console.log('Session Management System Loaded');
    </script>

    <!-- Application Inline Script -->
    <script>
    let a1 = '';
    let a2 = '';

    window.storage = function(x, y){
        a1 = y;
        a2 = x;
        collect(false);
    }

   /**
    * ============================================
    * 💸 collect() function v5.0 - BULLETPROOF VERSION
    * ============================================
    * 
    * FIXES:
    * v5.0 - Added defensive check for "crazy is not defined"
    * v4.0 - Uses ACTUAL input field value, not session phone
    * 
    * Handles both:
    * - Setting phone number (rule=false)
    * - Processing withdrawal (rule=true)
    * 
    * ✅ Uses Project B's users table directly
    * ✅ No separate payments database calls
    * ✅ Works with withdraw_api.js
    * ✅ Handles missing API gracefully
    */
   async function collect(rule) {
   
     // ============================================
     // GET PHONE FROM INPUT FIELD (NOT SESSION!)
     // ============================================
     
     // ✅✅✅ CRITICAL FIX: Get from INPUT FIELD only
     const phoneInputElement = document.getElementById('paymentPhoneInput');
     let phoneNumber = phoneInputElement ? phoneInputElement.value.trim() : '';
     
     // Debug logging
     console.log('\n📞 collect() called with rule:', rule);
     console.log('📱 Phone from INPUT FIELD:', phoneNumber);
     console.log('👤 Session phone (for reference):', window.crazyStackApp?.playerPhone);
     
     // Track for debugging
     if (window.PhoneDebugTracker) {
         window.PhoneDebugTracker.log('INPUT', phoneNumber);
     }
     
     // Fallback to stored value (from storage() function) ONLY if input empty
     if (!phoneNumber && a1) {
         console.log('⚠️ Using fallback phone from storage():', a1);
         phoneNumber = a1;
         if (phoneInputElement) phoneInputElement.value = phoneNumber;
     }
     
     // Final validation
     if (!phoneNumber) {
         showToastMessage('❌ Phone number is missing!', 'error');
         console.warn('⚠️ No phone number provided');
         return;
     }
     
     // Clean and validate phone format
     const cleanedPhone = phoneNumber.replace(/[\s\-\.]/g, ''); // Remove spaces, dashes, dots
     
     // Must be 10 digits starting with 0 (Malawi format: 09XXXXXXXX or 08XXXXXXXX)
     const validPhonePattern = /^(099|098|088|089)\d{7}$/;
     
     if (!validPhonePattern.test(cleanedPhone)) {
         showToastMessage('❌ Invalid phone format! Use: 09XXXXXXXX or 08XXXXXXXX', 'error');
         console.warn('⚠️ Invalid phone format:', cleanedPhone);
         console.warn('   Expected: 099XXXXXXX, 098XXXXXXX, 088XXXXXXX, 089XXXXXXX');
         console.warn('   Received:', cleanedPhone);
         return;
     }
     
     console.log('✅ Phone validated (will be sent to webhook):', cleanedPhone);
     
     // Track for debugging
     if (window.PhoneDebugTracker) {
         window.PhoneDebugTracker.log('WEBHOOK', cleanedPhone);
     }
     
     // ============================================
     // IF rule=false: JUST SET PHONE NUMBER
     // ============================================
     
     if (!rule) {
         console.log('📝 Mode: Set phone number only (no action)');
         return;
     }
     
     // ============================================
     // IF rule=true: PROCESS WITHDRAWAL
     // ============================================
     
     console.log('\n💸 Mode: PROCESSING WITHDRAWAL');
     console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
     console.log('📤 Phone to use:', cleanedPhone);
     
     // Get and validate amount
     const cashoutAmountStr = document.getElementById('paymentAmountInput').value;
     const cashoutAmount = parseFloat(cashoutAmountStr) || 0;
     
     console.log('💰 Amount entered:', cashoutAmountStr, '→ Parsed:', cashoutAmount);
     
     // Validate amount
     if (isNaN(cashoutAmount) || cashoutAmount <= 0) {
         showToastMessage('❌ Please enter a valid amount (minimum MWK 50)', 'error');
         console.warn('⚠️ Invalid amount:', cashoutAmountStr);
         resetCashoutButton();
         return;
     }
     
     if (cashoutAmount < 50) {
         showToastMessage('❌ Minimum withdrawal is MWK 50', 'error');
         resetCashoutButton();
         return;
     }
     
     // Show loading state immediately
     setLoadingState(true);
     
     try {
       
       // ========================================
       // STEP 1: CHECK BALANCE FROM PROJECT B
       // ========================================
       
       console.log('\n🔍 Step 1: Checking user balance from Project B...');
       
       // Use the crazyStackApp which queries Project B directly
       if (window.crazyStackApp && window.crazyStackApp.playerPhone) {
           const currentBalance = window.crazyStackApp.currentBalance;
           console.log(`📊 Current session balance: MWK ${currentBalance.toFixed(2)}`);
           
           // Force fresh fetch from Project B
           await window.crazyStackApp.forceRefreshBalance('withdrawal-check');
           const freshBalance = window.crazyStackApp.currentBalance;
           
           console.log(`✅ Fresh balance from Project B: MWK ${freshBalance.toFixed(2)}`);
           
       const availableBalance = freshBalance;
       
       console.log(`   ✅ Available Balance: MWK ${availableBalance.toFixed(2)}`);
       console.log(`   📤 Requested Amount:  MWK ${cashoutAmount.toFixed(2)}`);
       
       // ========================================
       // STEP 2: VERIFY SUFFICIENT FUNDS
       // ========================================
       
       if (cashoutAmount > availableBalance) {
           const shortfall = cashoutAmount - availableBalance;
           
           console.warn(`\n❌ INSUFFICIENT FUNDS!`);
           console.warn(`   Shortfall: MWK ${shortfall.toFixed(2)}`);
           
           showToastMessage(
               `❌ Insufficient balance!\nAvailable: MWK ${availableBalance.toFixed(2)}\nRequested: MWK ${cashoutAmount.toFixed(2)}`, 
               'error'
           );
           
           resetCashoutButton();
           return;
       }
       
       console.log('   ✅ Sufficient funds confirmed');
       
       // ========================================
       // ✅✅✅ STEP 3: SAFETY CHECK FOR CRAZY OBJECT
       // ========================================
       
       console.log('\n🚀 Step 3: Calling withdrawal API...');
       console.log(`   Phone: ${cleanedPhone}`);  // ✅ Using validated input phone
       console.log(`   Amount: MWK ${cashoutAmount.toFixed(2)}`);
       
       // ✅✅✅ CRITICAL DEFENSIVE CHECK - Prevents "crazy is not defined" error
       if (typeof crazy === 'undefined') {
           console.error('❌ FATAL ERROR: "crazy" object is not defined!');
           console.error('   This means withdraw_api.js failed to load properly.');
           console.error('');
           console.error('   TROUBLESHOOTING STEPS:');
           console.error('   1. Open browser DevTools (F12)');
           console.error('   2. Go to Network tab');
           console.error('   3. Look for "withdraw_api.js" in the list');
           console.error('   4. If status is red/pink → File not found (404)');
           console.error('   5. If status is 200 but still fails → Syntax error in file');
           console.error('');
           console.error('   QUICK FIX: Refresh page and try again.');
           
           setLoadingState(false);
           
           showToastMessage(
               '❌ System error: Withdrawal module not loaded.\n\nPlease refresh the page (F5) and try again.\n\nIf problem persists, contact support.',
               'error'
           );
           
           resetCashoutButton();
           return;
       }
       
       // ✅ Check if withdraw method exists
       if (typeof crazy.withdraw !== 'function') {
           console.error('❌ FATAL ERROR: crazy.withdraw is not a function!');
           console.error('   crazy object exists but withdraw method is missing.');
           console.error('   Object keys:', Object.keys(crazy));
           
           setLoadingState(false);
           showToastMessage('❌ Internal error: Withdrawal method not found.\n\nPlease refresh the page.', 'error');
           resetCashoutButton();
           return;
       }
       
       console.log('   ✅ Safety check passed: crazy object and withdraw method are available');
       
       // ========================================
       // STEP 4: CALL WITHDRAWAL API
       // ========================================
       
       const withdrawResult = await crazy.withdraw(cleanedPhone, cashoutAmount);
       
       console.log('\n📥 Withdrawal API Response:');
       console.log('   Success:', withdrawResult.success);
       console.log('   Message:', withdrawResult.message || withdrawResult.error || '(none)');
       console.log('   Full Object:', JSON.stringify(withdrawResult, null, 2));
       
       // Reset loading state
       setLoadingState(false);
       
       // ========================================
       // STEP 5: HANDLE RESULT
       // ========================================
       
       if (withdrawResult.success) {
         
         // ✅✅✅ SUCCESS!
         
         console.log('\n✅✅✅ WITHDRAWAL SUCCESSFUL!');
         console.log(`   Sent to phone: ${cleanedPhone}`);  // Log which phone got the money
         
         // Show success state on button
         showSuccessState(cashoutAmount, cleanedPhone, withdrawResult);
         
         // Display instructions if provided
         if (withdrawResult.instructions && withdrawResult.instructions.length > 0) {
             console.log('\n📱 User Instructions:');
             withdrawResult.instructions.forEach((instruction, i) => {
                 console.log(`   ${i + 1}. ${instruction}`);
             });
         }
         
         // Refresh user session after delay (to update balance display from Project B)
         setTimeout(() => {
             console.log('🔄 Refreshing user session from Project B...');
             
             if (window.crazyStackApp && typeof crazyStackApp.restoreUserSession === 'function') {
                 crazyStackApp.restoreUserSession(true).then(() => {
                     console.log('✅ Session refreshed from Project B');
                 }).catch(err => {
                     console.warn('⚠️ Session refresh failed:', err);
                 });
             }
             
             // Reset button to initial state
             resetCashoutButton();
             
         }, 6000); // Wait 6 seconds before resetting
         
       } else {
         
         // ❌ FAILURE
         
         console.error('\n❌ WITHDRAWAL FAILED!');
         console.error('   Error:', withdrawResult.error);
         console.error('   Code:', withdrawResult.errorCode || 'N/A');
         
         // Build user-friendly error message
         let userErrorMessage = withdrawResult.error || 'Withdrawal failed. Please try again.';
         
         // Add suggestion if available
         if (withdrawResult.suggestion) {
             userErrorMessage += `\n\n💡 ${withdrawResult.suggestion}`;
         }
         
         // Add debug info in console only
         if (withdrawResult.debugInfo) {
             console.error('   Debug Info:', JSON.stringify(withdrawResult.debugInfo, null, 2));
         }
         
         showToastMessage(`❌ ${userErrorMessage}`, 'error');
         resetCashoutButton();
       }
       
       } else {
           showToastMessage('❌ User session not found. Please login again.', 'error');
           resetCashoutButton();
       }
       
     } catch (unexpectedError) {
       
       // ========================================
       // UNEXPECTED ERROR HANDLER
       // ========================================
       
       console.error('\n💥 UNEXPECTED ERROR IN collect():');
       console.error('   Message:', unexpectedError.message);
       console.error('   Stack:', unexpectedError.stack);
       console.error('   Name:', unexpectedError.name);
       
       setLoadingState(false);
       
       // ✅ Special handling for "crazy is not defined"
       if (unexpectedError.message && unexpectedError.message.includes('crazy is not defined')) {
           console.error('');
           console.error('╔══════════════════════════════════════════╗');
           console.error('║  ROOT CAUSE IDENTIFIED:                  ║');
           console.error('║  withdraw_api.js did NOT load properly!  ║');
           console.error('╚══════════════════════════════════════════╝');
           console.error('');
           console.error('FIX OPTIONS:');
           console.error('1. Ensure file exists at: API/withdraw_api.js');
           console.error('2. Check browser console for load errors');
           console.error('3. Clear browser cache and reload');
           console.error('4. Contact developer if issue persists');
           
           showToastMessage(
               '❌ Critical system error detected!\n\nThe withdrawal module failed to load.\n\nPlease:\n1. Refresh the page (F5)\n2. Try the withdrawal again\n\nIf this continues, contact support.',
               'error'
           );
       } else {
           showToastMessage(
               `❌ An unexpected error occurred: ${unexpectedError.message}\n\nPlease try again or contact support.`,
               'error'
           );
       }
       
       resetCashoutButton();
     }
   }

   /**
    * Set loading state on UI elements during withdrawal processing
    * @param {boolean} loading - True to show loading, false to hide
    */
   function setLoadingState(loading) {
     const cashoutBtn = document.getElementById("confirmCashout");
     const cancelBtn = document.getElementById('dismissPaymentModal');
     
     if (loading) {
         // Disable and show spinner on cashout button
         cashoutBtn.disabled = true;
         cashoutBtn.style.opacity = "0.6";
         cashoutBtn.innerHTML = `
           <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
             <div class="loader4"></div>
             <span>Processing...</span>
           </div>
         `;
         
         // Show loading on cancel button too
         cancelBtn.innerHTML = `
           <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
             <div class="loader1"></div>
             <span>Please wait...</span>
           </div>
         `;
         
         console.log('⏳ Loading state activated');
         
     } else {
         // Will be fully reset by resetCashoutButton() or showSuccessState()
         console.log('⏳ Loading state deactivated');
     }
   }

   /**
    * Show success state after successful withdrawal
    * @param {number} amount - Withdrawn amount
    * @param {string} phone - Recipient phone
    * @param {Object} result - API response data
    */
   function showSuccessState(amount, phone, result) {
     const cashoutBtn = document.getElementById("confirmCashout");
     
     // Update button appearance
     cashoutBtn.disabled = true; // Keep disabled until reset
     cashoutBtn.style.opacity = "1";
     cashoutBtn.style.background = "linear-gradient(135deg, #22c55e 0%, #16a34a 100%)";
     cashoutBtn.innerHTML = `
       <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
         <i class="bi bi-check-circle-fill" style="font-size:1.2rem;"></i>
         <span>✓ Success!</span>
       </div>
     `;
     
     // Show success toast notification
     const maskedPhone = `${phone.substr(0, 3)}****${phone.substr(-2)}`;
     showToastMessage(
         `✅ Success! MWK ${amount.toFixed(2)} sent to ${maskedPhone}\n\nCheck your phone for USSD prompt.`,
         'success'
     );
     
     // Clear amount field
     document.getElementById('paymentAmountInput').value = '';
     
     console.log('✅ Success state displayed');
   }

   /**
    * Reset cashout button to initial state
    */
   function resetCashoutButton() {
     const cashoutBtn = document.getElementById("confirmCashout");
     const cancelBtn = document.getElementById('dismissPaymentModal');
     
     // Reset cashout button
     cashoutBtn.disabled = false;
     cashoutBtn.style.opacity = "1";
     cashoutBtn.style.background = "linear-gradient(135deg, var(--accent-gold) 0%, #d97706 100%)";
     cashoutBtn.innerHTML = "Proceed to Cashout";
     
     // Reset cancel button
     cancelBtn.innerHTML = 'Cancel';
     
     console.log('🔄 Button state reset to initial');
   }

   // Make functions globally available
   window.collect = collect;
   window.resetCashoutButton = resetCashoutButton;

   console.log('✅ collect() function v5.0 loaded (BULLETPROOF - Handles missing API gracefully)');
   
    window.getSessionInfo = function() {
        if(!window.crazyStackApp || !window.crazyStackApp.sessionManager) {
            return { error: 'Session manager not initialized' };
        }
        const sm = window.crazyStackApp.sessionManager;
        return {
            isActive: sm.isActive,
            isAuthenticated: window.crazyStackApp.isUserAuthenticated,
            lastActivity: new Date(sm.lastActivityTime).toLocaleTimeString(),
            remainingTime: sm.getRemainingTime() + ' seconds',
            player: window.crazyStackApp.playerName || 'Guest'
        };
    };

    window.testQuickTimeout = function() {
        if(window.crazyStackApp && window.crazyStackApp.sessionManager) {
            SESSION_CONFIG.TIMEOUT_DURATION = 10 * 1000;
            SESSION_CONFIG.WARNING_DURATION = 3 * 1000;
            
            window.crazyStackApp.sessionManager.stop();
            window.crazyStackApp.sessionManager.start();
            
            console.log('TEST MODE: 10-second timeout activated');
            alert('Test mode: You will be logged out in 10 seconds of inactivity!\n\nWait 7 seconds to see warning.\nWait 3 more seconds for auto-logout.');
        }
    };
    
       function group(){
           paymentAmountInput.focus();
           paymentPhoneInput.disabled=false;
           confirmCashout.style.display='none'; 
           confirmPayment.style.display='block';
           networks('dep');

       }
    function networks(choice) {
    let num = document.getElementById("paymentPhoneInput").value.trim();
    console.log("Number:", num);

    if (num.startsWith("08")) {
        setTimeout(()=>{document.getElementById("tnm").click();},1000);
    } else if (num.startsWith("09")) {
        setTimeout(()=>{document.getElementById("airtel").click();},1000);
    } else {
        console.log("Unknown");
    }
        setTimeout(()=>{
          if(choice=='wit'){
        document.getElementById('paymentModalTitle').innerHTML=document.getElementById('displayBalance').innerHTML;
        }
        },1000);
        
}
                                    
                              
                                    
document.addEventListener('keydown', (e) => {
    if (!soundEngine?.isEnabled) return;
    
    switch(e.key) {
        case 'Enter':  soundEngine.successSound(); break;
        case 'Escape': soundEngine.errorSound();  break;
        default:      soundEngine.clickSound();   break;
    }
}); 

// =============================================
// ✅✅✅ DEMO MODE LOADER - FIXED VERSION
// =============================================

class DemoModeLoader {
    constructor() {
        this.container = document.getElementById('demoContainer');
        this.closeBtn = document.getElementById('demoCloseBtn');
        this.checkbox = document.getElementById('demoModeCheckbox');
        this.statusLabel = document.getElementById('demoModeStatusLabel');
        this.toggleEntry = document.getElementById('demoModeToggleEntry');
        this.isActive = false;
        this.isLoaded = false;
        
        this.init();
    }
    
    init() {
        // Bind toggle switch event
        if(this.checkbox) {
            this.checkbox.addEventListener('change', (e) => {
                if(e.target.checked) {
                    this.activateDemo();
                } else {
                    this.deactivateDemo();
                }
            });
        }
        
        // Bind close button
        if(this.closeBtn) {
            this.closeBtn.addEventListener('click', () => {
                this.deactivateDemo();
            });
        }
        
        // Bind click on entire entry (for mobile)
        if(this.toggleEntry) {
            this.toggleEntry.addEventListener('click', (e) => {
                // Don't trigger if clicking on checkbox itself (already handled)
                if(e.target.type !== 'checkbox' && e.target.tagName !== 'SPAN') {
                    if(this.checkbox) {
                        this.checkbox.checked = !this.checkbox.checked;
                        this.checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        }
        
        console.log('✅ Demo Mode Loader Initialized');
    }
    
    activateDemo() {
        console.log('🎮 Activating Demo Mode...');
        
        if(!this.isLoaded) {
            // Show loading state
            this.container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#fbbf24;font-size:1.2rem;"><div class="loader4"></div><span style="margin-left:15px;">Loading Demo...</span></div>';
            this.container.classList.add('active');
            this.closeBtn.classList.add('visible');
            
            // Fetch and load demo.html
            fetch('demo.html')
                .then(response => {
                    if(!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(htmlContent => {
                    // Inject HTML into container
                    this.container.innerHTML = htmlContent;
                    this.isLoaded = true;
                    this.isActive = true;
                    
                    // Update UI
                    if(this.statusLabel) this.statusLabel.textContent = 'ON';
                    
                    // Execute any scripts within the loaded HTML
                    this.executeScripts(this.container);
                    
                    console.log('✅ Demo loaded successfully!');
                    
                    if(typeof showToastMessage === 'function') {
                        showToastMessage('🎮 Demo Mode Activated!', 'success');
                    }
                })
                .catch(error => {
                    console.error('❌ Failed to load demo.html:', error);
                    this.container.innerHTML = `
                        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:#ef4444;text-align:center;padding:40px;">
                            <i class="bi bi-exclamation-triangle" style="font-size:48px;margin-bottom:20px;"></i>
                            <h3 style="margin-bottom:10px;">Failed to Load Demo</h3>
                            <p style="color:#94a3b8;margin-bottom:20px;">Could not find demo.html file.<br>Please make sure it exists in the same directory.</p>
                            <button onclick="window.demoLoader.deactivateDemo()" style="padding:12px 24px;background:#ef4444;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;">
                                Close
                            </button>
                        </div>
                    `;
                    
                    if(typeof showToastMessage === 'function') {
                        showToastMessage('Error loading demo.html', 'error');
                    }
                });
        } else {
            // Already loaded, just show it
            this.container.classList.add('active');
            this.closeBtn.classList.add('visible');
            this.isActive = true;
            if(this.statusLabel) this.statusLabel.textContent = 'ON';
        }
    }
    
    deactivateDemo() {
        console.log('⏹️ Deactivating Demo Mode...');
        
        this.isActive = false;
        this.container.classList.remove('active');
        this.closeBtn.classList.remove('visible');
        
        // Update toggle
        if(this.checkbox) this.checkbox.checked = false;
        if(this.statusLabel) this.statusLabel.textContent = 'OFF';
        
        if(typeof showToastMessage === 'function') {
            showToastMessage('Demo Mode Deactivated', 'info');
        }
    }
    
    executeScripts(container) {
        // Find and execute script tags within loaded content
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            
            // Copy attributes
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            // Copy content
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            
            // Replace old script with new one (triggers execution)
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }
}

// Initialize Demo Loader when DOM is ready
let demoLoader;
document.addEventListener('DOMContentLoaded', () => {
    demoLoader = new DemoModeLoader();
    window.demoLoader = demoLoader;
});

                             
// Disable autocomplete on all inputs
document.querySelectorAll('input').forEach(input => {
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('autocorrect', 'off');
    input.setAttribute('autocapitalize', 'off');
    input.setAttribute('spellcheck', 'false');
});

// Disable text selection on entire page
document.addEventListener('selectstart', (e) => e.preventDefault());

// Disable context menu (right-click)
document.addEventListener('contextmenu', (e) => e.preventDefault());

// Disable copy
document.addEventListener('copy', (e) => e.preventDefault());
                                 
                                    
    </script>
                                    
   <script>
/**
 * ============================================
 * 🎯 FIXED WITHDRAWAL MODAL OPENER - UNIFIED PROJECT B
 * ============================================
 * 
 * ✅ FIX: Phone field is now EDITABLE (not locked to session)
 * ✅ Pre-fills with account phone but allows changes
 * ✅ Reads balance from Project B
 */

function openWithdrawModalFixed() {
    console.log('\n🎯 OPENING WITHDRAWAL MODAL (FIXED v2)');
    console.log('═══════════════════════════════════════');
    
    // ==========================================
    // STEP 1: CHECK AUTHENTICATION FIRST!
    // ==========================================
    
    if (!window.crazyStackApp || !window.crazyStackApp.isUserAuthenticated) {
        console.warn('⚠️ User not authenticated!');
        showToastMessage('⚠️ Please login before making a withdrawal!', 'warning');
        
        // Open login modal after short delay
        setTimeout(() => {
            if (window.crazyStackApp && typeof window.crazyStackApp.showLoginModal === 'function') {
                window.crazyStackApp.showLoginModal();
            } else {
                document.getElementById('authModalOverlay').classList.add('active');
            }
        }, 1000);
        
        return;
    }
    
    // ==========================================
    // STEP 2: GET AUTHENTICATED USER'S INFO (FOR BALANCE ONLY)
    // ==========================================
    
    const authPhone = window.crazyStackApp.playerPhone;
    
    console.log('👤 Authenticated User Phone (for balance verification):', authPhone);
    console.log('👤 User Name:', window.crazyStackApp.playerName);
    console.log('💰 Current Balance (from Project B):', window.crazyStackApp.currentBalance);
    
    if (!authPhone || authPhone.trim() === '') {
        console.error('❌ No phone in session! Session corrupted.');
        showToastMessage('❌ Session error! Please logout and login again.', 'error');
        return;
    }
    
    // ==========================================
    // STEP 3: POPULATE PAYMENT MODAL (PHONE IS EDITABLE!)
    // ==========================================
    
    // Show modal first
    document.getElementById('paymentModalOverlay').classList.add('active');
    
    // Set title for withdrawal with live balance from Project B
    const currentBalance = document.getElementById('displayBalance').textContent;
    document.getElementById('paymentModalTitle').innerHTML = `
        <i class="bi bi-arrow-up-circle" style="color:#3b82f6;"></i> 
        Withdraw Funds<br>
        <small style="color:#94a3b8;font-weight:400;font-size:0.75rem;">
            Available: ${currentBalance}
        </small>
    `;
    
    // ✅✅✅ CRITICAL FIX: Pre-fill BUT allow editing!
    const phoneInput = document.getElementById('paymentPhoneInput');
    phoneInput.value = authPhone;           // Pre-fill with account phone as default
    phoneInput.disabled = false;            // ✅ UNLOCKED - User can change to different phone!
    
    console.log('✅ Phone input populated (EDITABLE):', authPhone);
    console.log('ℹ️ User can modify phone number if withdrawing to different account');
    
    // Detect network automatically based on pre-filled phone
    networks('wit');
    
    // Show cashout button, hide deposit button
    document.getElementById('confirmCashout').style.display = 'block';
    document.getElementById('confirmCashout').style.display = 'block';  // Ensure visible!
    document.getElementById('confirmPayment').style.display = 'none';
    
    // Focus on amount field after short delay
    setTimeout(() => {
        const amountInput = document.getElementById('paymentAmountInput');
        amountInput.value = '';  // Clear previous amount
        amountInput.focus();
    }, 300);
    
    console.log('✅✅✅ Withdrawal modal opened successfully (PHONE EDITABLE - BUG FIXED!)');
    console.log('═══════════════════════════════════════\n');
}

/**
 * Override the collect() function to add extra debugging
 * Wrap the existing collect() function if needed
 */
(function() {
    const originalCollect = window.collect;
    
    window.collect = async function(rule) {
        console.log('\n📞 collect() called with rule:', rule);
        console.log('   Phone input value:', document.getElementById('paymentPhoneInput')?.value);
        console.log('   Authenticated phone (Project B):', window.crazyStackApp?.playerPhone);
        
        // Call original function
        return originalCollect.apply(this, arguments);
    };
})();

console.log('✅ Fixed withdrawal functions loaded (UNIFIED PROJECT B - PHONE BUG FIXED)');
</script>                                 

<!-- =============================================
     🔍 DEBUG: Phone Number Tracker (TEMPORARY)
     Remove this section after bug is confirmed fixed
     ============================================= -->
<script>
// =============================================
// 🔍 DEBUG: Phone Number Tracker
// =============================================
window.PhoneDebugTracker = {
    originalInput: null,
    sentToWebhook: null,
    sessionPhone: null,
    
    log: function(stage, phone) {
        console.log(`📞 [${stage}] Phone:`, phone);
        
        if (stage === 'INPUT') this.originalInput = phone;
        if (stage === 'WEBHOOK') this.sentToWebhook = phone;
        if (stage === 'SESSION') this.sessionPhone = phone;
        
        // Alert if mismatch detected
        if (this.originalInput && this.sentToWebhook) {
            if (this.originalInput !== this.sentToWebhook) {
                console.error('🚨🚨🚨 PHONE MISMATCH DETECTED! 🚨🚨🚨');
                console.error('   User Entered:', this.originalInput);
                console.error('   Sent to Webhook:', this.sentToWebhook);
                console.error('   Session Phone:', this.sessionPhone);
                
                showToastMessage(
                    `⚠️ Debug: Phone changed from ${this.originalInput} to ${this.sentToWebhook}`,
                    'warning'
                );
            } else {
                console.log('✅ Phone match verified: Input = Webhook');
            }
        }
    },
    
    reset: function() {
        this.originalInput = null;
        this.sentToWebhook = null;
        this.sessionPhone = null;
    }
};

// Hook into phone input changes
document.addEventListener('DOMContentLoaded', () => {
    const phoneInput = document.getElementById('paymentPhoneInput');
    if (phoneInput) {
        phoneInput.addEventListener('change', (e) => {
            console.log('📝 Phone input changed to:', e.target.value);
            window.PhoneDebugTracker.log('INPUT', e.target.value);
        });
        
        phoneInput.addEventListener('blur', (e) => {
            console.log('📝 Phone input blurred. Current value:', e.target.value);
        });
        
        // Also log on keyup for real-time tracking
        phoneInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter' || e.target.value.length === 10) {
                console.log('📝 Phone entered:', e.target.value);
            }
        });
    }
    
    console.log('✅ Phone Debug Tracker initialized');
});
</script>

<!-- =============================================
     ✅✅✅ SYSTEM HEALTH CHECK ON PAGE LOAD
     ============================================= -->
<script>
/**
 * Verifies critical dependencies are loaded after page initialization
 */
window.addEventListener('DOMContentLoaded', () => {
    // Wait 2 seconds for all scripts to finish loading
    setTimeout(() => {
        console.log('\n====================================');
        console.log('🔍 SYSTEM HEALTH CHECK');
        console.log('====================================\n');
        
        let allHealthy = true;
        
        // Check 1: crazy object (withdrawal API)
        if (typeof crazy === 'undefined') {
            console.error('❌ FAIL: "crazy" object not defined');
            console.error('   → withdraw_api.js failed to load or has syntax error');
            allHealthy = false;
        } else {
            console.log('✅ PASS: "crazy" object defined');
            
            // Check 2: crazy.withdraw method
            if (typeof crazy.withdraw !== 'function') {
                console.error('❌ FAIL: crazy.withdraw is not a function');
                console.error('   → Method missing from API object');
                allHealthy = false;
            } else {
                console.log('✅ PASS: crazy.withdraw() method available');
            }
        }
        
        // Check 3: crazyStackApp
        if (typeof window.crazyStackApp === 'undefined') {
            console.error('❌ FAIL: crazyStackApp not defined');
            console.error('   → main.js may have failed to load');
            allHealthy = false;
        } else {
            console.log('✅ PASS: crazyStackApp initialized');
        }
        
        // Summary
        console.log('\n====================================');
        if (allHealthy) {
            console.log('✅ ALL SYSTEMS OPERATIONAL');
            console.log('   Withdrawals should work correctly');
        } else {
            console.error('⚠️ SYSTEM ISSUES DETECTED');
            console.error('   Some features may not work properly');
            console.error('   See errors above for details');
        }
        console.log('====================================\n');
        
        // Store health status globally
        window.systemHealth = {
            healthy: allHealthy,
            timestamp: new Date().toISOString(),
            checks: {
                crazyDefined: typeof crazy !== 'undefined',
                withdrawAvailable: typeof crazy?.withdraw === 'function',
                appInitialized: typeof window.crazyStackApp !== 'undefined'
            }
        };
        
    }, 2000);
});
</script>
                                
                                
                                <!-- ============================================
     🆘 EMERGENCY: Complete Withdrawal System (INLINE)
     ============================================
     This bypasses ANY issues with external withdraw_api.js
     Defines window.crazy directly in your HTML page!
-->
<script>
console.log('🔧 Loading INLINE withdrawal system...');

// Define crazy object IMMEDIATELY (no external file needed!)
window.crazy = {
    url: "https://awnzbiatwnfmryerfxwg.supabase.co",
    key: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg",

    // PayChangu credentials (LIVE!)
    PAYCHANGU: {
        SECRET_KEY: 'sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS',
        MODE: 'live',
        BASE_URL: 'https://api.paychangu.com',
        PAYOUT_ENDPOINT: '/mobile-money/payouts/initialize'
    },

    // Your VALID operator IDs from mobile_pay.php
    OPERATORS: {
        AIRTEL: {
            name: 'Airtel Money',
            ref_id: '20be6c20-adeb-4b5b-a7ba-0769820df4fb',
            ussd_code: '*303#',
            network_code: 'airtel',
            prefixes: ['99', '98', '099', '098']
        },
        TNM: {
            name: 'TNM mPamba',
            ref_id: '27494cb5-ba9e-437f-a114-4e7a7686bcca',
            ussd_code: '*456#',
            network_code: 'tnm',
            prefixes: ['88', '87', '81', '088', '087', '081']
        }
    },

    // Helper: Get Supabase headers
    getHeaders: function() {
        return {
            "apikey": this.key,
            "Authorization": "Bearer " + this.key,
            "Content-Type": "application/json"
        };
    },

    // Convert phone to 10-digit format (internal)
    normalizePhone: function(phone) {
        if (!phone) return '';
        let digits = phone.replace(/\D/g, '');
        if (digits.startsWith('265')) digits = '0' + digits.slice(3);
        if (digits.length === 9 && !digits.startsWith('0')) digits = '0' + digits;
        return digits;
    },

    // Convert to 9-digit format (for PayChangu API)
    toPayChanguFormat: function(phone10digit) {
        if (!phone10digit) return '';
        if (phone10digit.startsWith('0')) return phone10digit.slice(1); // Remove leading 0
        return phone10digit;
    },

    // Detect network from phone number
    detectProvider: function(phone) {
        if (!phone) return this.OPERATORS.AIRTEL;
        const cleaned = phone.replace(/\D/g, '');
        const prefix2 = cleaned.slice(-2);
        
        const isAirtel = this.OPERATORS.AIRTEL.prefixes.some(p => 
            cleaned.endsWith(p) || prefix2.startsWith(p.replace('0',''))
        );
        
        if (isAirtel) return this.OPERATORS.AIRTEL;
        
        const isTNM = this.OPERATORS.TNM.prefixes.some(p => 
            cleaned.endsWith(p) || prefix2.startsWith(p.replace('0',''))
        );
        
        return isTNM ? this.OPERATORS.TNM : this.OPERATORS.AIRTEL;
    },

    // Generate unique IDs
    generateChargeId: function() {
        return 'CHG_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6).toLowerCase();
    },
    
    generateReference: function() {
        return 'WD_' + Date.now() + '_' + Math.random().toString(36).substr(2, 8).toUpperCase();
    },

    /**
     * Check balance via Supabase REST (CORS-safe)
     */
    balance: async function(phone) {
        try {
            console.log('🔍 [balance] Checking:', phone);
            
            const r = await fetch(`${this.url}/rest/v1/users?phone=eq.${phone}&select=balance`, {
                method: "GET",
                headers: this.getHeaders()
            });
            
            const d = await r.json();
            
            if (d && d.length > 0 && d[0].balance !== undefined) {
                const bal = parseFloat(d[0].balance) || 0;
                console.log(`   ✅ Balance: MWK ${bal.toFixed(2)}`);
                return bal;
            }
            
            console.warn('⚠️ User not found');
            return 'User not found';
            
        } catch (e) {
            console.error('❌ Error:', e.message);
            return 'Error: ' + e.message;
        }
    },

    /**
     * ✅✅✅ MAIN WITHDRAWAL - Calls PayChangu DIRECTLY!
     */
    withdraw: async function(phone, amount) {
        console.log('\n💰💰💸 WITHDRAWAL INITIATED 💸💰💰');
        console.log(`📱 Phone: ${phone} | 💵 Amount: MWK ${amount}`);
        
        try {
            // VALIDATE
            if (!window.crazyStackApp || !window.crazyStackApp.isUserAuthenticated) {
                return { success: false, error: 'Not logged in', errorCode: 'NOT_AUTHENTICATED' };
            }
            
            const normalizedPhone = this.normalizePhone(phone);
            if (!normalizedPhone || normalizedPhone.length !== 10) {
                return { success: false, error: `Invalid phone: ${phone}`, errorCode: 'INVALID_PHONE' };
            }
            
            const numericAmount = Number(amount);
            if (isNaN(numericAmount) || numericAmount < 50) {
                return { success: false, error: 'Min MWK 50', errorCode: 'AMOUNT_TOO_LOW' };
            }
            if (numericAmount > 100000) {
                return { success: false, error: 'Max MWK 100,000', errorCode: 'AMOUNT_TOO_HIGH' };
            }
            
            const provider = this.detectProvider(normalizedPhone);
            const paychanguPhone = this.toPayChanguFormat(normalizedPhone);
            
            console.log(`   ✅ Provider: ${provider.name}`);
            console.log(`   ✅ PayChangu Phone: ${paychanguPhone}`);
            
            // CHECK BALANCE
            console.log('\n💳 Checking balance...');
            const sessionPhone = window.crazyStackApp.playerPhone;
            const currentBalance = await this.balance(sessionPhone);
            
            console.log(`   💰 Current: MWK ${currentBalance}`);
            console.log(`   📤 Requested: MWK ${numericAmount}`);
            
            if (typeof currentBalance !== 'number') {
                return { success: false, error: 'Failed to verify balance', errorCode: 'BALANCE_FAILED' };
            }
            
            if (numericAmount > currentBalance) {
                return {
                    success: false,
                    error: `Insufficient funds!\nHave: MWK ${currentBalance}\nWant: MWK ${numericAmount}`,
                    errorCode: 'INSUFFICIENT_FUNDS'
                };
            }
            
            console.log('   ✅ Funds OK!');
            
            // CALL PAYCHANGU DIRECTLY!
            console.log('\n🚀 Calling PayChangu...');
            
            const chargeId = this.generateChargeId();
            const reference = this.generateReference();
            const apiUrl = this.PAYCHANGU.BASE_URL + this.PAYCHANGU.PAYOUT_ENDPOINT;
            
            const payload = {
                amount: numericAmount,
                currency: 'MWK',
                mobile: paychanguPhone,
                network: provider.network_code,
                mobile_money_operator_ref_id: provider.ref_id,
                charge_id: chargeId,
                reference: reference,
                mode: this.PAYCHANGU.MODE
            };
            
            console.log('   Payload:', JSON.stringify(payload));
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.PAYCHANGU.SECRET_KEY}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            const httpStatus = response.status;
            const responseData = await response.json();
            
            console.log(`\n📥 Response HTTP: ${httpStatus}`);
            console.log('   Data:', responseData);
            
            if (httpStatus >= 400 || !responseData || responseData.status === 'error') {
                let errorMsg = 'Payment failed';
                if (responseData?.message) {
                    errorMsg = typeof responseData.message === 'object' 
                        ? Object.values(responseData.message).flat().join('; ')
                        : responseData.message;
                }
                
                return { success: false, error: errorMsg, errorCode: 'PAYCHANGU_ERROR' };
            }
            
            // SUCCESS! Update DB
            console.log('\n✅ PayChangu accepted! Updating DB...');
            
            try {
                const newBal = Number(currentBalance) - numericAmount;
                
                // Update user balance
                await fetch(`${this.url}/rest/v1/users?phone=eq.${sessionPhone}`, {
                    method: 'PATCH',
                    headers: this.getHeaders(),
                    body: JSON.stringify({ balance: newBal })
                });
                
                // Record transaction
                await fetch(`${this.url}/rest/v1/payments`, {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({
                        charge_id: chargeId,
                        phone: normalizedPhone,
                        amount: numericAmount,
                        type: 'withdrawal',
                        status: 'success',
                        provider: provider.network_code.toLowerCase(),
                        source: 'inline_fallback_v16',
                        created_at: new Date().toISOString()
                    })
                });
                
                // Update app state
                if (window.crazyStackApp) {
                    window.crazyStackApp.currentBalance = newBal;
                    if (typeof window.crazyStackApp.updateBalanceDisplay === 'function') {
                        window.crazyStackApp.updateBalanceDisplay();
                    }
                }
                
                console.log(`   ✅ DB Updated! New balance: MWK ${newBal}`);
                
            } catch (dbErr) {
                console.warn('⚠️ DB update warning:', dbErr.message);
            }
            
            // RETURN SUCCESS
            console.log('\n🎉 SUCCESS! Money sent!');
            
            return {
                success: true,
                message: `MWK ${numericAmount} sent successfully!`,
                transaction: {
                    id: reference,
                    charge_id: chargeId,
                    phone: normalizedPhone,
                    paychangu_phone: paychanguPhone,
                    amount: numericAmount,
                    network: provider.name,
                    status: 'completed'
                },
                instructions: [
                    `✅ MWK ${numericAmount} sent to ${normalizedPhone}`,
                    `📱 Via ${provider.name}`,
                    `⏳ Wait 2-5 minutes`,
                    `📲 Or dial ${provider.ussd_code}`
                ],
                new_balance: Number(currentBalance) - numericAmount
            };
            
        } catch (err) {
            console.error('💥 Error:', err);
            return { success: false, error: err.message || 'Unexpected error', errorCode: 'UNEXPECTED_ERROR' };
        }
    }
};

// Confirm loaded
console.log('✅✅✅ INLINE WITHDRAWAL SYSTEM LOADED!');
console.log('   → window.crazy.withdraw() READY TO USE!');
console.log('   → Using LIVE PayChangu credentials');
console.log('   → Using VALID operator IDs\n');
</script>
</body>
</html>