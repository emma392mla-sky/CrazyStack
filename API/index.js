function handleWithdrawAPILoadError() {
        // Show user-friendly message only
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
   
    
    <!-- SESSION MANAGEMENT SCRIPT -->
   
    const SESSION_CONFIG = {
        TIMEOUT_DURATION: 5 * 60 * 1000,
        WARNING_DURATION: 30 * 1000,
        STORAGE_KEY: 'crazyStackSession',
        CHECK_INTERVAL: 1000
    };

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
            this.setupActivityListeners();
        }

        start() {
            if (this.isActive) return;
            this.isActive = true;
            this.lastActivityTime = Date.now();
            this.startTimers();
            this.startActivityCheck();
        }

        stop() {
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
            }, SESSION_CONFIG.CHECK_INTERVAL);
        }

        resetTimers() {
            if (!this.isActive) return;
            this.lastActivityTime = Date.now();
            this.warningShown = false;
            this.removeWarningBanner();
            this.startTimers();
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
                        this.handleTimeout();
                    } else if (timeAway > 0) {
                        this.resetTimers();
                    }
                }
            });
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
   

    <!-- Application Inline Script -->
   
<!-- PHONE DEBUG TRACKER -->

window.PhoneDebugTracker = {
    originalInput: null,
    sentToWebhook: null,
    sessionPhone: null,
    
    log: function(stage, phone) {
        if (stage === 'INPUT') this.originalInput = phone;
        if (stage === 'WEBHOOK') this.sentToWebhook = phone;
        if (stage === 'SESSION') this.sessionPhone = phone;
        
        if (this.originalInput && this.sentToWebhook) {
            if (this.originalInput !== this.sentToWebhook) {
                showToastMessage(
                    `⚠️ Debug: Phone changed from ${this.originalInput} to ${this.sentToWebhook}`,
                    'warning'
                );
            }
        }
    },
    
    reset: function() {
        this.originalInput = null;
        this.sentToWebhook = null;
        this.sessionPhone = null;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const phoneInput = document.getElementById('paymentPhoneInput');
    if (phoneInput) {
        phoneInput.addEventListener('change', (e) => {
            window.PhoneDebugTracker.log('INPUT', e.target.value);
        });
        phoneInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter' || e.target.value.length === 10) {
                // Optional: Handle real-time tracking here if needed
            }
        });
    }
});


<!-- SYSTEM HEALTH CHECK -->

window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Check 1: crazy object
        if (typeof crazy === 'undefined') {
            // Silent fail or silent notification if desired
        }
        
        // Check 2: crazyStackApp
        if (typeof window.crazyStackApp === 'undefined') {
            // Silent fail
        }
        
    }, 2000);
});


// Define crazy object IMMEDIATELY (no external file needed!)
window.crazy = {
    url: "https://awnzbiatwnfmryerfxwg.supabase.co",
    key: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg",

    PAYCHANGU: {
        SECRET_KEY: 'sec-live-Xvad0XHGxKVQ0w3QpIDIpNh1POaHtTkS',
        MODE: 'live',
        BASE_URL: 'https://api.paychangu.com',
        PAYOUT_ENDPOINT: '/mobile-money/payouts/initialize'
    },

    // ✅ UPDATED IDs FROM YOUR PHP SNIPPET
    OPERATORS: {
        AIRTEL: {
            name: 'Airtel Money',
            ref_id: '20be6c20-adeb-4b5b-a7ba-0769820df4fb',
            ussd_code: '*303#',
            network_code: 'airtel',
            prefixes: ['99', '98', '09', '099', '098']
        },
        TNM: {
            name: 'TNM mPamba',
            ref_id: '27494cb5-ba9e-437f-a114-4e7a7686bcca',
            ussd_code: '*456#',
            network_code: 'tnm',
            prefixes: ['88', '87', '81', '89', '08', '088', '087', '081', '089']
        }
    },

    getHeaders: function() {
        return {
            "apikey": this.key,
            "Authorization": "Bearer " + this.key,
            "Content-Type": "application/json"
        };
    },

    normalizePhone: function(phone) {
        if (!phone) return '';
        let digits = phone.replace(/\D/g, '');
        if (digits.startsWith('265')) digits = '0' + digits.slice(3);
        if (digits.length === 9 && !digits.startsWith('0')) digits = '0' + digits;
        return digits;
    },

    toPayChanguFormat: function(phone10digit) {
        if (!phone10digit) return '';
        if (phone10digit.startsWith('0')) return phone10digit.slice(1);
        return phone10digit;
    },

    // ✅ FIXED DETECTION LOGIC: Uses startsWith instead of endsWith
    detectProvider: function(phone) {
        if (!phone) return this.OPERATORS.AIRTEL;
        const cleaned = phone.replace(/\D/g, '');
        
        const isAirtel = this.OPERATORS.AIRTEL.prefixes.some(p => 
            cleaned.startsWith(p) || cleaned.startsWith('265' + p.slice(1))
        );
        
        if (isAirtel) return this.OPERATORS.AIRTEL;
        
        const isTNM = this.OPERATORS.TNM.prefixes.some(p => 
            cleaned.startsWith(p) || cleaned.startsWith('265' + p.slice(1))
        );
        
        return isTNM ? this.OPERATORS.TNM : this.OPERATORS.AIRTEL;
    },

    generateChargeId: function() {
        return 'CHG_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6).toLowerCase();
    },
    
    generateReference: function() {
        return 'WD_' + Date.now() + '_' + Math.random().toString(36).substr(2, 8).toUpperCase();
    },

    balance: async function(phone) {
        try {
            const r = await fetch(`${this.url}/rest/v1/users?phone=eq.${phone}&select=balance`, {
                method: "GET",
                headers: this.getHeaders()
            });
            
            const d = await r.json();
            
            if (d && d.length > 0 && d[0].balance !== undefined) {
                const bal = parseFloat(d[0].balance) || 0;
                return bal;
            }
            
            return 'User not found';
            
        } catch (e) {
            return 'Error: ' + e.message;
        }
    },

    withdraw: async function(phone, amount) {
        try {
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
            
            // CHECK BALANCE
            const sessionPhone = window.crazyStackApp.playerPhone;
            const currentBalance = await this.balance(sessionPhone);
            
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
            
            // CALL PAYCHANGU DIRECTLY!
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
                
            } catch (dbErr) {
                // DB update warning - fail silently as payment was successful
            }
            
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
            return { success: false, error: err.message || 'Unexpected error', errorCode: 'UNEXPECTED_ERROR' };
        }
    }
};

