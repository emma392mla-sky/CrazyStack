// ============================================
// API/withdraw_api.js
// This file holds your Secret Keys
// ============================================
console.log('🔧 Loading Withdrawal API...');

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
                return parseFloat(d[0].balance) || 0;
            }
            return 'User not found';
        } catch (e) {
            return 'Error: ' + e.message;
        }
    },

    withdraw: async function(phone, amount) {
        try {
            if (!window.crazyStackApp || !window.crazyStackApp.isUserAuthenticated) {
                return { success: false, error: 'Not logged in' };
            }
            
            const normalizedPhone = this.normalizePhone(phone);
            const numericAmount = Number(amount);
            
            if (!normalizedPhone || normalizedPhone.length !== 10) {
                return { success: false, error: 'Invalid phone format' };
            }
            if (isNaN(numericAmount) || numericAmount < 50) {
                return { success: false, error: 'Min MWK 50' };
            }

            const provider = this.detectProvider(normalizedPhone);
            const paychanguPhone = this.toPayChanguFormat(normalizedPhone);
            
            // Check Balance
            const sessionPhone = window.crazyStackApp.playerPhone;
            const currentBalance = await this.balance(sessionPhone);

            if (typeof currentBalance !== 'number') {
                return { success: false, error: 'Failed to verify balance' };
            }
            if (numericAmount > currentBalance) {
                return { success: false, error: 'Insufficient funds' };
            }

            // Call PayChangu
            const chargeId = this.generateChargeId();
            const reference = this.generateReference();
            
            const response = await fetch('https://api.paychangu.com/mobile-money/payouts/initialize', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.PAYCHANGU.SECRET_KEY}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: numericAmount,
                    currency: 'MWK',
                    mobile: paychanguPhone,
                    network: provider.network_code,
                    mobile_money_operator_ref_id: provider.ref_id,
                    charge_id: chargeId,
                    reference: reference,
                    mode: this.PAYCHANGU.MODE
                })
            });

            const responseData = await response.json();

            if (response.status >= 400 || responseData.status === 'error') {
                return { success: false, error: responseData.message || 'Payment failed' };
            }

            // Update DB
            const newBal = Number(currentBalance) - numericAmount;
            await fetch(`${this.url}/rest/v1/users?phone=eq.${sessionPhone}`, {
                method: 'PATCH',
                headers: this.getHeaders(),
                body: JSON.stringify({ balance: newBal })
            });

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
                    created_at: new Date().toISOString()
                })
            });

            if (window.crazyStackApp) {
                window.crazyStackApp.currentBalance = newBal;
                if (typeof window.crazyStackApp.updateBalanceDisplay === 'function') {
                    window.crazyStackApp.updateBalanceDisplay();
                }
            }

            return { success: true, message: 'MWK ' + numericAmount + ' sent successfully!' };

        } catch (err) {
            return { success: false, error: err.message };
        }
    }
};
console.log('✅ API Loaded');