// ============================================================
// STACK SPINNER — main.js (FULL)
// ============================================================

// --- AUTO UPDATE DB ---
setInterval(function () {
    var logged = sessionStorage.getItem('logged');
    if (logged) {
        fetch("timer.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ user: logged })
        }).catch(function () {});
    }
}, 1000);

// ============================================================
// CLICK SOUND ENGINE — Web Audio API
// ============================================================
var _audioCtx = null;

function ensureAudioCtx() {
    if (!_audioCtx) {
        try { _audioCtx = new (window.AudioContext || window.webkitAudioContext)(); }
        catch (e) { return null; }
    }
    if (_audioCtx.state === 'suspended') {
        _audioCtx.resume().catch(function(){});
    }
    return _audioCtx;
}

function playClickSound() {
    var ctx = ensureAudioCtx();
    if (!ctx) return;
    try {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1800, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.06);
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.07);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.07);
    } catch (e) {}
}

function playSuccessChime() {
    var ctx = ensureAudioCtx();
    if (!ctx) return;
    try {
        [0, 0.1, 0.2].forEach(function (delay, i) {
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sine';
            osc.frequency.value = [1000, 1300, 1600][i];
            gain.gain.setValueAtTime(0, ctx.currentTime + delay);
            gain.gain.linearRampToValueAtTime(0.1, ctx.currentTime + delay + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + 0.15);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.15);
        });
    } catch (e) {}
}

function playErrorBuzz() {
    var ctx = ensureAudioCtx();
    if (!ctx) return;
    try {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'square';
        osc.frequency.value = 200;
        gain.gain.setValueAtTime(0.08, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.15);
    } catch (e) {}
}

function attachClickSounds() {
    document.querySelectorAll('button, .chip, .cat-btn, .x2-btn, .icon-btn, .logout-btn, .modal-x, .m-btn, .toggle-track, .expect-card').forEach(function (el) {
        if (el._clickSoundAttached) return;
        el._clickSoundAttached = true;
        el.addEventListener('pointerdown', function (e) {
            if (this.disabled || this.style.pointerEvents === 'none') return;
            playClickSound();
        }, { passive: true });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attachClickSounds);
} else {
    attachClickSounds();
}
var _soundObserver = new MutationObserver(function () { attachClickSounds(); });
_soundObserver.observe(document.documentElement, { childList: true, subtree: true });

// ============================================================
// VISUAL FX HELPERS
// ============================================================
function flashScreen(type) {
    var el = document.getElementById('screen-flash');
    if (!el) return;
    el.className = '';
    void el.offsetWidth;
    el.className = type === 'win' ? 'flash-win' : 'flash-lose';
    setTimeout(function () { el.className = ''; }, 1200);
}

function pulseBalance(type) {
    var el = document.getElementById('bal');
    if (!el) return;
    el.classList.remove('pulse-green', 'pulse-red');
    void el.offsetWidth;
    el.classList.add(type === 'win' ? 'pulse-green' : 'pulse-red');
    setTimeout(function () { el.classList.remove('pulse-green', 'pulse-red'); }, 700);
}

function shakeElement(selector) {
    var el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return;
    el.classList.remove('shake-it');
    void el.offsetWidth;
    el.classList.add('shake-it');
    setTimeout(function () { el.classList.remove('shake-it'); }, 600);
}

function showWinText(text) {
    var el = document.getElementById('win-overlay-text');
    var big = document.getElementById('win-big-text');
    if (!el || !big) return;
    big.textContent = text;
    el.className = '';
    void el.offsetWidth;
    el.className = 'show';
    setTimeout(function () {
        el.className = 'hide';
        setTimeout(function () { el.className = ''; }, 400);
    }, 2500);
}

function showLoseText(text) {
    var el = document.getElementById('lose-overlay-text');
    var big = document.getElementById('lose-big-text');
    if (!el || !big) return;
    big.textContent = text;
    el.className = '';
    void el.offsetWidth;
    el.className = 'show';
    setTimeout(function () {
        el.className = 'hide';
        setTimeout(function () { el.className = ''; }, 350);
    }, 1800);
}

function spawnConfetti(count) {
    var colors = ['#c8a23d', '#e0c36a', '#34d399', '#60a5fa', '#f87171', '#fb923c', '#a78bfa', '#fbbf24'];
    for (var i = 0; i < (count || 40); i++) {
        var piece = document.createElement('div');
        piece.className = 'confetti-piece';
        piece.style.left = (Math.random() * 100) + 'vw';
        piece.style.top = '-10px';
        piece.style.background = colors[Math.floor(Math.random() * colors.length)];
        piece.style.width = (4 + Math.random() * 8) + 'px';
        piece.style.height = (4 + Math.random() * 8) + 'px';
        piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
        piece.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';

        var xDrift = (Math.random() - 0.5) * 200;
        var duration = 1500 + Math.random() * 2000;
        var delay = Math.random() * 400;

        piece.style.animation = 'none';
        document.body.appendChild(piece);

        (function (el, xD, dur, del) {
            setTimeout(function () {
                var startTime = null;
                function fall(ts) {
                    if (!startTime) startTime = ts;
                    var p = (ts - startTime) / dur;
                    if (p >= 1) { if (el.parentNode) el.parentNode.removeChild(el); return; }
                    var y = p * (window.innerHeight + 50);
                    var x = Math.sin(p * 6) * xD * p;
                    var rot = p * 720;
                    var opacity = p < 0.8 ? 1 : 1 - (p - 0.8) / 0.2;
                    el.style.transform = 'translate(' + x + 'px, ' + y + 'px) rotate(' + rot + 'deg)';
                    el.style.opacity = opacity;
                    requestAnimationFrame(fall);
                }
                requestAnimationFrame(fall);
            }, del);
        })(piece, xDrift, duration, delay);
    }
}

// ============================================================
// TOAST NOTIFICATION SYSTEM
// ============================================================
var notifyIcons = { success: 'fa-check', error: 'fa-xmark', warning: 'fa-exclamation', info: 'fa-info' };
var notifyDurations = { success: 4500, error: 5500, warning: 4000, info: 3000 };

function notify(type, title, msg, duration) {
    var container = document.getElementById('toast-container');
    if (!container) return;

    if (type === 'success') playSuccessChime();
    else if (type === 'error') playErrorBuzz();

    var dur = duration || notifyDurations[type] || 4000;
    var icon = notifyIcons[type] || 'fa-info';

    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML =
        '<div class="t-bar ' + type + '"></div>' +
        '<div class="t-body">' +
        '<div class="t-icon ' + type + '"><i class="fas ' + icon + '"></i></div>' +
        '<div class="t-content">' +
        '<div class="t-title">' + (title || '') + '</div>' +
        '<div class="t-msg">' + (msg || '') + '</div>' +
        '</div></div>' +
        '<button class="t-close"><i class="fas fa-times"></i></button>' +
        '<div class="t-prog"><div class="t-prog-fill ' + type + '"></div></div>';

    container.appendChild(toast);

    var progFill = toast.querySelector('.t-prog-fill');
    void progFill.offsetWidth;
    progFill.style.transition = 'transform ' + dur + 'ms linear';
    progFill.style.transform = 'scaleX(0)';

    toast.querySelector('.t-close').addEventListener('click', function () { dismissToast(toast); });
    var timer = setTimeout(function () { dismissToast(toast); }, dur);
    toast._timer = timer;
}

function dismissToast(toast) {
    if (toast._dismissed) return;
    toast._dismissed = true;
    clearTimeout(toast._timer);
    toast.classList.add('out');
    setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
}

// ============================================================
// BALANCE ANIMATION
// ============================================================
var balanceAnimFrame = null;

function formatBalance(val) {
    return val.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function setBalance(newVal, opts) {
    opts = opts || {};
    var el = document.getElementById('bal');
    if (!el) return;

    if (balanceAnimFrame) { cancelAnimationFrame(balanceAnimFrame); balanceAnimFrame = null; }

    var currentVal = parseFloat(el.textContent.replace(/,/g, '')) || 0;
    var targetVal = parseFloat(newVal) || 0;
    var delta = targetVal - currentVal;

    if (opts.showDelta !== false && Math.abs(delta) > 0.009) showBalanceDelta(delta);

    if (opts.animate !== false && Math.abs(delta) > 0.009) {
        var duration = opts.duration || 800;
        var startTime = null;

        applyBalanceGlow(el, delta);

        function step(ts) {
            if (!startTime) startTime = ts;
            var p = Math.min((ts - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            el.textContent = formatBalance(currentVal + delta * eased);
            if (p < 1) balanceAnimFrame = requestAnimationFrame(step);
            else { el.textContent = formatBalance(targetVal); balanceAnimFrame = null; }
        }
        balanceAnimFrame = requestAnimationFrame(step);
    } else {
        el.textContent = formatBalance(targetVal);
    }
}

function applyBalanceGlow(el, delta) {
    var c = delta >= 0 ? 'rgba(52,211,153,0.8)' : 'rgba(248,113,113,0.6)';
    var s = delta >= 0 ? '14px' : '10px';
    el.style.transition = 'filter 0s';
    el.style.filter = 'drop-shadow(0 0 ' + s + ' ' + c + ')';
    setTimeout(function () { el.style.transition = 'filter 1.5s ease-out'; el.style.filter = 'none'; }, 300);
}

function showBalanceDelta(delta) {
    var container = document.querySelector('.bal-block');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'bal-delta ' + (delta >= 0 ? 'up' : 'down');
    el.textContent = (delta >= 0 ? '+' : '') + formatBalance(delta);
    container.appendChild(el);
    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 1700);
}

// ============================================================
// EXPECTED WIN (SINGLE CARD)
// ============================================================
var expectMultipliers = { low: 2.4, mid: 3.6, high: 7.2 };
var expectColorMap = { low: '--green', mid: '--blue', high: '--red' };

function updateExpectedWin() {
    var betInput = document.getElementById('bet-amount');
    var cat = window._selectedCategory || null;
    var el = document.getElementById('expect-val');
    var labelEl = document.getElementById('expect-label');
    var multEl = document.getElementById('expect-mult');
    var card = document.getElementById('expect-card');
    if (!el || !card) return;

    card.classList.remove('glow-low', 'glow-mid', 'glow-high');
    el.classList.remove('pop');

    if (cat && expectMultipliers[cat]) {
        var betVal = parseFloat(betInput.value) || 0;
        var win = betVal * expectMultipliers[cat];
        var colorVar = expectColorMap[cat];
        el.textContent = 'MWK ' + formatBalance(win);
        el.style.color = 'var(' + colorVar + ')';
        labelEl.textContent = cat.toUpperCase() + ' WIN';
        labelEl.style.color = 'var(' + colorVar + ')';
        multEl.textContent = expectMultipliers[cat] + 'x multiplier';
        multEl.style.color = 'var(' + colorVar + ')';
        card.classList.add('glow-' + cat);
        void el.offsetWidth;
        el.classList.add('pop');
    } else {
        el.textContent = '--';
        el.style.color = 'var(--text-3)';
        labelEl.textContent = 'SELECT A CATEGORY';
        labelEl.style.color = 'var(--text-4)';
        multEl.textContent = 'Choose below';
        multEl.style.color = 'var(--text-4)';
    }
}

// ============================================================
// NETWORK STATUS
// ============================================================
window.addEventListener('online', function () { notify('success', 'Back Online', 'Internet connection restored'); });
window.addEventListener('offline', function () { notify('warning', 'No Internet', 'Check your connection'); });
if (!navigator.onLine) setTimeout(function () { notify('warning', 'No Internet', 'Check your connection'); }, 2500);

// ============================================================
// TOGGLES
// ============================================================
function toggleMusic() {
    var audio = document.getElementById('def_aud');
    var sm = document.getElementById('game_modal');
    var track = document.getElementById('music-toggle-track');
    var isOn = track.classList.toggle('on');
    if (isOn) { audio.volume = 0.5; audio.play().catch(function () {}); } else { audio.pause(); }
    sm.classList.remove('open'); sm.style.display = 'none';
    localStorage.setItem('audio', isOn ? 'playing' : 'paused');
}

function toggleTheme() {
    document.getElementById('theme-toggle').classList.toggle('on');
    document.documentElement.classList.toggle('light-mode');
    localStorage.setItem('theme', document.documentElement.classList.contains('light-mode') ? 'light' : 'dark');
}

if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.add('light-mode');
    var tt = document.getElementById('theme-toggle');
    if (tt) tt.classList.add('on');
}

// ============================================================
// CASHOUT
// ============================================================
async function cashout(zaka) {
    var takeBtn = document.getElementById('take_cash');
    if (takeBtn) { takeBtn.innerHTML = 'Wait...'; takeBtn.disabled = true; takeBtn.style.opacity = '0.2'; }

    var amount = parseFloat(zaka);
    var currentBalance = parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, ''));
    var logged = sessionStorage.getItem('logged');

    if (!logged) { notify('error', 'Not Logged In', 'Please sign in first.'); return; }
    if (isNaN(amount) || amount < 50) { notify('error', 'Invalid Amount', 'Minimum withdrawal is MWK 50'); return; }
    if (amount > currentBalance) { notify('error', 'Insufficient Balance', 'You have MWK ' + formatBalance(currentBalance)); return; }

    var newBalance = currentBalance - amount;
    var shortNumber = logged.slice(1);
    var trans_id = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
    var bankId = shortNumber.startsWith('8') ? 2 : 1;

    try {
        var mr = await fetch("zaka.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ bankId: bankId, wallet: '265' + shortNumber, amount: amount, merchantTrxId: trans_id }) });
        if (!mr.ok) throw new Error('HTTP ' + mr.status);
        var md = await mr.json();
        if (md.status !== 'Completed') { notify('error', 'Withdrawal Failed', 'Declined by provider.'); return; }

        var br = await fetch("bet.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ bet_user: logged, bet_amount: amount, bet_mode: 'withdraw' }) });
        if (!br.ok) throw new Error('HTTP ' + br.status);
        var bd = await br.json();

        if (bd.success) {
            setBalance(newBalance, { duration: 600 });
            pulseBalance('lose');
            notify('success', 'Cashout Sent', 'MWK ' + formatBalance(amount) + ' to your phone.');
        } else { notify('error', 'Update Failed', bd.message || 'Unknown'); }
    } catch (err) { notify('error', 'Cashout Error', err.message); }
    finally { if (takeBtn) { takeBtn.innerHTML = 'Withdraw Funds'; takeBtn.disabled = false; takeBtn.style.opacity = '1'; } }
}

// ============================================================
// DEPOSIT (Malipo)
// ============================================================
window.trans = function () {
    var topup = document.getElementById('deposit_amount').value;
    closeModal('deposit_card');
    var tx_Id = "bet_" + Date.now().toString(36).toUpperCase() + Math.random().toString(36).slice(2, 10).toUpperCase();
    var logged = sessionStorage.getItem('logged');
    if (!logged) { notify('error', 'Not Logged In', 'Sign in first.'); return; }

    window.Malipo.open({
        merchantAccount: '945454610', amount: topup, currency: "MWK", order_id: tx_Id, description: "bet deposit", customerPhone: logged,
        onSuccess: function () {
            notify('info', 'Processing', 'Payment received...');
            fetch("deposit.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ bet_user: logged, bet_amount: topup, bet_mode: 'deposit', tx_id: tx_Id }) })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success && data.new_balance !== undefined) {
                        var fb = parseFloat(data.new_balance);
                        var cb = parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, ''));
                        setBalance(fb, { duration: 1200 });
                        pulseBalance('win');
                        flashScreen('win');
                        spawnConfetti(25);
                        notify('success', 'Deposited!', '+MWK ' + formatBalance(fb - cb) + ' — Balance: MWK ' + formatBalance(fb));
                        var now = new Date(); var sn = logged.slice(1);
                        fetch("deposit_record.php", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ user: logged, deposit: topup, bank: sn.startsWith('8') ? 'TNM' : 'AIRTEL', tx_id: tx_Id, time: now.toTimeString().split(' ')[0], date: now.toISOString().split('T')[0] }) }).catch(function () {});
                    } else { notify('error', 'Failed', data.message || 'Server error.'); }
                }).catch(function () { notify('error', 'Network Error', 'Could not connect.'); });
        },
        onPending: function () { notify('warning', 'Pending', 'Check your phone to confirm.'); },
        onCancelled: function () { notify('error', 'Cancelled', 'Payment was cancelled.'); },
        onError: function () { notify('error', 'Failed', 'Unknown error.'); }
    });

    try {
        var mi = document.querySelector('.mlp_method-info');
        if (mi) mi.innerHTML = '<h1 style="font-size:30px;color:#003;font-weight:bold;">Mobile Payment</h1>';
        var lp = document.querySelector('.mlp_payment-wrapper .mlp_payment-left');
        if (lp) lp.remove();
        var wr = document.querySelector('.mlp_payment-wrapper');
        if (wr) wr.style.setProperty('width', '100%', 'important');
        var mn = logged.slice(1);
        var mpn = document.getElementById('mlp_mpamba-number');
        var an = document.getElementById('mlp_airtel-number');
        if (mn.startsWith('8') && mpn) mpn.value = mn;
        else if (an) an.value = mn;
    } catch (e) {}
};

// ============================================================
// MODAL HELPERS
// ============================================================
function openModal(id) { var el = document.getElementById(id); if (el) { el.classList.add('open'); el.style.display = 'flex'; } }
function closeModal(id) { var el = document.getElementById(id); if (el) { el.classList.remove('open'); el.style.display = 'none'; } }

document.getElementById('depo').addEventListener('click', function () { openModal('deposit_card'); });
document.getElementById('deposit-close-btn').addEventListener('click', function () { closeModal('deposit_card'); });
document.getElementById('deposit-submit-btn').addEventListener('click', function () { if (typeof window.trans === 'function') window.trans(); });

document.getElementById('cashout-icon-btn2').addEventListener('click', function () {
    if (!sessionStorage.getItem('logged')) { notify('error', 'Not Logged In', 'Sign in first.'); return; }
    openModal('cashout_card');
});
document.getElementById('cashout-close-btn').addEventListener('click', function () { closeModal('cashout_card'); });
document.getElementById('cashout-submit-btn').addEventListener('click', function () { closeModal('cashout_card'); cashout(document.getElementById('cashout_amount').value); });

document.getElementById('settings-icon-btn').addEventListener('click', function () { openModal('game_modal'); });
document.getElementById('settings-close-btn').addEventListener('click', function () { closeModal('game_modal'); });

document.getElementById('music-toggle-track').addEventListener('click', function () { toggleMusic(); });
document.getElementById('sfx-toggle').addEventListener('click', function () { this.classList.toggle('on'); localStorage.setItem('sfx', this.classList.contains('on') ? 'on' : 'off'); });
document.getElementById('theme-toggle').addEventListener('click', function () { toggleTheme(); });
document.getElementById('turbo-toggle').addEventListener('click', function () { this.classList.toggle('on'); localStorage.setItem('turbo', this.classList.contains('on') ? 'on' : 'off'); });

document.getElementById('logout-btn').addEventListener('click', function () {
    if (sessionStorage.getItem('logged')) { sessionStorage.setItem('logged', ''); location.reload(); }
    else { document.getElementById('start').style.display = 'block'; closeModal('game_modal'); }
});

document.getElementById('terms-footer-btn').addEventListener('click', function () { openModal('terms_card'); });
document.getElementById('terms-close-btn').addEventListener('click', function () { closeModal('terms_card'); });
document.getElementById('terms-ok-btn').addEventListener('click', function () { closeModal('terms_card'); });
document.getElementById('terms-from-login-btn').addEventListener('click', function () { openModal('terms_card'); });
document.getElementById('hist-close-btn').addEventListener('click', function () { document.getElementById('hist-panel').classList.remove('open'); document.getElementById('hist-overlay').classList.remove('open'); document.getElementById('hist-overlay').style.display = 'none'; });
document.getElementById('login-close-btn').addEventListener('click', function () { document.getElementById('start').style.display = 'none'; });
document.getElementById('user_login').addEventListener('submit', function (e) { e.preventDefault(); if (typeof window.log === 'function') window.log(e); });

document.querySelectorAll('.modal-bg').forEach(function (m) { m.addEventListener('click', function (e) { if (e.target === m) { m.classList.remove('open'); m.style.display = 'none'; } }); });

// ============================================================
// LIVE TEXT ANIMATION
// ============================================================
function animateLiveText(text, isAuthenticated) {
    var el = document.getElementById('live');
    if (!el) return;

    if (el.querySelector('.live-text')) {
        el.classList.remove('swapping');
        void el.offsetWidth;
        el.classList.add('swapping');
    }

    setTimeout(function () {
        el.classList.remove('authenticated', 'guest');
        el.classList.add(isAuthenticated ? 'authenticated' : 'guest');

        var span = document.createElement('span');
        span.className = 'live-text';
        span.textContent = text;
        el.innerHTML = '';
        el.appendChild(span);

        setTimeout(function () {
            span.classList.add('done');
        }, 1400);
    }, el.querySelector('.live-text') ? 200 : 0);
}

// ============================================================
// WHEEL GAME — IIFE
// ============================================================
(function () {

    var logged = sessionStorage.getItem('logged');
    var pox = document.getElementById('depo');
    if (logged) {
        pox.style.display = 'flex';
        animateLiveText(logged, true);
        fetchBalance(logged);
    } else {
        animateLiveText('Sign in to continue.', false);
        setTimeout(function () { document.getElementById('start').style.display = 'block'; }, 600);
    }

    var gameState = 'IDLE';
    window._selectedCategory = null;
    var $betInput = document.getElementById('bet-amount');
    var $spinButton = document.getElementById('spin-button');
    var $resultDisplay = document.getElementById('result-display');
    var $predictionButtons = document.querySelectorAll('.cat-btn[data-category]');

    var canvas = document.getElementById('wheelCanvas');
    var ctx = canvas.getContext('2d');
    var angle = 0, currentRotation = 0, FIXED_SPIN_ROTATIONS = 8, canvasLogicalSize = 0;

    var segments = [
        { label: "\u00d7 2.4", color: "#16a34a", category: 'low', textColor: "#fff" },
        { label: "\u00d7 2.4", color: "#16a34a", category: 'low', textColor: "#fff" },
        { label: "\u00d7 2.4", color: "#16a34a", category: 'low', textColor: "#fff" },
        { label: "Lose", color: "#6b7280", category: 'none', textColor: "#fff" },
        { label: "Lose", color: "#6b7280", category: 'none', textColor: "#fff" },
        { label: "\u00d7 3.6", color: "#2563eb", category: 'mid', textColor: "#fff" },
        { label: "\u00d7 3.6", color: "#2563eb", category: 'mid', textColor: "#fff" },
        { label: "\u00d7 7.2", color: "#dc2626", category: 'high', textColor: "#fff" }
    ];
    var segAngleRad = 2 * Math.PI / segments.length;
    var hoveredVisualIndex = null, selectedVisualIndex = null, isSpinning = false;
    var HUB = 0.15, TEXTD = 0.75, BC = '#1e293b', CHC = '#f1f5f9', CIHC = '#64748b', HC = '#f97316', SC = '#ef4444', SBW = 1.5;

    function na(a) { return ((a % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI); }

    function createVS(d) {
        if (!d.length) return [];
        var vs = [], c = { label: d[0].label, color: d[0].color, category: d[0].category, textColor: d[0].textColor, labels: [d[0]], startIdx: 0, vIndex: 0 };
        for (var i = 1; i < d.length; i++) {
            if (d[i].color === c.color) c.labels.push(d[i]);
            else { c.totalSlots = c.labels.length; vs.push(c); c = { label: d[i].label, color: d[i].color, category: d[i].category, textColor: d[i].textColor, labels: [d[i]], startIdx: i, vIndex: vs.length }; }
        }
        c.totalSlots = c.labels.length; vs.push(c); return vs;
    }
    var visualSegments = createVS(segments);

    function resizeCanvas() {
        var dpr = window.devicePixelRatio || 1, rect = canvas.getBoundingClientRect();
        canvasLogicalSize = rect.width;
        if (!rect.width || !rect.height) return;
        canvas.width = Math.round(rect.width * dpr); canvas.height = Math.round(rect.height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0); drawWheel(angle);
    }
    var rt = null;
    window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(resizeCanvas, 50); });

    function drawWheel(rot) {
        var w = canvasLogicalSize; if (!w) return;
        var cx = w / 2, cy = w / 2, R = Math.max(1, w / 2 - 10), hub = Math.max(1, R * HUB), tR = Math.max(1, R * TEXTD), a = rot - Math.PI / 2;
        ctx.clearRect(0, 0, w, w);
        for (var vi = 0; vi < visualSegments.length; vi++) {
            var vs = visualSegments[vi], vsa = vs.totalSlots * segAngleRad, s = a, e = s + vsa;
            ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, R, s, e); ctx.arc(cx, cy, hub, e, s, true); ctx.closePath(); ctx.fillStyle = vs.color; ctx.fill();
            if (vi === hoveredVisualIndex || vi === selectedVisualIndex) {
                var hc = vi === selectedVisualIndex ? SC : HC;
                ctx.save(); ctx.beginPath(); ctx.arc(cx, cy, R, s, e); ctx.arc(cx, cy, hub, e, s, true); ctx.clip();
                ctx.beginPath(); ctx.arc(cx, cy, R, s, e); ctx.strokeStyle = hc; ctx.lineWidth = 8; ctx.shadowColor = hc; ctx.shadowBlur = 15; ctx.stroke(); ctx.restore();
            }
            ctx.shadowBlur = 0; ctx.beginPath(); ctx.moveTo(cx + hub * Math.cos(s), cy + hub * Math.sin(s)); ctx.lineTo(cx + R * Math.cos(s), cy + R * Math.sin(s)); ctx.strokeStyle = BC; ctx.lineWidth = SBW; ctx.stroke();
            var la = s;
            for (var li = 0; li < vs.labels.length; li++) {
                ctx.save(); ctx.translate(cx, cy); ctx.rotate(la + segAngleRad / 2); ctx.textAlign = "right"; ctx.fillStyle = vs.labels[li].textColor || "#fff";
                ctx.font = 'bold ' + Math.max(8, R * 0.10) + "px 'Inter',sans-serif"; ctx.shadowColor = 'rgba(0,0,0,0.5)'; ctx.shadowBlur = 2;
                ctx.fillText(vs.labels[li].label.toUpperCase(), tR, 0); ctx.restore(); la += segAngleRad;
            }
            a = e;
        }
        ctx.shadowBlur = 0; ctx.beginPath(); ctx.arc(cx, cy, R, 0, 2 * Math.PI); ctx.strokeStyle = BC; ctx.lineWidth = 4; ctx.stroke();
        ctx.beginPath(); ctx.arc(cx, cy, hub, 0, 2 * Math.PI); ctx.fillStyle = CHC; ctx.shadowColor = 'rgba(0,0,0,0.5)'; ctx.shadowBlur = 15; ctx.fill(); ctx.shadowBlur = 0; ctx.strokeStyle = BC; ctx.lineWidth = 4; ctx.stroke();
        var ih = Math.max(1, hub * 0.6); ctx.beginPath(); ctx.arc(cx, cy, ih, 0, 2 * Math.PI); ctx.fillStyle = CIHC; ctx.fill(); ctx.strokeStyle = BC; ctx.lineWidth = 2; ctx.stroke();
    }

    function getVI(x, y) {
        var w = canvasLogicalSize; if (!w) return null;
        var cx = w / 2, cy = w / 2, R = Math.max(1, w / 2 - 10), hub = Math.max(1, R * HUB);
        var dx = x - cx, dy = y - cy, d = Math.sqrt(dx * dx + dy * dy);
        if (d > R || d < hub) return null;
        var pa = Math.atan2(dy, dx); if (pa < 0) pa += 2 * Math.PI;
        var oi = Math.floor(na(pa - (angle - Math.PI / 2)) / segAngleRad) % segments.length, cum = 0;
        for (var i = 0; i < visualSegments.length; i++) { cum += visualSegments[i].totalSlots; if (oi < cum) return i; }
        return null;
    }

    function handleSel(idx) {
        if (isSpinning) return;
        if (idx !== null && visualSegments[idx].category === 'none') { selectedVisualIndex = null; window._selectedCategory = null; selBet(null); drawWheel(angle); return; }
        if (idx !== null && idx !== -1) {
            if (idx === selectedVisualIndex) { selectedVisualIndex = null; window._selectedCategory = null; selBet(null); }
            else { selectedVisualIndex = idx; window._selectedCategory = visualSegments[idx].category; selBet(window._selectedCategory); }
        } else if (selectedVisualIndex !== null) { selectedVisualIndex = null; window._selectedCategory = null; selBet(null); }
        drawWheel(angle); updateSpinBtn();
    }

    function selBet(cat) {
        if (gameState !== 'IDLE') return;
        $predictionButtons.forEach(function (b) { b.classList.remove('selected'); });
        if (cat) {
            var b = document.querySelector('.cat-btn[data-category="' + cat + '"]');
            if (b) b.classList.add('selected');
            var vi = -1; for (var i = 0; i < visualSegments.length; i++) { if (visualSegments[i].category === cat) { vi = i; break; } }
            if (vi !== -1 && vi !== selectedVisualIndex) { selectedVisualIndex = vi; drawWheel(angle); }
        } else { selectedVisualIndex = null; drawWheel(angle); }
        updateSpinBtn(); updateExpectedWin();
    }

    function updateSpinBtn() { $spinButton.disabled = !(gameState === 'IDLE' && window._selectedCategory && sessionStorage.getItem('logged')); }

    canvas.addEventListener('mousemove', function (e) { if (isSpinning) return; var r = canvas.getBoundingClientRect(); var ni = getVI(e.clientX - r.left, e.clientY - r.top); var ai = (ni !== null && visualSegments[ni].category === 'none') ? null : ni; if (ai !== hoveredVisualIndex) { hoveredVisualIndex = ai; drawWheel(angle); canvas.style.cursor = ai !== null ? 'pointer' : 'default'; } });
    canvas.addEventListener('mouseleave', function () { if (isSpinning) return; if (hoveredVisualIndex !== null) { hoveredVisualIndex = null; drawWheel(angle); canvas.style.cursor = 'default'; } });
    canvas.addEventListener('click', function (e) { if (isSpinning) return; var r = canvas.getBoundingClientRect(); handleSel(getVI(e.clientX - r.left, e.clientY - r.top)); });
    canvas.addEventListener('touchstart', function (e) { if (isSpinning) return; var r = canvas.getBoundingClientRect(); var t = e.touches[0]; handleSel(getVI(t.clientX - r.left, t.clientY - r.top)); }, { passive: true });

    $predictionButtons.forEach(function (btn) { btn.addEventListener('click', function () { if (gameState !== 'IDLE') return; var cat = btn.getAttribute('data-category'); var vi = -1; for (var i = 0; i < visualSegments.length; i++) { if (visualSegments[i].category === cat) { vi = i; break; } } if (vi !== -1) handleSel(vi); }); });
    document.querySelectorAll('.chip[data-chip-val]').forEach(function (c) { c.addEventListener('click', function () { var v = parseFloat(this.getAttribute('data-chip-val')); if (!isNaN(v)) { $betInput.value = v; updateExpectedWin(); } }); });
    document.getElementById('x2-btn').addEventListener('click', function () { var c = parseFloat($betInput.value) || 0; c *= 2; if (c < 50) c = 50; if (c > 700000) c = 700000; $betInput.value = c; updateExpectedWin(); });
    $betInput.addEventListener('input', function () { updateExpectedWin(); });

    // ============================================================
    // SPIN
    // ============================================================
    async function spinWheel(betAmount) {
        if (isSpinning) return;
        isSpinning = true; $spinButton.disabled = true; selectedVisualIndex = null; hoveredVisualIndex = null; canvas.style.cursor = 'default';

        var rss;
        try { var r = await fetch('random.php'); var d = await r.text(); rss = parseInt(d, 10); if (isNaN(rss) || rss < 0 || rss >= segments.length) throw new Error('Invalid: ' + d); }
        catch (err) { notify('error', 'Spin Error', err.message); isSpinning = false; $spinButton.disabled = false; updateSpinBtn(); return; }

        var rs = segments[rss], wc = rs.category, wvs = null;
        for (var i = 0; i < visualSegments.length; i++) { if (visualSegments[i].category === wc) { wvs = visualSegments[i]; break; } }
        if (!wvs) { initGame(); return; }

        var si = wvs.startIdx + Math.floor(Math.random() * wvs.totalSlots);
        var ca = (si * segAngleRad) + (segAngleRad / 2);
        var tgt = na(2 * Math.PI - ca); currentRotation = angle;
        var cn = na(currentRotation), diff = tgt - cn; if (diff < 0) diff += 2 * Math.PI;
        var tot = FIXED_SPIN_ROTATIONS * 2 * Math.PI + diff + (Math.random() * 0.005 - 0.0025);
        var endR = currentRotation + tot, dur = 5000, st = null;
        document.getElementById('wheel-wrapper').classList.add('active');

        function anim(ts) {
            if (!st) st = ts; var p = Math.min((ts - st) / dur, 1), ease = p * (2 - p);
            angle = currentRotation + (endR - currentRotation) * ease; drawWheel(angle);
            if (p < 1) requestAnimationFrame(anim);
            else { isSpinning = false; document.getElementById('wheel-wrapper').classList.remove('active'); angle = endR; drawWheel(angle); checkResult(betAmount, rs); }
        }
        requestAnimationFrame(anim);
    }

    $spinButton.addEventListener('click', handleSpin);

    function handleSpin() {
        var cb = parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, ''));
        var lu = sessionStorage.getItem('logged');
        localStorage.setItem('FR', 0);
        setTimeout(function () { $spinButton.innerHTML = 'SPIN'; }, 6000);
        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (!lu) { notify('warning', 'Sign In', 'Please log in to play.'); document.getElementById('start').style.display = 'block'; return; }
        if (gameState !== 'IDLE') { notify('warning', 'Wait', 'Spin in progress.'); return; }
        if (!window._selectedCategory) { notify('error', 'No Category', 'Select LOW, MID, or HIGH.'); shakeElement('.cat-grid'); return; }

        var bv = parseFloat($betInput.value) || 0;
        if (isNaN(bv) || bv < 50) { notify('error', 'Invalid Bet', 'Minimum MWK 50.'); shakeElement('#bet-amount'); return; }
        if (cb < bv) { notify('error', 'Insufficient', 'You have MWK ' + formatBalance(cb)); shakeElement('.bal-block'); return; }

        $spinButton.disabled = true; $spinButton.textContent = 'SPINNING...'; $betInput.disabled = true;
        $predictionButtons.forEach(function (b) { b.disabled = true; });
        if ($resultDisplay) $resultDisplay.textContent = 'GOOD LUCK';
        gameState = 'SPINNING';

        var aud = document.getElementById('instant-audio'); if (aud) aud.play().catch(function () {});
        document.getElementById('def_aud').pause();

        setBalance(cb - bv, { duration: 400 });

        fetch('bet.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ bet_user: lu, bet_amount: bv.toFixed(2), bet_mode: 'withdraw' }) })
            .then(function (r) { return r.json(); })
            .then(function (d) { if (d.success) spinWheel(bv); else { setBalance(cb, { animate: false, showDelta: false }); notify('error', 'Bet Failed', d.message || 'Try again.'); initGame(); } })
            .catch(function () { setBalance(cb, { animate: false, showDelta: false }); notify('error', 'Network Error', 'Could not place bet.'); initGame(); });
    }

    // ============================================================
    // CHECK RESULT
    // ============================================================
    function checkResult(betAmount, resultSeg) {
        var wc = resultSeg.category, wl = resultSeg.label;
        if (!window._selectedCategory) { notify('error', 'Error', 'No category.'); initGame(); return; }

        var pd = document.querySelector('.cat-btn[data-category="' + window._selectedCategory + '"]');
        var payout = parseFloat(pd ? pd.getAttribute('data-payout') : 1.0);
        var lu = sessionStorage.getItem('logged'), tr = betAmount * payout, msc = localStorage.getItem('audio');
        var isW = (wc || '').toUpperCase().trim() === (window._selectedCategory || '').toUpperCase().trim();
        var now = new Date(), rd = { user: lu, stake: betAmount, won: tr, target_landed: wc, target_selected: window._selectedCategory, time: now.toTimeString().split(' ')[0], date: now.toISOString().split('T')[0], bet_user: lu, bet_amount: tr.toFixed(2), bet_mode: 'deposit' };

        if (isW) {
            var a2 = document.getElementById('instant-audio2'); if (a2) a2.play().catch(function () {});
            document.getElementById('instant-audio').pause();
            if (msc === 'playing') setTimeout(function () { document.getElementById('def_aud').play().catch(function () {}); }, 8000);
            if ($resultDisplay) $resultDisplay.innerHTML = '';

            flashScreen('win');
            spawnConfetti(60);
            showWinText('MWK ' + formatBalance(tr));
            pulseBalance('win');
            notify('success', 'Winner!', 'You won MWK ' + formatBalance(tr) + '!', 5500);

            fetch('win_record.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(rd) })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.success && d.new_balance) setBalance(parseFloat(d.new_balance), { duration: 1200 });
                    else if (d.success) creditWin(lu, tr, parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, '')));
                    else { notify('error', 'Record Failed', d.message || 'Error.'); creditWin(lu, tr, parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, ''))); }
                }).catch(function () { creditWin(lu, tr, parseFloat((document.getElementById('bal').textContent || '0').replace(/,/g, ''))); });
        } else {
            fetch('loss_record.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(rd) })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    var a3 = document.getElementById('instant-audio3'); if (a3) a3.play().catch(function () {});
                    document.getElementById('instant-audio').pause();
                    if (msc === 'playing') setTimeout(function () { document.getElementById('def_aud').play().catch(function () {}); }, 2000);
                    if ($resultDisplay) $resultDisplay.innerHTML = '';

                    flashScreen('lose');
                    showLoseText(wl.toUpperCase());
                    shakeElement('.wheel-frame');
                    pulseBalance('lose');
                    notify('warning', 'Lost', 'Landed on ' + wl.toUpperCase());
                }).catch(function () { notify('error', 'Error', 'Record failed.'); });
        }
        setTimeout(initGame, 2200);
    }

    function creditWin(u, amt, db) {
        fetch('bet.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ bet_user: u, bet_amount: amt.toFixed(2), bet_mode: 'deposit' }) })
            .then(function (r) { return r.json(); })
            .then(function (d) { setBalance(d.success && d.new_balance ? parseFloat(d.new_balance) : db + amt, { duration: 1200 }); })
            .catch(function () { setBalance(db + amt, { duration: 1200 }); });
    }

    function initGame() {
        gameState = 'IDLE'; window._selectedCategory = null; selectedVisualIndex = null;
        $spinButton.disabled = true; $spinButton.textContent = 'SPIN'; $spinButton.style.opacity = '';
        $betInput.disabled = false;
        if ($resultDisplay) $resultDisplay.textContent = '';
        $predictionButtons.forEach(function (b) { b.classList.remove('selected'); b.disabled = false; });
        drawWheel(angle); updateSpinBtn(); updateExpectedWin();
    }

    function initWheel() {
        resizeCanvas(); drawWheel(angle); initGame(); updateExpectedWin();
        var loader = document.getElementById('loader');
        if (loader) { loader.classList.add('hiding'); setTimeout(function () { loader.style.display = 'none'; }, 500); }
    }
    function tryInit() { if (canvas.getBoundingClientRect().width > 0) initWheel(); else setTimeout(tryInit, 50); }

    if (document.readyState === 'complete' || document.readyState === 'interactive') requestAnimationFrame(function () { requestAnimationFrame(tryInit); });
    else window.addEventListener('DOMContentLoaded', function () { requestAnimationFrame(function () { requestAnimationFrame(tryInit); }); });
    setTimeout(function () { var l = document.getElementById('loader'); if (l && l.style.display !== 'none' && !l.classList.contains('hiding')) initWheel(); }, 1500);
    window.addEventListener('load', function () { var l = document.getElementById('loader'); if (l && l.style.display !== 'none' && !l.classList.contains('hiding')) initWheel(); });
})();

// ============================================================
// LOGIN
// ============================================================
window.log = function (e) {
    if (e) e.preventDefault();
    var fd = new FormData(document.getElementById('user_login'));
    var phone = document.getElementById('phone').value;
    var btn = document.getElementById('logic');
    btn.innerHTML = 'Wait...'; btn.disabled = true; btn.style.opacity = '0.3'; btn.style.cursor = 'not-allowed';
    var sn = phone.slice(1);

    if (sn.startsWith('8') || sn.startsWith('9')) {
        fetch('login.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.innerHTML = 'Ready to play'; btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = '';
                if (data.success) {
                    sessionStorage.setItem('logged', phone);
                    animateLiveText(phone, true);
                    document.getElementById('start').style.display = 'none';
                    fetchBalance(phone);
                    document.getElementById('depo').style.display = 'flex';
                    var lt = document.getElementById('logout-text'); if (lt) lt.textContent = 'Logout';
                    flashScreen('win'); spawnConfetti(15);
                    notify('success', 'Welcome Back', 'Signed in as ' + phone);
                } else {
                    shakeElement('#user_login');
                    notify('error', 'Login Failed', data.message || 'Invalid credentials.');
                }
            })
            .catch(function () {
                btn.innerHTML = 'Ready to play'; btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = '';
                notify('error', 'Connection Error', 'Could not reach server.');
            });
    } else {
        btn.innerHTML = 'Ready to play'; btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = '';
        shakeElement('#phone');
        notify('error', 'Invalid Number', 'Use a TNM or AIRTEL number.');
    }
    return false;
};

function fetchBalance(user) {
    fetch('player.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ log_player: user }) })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                if (sessionStorage.getItem('logged')) { var lt = document.getElementById('logout-text'); if (lt) lt.textContent = 'Logout'; }
                setBalance(parseFloat(data.balance), { duration: 1800, showDelta: false });
                pulseBalance('win');
            } else { notify('error', 'Balance Error', data.message || 'Failed.'); setBalance(0, { animate: false, showDelta: false }); }
        })
        .catch(function () { notify('error', 'Network Error', 'Could not fetch balance.'); setBalance(0, { animate: false, showDelta: false }); });
}

setTimeout(function () { var l = document.getElementById('loader'); if (l && l.style.display !== 'none') { l.classList.add('hiding'); setTimeout(function () { l.style.display = 'none'; }, 500); } }, 8000);