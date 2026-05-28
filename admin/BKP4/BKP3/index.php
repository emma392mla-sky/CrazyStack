<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Stack Spinner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'accent': '#c8a23d',
                        'accent-light': '#e0c36a',
                        'surface-1': '#111318',
                        'surface-2': '#191c24',
                        'surface-3': '#22262f',
                        'surface-4': '#2a2e38',
                        'edge': 'rgba(255,255,255,0.06)',
                        'edge-light': 'rgba(255,255,255,0.1)',
                    },
                    fontFamily: {
                        'display': ['Space Grotesk', 'sans-serif'],
                        'ui': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg: #08090c;
            --s1: #0f1117;
            --s2: #161920;
            --s3: #1e2129;
            --s4: #282c36;
            --s5: #323742;
            --accent: #c8a23d;
            --accent-light: #e0c36a;
            --accent-dim: rgba(200,162,61,0.10);
            --accent-glow: rgba(200,162,61,0.25);
            --green: #34d399;
            --green-dim: rgba(52,211,153,0.10);
            --green-glow: rgba(52,211,153,0.25);
            --red: #f87171;
            --red-dim: rgba(248,113,113,0.10);
            --blue: #60a5fa;
            --blue-dim: rgba(96,165,250,0.10);
            --orange: #fb923c;
            --orange-dim: rgba(251,146,60,0.10);
            --text-1: #f1f1f3;
            --text-2: #9ca3af;
            --text-3: #6b7280;
            --text-4: #3f4550;
            --edge: rgba(255,255,255,0.05);
            --edge-light: rgba(255,255,255,0.08);
            --edge-strong: rgba(255,255,255,0.12);
        }
        html.light-mode {
            --bg: #f0f1f4;
            --s1: #ffffff;
            --s2: #f7f8fa;
            --s3: #eef0f3;
            --s4: #e2e5ea;
            --s5: #d4d8df;
            --accent: #92771e;
            --accent-light: #b8962a;
            --accent-dim: rgba(146,119,30,0.08);
            --accent-glow: rgba(146,119,30,0.2);
            --green: #059669;
            --green-dim: rgba(5,150,105,0.08);
            --green-glow: rgba(5,150,105,0.2);
            --red: #dc2626;
            --red-dim: rgba(220,38,38,0.08);
            --blue: #2563eb;
            --blue-dim: rgba(37,99,235,0.08);
            --orange: #ea580c;
            --orange-dim: rgba(234,88,12,0.08);
            --text-1: #111827;
            --text-2: #4b5563;
            --text-3: #6b7280;
            --text-4: #9ca3af;
            --edge: rgba(0,0,0,0.05);
            --edge-light: rgba(0,0,0,0.08);
            --edge-strong: rgba(0,0,0,0.12);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; width: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            background: var(--bg);
            color: var(--text-1);
            transition: background 0.4s, color 0.4s;
        }
        body::before {
            content: '';
            position: fixed;
            top: -30%; left: -20%;
            width: 70%; height: 70%;
            background: radial-gradient(ellipse, rgba(200,162,61,0.04) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -20%; right: -15%;
            width: 55%; height: 55%;
            background: radial-gradient(ellipse, rgba(96,165,250,0.025) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        .shell {
            display: flex; flex-direction: column;
            width: 100vw; height: 100vh; height: 100dvh;
            overflow: hidden; position: relative; z-index: 1;
        }
        .top-bar {
            height: 56px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px;
            background: var(--s1);
            border-bottom: 1px solid var(--edge);
            position: relative; z-index: 10;
        }
        .top-bar::after {
            content: '';
            position: absolute; bottom: -1px; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, var(--accent-dim), transparent);
        }
        .logo {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 1.15rem;
            color: var(--accent); letter-spacing: -0.02em;
            display: flex; align-items: center; gap: 10px;
        }
        .logo-mark {
            width: 30px; height: 30px; border-radius: 9px;
            background: linear-gradient(135deg, var(--accent) 0%, #9a7a28 100%);
            display: flex; align-items: center; justify-content: center;
            color: #0a0c10; font-size: 0.7rem;
            box-shadow: 0 2px 12px rgba(200,162,61,0.2);
        }
        .top-center { display: flex; align-items: center; gap: 6px; }
        .status-pill {
            display: flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 20px;
            background: var(--s2); border: 1px solid var(--edge);
            font-size: 0.6rem; color: var(--text-3); font-weight: 500;
        }
        .status-dot {
            width: 5px; height: 5px; border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 6px var(--green);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.35; } }
        .top-right { display: flex; align-items: center; gap: 8px; }
        .icon-btn {
            width: 36px; height: 36px; border-radius: 10px;
            border: 1px solid var(--edge);
            background: var(--s2); color: var(--text-2);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 0.8rem;
            transition: all 0.2s; position: relative; overflow: hidden;
        }
        .icon-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, transparent 60%);
            pointer-events: none;
        }
        .icon-btn:hover { border-color: var(--edge-light); color: var(--text-1); background: var(--s3); }
        .icon-btn:active { transform: scale(0.92); }
        .icon-btn.deposit-btn {
            border-color: rgba(52,211,153,0.15);
            color: var(--green); background: var(--green-dim);
            box-shadow: 0 0 12px rgba(52,211,153,0.08);
        }
        .icon-btn.deposit-btn:hover { background: rgba(52,211,153,0.15); border-color: rgba(52,211,153,0.25); }
        .bal-block {
            text-align: right; padding-left: 8px; border-left: 1px solid var(--edge);
            position: relative;
        }
        .bal-label {
            font-size: 0.5rem; color: var(--text-4);
            text-transform: uppercase; letter-spacing: 0.12em; font-weight: 600;
        }
        .bal-val {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 1rem;
            background: linear-gradient(135deg, var(--accent) 30%, var(--accent-light) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
        }
        .bal-unit { font-size: 0.6rem; color: var(--text-4); font-weight: 500; margin-left: 3px; -webkit-text-fill-color: var(--text-4); }

        .bal-delta {
            position: absolute;
            top: -2px; right: 0;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.65rem; font-weight: 700;
            white-space: nowrap; pointer-events: none;
            animation: deltaFloat 1.6s ease-out forwards;
        }
        .bal-delta.up { color: var(--green); text-shadow: 0 0 8px rgba(52,211,153,0.4); }
        .bal-delta.down { color: var(--red); text-shadow: 0 0 8px rgba(248,113,113,0.4); }
        @keyframes deltaFloat {
            0% { opacity: 0; transform: translateY(4px) scale(0.8); }
            12% { opacity: 1; transform: translateY(-6px) scale(1); }
            65% { opacity: 1; transform: translateY(-14px) scale(1); }
            100% { opacity: 0; transform: translateY(-22px) scale(0.9); }
        }

        .main-area {
            flex: 1; display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 0; overflow: hidden;
            position: relative;
        }
        .wheel-panel {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 16px; position: relative;
            background: linear-gradient(180deg, transparent 0%, rgba(200,162,61,0.015) 100%);
        }
        .wheel-panel::after {
            content: ''; position: absolute; right: 0; top: 10%; bottom: 10%; width: 1px;
            background: linear-gradient(180deg, transparent, var(--edge), transparent);
        }
        .wheel-frame {
            position: relative;
            width: 100%; max-width: 360px; max-height: 100%;
            aspect-ratio: 1;
        }
        canvas#wheelCanvas {
            width: 100%; height: 100%;
            border-radius: 50%;
            background: var(--s2);
            position: relative; z-index: 2;
        }
        .pointer-wrap {
            position: absolute; top: -4px; left: 50%;
            transform: translateX(-50%); z-index: 5;
            filter: drop-shadow(0 2px 10px rgba(200,162,61,0.6));
            transition: filter 0.4s;
        }
        .wheel-frame.active .pointer-wrap { filter: drop-shadow(0 2px 18px rgba(200,162,61,0.9)); }
        .wheel-ring-outer {
            position: absolute; inset: -10px;
            border-radius: 50%;
            border: 1.5px solid var(--edge-light);
            pointer-events: none; z-index: 1;
        }
        .wheel-ring-inner {
            position: absolute; inset: -4px;
            border-radius: 50%;
            border: 1px solid var(--edge);
            pointer-events: none; z-index: 3;
        }
        .wheel-glow {
            position: absolute; inset: -30px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 65%);
            opacity: 0; transition: opacity 0.8s;
            pointer-events: none; z-index: 0;
        }
        .wheel-frame.active .wheel-glow { opacity: 1; }
        .result-dots {
            display: flex; gap: 3px; margin-top: 10px;
            flex-wrap: wrap; justify-content: center; min-height: 18px;
        }
        .rdot {
            width: 20px; height: 20px; border-radius: 5px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.5rem; font-weight: 800;
            animation: dotIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards;
            font-family: 'Space Grotesk', sans-serif;
            border: 1px solid var(--edge);
        }
        .rdot.low { background: var(--green-dim); color: var(--green); border-color: rgba(52,211,153,0.15); }
        .rdot.mid { background: var(--blue-dim); color: var(--blue); border-color: rgba(96,165,250,0.15); }
        .rdot.high { background: var(--red-dim); color: var(--red); border-color: rgba(248,113,113,0.15); }
        .rdot.miss { background: var(--edge); color: var(--text-4); }
        @keyframes dotIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .ctrl-panel {
            display: flex; flex-direction: column;
            justify-content: center; padding: 24px;
            gap: 14px; overflow-y: auto;
        }
        .ctrl-panel::-webkit-scrollbar { width: 3px; }
        .ctrl-panel::-webkit-scrollbar-track { background: transparent; }
        .ctrl-panel::-webkit-scrollbar-thumb { background: var(--text-4); border-radius: 3px; }
        .section-label {
            font-size: 0.6rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            color: var(--text-4); margin-bottom: 6px;
            display: flex; align-items: center; gap: 6px;
        }
        .section-label::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, var(--edge), transparent);
        }
        .bet-row { display: flex; gap: 8px; align-items: stretch; }
        .bet-input-wrap {
            flex: 1; position: relative;
            background: var(--s2);
            border: 1px solid var(--edge-light);
            border-radius: 12px; overflow: hidden;
            transition: all 0.25s;
        }
        .bet-input-wrap:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
        }
        .bet-input-wrap input {
            width: 100%; padding: 12px 50px 12px 16px;
            background: transparent; border: none; outline: none;
            color: var(--text-1);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem; font-weight: 600;
        }
        .bet-input-wrap .unit {
            position: absolute; right: 16px; top: 50%;
            transform: translateY(-50%);
            font-size: 0.65rem; font-weight: 600;
            color: var(--text-4); letter-spacing: 0.05em;
        }
        .x2-btn {
            width: 50px; display: flex;
            align-items: center; justify-content: center;
            background: var(--accent-dim);
            color: var(--accent);
            border: 1px solid rgba(200,162,61,0.15);
            border-radius: 12px; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 0.8rem;
            transition: all 0.2s; flex-shrink: 0;
            position: relative; overflow: hidden;
        }
        .x2-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .x2-btn:hover { background: rgba(200,162,61,0.15); border-color: rgba(200,162,61,0.25); }
        .x2-btn:active { transform: scale(0.93); }
        .chip-row { display: flex; gap: 6px; flex-wrap: wrap; }
        .chip {
            padding: 6px 14px; border-radius: 9px;
            font-size: 0.7rem; font-weight: 600; cursor: pointer;
            background: var(--s2); border: 1px solid var(--edge);
            color: var(--text-3); transition: all 0.2s;
            font-family: 'Space Grotesk', sans-serif;
            position: relative; overflow: hidden;
        }
        .chip::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, transparent 60%);
            pointer-events: none;
        }
        .chip:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); transform: translateY(-1px); }
        .chip:active { transform: scale(0.93) translateY(0); }
        .cat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .cat-btn {
            padding: 12px 6px; border-radius: 12px;
            border: 2px solid transparent; cursor: pointer;
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 0.8rem;
            color: var(--text-1); transition: all 0.25s;
            position: relative; overflow: hidden;
        }
        .cat-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        .cat-btn .mult { display: block; font-size: 0.6rem; font-weight: 500; opacity: 0.5; margin-top: 3px; }
        .cat-btn.low { background: linear-gradient(160deg, rgba(52,211,153,0.12) 0%, rgba(52,211,153,0.06) 100%); }
        .cat-btn.mid { background: linear-gradient(160deg, rgba(96,165,250,0.12) 0%, rgba(96,165,250,0.06) 100%); }
        .cat-btn.high { background: linear-gradient(160deg, rgba(248,113,113,0.12) 0%, rgba(248,113,113,0.06) 100%); }
        .cat-btn.selected.low { border-color: var(--green); background: linear-gradient(160deg, rgba(52,211,153,0.22) 0%, rgba(52,211,153,0.1) 100%); box-shadow: 0 0 20px rgba(52,211,153,0.1); }
        .cat-btn.selected.mid { border-color: var(--blue); background: linear-gradient(160deg, rgba(96,165,250,0.22) 0%, rgba(96,165,250,0.1) 100%); box-shadow: 0 0 20px rgba(96,165,250,0.1); }
        .cat-btn.selected.high { border-color: var(--red); background: linear-gradient(160deg, rgba(248,113,113,0.22) 0%, rgba(248,113,113,0.1) 100%); box-shadow: 0 0 20px rgba(248,113,113,0.1); }
        .cat-btn:hover:not(.selected) { transform: translateY(-1px); }
        .cat-btn:active { transform: scale(0.96); }

        .expect-card {
            padding: 16px 18px;
            border-radius: 14px;
            background: var(--s2);
            border: 1.5px solid var(--edge);
            position: relative;
            overflow: hidden;
            transition: border-color 0.4s, background 0.4s, box-shadow 0.4s, transform 0.4s;
            transform: scale(1);
        }
        .expect-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, transparent 60%);
            pointer-events: none;
        }
        .expect-card.glow-low { border-color: rgba(52,211,153,0.35); background: rgba(52,211,153,0.08); box-shadow: 0 0 30px rgba(52,211,153,0.12), inset 0 0 30px rgba(52,211,153,0.04); transform: scale(1.02); }
        .expect-card.glow-mid { border-color: rgba(96,165,250,0.35); background: rgba(96,165,250,0.08); box-shadow: 0 0 30px rgba(96,165,250,0.12), inset 0 0 30px rgba(96,165,250,0.04); transform: scale(1.02); }
        .expect-card.glow-high { border-color: rgba(248,113,113,0.35); background: rgba(248,113,113,0.08); box-shadow: 0 0 30px rgba(248,113,113,0.12), inset 0 0 30px rgba(248,113,113,0.04); transform: scale(1.02); }
        .expect-inner { position: relative; z-index: 1; }
        .expect-label { font-size: 0.55rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-4); display: block; transition: color 0.3s; }
        .expect-val { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1.2rem; color: var(--text-3); display: block; margin-top: 4px; transition: color 0.3s, transform 0.3s; }
        .expect-val.pop { animation: expectPop 0.35s cubic-bezier(0.16,1,0.3,1); }
        @keyframes expectPop { 0% { transform: scale(0.85); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
        .expect-mult { font-size: 0.65rem; color: var(--text-4); font-weight: 500; display: block; margin-top: 2px; transition: color 0.3s; }
        .expect-glow { position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; pointer-events: none; opacity: 0; transition: opacity 0.6s; }
        .glow-low .expect-glow { opacity: 1; background: radial-gradient(circle at center, rgba(52,211,153,0.08) 0%, transparent 50%); }
        .glow-mid .expect-glow { opacity: 1; background: radial-gradient(circle at center, rgba(96,165,250,0.08) 0%, transparent 50%); }
        .glow-high .expect-glow { opacity: 1; background: radial-gradient(circle at center, rgba(248,113,113,0.08) 0%, transparent 50%); }

        .action-row { display: flex; gap: 8px; }
        .spin-btn {
            flex: 1; padding: 14px; border-radius: 14px;
            border: none; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 1.05rem;
            letter-spacing: 0.06em;
            background: linear-gradient(135deg, var(--accent) 0%, #a8862e 60%, #8a6d22 100%);
            color: #0a0c10;
            box-shadow: 0 4px 24px rgba(200,162,61,0.2), inset 0 1px 0 rgba(255,255,255,0.2);
            transition: all 0.25s; position: relative; overflow: hidden;
        }
        .spin-btn::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.18) 0%, transparent 45%);
            pointer-events: none;
        }
        .spin-btn:disabled { opacity: 0.2; cursor: not-allowed; box-shadow: none; }
        .spin-btn:not(:disabled):hover { box-shadow: 0 6px 32px rgba(200,162,61,0.35), inset 0 1px 0 rgba(255,255,255,0.2); transform: translateY(-2px); }
        .spin-btn:not(:disabled):active { transform: scale(0.97); box-shadow: 0 2px 12px rgba(200,162,61,0.2); }
        .cashout-btn {
            padding: 14px 18px; border-radius: 14px;
            border: 1px solid rgba(52,211,153,0.15);
            cursor: pointer; background: var(--green-dim);
            color: var(--green);
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 0.75rem;
            transition: all 0.2s; flex-shrink: 0;
            position: relative; overflow: hidden;
            text-align: center;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .cashout-btn::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.04) 0%, transparent 50%);
            pointer-events: none;
        }
        .cashout-btn:hover { background: rgba(52,211,153,0.15); border-color: rgba(52,211,153,0.25); }
        .cashout-btn:active { transform: scale(0.94); }

        .foot-bar {
            height: 36px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px;
            border-top: 1px solid var(--edge);
            background: var(--s1);
        }
        .foot-bar span {
            font-size: 0.55rem; color: var(--text-4);
            font-weight: 500; letter-spacing: 0.03em;
            display: flex; align-items: center; gap: 5px;
        }
        .foot-bar button {
            font-size: 0.55rem; color: var(--text-3);
            background: none; border: none; cursor: pointer;
            font-weight: 500; transition: color 0.2s;
        }
        .foot-bar button:hover { color: var(--text-1); }

        .modal-bg {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(12px);
            display: none; justify-content: center; align-items: center;
            z-index: 100000;
        }
        .modal-bg.open { display: flex; animation: mBgIn 0.3s ease forwards; }
        @keyframes mBgIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-box {
            background: var(--s1);
            border: 1px solid var(--edge-light);
            border-radius: 18px; width: 90%; max-width: 400px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5), 0 0 0 1px var(--edge);
            animation: mBoxIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        @keyframes mBoxIn { from { opacity: 0; transform: scale(0.92) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .modal-head { padding: 20px 20px 0; display: flex; justify-content: space-between; align-items: center; }
        .modal-title { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1.05rem; }
        .modal-x {
            width: 32px; height: 32px; border-radius: 9px;
            border: 1px solid var(--edge); background: var(--s2);
            color: var(--text-3); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; transition: all 0.2s;
        }
        .modal-x:hover { color: var(--red); background: var(--red-dim); border-color: rgba(248,113,113,0.15); }
        .modal-body { padding: 16px 20px; }
        .modal-foot { padding: 0 20px 20px; }
        .m-input {
            width: 100%; padding: 12px 16px;
            background: var(--s2); border: 1px solid var(--edge-light);
            border-radius: 12px; color: var(--text-1); outline: none;
            font-family: 'Inter', sans-serif; font-size: 0.9rem;
            transition: all 0.2s;
        }
        .m-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-dim); }
        .m-input::placeholder { color: var(--text-4); }
        .m-btn {
            width: 100%; padding: 12px; border-radius: 12px;
            border: none; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 0.9rem;
            transition: all 0.2s; position: relative; overflow: hidden;
        }
        .m-btn::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .m-btn:active { transform: scale(0.97); }
        .m-btn.gold {
            background: linear-gradient(135deg, var(--accent), #a8862e);
            color: #0a0c10; box-shadow: 0 4px 16px rgba(200,162,61,0.2);
        }
        .m-btn.gold:hover { box-shadow: 0 6px 24px rgba(200,162,61,0.3); }
        .m-btn.danger { background: var(--red-dim); color: var(--red); border: 1px solid rgba(248,113,113,0.15); }

        #toast-container {
            position: fixed; top: 64px; right: 12px;
            z-index: 400000; display: flex; flex-direction: column;
            gap: 8px; max-width: 340px; width: calc(100% - 24px);
            pointer-events: none;
        }
        #toast-container > * { pointer-events: auto; }
        .toast {
            background: var(--s1); border: 1px solid var(--edge-light);
            border-radius: 14px; display: flex; overflow: hidden;
            animation: tIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
            box-shadow: 0 12px 40px rgba(0,0,0,0.4); position: relative;
        }
        .toast.out { animation: tOut 0.3s ease forwards; }
        .t-bar { width: 3px; flex-shrink: 0; }
        .t-bar.success { background: var(--green); }
        .t-bar.error { background: var(--red); }
        .t-bar.warning { background: var(--orange); }
        .t-bar.info { background: var(--blue); }
        .t-body { flex: 1; padding: 12px; display: flex; gap: 10px; align-items: flex-start; min-width: 0; }
        .t-icon {
            width: 30px; height: 30px; border-radius: 8px;
            flex-shrink: 0; display: flex; align-items: center;
            justify-content: center; font-size: 0.75rem;
        }
        .t-icon.success { background: var(--green-dim); color: var(--green); }
        .t-icon.error { background: var(--red-dim); color: var(--red); }
        .t-icon.warning { background: var(--orange-dim); color: var(--orange); }
        .t-icon.info { background: var(--blue-dim); color: var(--blue); }
        .t-content { flex: 1; min-width: 0; }
        .t-title { font-weight: 700; font-size: 0.75rem; color: var(--text-1); }
        .t-msg { font-size: 0.68rem; color: var(--text-2); line-height: 1.4; margin-top: 2px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .t-close { position: absolute; top: 8px; right: 8px; background: none; border: none; color: var(--text-4); cursor: pointer; font-size: 0.65rem; opacity: 0; transition: opacity 0.15s; padding: 4px; }
        .toast:hover .t-close { opacity: 1; }
        .t-prog { position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: var(--edge); overflow: hidden; }
        .t-prog-fill { height: 100%; transform-origin: left; }
        .t-prog-fill.success { background: var(--green); }
        .t-prog-fill.error { background: var(--red); }
        .t-prog-fill.warning { background: var(--orange); }
        .t-prog-fill.info { background: var(--blue); }
        @keyframes tIn { from { opacity: 0; transform: translateX(80px) scale(0.9); } to { opacity: 1; transform: translateX(0) scale(1); } }
        @keyframes tOut { to { opacity: 0; transform: translateX(80px) scale(0.9); } }

        #dialog-bg {
            position: fixed; inset: 0; z-index: 500000;
            background: rgba(0,0,0,0.65); backdrop-filter: blur(10px);
            display: none; justify-content: center; align-items: center; padding: 20px;
        }
        #dialog-bg.open { display: flex; animation: mBgIn 0.3s ease forwards; }
        .dlg-card {
            background: var(--s1); border: 1px solid var(--edge-light);
            border-radius: 18px; max-width: 380px; width: 100%;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5);
            animation: mBoxIn 0.4s cubic-bezier(0.16,1,0.3,1) forwards;
        }
        .dlg-head { padding: 28px 24px 0; text-align: center; }
        .dlg-icon { width: 52px; height: 52px; border-radius: 14px; margin: 0 auto 14px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .dlg-title { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 1rem; color: var(--text-1); margin-bottom: 6px; }
        .dlg-msg { font-size: 0.82rem; color: var(--text-2); line-height: 1.5; }
        .dlg-actions { padding: 18px 24px 24px; display: flex; gap: 10px; }
        .dlg-btn {
            flex: 1; padding: 11px; border-radius: 12px; border: none;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700; font-size: 0.82rem; cursor: pointer;
            transition: all 0.2s; position: relative; overflow: hidden;
        }
        .dlg-btn::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, transparent 50%); pointer-events: none; }
        .dlg-btn:active { transform: scale(0.96); }
        .dlg-btn.cancel { background: var(--s3); color: var(--text-2); }
        .dlg-btn.confirm-gold { background: linear-gradient(135deg, var(--accent), #a8862e); color: #0a0c10; box-shadow: 0 4px 16px rgba(200,162,61,0.2); }
        .dlg-btn.confirm-red { background: var(--red-dim); color: var(--red); border: 1px solid rgba(248,113,113,0.15); }
        .dlg-divider { height: 1px; background: var(--edge); margin: 14px 24px 0; }

        #hist-panel {
            position: fixed; top: 0; right: 0; bottom: 0;
            width: 320px; max-width: 90vw;
            background: var(--s1); border-left: 1px solid var(--edge);
            z-index: 200000; transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
            display: flex; flex-direction: column;
            box-shadow: -16px 0 48px rgba(0,0,0,0.3);
        }
        #hist-panel.open { transform: translateX(0); }
        #hist-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 199999; display: none; backdrop-filter: blur(4px); }
        #hist-overlay.open { display: block; animation: mBgIn 0.3s ease forwards; }
        .hist-head { padding: 16px; border-bottom: 1px solid var(--edge); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .hist-head h3 { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
        .hist-head h3 i { color: var(--text-4); }
        .hist-list { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 6px; }
        .hist-item {
            background: var(--s2); border: 1px solid var(--edge);
            border-radius: 12px; padding: 12px;
            display: flex; align-items: center; gap: 10px;
            opacity: 0; animation: hIn 0.35s ease forwards;
        }
        @keyframes hIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        .hist-num { width: 28px; height: 28px; border-radius: 7px; background: var(--s3); display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: 700; color: var(--text-4); flex-shrink: 0; }
        .hist-info { flex: 1; min-width: 0; }
        .hist-bet { font-size: 0.7rem; font-weight: 600; color: var(--text-1); }
        .hist-detail { font-size: 0.6rem; color: var(--text-4); margin-top: 1px; }
        .hist-profit { font-family: 'Space Grotesk', sans-serif; font-size: 0.75rem; font-weight: 700; text-align: right; flex-shrink: 0; }
        .hist-profit.win { color: var(--green); }
        .hist-profit.lose { color: var(--red); }
        .hist-empty { text-align: center; padding: 40px 16px; color: var(--text-4); font-size: 0.8rem; }
        .hist-empty i { font-size: 1.8rem; margin-bottom: 10px; display: block; opacity: 0.4; }

        #bg-particles, #confetti-canvas, #petal-canvas {
            position: fixed; top: 0; left: 0;
            width: 100vw; height: 100vh;
            pointer-events: none; contain: strict;
        }
        #bg-particles { z-index: 0; }
        #confetti-canvas { z-index: 150000; display: none; }
        #petal-canvas { z-index: 149000; display: none; }
        .mouse-glow {
            position: fixed; width: 220px; height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200,162,61,0.025) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
            transform: translate(-50%,-50%); will-change: transform;
        }
        #result-display { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: 130000; pointer-events: none; font-family: 'Space Grotesk', sans-serif; font-size: 1.3rem; font-weight: 700; text-shadow: 0 4px 20px rgba(0,0,0,0.8); }

        #screen-flash { position: fixed; inset: 0; z-index: 350000; pointer-events: none; opacity: 0; }
        #screen-flash.flash-win { background: radial-gradient(ellipse at center, rgba(200,162,61,0.25) 0%, transparent 70%); animation: flashIn 0.15s ease forwards, flashOut 0.8s 0.15s ease forwards; }
        #screen-flash.flash-lose { background: radial-gradient(ellipse at center, rgba(248,113,113,0.18) 0%, transparent 70%); animation: flashIn 0.15s ease forwards, flashOut 0.6s 0.15s ease forwards; }
        @keyframes flashIn { to { opacity: 1; } }
        @keyframes flashOut { to { opacity: 0; } }

        .bal-val.pulse-green { animation: balPulseG 0.6s ease; }
        .bal-val.pulse-red { animation: balPulseR 0.6s ease; }
        @keyframes balPulseG { 0%,100% { transform: scale(1); } 40% { transform: scale(1.15); filter: drop-shadow(0 0 14px rgba(52,211,153,0.7)); } }
        @keyframes balPulseR { 0%,100% { transform: scale(1); } 40% { transform: scale(1.1); filter: drop-shadow(0 0 12px rgba(248,113,113,0.6)); } }

        .shake-it { animation: shakeHard 0.5s ease; }
        @keyframes shakeHard { 0%,100%{transform:translateX(0)} 10%,50%,90%{transform:translateX(-5px)} 30%,70%{transform:translateX(5px)} }

        #win-overlay-text {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%,-50%) scale(0);
            z-index: 360000; pointer-events: none;
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            opacity: 0;
        }
        #win-overlay-text.show { animation: winTextIn 0.5s 0.1s cubic-bezier(0.16,1,0.3,1) forwards; }
        #win-overlay-text.hide { animation: winTextOut 0.3s ease forwards; }
        #win-overlay-text .win-big {
            font-size: clamp(2rem,8vw,4.5rem);
            font-weight: 700;
            color: var(--accent);
            text-shadow: 0 0 60px rgba(200,162,61,0.6), 0 0 120px rgba(200,162,61,0.3), 0 6px 30px rgba(0,0,0,0.9);
            line-height: 1.1;
        }
        #win-overlay-text .win-sub {
            font-size: 0.85rem;
            color: var(--text-2);
            letter-spacing: 0.3em;
            text-transform: uppercase;
            margin-top: 8px;
        }
        @keyframes winTextIn { 0% { opacity:0; transform: translate(-50%,-50%) scale(0.3); } 60% { transform: translate(-50%,-50%) scale(1.08); } 100% { opacity:1; transform: translate(-50%,-50%) scale(1); } }
        @keyframes winTextOut { to { opacity:0; transform: translate(-50%,-50%) scale(0.8); } }

        #lose-overlay-text {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%,-50%) scale(0);
            z-index: 360000; pointer-events: none;
            text-align: center;
            font-family: 'Space Grotesk', sans-serif;
            opacity: 0;
        }
        #lose-overlay-text.show { animation: loseTextIn 0.4s 0.05s cubic-bezier(0.16,1,0.3,1) forwards; }
        #lose-overlay-text.hide { animation: winTextOut 0.25s ease forwards; }
        #lose-overlay-text .lose-big {
            font-size: clamp(1.5rem,5vw,3rem);
            font-weight: 700;
            color: var(--red);
            text-shadow: 0 0 40px rgba(248,113,113,0.5), 0 4px 20px rgba(0,0,0,0.8);
        }
        @keyframes loseTextIn { 0% { opacity:0; transform: translate(-50%,-50%) scale(0.3); } 60% { transform: translate(-50%,-50%) scale(1.06); } 100% { opacity:1; transform: translate(-50%,-50%) scale(1); } }

        .confetti-piece {
            position: fixed;
            width: 8px; height: 8px;
            z-index: 355000;
            pointer-events: none;
            border-radius: 2px;
        }

        .custom-loader-overlay {
            width: 100%; height: 100%; background: var(--bg);
            position: fixed; z-index: 100000;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            transition: opacity 0.5s ease;
        }
        .custom-loader-overlay.hiding { opacity: 0; pointer-events: none; }
        .loader-text {
            font-family: 'Space Grotesk', sans-serif;
            color: var(--accent); font-size: 1.1rem;
            font-weight: 600; letter-spacing: 0.2em;
            text-transform: uppercase; margin-bottom: 32px;
        }
        .loader-dots { display: flex; gap: 8px; }
        .loader-dots span {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--accent);
            animation: lDot 1.2s infinite ease-in-out both;
        }
        .loader-dots span:nth-child(2) { animation-delay: 0.15s; }
        .loader-dots span:nth-child(3) { animation-delay: 0.3s; }
        @keyframes lDot { 0%,80%,100% { transform: scale(0); } 40% { transform: scale(1); } }

        .toggle-track {
            width: 40px; height: 22px; border-radius: 11px;
            background: var(--s4); position: relative;
            cursor: pointer; transition: background 0.3s; flex-shrink: 0;
        }
        .toggle-track.on { background: var(--green); }
        .toggle-thumb {
            width: 16px; height: 16px; border-radius: 50%;
            background: white; position: absolute; top: 3px; left: 3px;
            transition: transform 0.3s; box-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }
        .toggle-track.on .toggle-thumb { transform: translateX(18px); }
        .settings-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 14px; border-radius: 12px;
            background: var(--s2); border: 1px solid var(--edge);
        }
        .settings-label {
            font-size: 0.8rem; font-weight: 500;
            color: var(--text-1); display: flex;
            align-items: center; gap: 10px;
        }
        .settings-label i { width: 18px; text-align: center; color: var(--text-4); }
        .login-msg-container { min-height: 0; max-height: 0; opacity: 0; overflow: hidden; transition: all 0.3s; }
        .login-msg-container.visible { min-height: auto; max-height: 50px; opacity: 1; padding: 8px 10px; }
        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: 12px;
            background: var(--red-dim); border: 1px solid rgba(248,113,113,0.15);
            color: var(--red); cursor: pointer; font-size: 0.8rem; font-weight: 600;
            transition: all 0.2s; font-family: 'Inter', sans-serif;
        }
        .logout-btn:hover { background: rgba(248,113,113,0.15); border-color: rgba(248,113,113,0.25); }

        /* ===== LIVE INDICATOR ===== */
        #live {
            position: absolute;
            top: 10px;
            left: 12px;
            z-index: 8;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 5px 14px 5px 10px;
            border-radius: 20px;
            background: var(--s2);
            border: 1px solid var(--edge);
            font-size: 0.62rem;
            font-weight: 600;
            color: var(--text-3);
            letter-spacing: 0.03em;
            overflow: hidden;
            white-space: nowrap;
            opacity: 0;
            transform: translateY(-8px) scale(0.92);
            animation: liveIn 0.5s 0.8s cubic-bezier(0.16,1,0.3,1) forwards;
            pointer-events: none;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        #live::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            flex-shrink: 0;
            box-shadow: 0 0 6px var(--green);
            animation: livePulse 1.8s ease-in-out infinite;
        }
        #live::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            border: 1px solid transparent;
            background: linear-gradient(135deg, rgba(52,211,153,0.12), transparent 60%) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            animation: liveBorderGlow 3s 1.5s ease-in-out infinite;
        }
        #live.authenticated {
            color: var(--green);
            border-color: rgba(52,211,153,0.12);
            background: linear-gradient(135deg, rgba(52,211,153,0.06), var(--s2));
        }
        #live.guest {
            color: var(--text-4);
        }
        @keyframes liveIn {
            0%   { opacity: 0; transform: translateY(-8px) scale(0.92); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.3; transform: scale(0.8); }
        }
        @keyframes liveBorderGlow {
            0%, 100% { opacity: 0; }
            50%      { opacity: 1; }
        }
        #live .live-text {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            border-right: 1.5px solid var(--text-4);
            animation: liveType 1.2s steps(20, end) forwards, liveBlink 0.7s step-end infinite;
            max-width: 0;
            vertical-align: middle;
        }
        #live.authenticated .live-text {
            border-right-color: var(--green);
        }
        @keyframes liveType {
            0%   { max-width: 0; }
            100% { max-width: 200px; }
        }
        @keyframes liveBlink {
            0%, 100% { border-right-color: transparent; }
            50%      { border-right-color: inherit; }
        }
        #live.swapping {
            animation: liveSwap 0.4s ease forwards;
        }
        @keyframes liveSwap {
            0%   { opacity: 1; transform: translateY(0) scale(1); }
            40%  { opacity: 0; transform: translateY(-6px) scale(0.95); }
            60%  { opacity: 0; transform: translateY(6px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        #live .live-text.done {
            border-right-color: transparent;
            animation: none;
        }

        @media (max-width: 767px) {
            .main-area { grid-template-columns: 1fr; grid-template-rows: 1fr auto; }
            .wheel-panel { border-right: none; padding: 8px 12px; background: none; }
            .wheel-panel::after { display: none; }
            .wheel-frame { max-width: 48vw; max-height: 48vw; }
            .ctrl-panel { padding: 12px 14px; gap: 10px; }
            .top-bar { height: 50px; padding: 0 12px; }
            .logo { font-size: 1rem; }
            .bal-val { font-size: 0.85rem; }
            .foot-bar { padding: 0 12px; }
            .status-pill { display: none; }
            #live { top: 8px; left: 8px; font-size: 0.58rem; padding: 4px 10px 4px 8px; }
        }
        @media (max-width: 400px) {
            .wheel-frame { max-width: 42vw; max-height: 42vw; }
            .chip { padding: 4px 10px; font-size: 0.6rem; }
            .cat-btn { padding: 10px 4px; font-size: 0.7rem; }
            .spin-btn { font-size: 0.9rem; padding: 12px; }
            .cashout-btn { padding: 12px 14px; font-size: 0.65rem; }
            .expect-card { padding: 12px 14px; }
            .expect-val { font-size: 1rem; }
        }
        @media (max-height: 500px) and (orientation: landscape) {
            .main-area { grid-template-columns: 1fr 2fr; }
            .wheel-frame { max-width: 36vh; max-height: 36vh; }
            .ctrl-panel { flex-direction: row; flex-wrap: wrap; gap: 8px; align-items: center; }
            .ctrl-panel > div { width: auto !important; }
            .ctrl-panel .cat-grid { width: 100%; }
            .ctrl-panel .action-row { width: 100%; }
        }
    </style>
</head>
<body>
    
    <div id='take_cash' style='display:none;'></div>

    <canvas id="bg-particles"></canvas>
    <canvas id="confetti-canvas"></canvas>
    <canvas id="petal-canvas"></canvas>
    <div class="mouse-glow" id="mouse-glow"></div>
    <div class="custom-loader-overlay" id="loader">
        <div class="loader-text">Loading</div>
        <div class="loader-dots"><span></span><span></span><span></span></div>
    </div>
    <div id="result-display"></div>
    <div id="screen-flash"></div>
    <div id="win-overlay-text"><div class="win-big" id="win-big-text"></div><div class="win-sub">Winner</div></div>
    <div id="lose-overlay-text"><div class="lose-big" id="lose-big-text"></div></div>
    <div id="toast-container"></div>

    <div id="dialog-bg">
        <div class="dlg-card" id="dlg-card">
            <div class="dlg-head" id="dlg-head"></div>
            <div class="dlg-divider" id="dlg-divider" style="display:none;"></div>
            <div class="dlg-actions" id="dlg-actions"></div>
        </div>
    </div>

    <div id="hist-overlay"></div>
    <div id="hist-panel">
        <div class="hist-head">
            <h3><i class="fas fa-clock-rotate-left"></i> History</h3>
            <button class="modal-x" style="width:28px;height:28px;font-size:0.8rem;" id="hist-close-btn">&#10005;</button>
        </div>
        <div class="hist-list" id="hist-list">
            <div class="hist-empty"><i class="fas fa-dice"></i>No spins yet.</div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div class="modal-bg" id="deposit_card">
        <div class="modal-box">
            <div class="modal-head">
                <span class="modal-title" style="color:var(--green);display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-down" style="font-size:0.8rem;"></i> Deposit Funds
                </span>
                <button class="modal-x" id="deposit-close-btn">&#10005;</button>
            </div>
            <div class="modal-body">
                <div style="position:relative;">
                    <input type="number" placeholder="Enter amount" min="1" max="700000" value="50" id="deposit_amount" class="m-input" style="padding-right:52px;">
                    <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:0.65rem;color:var(--text-4);font-weight:600;">MWK</span>
                </div>
            </div>
            <div class="modal-foot">
                <button class="m-btn gold" id="deposit-submit-btn">
                    <i class="fas fa-bolt" style="margin-right:6px;"></i>Deposit Now
                </button>
            </div>
        </div>
    </div>

    <!-- Cashout Modal -->
    <div class="modal-bg" id="cashout_card">
        <div class="modal-box">
            <div class="modal-head">
                <span class="modal-title" style="color:var(--red);display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-arrow-up-from-bracket" style="font-size:0.8rem;"></i> Withdraw
                </span>
                <button class="modal-x" id="cashout-close-btn">&#10005;</button>
            </div>
            <div class="modal-body">
                <div style="position:relative;">
                    <input type="tel" placeholder="Amount to withdraw" min="50" max="700000" value="50" id="cashout_amount" class="m-input" style="padding-right:52px;">
                    <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:0.65rem;color:var(--text-4);font-weight:600;">MWK</span>
                </div>
            </div>
            <div class="modal-foot">
                <button class="m-btn danger" id="cashout-submit-btn">
                    <i class="fas fa-paper-plane" style="margin-right:6px;"></i>Withdraw
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div class="modal-bg" id="game_modal">
        <div class="modal-box">
            <div class="modal-head">
                <span class="modal-title" style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-sliders-h" style="font-size:0.85rem;color:var(--text-3);"></i> Settings
                </span>
                <button class="modal-x" id="settings-close-btn">&#10005;</button>
            </div>
            <div class="modal-body" style="display:flex;flex-direction:column;gap:8px;">
                <div class="settings-row">
                    <span class="settings-label"><i class="fas fa-music"></i> Music</span>
                    <div class="toggle-track" id="music-toggle-track"><div class="toggle-thumb"></div></div>
                </div>
                <div class="settings-row">
                    <span class="settings-label"><i class="fas fa-volume-up"></i> Sound FX</span>
                    <div class="toggle-track on" id="sfx-toggle"><div class="toggle-thumb"></div></div>
                </div>
                <div class="settings-row">
                    <span class="settings-label"><i class="fas fa-sun"></i> Light Mode</span>
                    <div class="toggle-track" id="theme-toggle"><div class="toggle-thumb"></div></div>
                </div>
                <div class="settings-row">
                    <span class="settings-label"><i class="fas fa-bolt"></i> Turbo</span>
                    <div class="toggle-track" id="turbo-toggle"><div class="toggle-thumb"></div></div>
                </div>
                <button class="logout-btn" id="logout-btn" title="Sign In">
                    <i class="fas fa-user"></i> <span id="logout-text">Login</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    <div class="modal-bg" id="terms_card">
        <div class="modal-box" style="max-height:80vh;display:flex;flex-direction:column;">
            <div class="modal-head">
                <span class="modal-title">Terms & Conditions</span>
                <button class="modal-x" id="terms-close-btn">&#10005;</button>
            </div>
            <div class="modal-body" style="overflow-y:auto;flex:1;font-size:0.82rem;color:var(--text-2);line-height:1.7;">
                <p>By using <b>Stack Spinner</b>, you agree to these terms. Results are determined by a Random Number Generator (RNG). All transactions are in <b>Malawian Kwacha (MWK)</b>. You must register with a valid phone number.</p>
            </div>
            <div class="modal-foot">
                <button class="m-btn" style="background:var(--s3);color:var(--text-2);" id="terms-ok-btn">Understood</button>
            </div>
        </div>
    </div>

    <!-- Login Overlay -->
    <div style="display:none;" id="start">
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.75);backdrop-filter:blur(14px);z-index:100010;display:flex;align-items:center;justify-content:center;">
            <div class="modal-box" style="max-width:360px;">
                <div class="modal-head" style="justify-content:flex-end;padding-bottom:0;">
                    <button class="modal-x" id="login-close-btn">&#10005;</button>
                </div>
                <div style="text-align:center;padding:0 24px 16px;">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--accent-dim),rgba(200,162,61,0.05));border:1px solid rgba(200,162,61,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="fas fa-coins" style="color:var(--accent);font-size:1.1rem;"></i>
                    </div>
                    <div style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.35rem;color:var(--accent);">Access Stack</div>
                    <div style="font-size:0.75rem;color:var(--text-4);margin-top:4px;">Sign in to play</div>
                </div>
                <div class="modal-body" style="display:flex;flex-direction:column;gap:10px;">
                    <div id="login-msg" class="login-msg-container"><span id="login-msg-text"></span></div>
                    <form id="user_login" style="display:flex;flex-direction:column;gap:10px;">
                        <input type="tel" name="phone" id="phone" placeholder="Phone e.g 0800000000" maxlength="10" class="m-input">
                        <input type="tel" name="pass" placeholder="Pin e.g 1234" maxlength="4" class="m-input">
                        <button type="submit" id="logic" style="cursor:pointer;" class="m-btn gold">
                            <span id="logic-text">Ready to play</span>
                        </button>
                    </form>
                    <div style="font-size:0.62rem;color:var(--text-4);text-align:center;">
                        By signing in, you agree to our <button id="terms-from-login-btn" style="color:var(--blue);background:none;border:none;cursor:pointer;font-size:0.62rem;">terms</button>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GAME SHELL -->
    <div class="shell" id="game-container">

        <div class="top-bar">
            <div class="logo">
                <div class="logo-mark"><i class="fas fa-layer-group"></i></div>
                <h1>Stack</h1>
            </div>
            <div class="top-center">
                <div class="status-pill" id="status-pill">
                    <span class="status-dot"></span>
                    <span id="status-text">Online</span>
                </div>
            </div>
            <div class="top-right">
                <button class="icon-btn deposit-btn" id="depo" style="display:none;" title="Deposit">
                    <i class="fas fa-arrow-down"></i>
                </button>
                <button class="icon-btn" id="settings-icon-btn" title="Settings">
                    <i class="fas fa-sliders-h"></i>
                </button>
                <div class="bal-block">
                    <div class="bal-label">Balance</div>
                    <div>
                        <span class="bal-val" id="bal">0.00</span>
                        <span class="bal-unit">MWK</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-area">
            <div id='live'></div>
            <div class="wheel-panel">
                <div class="wheel-frame" id="wheel-wrapper">
                    <div class="wheel-glow"></div>
                    <div class="wheel-ring-outer"></div>
                    <canvas id="wheelCanvas"></canvas>
                    <div class="wheel-ring-inner"></div>
                    <div class="pointer-wrap">
                        <svg width="30" height="30" viewBox="0 0 30 30" fill="none">
                            <path d="M15 30L0 0h30L15 30z" fill="#c8a23d"/>
                            <path d="M15 30L4 15l11-7 11 7z" fill="rgba(255,255,255,0.15)"/>
                        </svg>
                    </div>
                </div>
                <div class="result-dots" id="last-results-strip"></div>
            </div>

            <div class="ctrl-panel">
                <div>
                    <div class="section-label">Bet Amount</div>
                    <div class="bet-row">
                        <div class="bet-input-wrap">
                            <input type="tel" id="bet-amount" value="50" min="50" maxlength="7">
                            <span class="unit">MWK</span>
                        </div>
                        <button class="x2-btn" id="x2-btn">x2</button>
                    </div>
                </div>
                <div class="chip-row">
                    <button class="chip" data-chip-val="50">50</button>
                    <button class="chip" data-chip-val="100">100</button>
                    <button class="chip" data-chip-val="200">200</button>
                    <button class="chip" data-chip-val="500">500</button>
                    <button class="chip" data-chip-val="1000">1K</button>
                    <button class="chip" data-chip-val="5000">5K</button>
                </div>
                <div>
                    <div class="section-label">Category</div>
                    <div class="cat-grid">
                        <button id="cart1" data-category="low" data-payout="2.4" data-segindex="0" class="cat-btn low">LOW<span class="mult">2.4x</span></button>
                        <button id="cart2" data-category="mid" data-payout="3.6" data-segindex="1" class="cat-btn mid">MID<span class="mult">3.6x</span></button>
                        <button id="cart3" data-category="high" data-payout="7.2" data-segindex="2" class="cat-btn high">HIGH<span class="mult">7.2x</span></button>
                    </div>
                </div>
                <div>
                    <div class="section-label">Potential Win</div>
                    <div id="expect-card" class="expect-card">
                        <div class="expect-inner">
                            <span class="expect-label" id="expect-label">SELECT A CATEGORY</span>
                            <span class="expect-val" id="expect-val">--</span>
                            <span class="expect-mult" id="expect-mult">Choose below</span>
                        </div>
                        <div class="expect-glow"></div>
                    </div>
                </div>
                <div class="action-row">
                    <button id="spin-button" class="spin-btn" disabled>SPIN</button>
                    <button id="cashout-icon-btn2" class="cashout-btn">
                        <i class="fas fa-money-bill-transfer"></i> Cashout
                    </button>
                </div>
            </div>
        </div>

        <div class="foot-bar">
            <span><span class="status-dot" style="margin-right:5px;"></span>Crazy Stack Inc — Malawi</span>
            <button id="terms-footer-btn">Terms & Conditions</button>
        </div>

        <audio id="def_aud" preload="auto" loop><source src="aud/bg_music.m4a" type="audio/mp4"></audio>
        <audio id="instant-audio" preload="auto"><source src="aud/play2.m4a" type="audio/mp4"></audio>
        <audio id="instant-audio2" preload="auto"><source src="aud/win.m4a" type="audio/mp4"></audio>
        <audio id="instant-audio3" preload="auto"><source src="aud/lose.m4a" type="audio/mp4"></audio>
    </div>

    <script src="https://app.malipo.mw/sdk/v1-malipo-hosted-checkout.js" onerror="console.warn('Malipo SDK unavailable')"></script>
    <script src="main.js"></script>
</body>
</html>