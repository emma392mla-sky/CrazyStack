// =============================================
// SUPABASE CONFIGURATION
// =============================================
const SUPABASE_PROJECT_URL = "https://awnzbiatwnfmryerfxwg.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";
const supabaseClient = supabase.createClient(SUPABASE_PROJECT_URL, SUPABASE_ANON_KEY);

const PAYMENTS_DB_URL = "https://vfntorjzpselgbhkjetz.supabase.co";
const PAYMENTS_DB_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZmbnRvcmp6cHNlbGdiaGtqZXR6Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzYyNTk4ODAsImV4cCI6MjA5MTgzNTg4MH0._AkGUZJ-D5nsLEfcD1xzbEBEz2KLJdzo3pxuZMLTb4A";
const paymentsDbClient = supabase.createClient(PAYMENTS_DB_URL, PAYMENTS_DB_KEY);

// =============================================
// GAME CONFIGURATION
// =============================================
const WHEEL_CONFIG = {
    segments: [
        { name: 'Green', key: 'green', hexColor: '#10b981', multiplier: 2.4, slots: 3 },
        { name: 'Blue', key: 'blue', hexColor: '#3b82f6', multiplier: 3.6, slots: 2 },
        { name: 'Red', key: 'red', hexColor: '#ef4444', multiplier: 7.2, slots: 1 },
        { name: 'Gray', key: 'gray', hexColor: '#64748b', multiplier: 0, slots: 2 }
    ],
    minimumBet: 50,
    totalSegments: 8
};

let currentActiveTab = 'login';
let isTabTransitioning = false;
let confirmCallback = null;

// =============================================
// 🆕 DUPLICATE PREVENTION SYSTEM
// =============================================
class DuplicatePreventionSystem {
    constructor() {
        // Track processed transactions to prevent duplicates
        this.processedTransactions = new Set();
        // Track active polling sessions to prevent concurrent processing
        this.activePollingSessions = new Map();
        // Lock mechanism for payment processing
        this.isProcessingPayment = false;
    }

    /**
     * Check if a transaction has already been processed
     * @param {string} transactionId - The unique transaction/charge ID
     * @returns {boolean} - True if already processed
     */
    isTransactionProcessed(transactionId) {
        return this.processedTransactions.has(transactionId);
    }

    /**
     * Mark a transaction as processed
     * @param {string} transactionId - The unique transaction/charge ID
     */
    markTransactionProcessed(transactionId) {
        this.processedTransactions.add(transactionId);
        // Also persist to sessionStorage for page reload protection
        this.persistProcessedTransactions();
    }

    /**
     * Check if there's an active polling session for a transaction
     * @param {string} transactionId - The transaction ID
     * @returns {boolean}
     */
    hasActivePollingSession(transactionId) {
        return this.activePollingSessions.has(transactionId);
    }

    /**
     * Register an active polling session
     * @param {string} transactionId - Transaction ID
     * @param {number} intervalId - The setInterval ID
     */
    registerPollingSession(transactionId, intervalId) {
        this.activePollingSessions.set(transactionId, intervalId);
    }

    /**
     * Unregister and clear a polling session
     * @param {string} transactionId - Transaction ID
     */
    unregisterPollingSession(transactionId) {
        if (this.activePollingSessions.has(transactionId)) {
            clearInterval(this.activePollingSessions.get(transactionId));
            this.activePollingSessions.delete(transactionId);
        }
    }

    /**
     * Acquire processing lock (prevents concurrent payment processing)
     * @returns {boolean} - True if lock acquired successfully
     */
    acquireProcessingLock() {
        if (this.isProcessingPayment) {
            return false;
        }
        this.isProcessingPayment = true;
        return true;
    }

    /**
     * Release processing lock
     */
    releaseProcessingLock() {
        this.isProcessingPayment = false;
    }

    /**
     * Persist processed transactions to sessionStorage
     */
    persistProcessedTransactions() {
        try {
            const transactionsArray = Array.from(this.processedTransactions);
            sessionStorage.setItem('crazyStack_processed_txns', JSON.stringify(transactionsArray));
        } catch (e) {
            console.warn('Failed to persist processed transactions:', e);
        }
    }

    /**
     * Load processed transactions from sessionStorage (on app init)
     */
    loadPersistedTransactions() {
        try {
            const stored = sessionStorage.getItem('crazyStack_processed_txns');
            if (stored) {
                const transactionsArray = JSON.parse(stored);
                this.processedTransactions = new Set(transactionsArray);
                
                // Clean up old transactions (older than 1 hour)
                this.cleanupOldTransactions();
            }
        } catch (e) {
            console.warn('Failed to load persisted transactions:', e);
        }
    }

    /**
     * Clean up transactions older than 1 hour to prevent memory bloat
     */
    cleanupOldTransactions() {
        // In a real implementation, you'd store timestamps
        // For now, just clear if too many accumulate
        if (this.processedTransactions.size > 100) {
            const array = Array.from(this.processedTransactions);
            // Keep only the last 50
            const recentOnes = array.slice(-50);
            this.processedTransactions = new Set(recentOnes);
            this.persistProcessedTransactions();
        }
    }

    /**
     * Clear all tracking data (useful for testing/logout)
     */
    clearAll() {
        this.processedTransactions.clear();
        this.activePollingSessions.forEach((intervalId) => clearInterval(intervalId));
        this.activePollingSessions.clear();
        this.isProcessingPayment = false;
        sessionStorage.removeItem('crazyStack_processed_txns');
    }
}

// Initialize global duplicate prevention instance
const duplicatePrevention = new DuplicatePreventionSystem();

// =============================================
// AUDIO ENGINE
// =============================================
class SoundEngine {
    constructor() {
        this.isEnabled = true;
        this.audioContext = null;
        this.initializeContext();
    }

    initializeContext() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (error) { console.warn('Audio context not available:', error); }
    }

    playTone(frequency = 850, duration = 0.1, waveformType = 'sine') {
        if (!this.isEnabled || !this.audioContext) return;
        try {
            if (this.audioContext.state === 'suspended') this.audioContext.resume();
            const osc = this.audioContext.createOscillator();
            const gain = this.audioContext.createGain();
            osc.connect(gain);
            gain.connect(this.audioContext.destination);
            osc.frequency.value = frequency;
            osc.type = waveformType;
            const now = this.audioContext.currentTime;
            gain.gain.setValueAtTime(0.18, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + duration);
            osc.start(now);
            osc.stop(now + duration);
        } catch (e) { /* Ignore audio errors */ }
    }

    clickSound() { this.playTone(920, 0.07); }
    successSound() { 
        this.playTone(523, 0.14); 
        setTimeout(() => this.playTone(659, 0.14), 140); 
        setTimeout(() => this.playTone(784, 0.2), 280); 
    }
    errorSound() { this.playTone(260, 0.2, 'square'); }
    spinSound() { this.playTone(130, 4.2, 'triangle'); }

    toggleSound() {
        this.isEnabled = !this.isEnabled;
        if (this.isEnabled) this.clickSound();
        return this.isEnabled;
    }
}

const soundEngine = new SoundEngine();

// =============================================
// TOAST NOTIFICATION SYSTEM
// =============================================
function showToastMessage(message, toastType = '') {
    const toastEl = document.getElementById('toastElement');
    if (!toastEl) return;

    toastEl.textContent = message;
    toastEl.className = `toast-notification visible ${toastType ? 'type-' + toastType : ''}`;

    if (toastType === 'success') soundEngine.successSound();
    else if (toastType === 'error') soundEngine.errorSound();

    setTimeout(() => {
        toastEl.className = 'toast-notification';
    }, 3800);
}

// =============================================
// CUSTOM CONFIRMATION MODAL
// =============================================
function showCustomConfirm(message, onYes) {
    const overlay = document.getElementById('confirmOverlay');
    const title = overlay.querySelector('.confirm-title');
    const msg = overlay.querySelector('.confirm-msg');
    const yesBtn = document.getElementById('confirmYes');
    const noBtn = document.getElementById('confirmNo');

    title.textContent = 'Confirm Action';
    msg.textContent = message || 'Are you sure?';
    
    overlay.classList.add('active');
    soundEngine.clickSound();

    const newYesBtn = yesBtn.cloneNode(true);
    const newNoBtn = noBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
    noBtn.parentNode.replaceChild(newNoBtn, noBtn);

    newYesBtn.addEventListener('click', () => {
        overlay.classList.remove('active');
        if (onYes) onYes();
    });

    newNoBtn.addEventListener('click', () => {
        overlay.classList.remove('active');
    });
}

// =============================================
// TAB SWITCHING LOGIC
// =============================================
function switchToTab(targetTabId) {
    soundEngine.clickSound();
    if (isTabTransitioning || targetTabId === currentActiveTab) return;
    isTabTransitioning = true;

    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.classList.toggle('current', tab.dataset.target === targetTabId);
    });

    document.getElementById('loginPanel').classList.toggle('hidden', targetTabId !== 'loginPanel');
    document.getElementById('signupPanel').classList.toggle('hidden', targetTabId !== 'signupPanel');

    const authTitle = document.getElementById('authModalTitle');
    const authDesc = document.getElementById('authModalDesc');
    
    if (targetTabId === 'loginPanel') {
        authTitle.textContent = 'Welcome Back';
        authDesc.textContent = 'Sign in to start playing';
    } else {
        authTitle.textContent = 'Create Account';
        authDesc.textContent = 'Join CrazyStack today';
    }

    setTimeout(() => {
        isTabTransitioning = false;
        currentActiveTab = targetTabId;
    }, 180);
}

// =============================================
// MAIN APPLICATION CLASS
// =============================================
class CrazyStackApp {
    constructor() {
        this.currentBalance = 0;
        this.selectedColor = null;
        this.isWheelSpinning = false;
        this.wheelRotationAngle = 0;
        this.isUserAuthenticated = false;
        this.playerName = '';
        this.playerPhone = '';
        this.selectedPaymentProvider = null;

        this.canvasElement = document.getElementById('wheelCanvas');
        this.canvasContext = this.canvasElement.getContext('2d');

        this.initializeApplication();
    }

    initializeApplication() {
        // 🆕 Load persisted duplicate prevention data
        duplicatePrevention.loadPersistedTransactions();
        
        this.renderWheelGraphic();
        this.attachAllEventListeners();
        this.restoreUserSession();
        this.setupGlobalClickSounds();
    }

    setupGlobalClickSounds() {
        document.addEventListener('click', (event) => {
            if (event.target.closest('button')) {
                soundEngine.clickSound();
            }
        });
    }

    restoreUserSession(hood) {
        try {
            const savedSession = sessionStorage.getItem('crazyStackSession');
            if (!savedSession) return;

            const sessionData = JSON.parse(savedSession);
            
            if (sessionData && sessionData.authenticated && sessionData.playerPhone) {
                storage(false,sessionData.playerPhone); // stored the user phonenumber
                this.isUserAuthenticated = true;
                this.playerName = sessionData.playerName;
                this.playerPhone = sessionData.playerPhone;
                
                supabaseClient
                    .from('users')
                    .select('balance')
                    .eq('phone', this.playerPhone)
                    .single()
                    .then(({ data, error }) => {
                        if (!error && data) {
                            this.currentBalance = parseFloat(data.balance) || 0;
                        } else {
                            // fallback to stored value
                            this.currentBalance = parseFloat(sessionData.balance) || 0;
                        }

                        // ✅ update UI AFTER fetching
                        this.updateBalanceDisplay();
                        this.updateAuthenticationUI();
                        this.hideModal('authModalOverlay');

                        if(hood){
                            showToastMessage(`Ready to Cashout`, 'success');
                        }else{
                            showToastMessage(`✅ Welcome back, ${this.playerName}!`, 'success');
                        }
                    })
                    .catch(err => {
                        console.error('Fetch balance error:', err);

                        // fallback if request fails completely
                        this.currentBalance = parseFloat(sessionData.balance) || 0;
                        this.updateBalanceDisplay();
                    });
            }
        } catch (error) {
            console.error('Failed to restore session:', error);
            sessionStorage.removeItem('crazyStackSession');
        }
    }

    saveUserSession(phone, name, balance) {
        const sessionData = {
            authenticated: true,
            playerPhone: phone,
            playerName: name,
            balance: balance,
            timestamp: Date.now()
        };
        sessionStorage.setItem('crazyStackSession', JSON.stringify(sessionData));
    }

    clearUserSession() {
        sessionStorage.removeItem('crazyStackSession');
        // 🆕 Also clear duplicate prevention data on logout
        duplicatePrevention.clearAll();
    }

    attachAllEventListeners() {
        document.querySelectorAll('.color-option').forEach(optionButton => {
            optionButton.addEventListener('click', () => {
                this.selectBettingColor(optionButton.dataset.color);
            });
        });

        document.getElementById('spinAction').addEventListener('click', () => this.executeSpin());
        document.getElementById('authButton').addEventListener('click', () => this.handleAuthenticationToggle());

        document.querySelectorAll('.tab-item').forEach(tabButton => {
            tabButton.addEventListener('click', () => switchToTab(tabButton.dataset.target));
        });

        document.getElementById('executeLogin').addEventListener('click', () => this.processLogin());
        document.getElementById('executeSignup').addEventListener('click', () => this.processSignup());

        document.getElementById('dismissAuthModal').addEventListener('click', () => this.hideModal('authModalOverlay'));
        document.querySelectorAll('.auth-switcher strong').forEach(link => {
            link.addEventListener('click', () => switchToTab(link.dataset.target));
        });

        document.getElementById('depositBtn').addEventListener('click', () => this.openPaymentModal('deposit'));
        document.getElementById('withdrawBtn').addEventListener('click', () => this.openPaymentModal('withdraw'));
        document.getElementById('confirmPayment').addEventListener('click', (e) => this.processPaymentTransaction(e));
        document.getElementById('dismissPaymentModal').addEventListener('click', () => this.hideModal('paymentModalOverlay'));

        document.querySelectorAll('.provider-choice').forEach(providerBtn => {
            providerBtn.addEventListener('click', () => this.selectPaymentProvider(providerBtn.dataset.provider));
        });

        document.getElementById('paymentPhoneInput').addEventListener('input', () => this.autoDetectNetworkProvider());

        document.getElementById('fabSettingsBtn').addEventListener('click', () => this.toggleSettingsPopover());
        document.getElementById('logoutSettingEntry').addEventListener('click', () => {
            this.toggleSettingsPopover(false);
            showCustomConfirm('Are you sure you want to logout?', () => this.executeLogout());
        });
        document.getElementById('soundToggleEntry').addEventListener('click', () => {
            const soundState = soundEngine.toggleSound();
            document.getElementById('soundToggleEntry').innerHTML = `
                 
                Sound: ${soundState ? 'ON' : 'OFF'}
            `;
        });

        window.addEventListener('resize', () => this.renderWheelGraphic());
    }

    updateBalanceDisplay() {
        const balanceDisplayEl = document.getElementById('displayBalance');
        if (balanceDisplayEl) {
            balanceDisplayEl.textContent = `MWK ${this.currentBalance.toFixed(2)}`;
        }
    }

    updateAuthenticationUI() {
        const authButton = document.getElementById('authButton');
        if (!authButton) return;

        if (this.isUserAuthenticated) {
            authButton.innerHTML = '';
            authButton.classList.remove('guest');
            authButton.title = 'Click to logout';
        } else {
            authButton.innerHTML = '';
            authButton.classList.add('guest');
            authButton.title = 'Click to login';
        }
    }

    renderWheelGraphic() {
        const ctx = this.canvasContext;
        const centerX = 400;
        const centerY = 400;
        const radius = 385;
        const totalSlots = WHEEL_CONFIG.totalSegments;
        const arcSize = (Math.PI * 2) / totalSlots;

        ctx.clearRect(0, 0, 800, 800);

        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(-Math.PI / 2);
        ctx.translate(-centerX, -centerY);

        let startAngle = 0;

        WHEEL_CONFIG.segments.forEach(segment => {
            for (let slotIndex = 0; slotIndex < segment.slots; slotIndex++) {
                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, startAngle, startAngle + arcSize);
                ctx.fillStyle = segment.hexColor;
                ctx.fill();
                
                ctx.strokeStyle = 'rgba(0,0,0,0.18)';
                ctx.lineWidth = 2;
                ctx.stroke();

                ctx.save();
                ctx.translate(centerX, centerY);
                ctx.rotate(startAngle + arcSize / 2);
                
                ctx.textAlign = 'right';
                ctx.fillStyle = segment.key === 'gray' ? '#a1a1aa' : '#ffffff';
                ctx.font = 'bold 46px Inter, sans-serif';
                ctx.shadowColor = 'rgba(0,0,0,0.45)';
                ctx.shadowBlur = 5;
                ctx.fillText(segment.name, radius - 50, 14);

                ctx.font = '30px Inter, sans-serif';
                ctx.fillStyle = 'rgba(255,255,255,0.78)';
                ctx.fillText(segment.multiplier + 'x', radius - 50, 56);
                
                ctx.restore();

                startAngle += arcSize;
            }
        });

        ctx.restore();

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.lineWidth = 26;
        ctx.strokeStyle = '#cbd5e1';
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius - 14, 0, Math.PI * 2);
        ctx.lineWidth = 4;
        ctx.strokeStyle = '#030712';
        ctx.stroke();

        const hubGradient = ctx.createRadialGradient(centerX, centerY, 10, centerX, centerY, 90);
        hubGradient.addColorStop(0, '#334155');
        hubGradient.addColorStop(1, '#0c1222');
        
        ctx.beginPath();
        ctx.arc(centerX, centerY, 90, 0, Math.PI * 2);
        ctx.fillStyle = hubGradient;
        ctx.fill();
        
        ctx.lineWidth = 6;
        ctx.strokeStyle = '#fbbf24';
        ctx.stroke();

        ctx.fillStyle = '#fbbf24';
        ctx.font = 'bold 36px Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('CS', centerX, centerY);
    }

    selectBettingColor(colorKey) {
        if (this.isWheelSpinning) return;

        this.selectedColor = colorKey;
        document.querySelectorAll('.color-option').forEach(btn => btn.classList.remove('selected'));
        
        const selectedButton = document.querySelector(`.option-${colorKey}`);
        if (selectedButton) selectedButton.classList.add('selected');

        const statusIndicator = document.getElementById('statusIndicator');
        statusIndicator.textContent = `Selected: ${colorKey.toUpperCase()}`;
        statusIndicator.className = 'status-indicator';
    }

    async updateDatabaseBalance(amount, phone) {
        try {
            const numericAmount = parseFloat(amount);
            if (isNaN(numericAmount)) throw new Error('Invalid amount');

            const { data: userData, error: fetchError } = await supabaseClient
                .from('users')
                .select('balance')
                .eq('phone', phone)
                .single();

            if (fetchError || !userData) throw fetchError || new Error('User not found');

            const currentBalance = parseFloat(userData.balance) || 0;
            const newBalance = currentBalance + numericAmount;

            const { error: updateError } = await supabaseClient
                .from('users')
                .update({ balance: newBalance })
                .eq('phone', phone);

            if (updateError) throw updateError;

            return newBalance;
        } catch (error) {
            console.error('DB Update Error:', error);
            throw error;
        }
    }

    executeSpin() {
        if (this.isWheelSpinning) return;

        if (!this.isUserAuthenticated) {
            showToastMessage('⚠️ Please login first!', 'error');
            this.showModal('authModalOverlay');
            return;
        }

        const wagerInput = document.getElementById('wagerInput');
        const wagerAmount = parseFloat(wagerInput.value);

        if (!this.selectedColor) {
            showToastMessage('⚠️ Select a betting color first!', 'error');
            return;
        }

        if (isNaN(wagerAmount) || wagerAmount < WHEEL_CONFIG.minimumBet) {
            showToastMessage(`⚠️ Minimum bet is MWK ${WHEEL_CONFIG.minimumBet}`, 'error');
            return;
        }

        if (wagerAmount > this.currentBalance) {
            showToastMessage('⚠️ Insufficient balance!', 'error');
            return;
        }

        this.currentBalance -= wagerAmount;
        this.updateBalanceDisplay();
        this.saveUserSession(this.playerPhone, this.playerName, this.currentBalance);

        this.isWheelSpinning = true;
        document.getElementById('spinAction').disabled = true;
        document.getElementById('wheelContainer').classList.add('spinning');

        const statusIndicator = document.getElementById('statusIndicator');
        statusIndicator.textContent = 'SPINNING...';
        statusIndicator.className = 'status-indicator';

        soundEngine.spinSound();

        const winningSegmentIndex = Math.floor(Math.random() * WHEEL_CONFIG.totalSegments);
        const segmentDegreeSize = 360 / WHEEL_CONFIG.totalSegments;
        const targetSegmentCenter = (winningSegmentIndex * segmentDegreeSize) + (segmentDegreeSize / 2);
        const targetRotation = 360 - targetSegmentCenter;
        const fullRotations = 360 * 7;
        const randomOffset = (Math.random() * 28) - 14;
        
        const currentModulo = this.wheelRotationAngle % 360;
        let deltaRotation = (targetRotation - currentModulo + 360) % 360;
        const finalRotationAngle = this.wheelRotationAngle + fullRotations + deltaRotation + randomOffset;

        this.wheelRotationAngle = finalRotationAngle;
        this.canvasElement.style.transform = `rotate(${this.wheelRotationAngle}deg)`;

        setTimeout(() => {
            this.resolveSpinResult(winningSegmentIndex, wagerAmount);
        }, 4300);
    }

    async resolveSpinResult(winnerIndex, wagerAmount) {
        this.isWheelSpinning = false;
        document.getElementById('spinAction').disabled = false;
        document.getElementById('wheelContainer').classList.remove('spinning');

        let counter = 0;
        let winningSegment = null;

        for (const segment of WHEEL_CONFIG.segments) {
            for (let k = 0; k < segment.slots; k++) {
                if (counter === winnerIndex) {
                    winningSegment = segment;
                    break;
                }
                counter++;
            }
            if(winningSegment) break;
        }

        const statusIndicator = document.getElementById('statusIndicator');
        const isWin = winningSegment.key === this.selectedColor && winningSegment.key !== 'gray';

        try {
            if (isWin) {
                // WIN CASE: Add Payout
                const payoutAmount = wagerAmount * winningSegment.multiplier;
                const newDbBalance = await this.updateDatabaseBalance(payoutAmount, this.playerPhone);
                
                this.currentBalance = newDbBalance;
                this.updateBalanceDisplay();
                this.saveUserSession(this.playerPhone, this.playerName, this.currentBalance);

                statusIndicator.textContent = `🎉 WIN! +MWK ${payoutAmount.toFixed(2)}`;
                statusIndicator.className = 'status-indicator is-win';
                showToastMessage(`🎉 WINNER! You won MWK ${payoutAmount.toFixed(2)}!`, 'success');
            } else {
                // LOSS CASE: Deduct Wager from DB (CRITICAL FIX)
                const lossAmount = -wagerAmount;
                const newDbBalance = await this.updateDatabaseBalance(lossAmount, this.playerPhone);
                
                this.currentBalance = newDbBalance;
                this.updateBalanceDisplay();
                this.saveUserSession(this.playerPhone, this.playerName, this.currentBalance);

                statusIndicator.textContent = winningSegment.key === 'gray' ? 'GRAY ZONE' : 'TRY AGAIN';
                statusIndicator.className = 'status-indicator is-loss';
                showToastMessage(winningSegment.key === 'gray' ? 'Gray zone - No win' : 'Better luck next time!', 'error');
            }
        } catch (err) {
            // Fallback if DB connection fails
            console.error("Balance sync failed", err);
            showToastMessage("⚠️ Connection issue. Balance update pending.", "info");
        }
    }

    handleAuthenticationToggle() {
        if (this.isUserAuthenticated) {
            showCustomConfirm('Are you sure you want to logout?', () => this.executeLogout());
            return;
        }
        const authModal = document.getElementById('authModalOverlay');
        if (authModal.classList.contains('active')) {
            this.hideModal('authModalOverlay');
        } else {
            isTabTransitioning = false;
            currentActiveTab = 'login';
            switchToTab('loginPanel');
            this.showModal('authModalOverlay');
        }
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('active');
    }

    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    }

    async processLogin() {
        const phoneInput = document.getElementById('loginPhoneInput').value.trim();
        const pinInput = document.getElementById('loginPinInput').value.trim();

        if (!phoneInput || phoneInput.length < 9) {
            showToastMessage('⚠️ Enter valid phone number (9+ digits)', 'error');
            return;
        }
        if (!pinInput || pinInput.length < 4) {
            showToastMessage('⚠️ Enter valid PIN (4+ digits)', 'error');
            return;
        }

        const loginButton = document.getElementById('executeLogin');
        loginButton.disabled = true;
        loginButton.textContent = 'Verifying...';

        try {
            const { data: userData, error: queryError } = await supabaseClient
                .from('users')
                .select('*')
                .eq('phone', phoneInput)
                .eq('password', pinInput)
                .single();

            if (userData && !queryError) {
                this.isUserAuthenticated = true;
                this.playerName = userData.name || phoneInput;
                this.playerPhone = phoneInput;
                this.currentBalance = parseFloat(userData.balance) || 0;

                this.saveUserSession(phoneInput, this.playerName, this.currentBalance);
                this.updateBalanceDisplay();
                this.updateAuthenticationUI();
                this.hideModal('authModalOverlay');
                showToastMessage(`✅ Welcome back, ${this.playerName}!`, 'success');
            } else {
                showToastMessage('❌ Invalid phone number or PIN', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            showToastMessage(`❌ Login Error: ${error.message}`, 'error');
        } finally {
            loginButton.disabled = false;
            loginButton.textContent = 'Sign In';
        }
    }

    async processSignup() {
        const nameInput = document.getElementById('signupNameInput').value.trim();
        const phoneInput = document.getElementById('signupPhoneInput').value.trim();
        const pinInput = document.getElementById('signupPinInput').value.trim();
        const confirmPinInput = document.getElementById('signupConfirmPinInput').value.trim();

        if (!nameInput) return showToastMessage('⚠️ Please enter your full name', 'error');
        if (!phoneInput || phoneInput.length < 9) return showToastMessage('⚠️ Enter valid phone number', 'error');
        if (!pinInput || pinInput.length < 4) return showToastMessage('⚠️ PIN must be at least 4 digits', 'error');
        if (pinInput !== confirmPinInput) return showToastMessage('⚠️ PIN codes do not match', 'error');

        const signupButton = document.getElementById('executeSignup');
        signupButton.disabled = true;
        signupButton.textContent = 'Creating...';

        try {
            const { data: newUser, error: insertError } = await supabaseClient
                .from('users')
                .insert([{
                    phone: phoneInput,
                    password: pinInput,
                    name: nameInput,
                    balance: 0,
                    created_at: new Date().toISOString()
                }])
                .select();

            if (insertError) {
                if (insertError.code === '23505') showToastMessage('❌ Phone number already registered!', 'error');
                else showToastMessage(`❌ Signup Error: ${insertError.message}`, 'error');
            } else {
                this.isUserAuthenticated = true;
                this.playerName = nameInput;
                this.playerPhone = phoneInput;
                this.currentBalance = 0;

                this.saveUserSession(phoneInput, nameInput, 0);
                this.updateBalanceDisplay();
                this.updateAuthenticationUI();
                this.hideModal('authModalOverlay');
                showToastMessage(`✅ Account created! Welcome, ${nameInput}! 🎉`, 'success');
            }
        } catch (error) {
            console.error('Signup error:', error);
            showToastMessage(`❌ Signup Error: ${error.message}`, 'error');
        } finally {
            signupButton.disabled = false;
            signupButton.textContent = 'Create Account';
        }
    }

    executeLogout() {
        this.clearUserSession();
        this.isUserAuthenticated = false;
        this.playerName = '';
        this.playerPhone = '';
        this.currentBalance = 0;

        this.updateBalanceDisplay();
        this.updateAuthenticationUI();
        this.showModal('authModalOverlay');
        
        isTabTransitioning = false;
        currentActiveTab = 'login';
        switchToTab('loginPanel');

        showToastMessage('👋 Logged out successfully', 'info');
    }

    openPaymentModal(transactionType) {
        if (!this.isUserAuthenticated) {
            showToastMessage('⚠️ Please login first!', 'error');
            this.showModal('authModalOverlay');
            return;
        }

        const paymentTitle = document.getElementById('paymentModalTitle');
        paymentTitle.textContent = transactionType === 'deposit' ? '💰 Deposit Funds' : '💸 Withdraw Funds';

        this.selectedPaymentProvider = null;
        document.querySelectorAll('.provider-choice').forEach(btn => {
            btn.classList.remove('chosen', 'disabled-state');
        });

        //document.getElementById('paymentPhoneInput').value = '';
        document.getElementById('paymentAmountInput').value = '';
        this.currentTransactionType = transactionType;

        this.showModal('paymentModalOverlay');
    }

    selectPaymentProvider(providerNetwork) {
        this.selectedPaymentProvider = providerNetwork;
        document.querySelectorAll('.provider-choice').forEach(btn => {
            btn.classList.remove('chosen', 'disabled-state');
            if (btn.dataset.provider === providerNetwork) {
                btn.classList.add('chosen');
            } else {
                btn.classList.add('disabled-state');
            }
        });
    }

    autoDetectNetworkProvider() {
        const rawPhoneNumber = document.getElementById('paymentPhoneInput').value.replace(/\D/g, '');
        if (rawPhoneNumber.length < 2) return;

        let detectedNetwork = null;
        if (rawPhoneNumber.startsWith('2659') || rawPhoneNumber.startsWith('09')) detectedNetwork = 'airtel';
        else if (rawPhoneNumber.startsWith('2658') || rawPhoneNumber.startsWith('08')) detectedNetwork = 'tnm';

        if (detectedNetwork) this.selectPaymentProvider(detectedNetwork);
    }

    // =============================================
    // 🆕 IMPROVED PAYMENT PROCESSING WITH DUPLICATE PREVENTION
    // =============================================
    processPaymentTransaction(event) {
        event.preventDefault();

        // 🆕 PREVENT DUPLICATE: Check if already processing a payment
        if (!duplicatePrevention.acquireProcessingLock()) {
            showToastMessage('⏳ Payment already in progress. Please wait...', 'warning');
            return;
        }

        const phoneNumber = document.getElementById('paymentPhoneInput').value.trim();
        const amountValue = document.getElementById('paymentAmountInput').value;
        const payButton = document.getElementById('confirmPayment');
        const originalButtonText = payButton.textContent;

        if (!this.selectedPaymentProvider || !phoneNumber || phoneNumber.length < 9 || !amountValue || parseFloat(amountValue) < 50) {
            showToastMessage('⚠️ Please fill all fields correctly', 'error');
            // 🆕 Release lock on validation failure
            duplicatePrevention.releaseProcessingLock();
            return;
        }

        payButton.textContent = 'Processing...';
        payButton.disabled = true;

        // NOTE: This fetch requires a live backend server running mobile_pay.php
        fetch('mobile_pay.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mobile: phoneNumber, amount: amountValue })
        })
        .then(response => {
            return response.text().then(text => {
                try { return JSON.parse(text); } 
                catch (e) { throw new Error("Server unavailable or invalid response"); }
            });
        })
        .then(responseData => {
            payButton.textContent = originalButtonText;
            payButton.disabled = false;

            if (responseData.status !== 'success') {
                showToastMessage(responseData.message || '❌ Transaction failed', 'error');
                // 🆕 Release lock on failure
                duplicatePrevention.releaseProcessingLock();
                return;
            }

            const transactionRef = responseData.transaction?.charge_id;
            const transactionAmount = responseData.transaction?.amount || amountValue;
            const customerPhoneNumber = responseData.phone || phoneNumber;

            // 🆕 CRITICAL DUPLICATE CHECK: Verify this transaction hasn't been processed
            if (duplicatePrevention.isTransactionProcessed(transactionRef)) {
                console.warn('⚠️ Duplicate transaction attempt blocked:', transactionRef);
                showToastMessage('✅ This transaction was already processed!', 'warning');
                // Refresh balance to show existing credit
                this.refreshBalanceFromDB(customerPhoneNumber);
                // Release lock and reset UI
                duplicatePrevention.releaseProcessingLock();
                payButton.textContent = originalButtonText;
                payButton.disabled = false;
                return;
            }

            // 🆕 Check for existing polling session
            if (duplicatePrevention.hasActivePollingSession(transactionRef)) {
                console.log('ℹ️ Polling already active for:', transactionRef);
                showToastMessage('⏳ Already monitoring this transaction...', 'info');
                duplicatePrevention.releaseProcessingLock();
                return;
            }

            showToastMessage('✅ Transaction initiated! Dial *303# or *456# to confirm', 'success');
            payButton.textContent = 'Waiting for confirmation...';

            let pollAttempts = 0;
            const maxPollAttempts = 25;
            const appInstance = this;

            // 🆕 Create polling interval with proper cleanup
            const pollingInterval = setInterval(async () => {
                pollAttempts++;

                try {
                    const { data: paymentRecord } = await paymentsDbClient
                        .from('payments')
                        .select('status, amount, charge_id')
                        .eq('charge_id', transactionRef)
                        .maybeSingle();

                    if (!paymentRecord) {
                        if (pollAttempts >= maxPollAttempts) {
                            // 🆕 Cleanup on timeout
                            duplicatePrevention.unregisterPollingSession(transactionRef);
                            duplicatePrevention.releaseProcessingLock();
                            
                            clearInterval(pollingInterval);
                            showToastMessage('⏱️ Timeout - Check your transaction status later', 'info');
                            payButton.textContent = originalButtonText;
                            payButton.disabled = false;
                        }
                        return;
                    }

                    
                    if (paymentRecord.status === 'success') {
                        // 🆕 FINAL DUPLICATE CHECK before processing (belt-and-suspenders approach)
                        if (duplicatePrevention.isTransactionProcessed(transactionRef)) {
                            console.warn('🔄 Duplicate detected during success processing:', transactionRef);
                            duplicatePrevention.unregisterPollingSession(transactionRef);
                            duplicatePrevention.releaseProcessingLock();
                            clearInterval(pollingInterval);
                            
                            payButton.textContent = '✅ Already Credited';
                            this.refreshBalanceFromDB(customerPhoneNumber);
                            setTimeout(() => {
                                payButton.textContent = originalButtonText;
                                payButton.disabled = false;
                            }, 2500);
                            return;
                        }

                        // 🆕 Mark as processed IMMEDIATELY to prevent race conditions
                        duplicatePrevention.markTransactionProcessed(transactionRef);
                        
                        // Stop polling
                        clearInterval(pollingInterval);
                        duplicatePrevention.unregisterPollingSession(transactionRef);
                        
                        payButton.textContent = '✅ Complete';
                        
                        // Process the payment
                        const updatedBalance = await appInstance.updateDatabaseBalance(
                            paymentRecord.amount || transactionAmount, 
                            customerPhoneNumber
                        );
                                
                        if (updatedBalance !== null) {
                            appInstance.currentBalance = updatedBalance;
                            appInstance.updateBalanceDisplay();
                            appInstance.saveUserSession(customerPhoneNumber, appInstance.playerName, updatedBalance);
                        }

                        showToastMessage(`💰 Payment received! +MWK ${paymentRecord.amount || transactionAmount}`, 'success');
                        
                        // 🆕 Release lock after successful processing
                        duplicatePrevention.releaseProcessingLock();
                        
                        setTimeout(() => {
                            payButton.textContent = originalButtonText;
                            payButton.disabled = false;
                        }, 3500);

                    } else if (paymentRecord.status === 'failed') {
                        // 🆕 Cleanup on failure
                        duplicatePrevention.unregisterPollingSession(transactionRef);
                        duplicatePrevention.releaseProcessingLock();
                        
                        payButton.textContent = '❌ Failed';
                        showToastMessage('❌ Payment failed or cancelled', 'error');
                        setTimeout(() => {
                            payButton.textContent = originalButtonText;
                            payButton.disabled = false;
                        }, 3500);
                    } else {
                        // Still pending - continue polling
                        if (pollAttempts % 5 === 0) {
                            showToastMessage(`⏳ Still waiting... (${pollAttempts}/${maxPollAttempts})`, 'info');
                        }
                        payButton.textContent = `Checking... (${pollAttempts})`;
                    }

                } catch (pollError) {
                    console.error('Polling error:', pollError);
                    // Don't stop polling on transient errors
                }

            }, 1200);

            // 🆕 Register the polling session for management
            duplicatePrevention.registerPollingSession(transactionRef, pollingInterval);

        })
        .catch(fetchError => {
            console.error('Fetch error:', fetchError);
            payButton.textContent = originalButtonText;
            payButton.disabled = false;
            
            // 🆕 Release lock on network error
            duplicatePrevention.releaseProcessingLock();
            
            if(fetchError.message.includes("Server unavailable")) {
                 showToastMessage('❌ Payment Backend Error: Ensure mobile_pay.php is running.', 'error');
            } else {
                 showToastMessage('❌ Connection error - Try again', 'error');
            }
        });
    }

    // 🆕 Helper method to refresh balance from database
    async refreshBalanceFromDB(phoneNumber) {
        try {
            const { data: userData } = await supabaseClient
                .from('users')
                .select('balance')
                .eq('phone', phoneNumber)
                .single();

            if (userData) {
                this.currentBalance = parseFloat(userData.balance) || 0;
                this.updateBalanceDisplay();
                this.saveUserSession(phoneNumber, this.playerName, this.currentBalance);
            }
        } catch (err) {
            console.error('Balance refresh error:', err);
        }
    }

    toggleSettingsPopover(state = null) {
        const popover = document.getElementById('settingsPopover');
        const shouldShow = state !== null ? state : !popover.classList.contains('visible');
        popover.classList.toggle('visible', shouldShow);
    }
}

// =============================================
// INIT
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎡 Initializing CrazyStack Application (with Duplicate Protection)...');
    window.crazyStackApp = new CrazyStackApp();
    console.log('✅ CrazyStack Ready!');
    if (location.hostname !== "localhost") {
          console.log = () => {};
          console.warn = () => {};
          console.error = () => {};
}
});




