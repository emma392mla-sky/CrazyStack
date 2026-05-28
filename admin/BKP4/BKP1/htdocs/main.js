// ============================================================
// STACK SPINNER — main.js (FIXED: wheel loads perfectly)
// ============================================================

// --- AUTO UPDATE DB ---
setInterval(() => {
    const logged = sessionStorage.getItem('logged');
    if (logged) {
        fetch("timer.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ user: logged })
        })
        .then(res => res.json())
        .catch(error => {
            console.error('Fetch error during DB update:', error);
        });
    }
}, 1000);

// --- AUTO CHECK NETWORK ---
const $Box = document.getElementById('message-box') || document.getElementById('messageBox');

function showStatusMessage(isOnline) {
    if (!$Box) return;
    if (isOnline) {
        $Box.className = "p-3 rounded-lg text-sm transition-opacity duration-300 bg-green-100 text-green-800";
        $Box.innerHTML = "Back online";
    } else {
        $Box.className = "p-3 rounded-lg text-sm transition-opacity duration-300 bg-orange-100 text-orange-800";
        $Box.innerHTML = "No Internet Connection";
    }
    $Box.style.display = "block";
    if (isOnline) {
        setTimeout(() => { $Box.style.display = "none"; }, 5000);
    }
}

window.addEventListener('online', () => { showStatusMessage(true); });
window.addEventListener('offline', () => { showStatusMessage(false); });
if (!navigator.onLine) { showStatusMessage(false); }

// --- TOGGLE MUSIC ---
function toggleMusic() {
    const audio = document.getElementById('def_aud');
    const aud = document.getElementById('game_modal');
    const track = document.getElementById('music-toggle-track');
    const isOn = track.classList.toggle('on');
    if (isOn) {
        audio.volume = 0.5;
        audio.play();
    } else {
        audio.pause();
    }
    aud.style.display = 'none';
    localStorage.setItem('audio', isOn ? 'playing' : 'paused');
}

// --- CASHOUT ---
async function cashout(zaka) {
    const takeBtn = document.getElementById('take_cash');
    if (takeBtn) {
        takeBtn.innerHTML = 'Wait a bit...';
        takeBtn.disabled = true;
        takeBtn.style.opacity = '20%';
    }

    const amount = parseFloat(zaka);

    const balanceElement = document.getElementById('bal');
    const currentBalance = parseFloat(balanceElement.innerHTML);
    const logged = sessionStorage.getItem('logged');

    if (!logged) {
        $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
        $Box.innerHTML = 'User not logged in or wallet number missing.';
        $Box.style.display = 'block';
        setTimeout(() => { $Box.style.display = 'none'; }, 5000);
        return;
    }

    const shortNumber = logged.slice(1);
    const fullWalletNumber = '265' + shortNumber;
    const trans_id = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
    const bankId = shortNumber.startsWith('8') ? 2 : 1;

    if (amount > currentBalance) {
        $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
        $Box.innerHTML = 'Insufficient balance';
        $Box.style.display = 'block';
        setTimeout(() => { $Box.style.display = 'none'; }, 5000);
        return;
    }

    const newBalance = currentBalance - amount;

    try {
        let malipoRes = await fetch("zaka.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ bankId, wallet: fullWalletNumber, amount, merchantTrxId: trans_id })
        });

        if (!malipoRes.ok) throw new Error(`HTTP error! Status: ${malipoRes.status}`);
        let malipoData = await malipoRes.json();

        if (malipoData.status !== 'Completed') {
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
            $Box.innerHTML = '❌ Withdrawal failed on server.';
            $Box.style.display = 'block';
            setTimeout(() => { $Box.style.display = 'none'; }, 5000);
            return;
        }

        let betRes = await fetch("bet.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ bet_user: logged, bet_amount: amount, bet_mode: 'withdraw' })
        });

        if (!betRes.ok) throw new Error(`HTTP error! Status: ${betRes.status} on bet.php`);
        let betData = await betRes.json();

        if (betData.success) {
            balanceElement.innerHTML = newBalance.toFixed(2);
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-green-900/50 text-green-300`;
            $Box.innerHTML = "🎉 Cashout successful! New Balance: MWK " + newBalance.toFixed(2);
        } else {
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
            $Box.innerHTML = "⚠️ WARNING: Malipo cashout succeeded, but balance update failed. Reason: " + (betData.message || "Unknown error.");
        }
        $Box.style.display = 'block';
        setTimeout(() => { $Box.style.display = 'none'; }, 5000);

    } catch (err) {
        console.error("Fetch/Processing Error:", err);
        $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
        $Box.innerHTML = "🚨 Critical Error: " + err.message;
        $Box.style.display = 'block';
        setTimeout(() => { $Box.style.display = 'none'; }, 5000);
    } finally {
        if (takeBtn) {
            takeBtn.innerHTML = 'Withdraw Funds';
            takeBtn.disabled = false;
            takeBtn.style.opacity = '100%';
        }
    }
}

// --- LOGIN CHECK & DEPOSIT BUTTON ---
if (sessionStorage.getItem('logged')) {
    document.getElementById('logout').innerHTML = 'Logout';
}

// --- DEPOSIT (Malipo) ---
window.trans = function() {
    const $Box = document.getElementById('message-box') || document.getElementById('messageBox');
    var topup = document.getElementById('deposit_amount').value;
    document.getElementById('deposit_card').style.display = 'none';
    var tx_Id = "bet_" + Date.now().toString(36).toUpperCase() + Math.random().toString(36).slice(2, 10).toUpperCase();
    var logged = sessionStorage.getItem('logged');

    $Box.style.display = 'none';
    $Box.innerHTML = '';

    const depositButton = document.querySelector('button[onclick="trans()"]');
    if (depositButton) depositButton.disabled = true;

    window.Malipo.open({
        merchantAccount: '945454610',
        amount: topup,
        currency: "MWK",
        order_id: tx_Id,
        description: "bet deposit",
        customerPhone: logged,

        onSuccess(result) {
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-green-900/50 text-green-300`;
            $Box.innerHTML = "Payment successful! Updating balance...";
            $Box.style.display = 'block';

            fetch("deposit.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ bet_user: logged, bet_amount: topup, bet_mode: 'deposit', tx_id: tx_Id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.new_balance !== undefined) {
                    const finalBalance = parseFloat(data.new_balance).toFixed(2);
                    document.getElementById('bal').innerHTML = finalBalance;
                    $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-green-900/50 text-green-300`;
                    $Box.innerHTML = `Deposit successful! New Balance: MWK ${finalBalance}`;

                    const now = new Date();
                    const formattedDate = now.toISOString().split("T")[0];
                    const formattedTime = now.toTimeString().split(" ")[0];
                    const User_No = logged.slice(1);
                    const bankId = User_No.startsWith('8') ? 'TNM' : 'AIRTEL';

                    fetch("deposit_record.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ user: logged, deposit: topup, bank: bankId, tx_id: tx_Id, time: formattedTime, date: formattedDate })
                    })
                    .then(res => res.json())
                    .then(d => { if (!d.success) showMessage(d.message || "Record save failed", 'error'); })
                    .catch(err => { showMessage("Network error saving record", 'error'); });
                } else {
                    $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
                    $Box.innerHTML = data.message || "Server Error: Failed to update balance.";
                }
            })
            .catch(err => {
                $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
                $Box.innerHTML = 'Network Error: Could not connect to server.';
            })
            .finally(() => {
                if (depositButton) { depositButton.disabled = false; depositButton.innerHTML = 'Deposit'; }
                setTimeout(() => { $Box.style.display = 'none'; }, 5000);
            });
        },
        onPending(result) {
            if (depositButton) { depositButton.disabled = false; depositButton.innerHTML = 'Deposit'; }
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-orange-900/50 text-orange-300`;
            $Box.innerHTML = "**Payment is pending.** Check your phone.";
            $Box.style.display = 'block';
            setTimeout(() => { $Box.style.display = 'none'; }, 5000);
        },
        onCancelled(result) {
            if (depositButton) { depositButton.disabled = false; depositButton.innerHTML = 'Deposit'; }
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
            $Box.innerHTML = "**Payment was cancelled.**";
            $Box.style.display = 'block';
            setTimeout(() => { $Box.style.display = 'none'; }, 5000);
        },
        onError(error) {
            if (depositButton) { depositButton.disabled = false; depositButton.innerHTML = 'Deposit'; }
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block bg-red-900/50 text-red-300`;
            $Box.innerHTML = "Payment failed. Unknown error.";
            $Box.style.display = 'block';
            setTimeout(() => { $Box.style.display = 'none'; }, 5000);
        },
    });

    try {
        document.querySelector('.mlp_method-info').innerHTML = "<h1 style='font-size:30px;color:#003;font-weight:bold;text-shadow:1px 2px 2px #000;'>Mobile Payment</h1>";
        document.querySelector('.mlp_payment-wrapper .mlp_payment-left')?.remove();
        document.querySelector('.mlp_payment-wrapper')?.style.setProperty('width', '100%', 'important');
        document.querySelector('.mlp_payment-wrapper').parentElement.classList.add('has_child');
        const element = document.querySelector('.has_child');
        if (element) {
            element.style.setProperty('width', '90vw', 'important');
            element.style.setProperty('max-width', '1000px', 'important');
            element.style.setProperty('margin-left', 'auto', 'important');
            element.style.setProperty('margin-right', 'auto', 'important');
        }
        document.getElementById('mlp_airtel-number').style.color = '#000';
        document.getElementById('mlp_display-amount').style.color = '#fff';
        document.querySelector('#mlp_mpamba-content').classList.add('mlp_active');
        document.querySelector('#mlp_mpamba-content').style.display = 'block';
        document.querySelector('#mlp_airtel-content').classList.remove('mlp_active');
        document.querySelector('#mlp_airtel-content').style.display = 'none';
        document.querySelector('.mlp_wallet-tab').click();
    } catch (e) { /* Ignore external library tweak errors */ }

    const shortNumber = logged.slice(1);
    const ISP = shortNumber.startsWith('8') ? 2 : 1;
    if (ISP == 2) {
        document.getElementById('mlp_mpamba-number').value = shortNumber;
    } else {
        document.getElementById('mlp_airtel-number').value = shortNumber;
    }
};

// ============================================================
// WHEEL GAME — IIFE
// ============================================================
(function() {

    // --- Login check ---
    let logged = sessionStorage.getItem('logged');
    const pox = document.getElementById('depo');
    if (logged) { pox.style.display = 'block'; }
    if (logged) {
        const liveElement = document.getElementById('live');
        if (liveElement) liveElement.innerHTML = logged;
        if (typeof fetchBalance === 'function') fetchBalance(logged);
    } else {
        const liveElement = document.getElementById('live');
        if (liveElement) liveElement.innerHTML = 'Sign in to continue.';
    }

    // --- Game state ---
    let gameState = 'IDLE';
    let selectedCategory = null;

    // --- DOM refs ---
    const $betInput = document.getElementById('bet-amount') || document.getElementById('betInput');
    const $spinButton = document.getElementById('spin-button') || document.getElementById('spinButton');
    const $resultDisplay = document.getElementById('result-display') || document.getElementById('resultDisplay');
    const $predictionButtons = document.querySelectorAll('.cat-btn[data-category]') || document.querySelectorAll('[data-category]');
    const $messageBox = document.getElementById('message-box') || document.getElementById('messageBox');
    const $balDisplay = document.getElementById('bal');

    // ============================================================
    // CANVAS SETUP — THE KEY FIX
    // ============================================================
    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');
    let angle = 0;
    let currentRotation = 0;
    const FIXED_SPIN_ROTATIONS = 8;
    let canvasLogicalSize = 0; // Track the CSS pixel size for mouse coord translation

    const segments = [
        { label: "× 2.4", color: "#16a34a", category: 'low',  textColor: "#fff" },
        { label: "× 2.4", color: "#16a34a", category: 'low',  textColor: "#fff" },
        { label: "× 2.4", color: "#16a34a", category: 'low',  textColor: "#fff" },
        { label: "Lose",  color: "#6b7280", category: 'none', textColor: "#fff" },
        { label: "Lose",  color: "#6b7280", category: 'none', textColor: "#fff" },
        { label: "× 3.6", color: "#2563eb", category: 'mid',  textColor: "#fff" },
        { label: "× 3.6", color: "#2563eb", category: 'mid',  textColor: "#fff" },
        { label: "× 7.2", color: "#dc2626", category: 'high', textColor: "#fff" }
    ];
    const segAngleRad = 2 * Math.PI / segments.length;

    let hoveredVisualIndex = null;
    let selectedVisualIndex = null;
    let isSpinning = false;

    const HUB_RADIUS_RATIO = 0.15;
    const TEXT_DISTANCE_RATIO = 0.75;
    const BORDER_COLOR = '#1e293b';
    const CENTER_HUB_COLOR = '#f1f5f9';
    const CENTER_HUB_INNER_COLOR = '#64748b';
    const HOVER_BORDER_COLOR = '#f97316';
    const SELECTED_BORDER_COLOR = '#ef4444';
    const SEGMENT_BORDER_WIDTH = 1.5;

    const normalizeAngle = (a) => ((a % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);

    // --- Visual segments (merged contiguous same-color segments) ---
    function createVisualSegments(data) {
        if (data.length === 0) return [];
        const visualSegments = [];
        let currentSegment = { ...data[0], labels: [data[0]], startIdx: 0, vIndex: 0 };
        for (let i = 1; i < data.length; i++) {
            const nextSegment = data[i];
            if (nextSegment.color === currentSegment.color) {
                currentSegment.labels.push(nextSegment);
            } else {
                currentSegment = { ...currentSegment, totalSlots: currentSegment.labels.length };
                visualSegments.push(currentSegment);
                currentSegment = { ...nextSegment, labels: [nextSegment], startIdx: i, vIndex: visualSegments.length };
            }
        }
        currentSegment = { ...currentSegment, totalSlots: currentSegment.labels.length };
        visualSegments.push(currentSegment);
        return visualSegments;
    }

    const visualSegments = createVisualSegments(segments);

    // ============================================================
    // RESIZE CANVAS — handles devicePixelRatio for crisp rendering
    // ============================================================
    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;

        // Get the CSS layout size (the size the canvas element occupies)
        const rect = canvas.getBoundingClientRect();
        const cssWidth = rect.width;
        const cssHeight = rect.height;

        // Store for mouse coordinate translation
        canvasLogicalSize = cssWidth;

        // Guard: if CSS size is 0 (element hidden / not laid out yet), skip
        if (cssWidth === 0 || cssHeight === 0) return;

        // Set the canvas *buffer* size to match physical pixels
        canvas.width = Math.round(cssWidth * dpr);
        canvas.height = Math.round(cssHeight * dpr);

        // Scale the drawing context so we can draw in CSS-pixel coordinates
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        // Redraw immediately with correct sizing
        drawWheel(angle);
    }

    // Listen for resize — debounced for safety
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(resizeCanvas, 50);
    });

    // ============================================================
    // DRAW WHEEL — all coordinates in CSS pixels
    // ============================================================
    function drawWheel(rotation) {
        const cssW = canvasLogicalSize;
        const cssH = canvasLogicalSize;
        if (cssW === 0 || cssH === 0) return;

        const cx = cssW / 2;
        const cy = cssH / 2;
        const wheelRadius = Math.max(1, Math.min(cssW, cssH) / 2 - 10);
        const hubRadius = Math.max(1, wheelRadius * HUB_RADIUS_RATIO);
        const textRadius = Math.max(1, wheelRadius * TEXT_DISTANCE_RATIO);

        let currentAngle = rotation - Math.PI / 2;

        ctx.clearRect(0, 0, cssW, cssH);

        // 1. Draw segments
        visualSegments.forEach((vSeg, vIdx) => {
            const vSegAngleRad = vSeg.totalSlots * segAngleRad;
            const start = currentAngle;
            const end = start + vSegAngleRad;

            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, wheelRadius, start, end);
            ctx.arc(cx, cy, hubRadius, end, start, true);
            ctx.closePath();

            ctx.fillStyle = vSeg.color;
            ctx.fill();

            // 2. Highlight (hover / selected)
            const isHighlighted = (vIdx === hoveredVisualIndex || vIdx === selectedVisualIndex);
            const highlightColor = (vIdx === selectedVisualIndex) ? SELECTED_BORDER_COLOR : HOVER_BORDER_COLOR;

            if (isHighlighted) {
                ctx.save();
                ctx.beginPath();
                ctx.arc(cx, cy, wheelRadius, start, end);
                ctx.arc(cx, cy, hubRadius, end, start, true);
                ctx.clip();
                ctx.beginPath();
                ctx.arc(cx, cy, wheelRadius, start, end);
                ctx.strokeStyle = highlightColor;
                ctx.lineWidth = 8;
                ctx.shadowColor = highlightColor;
                ctx.shadowBlur = 15;
                ctx.stroke();
                ctx.restore();
            }

            // 3. Segment separator
            ctx.beginPath();
            ctx.moveTo(cx + hubRadius * Math.cos(start), cy + hubRadius * Math.sin(start));
            ctx.lineTo(cx + wheelRadius * Math.cos(start), cy + wheelRadius * Math.sin(start));
            ctx.strokeStyle = BORDER_COLOR;
            ctx.lineWidth = SEGMENT_BORDER_WIDTH;
            ctx.stroke();

            // 4. Text labels
            let labelAngle = start;
            vSeg.labels.forEach((labelData) => {
                const labelCenterAngle = labelAngle + segAngleRad / 2;
                ctx.save();
                ctx.translate(cx, cy);
                ctx.rotate(labelCenterAngle);
                ctx.textAlign = "right";
                ctx.fillStyle = labelData.textColor || "#fff";
                ctx.font = `bold ${Math.max(8, wheelRadius * 0.10)}px 'Inter', sans-serif`;
                ctx.shadowColor = 'rgba(0,0,0,0.5)';
                ctx.shadowBlur = 2;
                ctx.fillText(labelData.label.toUpperCase(), textRadius, 0);
                ctx.restore();
                labelAngle += segAngleRad;
            });

            currentAngle = end;
        });

        // 5. Outer border
        ctx.shadowBlur = 0;
        ctx.beginPath();
        ctx.arc(cx, cy, wheelRadius, 0, 2 * Math.PI);
        ctx.strokeStyle = BORDER_COLOR;
        ctx.lineWidth = 4;
        ctx.stroke();

        // 6. Center hub
        ctx.shadowBlur = 0;
        ctx.beginPath();
        ctx.arc(cx, cy, hubRadius, 0, 2 * Math.PI);
        ctx.fillStyle = CENTER_HUB_COLOR;
        ctx.shadowColor = 'rgba(0,0,0,0.5)';
        ctx.shadowBlur = 15;
        ctx.fill();
        ctx.shadowBlur = 0;
        ctx.strokeStyle = BORDER_COLOR;
        ctx.lineWidth = 4;
        ctx.stroke();

        const innerHubRadius = Math.max(1, hubRadius * 0.6);
        ctx.beginPath();
        ctx.arc(cx, cy, innerHubRadius, 0, 2 * Math.PI);
        ctx.fillStyle = CENTER_HUB_INNER_COLOR;
        ctx.fill();
        ctx.strokeStyle = BORDER_COLOR;
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    // ============================================================
    // INTERACTION — mouse coordinates in CSS pixels
    // ============================================================
    function getVisualSegmentIndexFromAngle(x, y) {
        const cssW = canvasLogicalSize;
        if (cssW === 0) return null;
        const cx = cssW / 2;
        const cy = cssW / 2;
        const wheelRadius = Math.max(1, cssW / 2 - 10);
        const hubRadius = Math.max(1, wheelRadius * HUB_RADIUS_RATIO);
        const dx = x - cx;
        const dy = y - cy;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist > wheelRadius || dist < hubRadius) return null;

        let pointerAngle = Math.atan2(dy, dx);
        if (pointerAngle < 0) pointerAngle += 2 * Math.PI;

        let normalizedAngle = normalizeAngle(pointerAngle - (angle - Math.PI / 2));
        const originalIndex = Math.floor(normalizedAngle / segAngleRad) % segments.length;

        let cumulativeSlots = 0;
        for (let i = 0; i < visualSegments.length; i++) {
            cumulativeSlots += visualSegments[i].totalSlots;
            if (originalIndex < cumulativeSlots) return i;
        }
        return null;
    }

    window.handleSegmentSelection = function(clickedVisualIndex) {
        if (isSpinning) return;
        if (clickedVisualIndex !== null && visualSegments[clickedVisualIndex].category === 'none') {
            selectedVisualIndex = null;
            selectedCategory = null;
            window.selectBet(null);
            drawWheel(angle);
            return;
        }
        if (clickedVisualIndex !== null && clickedVisualIndex !== -1) {
            const selectedVseg = visualSegments[clickedVisualIndex];
            if (clickedVisualIndex === selectedVisualIndex) {
                selectedVisualIndex = null;
                selectedCategory = null;
                window.selectBet(null);
            } else {
                selectedVisualIndex = clickedVisualIndex;
                selectedCategory = selectedVseg.category;
                window.selectBet(selectedCategory);
            }
        } else if (selectedVisualIndex !== null) {
            selectedVisualIndex = null;
            selectedCategory = null;
            window.selectBet(null);
        }
        drawWheel(angle);
    };

    window.selectBet = function(category) {
        if (gameState !== 'IDLE') return;
        $predictionButtons.forEach(btn => btn.classList.remove('selected'));
        if (category) {
            const btn = document.querySelector(`.cat-btn[data-category="${category}"]`);
            if (btn) btn.classList.add('selected');
            selectedCategory = category;
            const visualIndex = visualSegments.findIndex(vSeg => vSeg.category === category);
            if (visualIndex !== -1 && visualIndex !== selectedVisualIndex) {
                selectedVisualIndex = visualIndex;
                drawWheel(angle);
            }
        } else {
            selectedCategory = null;
            selectedVisualIndex = null;
            drawWheel(angle);
        }
    };

    // Mouse events — use CSS-pixel coords via getBoundingClientRect
    canvas.addEventListener('mousemove', (e) => {
        if (isSpinning) return;
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        const newHoveredIndex = getVisualSegmentIndexFromAngle(mouseX, mouseY);

        let actualHoverIndex = newHoveredIndex;
        if (newHoveredIndex !== null && visualSegments[newHoveredIndex].category === 'none') {
            actualHoverIndex = null;
        }

        if (actualHoverIndex !== hoveredVisualIndex) {
            hoveredVisualIndex = actualHoverIndex;
            drawWheel(angle);
            canvas.style.cursor = actualHoverIndex !== null ? 'pointer' : 'default';
        }
    });

    canvas.addEventListener('mouseleave', () => {
        if (isSpinning) return;
        if (hoveredVisualIndex !== null) {
            hoveredVisualIndex = null;
            drawWheel(angle);
            canvas.style.cursor = 'default';
        }
    });

    canvas.addEventListener('click', (e) => {
        if (isSpinning) return;
        const rect = canvas.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        const clickedIndex = getVisualSegmentIndexFromAngle(mouseX, mouseY);
        window.handleSegmentSelection(clickedIndex);
    });

    // Touch support — same CSS-pixel translation
    canvas.addEventListener('touchstart', (e) => {
        if (isSpinning) return;
        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];
        const mouseX = touch.clientX - rect.left;
        const mouseY = touch.clientY - rect.top;
        const clickedIndex = getVisualSegmentIndexFromAngle(mouseX, mouseY);
        window.handleSegmentSelection(clickedIndex);
    }, { passive: true });

    $predictionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (gameState !== 'IDLE') return;
            const category = btn.getAttribute('data-category');
            const visualIndex = visualSegments.findIndex(vSeg => vSeg.category === category);
            if (visualIndex !== -1) window.handleSegmentSelection(visualIndex);
        });
    });

    // ============================================================
    // SPIN WHEEL — pointer math unchanged, drawing uses CSS pixels
    // ============================================================
    async function spinWheel(betAmount) {
        if (isSpinning) return;
        isSpinning = true;
        if ($spinButton) $spinButton.disabled = true;
        selectedVisualIndex = null;
        hoveredVisualIndex = null;
        canvas.style.cursor = 'default';

        // Fetch random winning segment from server
        let randomStopSegment;
        try {
            const response = await fetch('random.php');
            const data = await response.text();
            randomStopSegment = parseInt(data, 10);
            if (isNaN(randomStopSegment)) throw new Error('Invalid random number');
        } catch (error) {
            showMessage("Error fetching random number: " + error.message, 'error');
            isSpinning = false;
            if ($spinButton) $spinButton.disabled = false;
            return;
        }

        const resultSegment = segments[randomStopSegment];
        const winningCategory = resultSegment.category;

        const winningVsegForLanding = visualSegments.find(vSeg => vSeg.category === winningCategory);
        if (!winningVsegForLanding) {
            console.error("Critical Error: Winning visual segment not found (" + winningCategory + ").");
            initializeGame();
            return;
        }

        const startSegmentIndex = winningVsegForLanding.startIdx;
        const totalSlotsInVisualSegment = winningVsegForLanding.totalSlots;
        const segmentIndexToCenter = startSegmentIndex + Math.floor(Math.random() * totalSlotsInVisualSegment);
        const centerOfSegmentAngle = (segmentIndexToCenter * segAngleRad) + (segAngleRad / 2);

        let targetStopOffset = normalizeAngle(2 * Math.PI - centerOfSegmentAngle);
        const normalizedTargetStop = normalizeAngle(targetStopOffset);

        currentRotation = angle;
        let currentNormalizedAngle = normalizeAngle(currentRotation);

        let angleDifference = normalizedTargetStop - currentNormalizedAngle;
        if (angleDifference < 0) angleDifference += 2 * Math.PI;

        const totalRotationNeeded = FIXED_SPIN_ROTATIONS * 2 * Math.PI + angleDifference;
        const jitter = (Math.random() * 0.005) - 0.0025;
        const endRotation = currentRotation + totalRotationNeeded + jitter;

        const duration = 5000;
        let startTime = null;

        function animate(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = timestamp - startTime;
            const normalizedProgress = Math.min(progress / duration, 1);
            const easing = normalizedProgress * (2 - normalizedProgress);

            angle = currentRotation + (endRotation - currentRotation) * easing;
            drawWheel(angle);

            if (progress < duration) {
                requestAnimationFrame(animate);
            } else {
                isSpinning = false;
                if ($spinButton) $spinButton.disabled = false;
                angle = endRotation;
                drawWheel(angle);

                const deductedBalance = parseFloat($balDisplay.innerHTML) || 0;
                checkResult(betAmount, resultSegment, deductedBalance);
            }
        }
        requestAnimationFrame(animate);
    }

    if ($spinButton) {
        $spinButton.removeEventListener('click', window.handleSpin);
        $spinButton.addEventListener('click', window.handleSpin);
    }

    // --- Utility ---
    function showMessage(message, type) {
        if (!$messageBox) return;
        let bgColor = 'bg-blue-100';
        let textColor = 'text-blue-800';
        if (type === 'success') { bgColor = 'bg-green-100'; textColor = 'text-green-800'; }
        else if (type === 'error') { bgColor = 'bg-red-100'; textColor = 'text-red-800'; }
        else if (type === 'warn') { bgColor = 'bg-yellow-100'; textColor = 'text-yellow-800'; }

        $messageBox.className = `p-3 rounded-lg text-sm transition-opacity duration-300 block ${bgColor} ${textColor}`;
        $messageBox.innerHTML = message;
        $messageBox.style.display = 'block';
        setTimeout(() => { $messageBox.style.display = 'none'; }, 5000);
    }

    window.initializeGame = function() {
        gameState = 'IDLE';
        selectedCategory = null;
        selectedVisualIndex = null;
        if ($spinButton) {
            $spinButton.disabled = false;
            $spinButton.textContent = 'SPIN';
            $spinButton.style.opacity = '';
        }
        if ($betInput) $betInput.disabled = false;
        if ($resultDisplay) {
            $resultDisplay.textContent = '';
            $resultDisplay.classList.remove('text-green-400', 'text-red-400');
            $resultDisplay.classList.add('text-white');
        }
        $predictionButtons.forEach(btn => { btn.classList.remove('selected'); btn.disabled = false; });
        drawWheel(angle);
    };

    window.initializeFreeGame = function() {
        gameState = 'IDLE';
        selectedCategory = null;
        selectedVisualIndex = null;
        if ($spinButton) {
            $spinButton.disabled = false;
            $spinButton.textContent = 'FREE SPIN';
            $spinButton.style.opacity = '';
        }
        if ($resultDisplay) {
            $resultDisplay.textContent = '';
            $resultDisplay.classList.remove('text-green-400', 'text-red-400');
            $resultDisplay.classList.add('text-white');
        }
        $predictionButtons.forEach(btn => { btn.classList.remove('selected'); btn.disabled = false; });
        drawWheel(angle);
    };

    window.changeBet = function(multiplier) {
        if (!$betInput) return;
        let current = parseFloat($betInput.value);
        if (isNaN(current)) current = 0;
        current *= multiplier;
        if (current < 50) current = 50;
        if (current > 700000) current = 700000;
        $betInput.value = current.toFixed(2);
    };

    let isFreeSpin = false;

    function performSpinStart(betValue) {
        $spinButton.disabled = true;
        $spinButton.textContent = isFreeSpin ? 'FREE SPINNING...' : 'SPINNING...';
        $betInput.disabled = true;
        $predictionButtons.forEach(btn => btn.disabled = true);
        if ($resultDisplay) $resultDisplay.textContent = 'GOOD LUCK';
        gameState = 'SPINNING';
        spinWheel(betValue);
        const aud = document.getElementById("instant-audio");
        if (aud) aud.play();
        document.getElementById('def_aud').pause();
    }

    window.handleSpin = function() {
        const currentBalance = parseFloat($balDisplay.innerHTML) || 0;
        const logged = sessionStorage.getItem('logged');
        localStorage.setItem('FR', 0);
        const performingFreeSpin = isFreeSpin;

        setTimeout(() => {
            document.getElementById('spin-button').innerHTML = performingFreeSpin ? 'FREE SPIN' : 'SPIN';
        }, 6000);

        window.scrollTo({ top: 0, behavior: 'smooth' });

        if (typeof spinWheel !== 'function' || !$balDisplay || !$betInput || !$spinButton) {
            showMessage("Game element not found. Please reload.", 'error');
            return;
        }
        if (!logged) {
            showMessage("Please log in first.", 'warn');
            const startElement = document.getElementById('start');
            if (startElement) startElement.style.display = 'block';
            return;
        }
        if (gameState !== 'IDLE') {
            showMessage("Please wait for the current spin to finish.", 'warn');
            return;
        }

        const betValue = parseFloat($betInput.value) || 0;
        if (!selectedCategory) {
            showMessage("Please select a betting category (LOW, MID, or HIGH).", 'error');
            return;
        }
        if (!performingFreeSpin) {
            if (isNaN(betValue) || betValue < 50) {
                showMessage("Bet must be at least MWK 50.00.", 'error');
                return;
            }
            if (currentBalance < betValue) {
                showMessage(`Insufficient balance (MWK ${currentBalance.toFixed(2)}).`, 'error');
                return;
            }
        }
        if (selectedCategory === 'none') {
            showMessage("Cannot bet on 'Lose'. Select LOW, MID, or HIGH.", 'error');
            selectedCategory = null;
            selectedVisualIndex = null;
            window.selectBet(null);
            return;
        }

        $spinButton.disabled = true;

        if (performingFreeSpin) {
            isFreeSpin = false;
            performSpinStart(betValue);
        } else {
            const deductedBalance = currentBalance - betValue;
            $balDisplay.innerHTML = deductedBalance.toFixed(2);

            fetch("bet.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ bet_user: logged, bet_amount: betValue.toFixed(2), bet_mode: 'withdraw' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    performSpinStart(betValue);
                } else {
                    $balDisplay.innerHTML = currentBalance.toFixed(2);
                    showMessage("Server failed to lock bet: " + (data.message || "Try again."), 'error');
                    initializeGame();
                }
            })
            .catch(err => {
                $balDisplay.innerHTML = currentBalance.toFixed(2);
                showMessage("Network error during bet transaction.", 'error');
                initializeGame();
            });
        }
    };

    // ============================================================
    // CHECK RESULT
    // ============================================================
    function checkResult(betAmount, resultSegment, deductedBalance) {
        const winningCategory = resultSegment.category;
        const winningLabel = resultSegment.label;

        if (!selectedCategory) {
            showMessage("Error: No bet category selected.", 'error');
            initializeGame();
            return;
        }

        const betPayoutData = document.querySelector(`.cat-btn[data-category="${selectedCategory}"]`);
        const betPayout = parseFloat(betPayoutData ? betPayoutData.getAttribute('data-payout') : 1.0);
        const logged = sessionStorage.getItem('logged');

        const totalReturn = betAmount * betPayout;
        const winAmountOnly = totalReturn;
        const msc = localStorage.getItem('audio');

        const normalizedWinningCategory = (winningCategory || '').toUpperCase().trim();
        const normalizedSelectedCategory = (selectedCategory || '').toUpperCase().trim();

        const now = new Date();
        const formattedDate = now.toISOString().split("T")[0];
        const formattedTime = now.toTimeString().split(" ")[0];

        const requestData = {
            user: logged,
            stake: betAmount,
            won: totalReturn,
            target_landed: winningCategory,
            target_selected: selectedCategory,
            time: formattedTime,
            date: formattedDate,
            bet_user: logged,
            bet_amount: winAmountOnly.toFixed(2),
            bet_mode: 'deposit'
        };

        if (normalizedWinningCategory && normalizedWinningCategory === normalizedSelectedCategory) {
            // --- WIN ---
            fetch("win_record.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage("Win record saved.", 'success');
                    if (data.new_balance && $balDisplay) {
                        $balDisplay.innerHTML = parseFloat(data.new_balance).toFixed(2);
                    }
                } else {
                    showMessage(data.message || "Server failed", 'error');
                }
            })
            .catch(err => { showMessage("Network error, win record failed", 'error'); });

            const aud2 = document.getElementById("instant-audio2");
            if (aud2) aud2.play();
            document.getElementById("instant-audio").pause();
            if (msc === 'playing') setTimeout(() => { document.getElementById('def_aud').play(); }, 8000);
            document.getElementById('result-display').innerHTML = '';
            showMessage(`✅ WINNER! You won MWK ${totalReturn.toFixed(2)}!`, 'success');

            fetch("bet.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ bet_user: logged, bet_amount: winAmountOnly.toFixed(2), bet_mode: 'deposit' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.new_balance && $balDisplay) {
                    $balDisplay.innerHTML = parseFloat(data.new_balance).toFixed(2);
                } else {
                    $balDisplay.innerHTML = (deductedBalance + winAmountOnly).toFixed(2);
                }
            })
            .catch(err => { showMessage("Network error during win transaction.", 'error'); });

        } else {
            // --- LOSS ---
            fetch("loss_record.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const aud3 = document.getElementById("instant-audio3");
                    if (aud3) aud3.play();
                    document.getElementById("instant-audio").pause();
                    if (msc === 'playing') setTimeout(() => { document.getElementById('def_aud').play(); }, 2000);
                    document.getElementById('result-display').innerHTML = '';
                    showMessage(`❌ LOST! Landed on ${winningLabel.toUpperCase()}`, 'warn');
                } else {
                    showMessage(data.message || "Server failed", 'error');
                }
            })
            .catch(err => { showMessage("Network error, bet record failed", 'error'); });
        }

        setTimeout(() => { initializeGame(); }, 2000);
    }

    // ============================================================
    // INITIALIZATION — THE CRITICAL FIX
    // ============================================================
    // Use requestAnimationFrame + fallback to ensure canvas has layout size
    function initWheel() {
        resizeCanvas();
        drawWheel(angle);
        initializeGame();

        // Fade out the loader AFTER the wheel has been drawn
        const loader = document.getElementById('loader');
        if (loader) {
            loader.classList.add('hiding');
            setTimeout(() => { loader.style.display = 'none'; }, 500);
        }
    }

    // Try immediately (for scripts at bottom of body, layout may already be ready)
    // Also schedule via rAF and setTimeout as fallbacks
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // DOM is ready, but layout might not be calculated yet
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                // Double rAF ensures at least one layout pass
                if (canvas.getBoundingClientRect().width > 0) {
                    initWheel();
                } else {
                    // Still no size — use setTimeout as last resort
                    setTimeout(initWheel, 100);
                }
            });
        });
    } else {
        // DOM not ready yet — wait for it
        window.addEventListener('DOMContentLoaded', () => {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    if (canvas.getBoundingClientRect().width > 0) {
                        initWheel();
                    } else {
                        setTimeout(initWheel, 100);
                    }
                });
            });
        });
    }

    // Absolute final safety net — if nothing above fired after 500ms, force init
    setTimeout(() => {
        const loader = document.getElementById('loader');
        if (loader && loader.style.display !== 'none' && !loader.classList.contains('hiding')) {
            initWheel();
        }
    }, 500);

    // Also handle window.onload as a catch-all
    window.addEventListener('load', () => {
        const loader = document.getElementById('loader');
        if (loader && loader.style.display !== 'none' && !loader.classList.contains('hiding')) {
            initWheel();
        }
    });

})();

// ============================================================
// LOGIN LOGIC
// ============================================================
window.log = function(e) {
    if (e) e.preventDefault();

    const login_formData = new FormData(document.getElementById('user_login'));
    const phone = document.getElementById('phone').value;
    const po = document.getElementById('depo');
    const $Box = document.getElementById('message-box') || document.getElementById('messageBox');
    const shortNumber = phone.slice(1);

    document.getElementById('logic').innerHTML = 'Wait a bit...';
    document.getElementById('logic').disabled = true;
    document.getElementById('logic').style.opacity = '0.3';
    document.getElementById('logic').style.cursor = 'not-allowed';

    if (shortNumber.startsWith('8') || shortNumber.startsWith('9')) {
        fetch("login.php", { method: "POST", body: login_formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                sessionStorage.setItem('logged', phone);
                document.getElementById('live').innerHTML = phone;
                document.getElementById('start').style.display = 'none';
                fetchBalance(phone);
                $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 bg-green-100 text-green-800`;
                $Box.innerHTML = data.message;
                $Box.style.display = 'block';
                setTimeout(() => { hid(); $Box.style.display = 'none'; }, 5000);
                po.style.display = 'block';
            } else {
                $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 bg-red-100 text-red-800`;
                $Box.innerHTML = data.message;
                $Box.style.display = 'block';
                setTimeout(() => { hid(); $Box.style.display = 'none'; }, 5000);
            }
        })
        .catch(err => {
            $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 bg-red-100 text-red-800`;
            $Box.innerHTML = 'Error connecting to server.';
            $Box.style.display = 'block';
            setTimeout(() => { hid(); $Box.style.display = 'none'; }, 5000);
        });
    } else {
        $Box.className = `p-3 rounded-lg text-sm transition-opacity duration-300 bg-red-100 text-red-800`;
        $Box.innerHTML = "Please use a TNM or AIRTEL number!";
        $Box.style.display = 'block';
        setTimeout(() => { hid(); $Box.style.display = 'none'; }, 5000);
    }

    return false;
};

function fetchBalance(user) {
    fetch("player.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ log_player: user })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (sessionStorage.getItem('logged')) document.getElementById('logout').innerHTML = 'Logout';
            document.getElementById('bal').innerHTML = parseFloat(data.balance).toFixed(2);
        } else {
            showMessage("Failed to retrieve balance: " + (data.message || 'Unknown error'), 'error');
            document.getElementById('bal').innerHTML = '0.00';
        }
    })
    .catch(err => {
        showMessage("Error fetching balance.", 'error');
        document.getElementById('bal').innerHTML = '0.00';
    });
}

function hid() {
    document.getElementById('logic').innerHTML = 'Ready to play';
    document.getElementById('logic').disabled = '';
    document.getElementById('logic').style.opacity = '';
    document.getElementById('logic').style.cursor = '';
}

// --- Theme toggle (referenced from HTML) ---
function toggleTheme() {
    const track = document.getElementById('theme-toggle');
    track.classList.toggle('on');
    document.documentElement.classList.toggle('light-mode');
    localStorage.setItem('theme', document.documentElement.classList.contains('light-mode') ? 'light' : 'dark');
}

// Restore theme on load
if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.add('light-mode');
    const track = document.getElementById('theme-toggle');
    if (track) track.classList.add('on');
}