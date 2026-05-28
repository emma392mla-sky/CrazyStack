//=============================================
// SUPABASE CONFIGURATION - PROJECT B ONLY
//=============================================
const SUPABASE_URL = "https://awnzbiatwnfmryerfxwg.supabase.co";
const SUPABASE_ANON_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImF3bnpiaWF0d25mbXJ5ZXJmeHdnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NzI3NzcsImV4cCI6MjA5MjM0ODc3N30.yIxpa2cguXFE44PlcTy1a3FaLX7Z57geskmnXEmkFKg";

const supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

function createAuthenticatedClient(sessionToken) {
    return supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
        headers: { 'x-session-token': sessionToken }
    });
}

const SESSION_CONFIG = {
    STORAGE_KEY: 'crazyStack_user_session'
};

function toLocalPhoneFormat(rawPhone) {
    const digits = rawPhone.replace(/\D/g, '');
    if (digits.length >= 12) return '0' + digits.slice(-9);
    if (digits.length === 10 && digits.startsWith('0')) return digits;
    if (digits.length === 9) return '0' + digits;
    return '0' + digits.slice(-9);
}

const WHEEL_CONFIG = {
    segments: [
        { name: 'Green', key: 'green', hexColor: '#10b981', multiplier: 1.6, slots: 3 },
        { name: 'Blue', key: 'blue', hexColor: '#3b82f6', multiplier: 2.4, slots: 2 },
        { name: 'Red', key: 'red', hexColor: '#ef4444', multiplier: 4.8, slots: 1 },
        { name: 'Gray', key: 'gray', hexColor: '#64748b', multiplier: 0, slots: 2 }
    ],
    minimumBet: 50,
    totalSegments: 8
};

let currentActiveTab = 'login';
let isTabTransitioning = false;

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
                ? '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0"></path>' 
                : '<circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="23"></line>';
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

function showToastMessage(message, toastType = '') {
    const t = document.getElementById('toastElement'); 
    if(!t) return; 
    
    t.textContent = message; 
    t.className = `toast-notification visible ${toastType ? 'type-' + toastType : ''}`;
    
    if(toastType === 'success') soundEngine.successSound(); 
    else if (toastType === 'error') soundEngine.errorSound();
    
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
        this.currentTransactionType = null;
        this.sessionToken = null;
        this.sessionMonitorInterval = null;
        this.isRestoringSession = false;
        this.balanceFetchInProgress = false;
        this.balanceRetryCount = 0;
        this.maxBalanceRetries = 3;
        
        this.canvasElement = document.getElementById('wheelCanvas');
        this.canvasContext = this.canvasElement ? this.canvasElement.getContext('2d') : null;
        
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
    }

    setupGlobalClickSounds() {
        document.addEventListener('click', (e) => { 
            if(e.target.closest('button') && !e.target.closest('#settingsPopover')) soundEngine.clickSound(); 
        });
    }

    async forceRefreshBalance(source = 'unknown', retryCount = 0) {
        if (this.balanceFetchInProgress) {
            console.log(`⏳ Balance fetch already in progress, waiting...`);
            await new Promise(resolve => setTimeout(resolve, 500));
            if (this.balanceFetchInProgress) return this.currentBalance;
        }
        
        if (!this.playerPhone) {
            console.warn(`[${source}] ❌ Cannot fetch balance: no phone number`);
            return this.currentBalance;
        }
        
        this.balanceFetchInProgress = true;
        this.balanceRetryCount = retryCount;
        
        try {
            console.log(`🔄 [${source}] Fetching balance from Project B (attempt ${retryCount + 1}/${this.maxBalanceRetries + 1})...`);
            
            this.showBalanceLoading();
            
            const result = await supabaseClient.rpc('get_balance_by_phone', { 
                p_phone: this.playerPhone 
            });
            
            const { data: dbBalance, error } = result;
            
            if (error) {
                console.error(`❌ [${source}] Database Error:`, error);
                
                if (retryCount < this.maxBalanceRetries) {
                    this.balanceFetchInProgress = false;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    return this.forceRefreshBalance(`${source}-retry`, retryCount + 1);
                }
                
                throw error;
            }
            
            if (dbBalance === null || dbBalance === undefined) {
                if (retryCount < this.maxBalanceRetries) {
                    this.balanceFetchInProgress = false;
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    return this.forceRefreshBalance(`${source}-retry`, retryCount + 1);
                }
                
                throw new Error('No balance data returned');
            }
            
            const newBalance = parseFloat(dbBalance);
            
            if (isNaN(newBalance)) throw new Error(`Invalid balance value: ${dbBalance}`);
            
            console.log(`✅ [${source}] Balance fetched: MWK ${newBalance.toFixed(2)}`);
            
            this.currentBalance = newBalance;
            this.updateBalanceDisplay();
            this.saveUserSession(this.playerPhone, this.playerName, this.currentBalance);
            
            return this.currentBalance;
            
        } catch (error) {
            console.error(`❌ [${source}] Failed to fetch balance:`, error.message);
            return this.currentBalance;
            
        } finally {
            this.balanceFetchInProgress = false;
            this.hideBalanceLoading();
        }
    }
    
    showBalanceLoading() {
        const el = document.getElementById('displayBalance');
        if (el) el.innerHTML = '<span style="opacity: 0.7;">Loading...</span>';
    }
    
    hideBalanceLoading() { this.updateBalanceDisplay(); }
    
    setBalanceImmediate(newBalance, source = 'manual') {
        const parsed = parseFloat(newBalance) || 0;
        this.currentBalance = parsed;
        this.updateBalanceDisplay();
    }

    async restoreUserSession(hood) {
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
            
            if(!sessionData || !sessionData.authenticated || !sessionData.playerPhone) { 
                this.clearUserSession(); 
                this.showLoginModal(); 
                return; 
            }
            
            // ✅ CHECK BLOCK STATUS ON SESSION RESTORE
            console.log('🔍 Checking block status for session restore...');
            const { data: userData, error: userError } = await supabaseClient
                .from('users')
                .select('id, phone, name, balance, block_status')
                .eq('phone', sessionData.playerPhone)
                .single();
            
            if (userError || !userData) {
                console.error('❌ User not found or error:', userError);
                this.clearUserSession();
                this.showLoginModal();
                showToastMessage('User account not found.', 'error');
                return;
            }
            
            // ✅✅✅ BLOCK STATUS CHECK ✅✅✅
            if (userData.block_status === 'blocked') {
                console.error('🚫 USER IS BLOCKED - Session restore denied!');
                this.clearUserSession();
                this.showLoginModal();
                showToastMessage('⛔ Your account has been BLOCKED. Contact support.', 'error');
                soundEngine.errorSound();
                return;
            }
            
            console.log('✅ User is NOT blocked, restoring session...');
            
            this.sessionToken = sessionStorage.getItem('crazyStack_session_token');
            
            if(!this.sessionToken) { 
                this.clearUserSession(); 
                this.showLoginModal(); 
                showToastMessage('Session invalid, please log in again.', 'error'); 
                return; 
            }

            this.isUserAuthenticated = true; 
            this.playerName = userData.name || ''; 
            this.playerPhone = userData.phone;
            
            await this.forceRefreshBalance('session-restore');
            
            this.updateAuthenticationUI(); 
            this.hideModal('authModalOverlay');
            
            const msg = hood 
                ? `Ready! Your balance: MWK ${this.currentBalance.toFixed(2)}`
                : `Welcome back, ${this.playerName}! Balance: MWK ${this.currentBalance.toFixed(2)}`;
            showToastMessage(msg, 'success');
            document.getElementById('paymentPhoneInput').value=this.playerPhone;
            
            this.startSessionMonitoring();
            
        } catch(error) { 
            console.error('❌ Session restoration failed:', error);
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
                    // Check session AND block status from database
                    const {data, error} = await supabaseClient.from('users')
                        .select('session_token, block_status')
                        .eq('phone', this.playerPhone)
                        .single();
                        
                    if(error || !data) {
                        console.error('Session check error:', error);
                        return;
                    }
                    
                    // ✅ Check if session token changed (logged out elsewhere)
                    if(data.session_token !== this.sessionToken) {
                        console.log('⏰ Session invalidated - forcing logout');
                        this.forceLogoutDueToTimeout();
                        return;
                    }
                    
                    // ✅ CHECK IF USER WAS BLOCKED DURING ACTIVE SESSION
                    if(data.block_status === 'blocked') {
                        console.log('🚫 User was BLOCKED during session - forcing logout');
                        showToastMessage('⛔ Your account has been blocked by administrator.', 'error');
                        this.forceLogoutDueToTimeout();
                        return;
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
        } catch(e) {
            console.error('❌ Failed to save session:', e);
        }
    }
    
    clearUserSession() { 
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
        console.log('⚠️ FORCE LOGOUT');
        
        this.stopSessionMonitoring();
        
        if(this.playerPhone && this.sessionToken) { 
            supabaseClient.from('users')
                .update({session_token: null})
                .eq('phone', this.playerPhone)
                .eq('session_token', this.sessionToken)
                .then(({error}) => {
                    if(error) console.error('Failed to clear server token:', error);
                });
        }
        
        this.setBalanceImmediate(0, 'logout');
        this.clearUserSession(); 
        this.isUserAuthenticated = false; 
        this.playerName = ''; 
        this.playerPhone = '';
        this.sessionToken = null;
        
        this.updateAuthenticationUI(); 
        this.showModal('authModalOverlay');
        
        isTabTransitioning = false; 
        currentActiveTab = 'login'; 
        switchToTab('loginPanel');
        
        const at=document.getElementById('authModalTitle'),
              ad=document.getElementById('authModalDesc');
              
        if(at) at.textContent='Access Denied'; 
        if(ad) ad.textContent='Your account access has been revoked.';
        
        showToastMessage('Access denied', 'error'); 
        soundEngine.errorSound();
    }

    attachAllEventListeners() {
        document.querySelectorAll('.color-option').forEach(b => 
            b.addEventListener('click', () => this.selectBettingColor(b.dataset.color))
        );
        
        document.getElementById('spinAction')?.addEventListener('click', () => this.executeSpin());
        document.getElementById('authButton')?.addEventListener('click', () => this.handleAuthenticationToggle());
        
        document.querySelectorAll('.tab-item').forEach(b => 
            b.addEventListener('click', () => switchToTab(b.dataset.target))
        );
        
        document.getElementById('executeLogin')?.addEventListener('click', () => this.processLogin());
        document.getElementById('executeSignup')?.addEventListener('click', () => this.processSignup());
        
        document.getElementById('dismissAuthModal')?.addEventListener('click', () => this.hideModal('authModalOverlay'));
        document.querySelectorAll('.auth-switcher strong').forEach(l => 
            l.addEventListener('click', () => switchToTab(l.dataset.target))
        );
        
        document.getElementById('depositBtn')?.addEventListener('click', () => this.openPaymentModal('deposit'));
        document.getElementById('withdrawBtn')?.addEventListener('click', () => this.openPaymentModal('withdraw'));
        document.getElementById('confirmPayment')?.addEventListener('click', (e) => this.processPaymentTransaction(e));
        document.getElementById('dismissPaymentModal')?.addEventListener('click', () => this.hideModal('paymentModalOverlay'));
        
        document.querySelectorAll('.provider-choice').forEach(b => 
            b.addEventListener('click', () => this.selectPaymentProvider(b.dataset.provider))
        );
        
        document.getElementById('paymentPhoneInput')?.addEventListener('input', () => this.autoDetectNetworkProvider());
        
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
            soundEngine.setMusicVolume(parseInt(e.value) / 100)
        );
        
        document.getElementById('logoutSettingEntry')?.addEventListener('click', () => { 
            this.toggleSettingsPopover(false); 
            showCustomConfirm('Are you sure you want to logout?', () => this.executeLogout()); 
        });
        
        document.addEventListener('click', (e) => { 
            const p=document.getElementById('settingsPopover'),
                  f=document.getElementById('fabSettings');
                  
            if(p && p.classList.contains('visible') && !p.contains(e.target) && !f.contains(e.target)) {
                this.toggleSettingsPopover(false);
            } 
        });
        
        window.addEventListener('resize', () => this.renderWheelGraphic());
    }

    updateBalanceDisplay() { 
        const el = document.getElementById('displayBalance'); 
        if(el) {
            el.textContent = `MWK ${this.currentBalance.toFixed(2)}`;
            el.style.transition = 'transform 0.15s ease-out';
            el.style.transform = 'scale(1.02)';
            setTimeout(() => { el.style.transform = 'scale(1)'; }, 150);
        }
    }
    
    updateAuthenticationUI() {
        const b = document.getElementById('authButton'); 
        if(!b) return;
        
        if(this.isUserAuthenticated) { 
            b.innerHTML = '<i class="bi bi-person-check-fill"></i>'; 
            b.classList.remove('guest'); 
            b.title = `${this.playerName} - Click to logout`; 
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
            
            const { data: nB, error: uEr } = await supabaseClient.rpc('update_balance_by_phone', {
                p_phone: phone,
                p_amount: nA
            });
            
            if(uEr) throw uEr; 
            
            return parseFloat(nB) || 0;
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

            this.updateDatabaseBalance(pA, this.playerPhone)
                .then(async (nB) => {
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
                })
                .catch(err => {
                    console.error('Win processing error:', err);
                });

        } else {
            this.updateDatabaseBalance(-betAmount, this.playerPhone)
                .then(async (nB) => {
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
    // ✅✅✅ LOGIN - WITH PROPER BLOCK CHECK ✅✅✅
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
            
            // Normalize phone
            const localPhone = toLocalPhoneFormat(pI);
            
            console.log('🔐 ATTEMPTING LOGIN');
            console.log('Phone:', localPhone);

            document.getElementById('paymentPhoneInput').value=pI;
            
            // ============================================
            // ✅ STEP 1: CHECK IF USER EXISTS & GET BLOCK STATUS
            // ============================================
            console.log('📋 STEP 1: Checking user existence and block status...');
            
            const { data: userRecord, error: userCheckError } = await supabaseClient
                .from('users')
                .select('id, phone, name, password, balance, block_status, created_at')
                .eq('phone', localPhone)
                .maybeSingle();
            
            if (userCheckError) {
                console.error('❌ Database error checking user:', userCheckError);
                showToastMessage('Database error. Please try again.', 'error');
                lB.disabled=false;
                lB.textContent='Sign In';
                return;
            }
            
            // Check if user exists
            if (!userRecord) {
                console.log('❌ User not found with phone:', localPhone);
                showToastMessage('Account not found. Please sign up first.', 'error');
                lB.disabled=false;
                lB.textContent='Sign In';
                return;
            }
            
            console.log('✅ User found:', userRecord.phone);
            console.log('   Name:', userRecord.name);
            console.log('   Block Status:', userRecord.block_status);
            
            // ============================================
            // ✅✅✅ STEP 2: CHECK BLOCK STATUS ✅✅✅
            // ============================================
            console.log('🚦 STEP 2: Checking block status...');
            
            if (userRecord.block_status === 'blocked') {
                // 🚫🚫🚫 USER IS BLOCKED - STOP IMMEDIATELY! 🚫🚫🚫
                console.error('═══════════════════════════════════════');
                console.error('🚫🚫🚫 ACCESS DENIED - USER IS BLOCKED 🚫🚫🚫');
                console.error('═══════════════════════════════════════');
                console.error('Phone:', localPhone);
                console.error('Name:', userRecord.name);
                console.error('Block Status:', userRecord.block_status);
                console.error('Blocked Since:', userRecord.updated_at);
                console.error('═══════════════════════════════════════');
                
                // Show error to user
                showToastMessage('⛔ ACCOUNT BLOCKED! Contact administrator.', 'error');
                soundEngine.errorSound();
                
                // Reset button
                lB.disabled = false;
                lB.textContent = 'Sign In';
                
                // ❌❌❌ DO NOT CONTINUE - RETURN IMMEDIATELY ❌❌❌
                return;
            }
            
            // ✅ User is NOT blocked - continue with password verification
            console.log('✅ Block status check PASSED - User is active');
            
            // ============================================
            // STEP 3: VERIFY PASSWORD
            // ============================================
            console.log('🔑 STEP 3: Verifying password...');
            
            // Simple password comparison (adjust if you use hashing)
            if (userRecord.password !== pnI) {
                console.log('❌ Invalid password');
                showToastMessage('Invalid phone number or PIN', 'error');
                lB.disabled=false;
                lB.textContent='Sign In';
                return;
            }
            
            console.log('✅ Password verified successfully');
            
            // ============================================
            // STEP 4: UPDATE SESSION TOKEN IN DATABASE
            // ============================================
            console.log('🎫 STEP 4: Updating session token...');
            
            const { error: updateError } = await supabaseClient
                .from('users')
                .update({ 
                    session_token: newToken,
                    updated_at: new Date().toISOString()
                })
                .eq('phone', localPhone);
            
            if (updateError) {
                console.error('❌ Failed to update session token:', updateError);
                showToastMessage('Login error. Please try again.', 'error');
                lB.disabled=false;
                lB.textContent='Sign In';
                return;
            }
            
            console.log('✅ Session token updated in database');
            
            // ============================================
            // ✅ STEP 5: COMPLETE LOGIN SUCCESS
            // ============================================
            console.log('🎉 LOGIN SUCCESSFUL!');
            console.log('   Phone:', localPhone);
            console.log('   Name:', userRecord.name);
            console.log('   Balance:', userRecord.balance);
            console.log('   Block Status:', userRecord.block_status);
            
            // Set session
            this.sessionToken = newToken;
            sessionStorage.setItem('crazyStack_session_token', newToken);
            
            // Set user data
            this.isUserAuthenticated = true;
            this.playerName = userRecord.name || localPhone;
            this.playerPhone = localPhone;
            
            // Set balance
            const loginBalance = parseFloat(userRecord.balance) || 0;
            this.setBalanceImmediate(loginBalance, 'login-response');
            
            // Save session locally
            this.saveUserSession(localPhone, this.playerName, this.currentBalance);
            
            // Update UI
            this.updateAuthenticationUI();
            this.hideModal('authModalOverlay');
            
            // Show success message
            showToastMessage(
                `Welcome back, ${this.playerName}! Balance: MWK ${this.currentBalance.toFixed(2)}`,
                'success'
            );
            
            // Start session monitoring
            this.startSessionMonitoring();
            
            // Verify balance after delay
            setTimeout(async () => {
                console.log('🔄 Verifying balance...');
                await this.forceRefreshBalance('login-verify');
                console.log(`✅ Final Balance: MWK ${this.currentBalance.toFixed(2)}`);
            }, 1500);
            
        } catch(error) { 
            console.error('❌ Login error:',error); 
            showToastMessage(`Login Error: ${error.message}`,'error'); 
        } finally { 
            lB.disabled=false;
            lB.textContent='Sign In'; 
        }
    }

    // =============================================
    // SIGNUP
    // =============================================
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
            const localPhone = toLocalPhoneFormat(pI);
            
            console.log('📝 Creating account...');
            
            const { data: success, error: rpcError } = await supabaseClient.rpc('signup_new_user', {
                p_phone: localPhone,
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
                this.playerPhone=localPhone;
                
                this.setBalanceImmediate(0, 'signup');
                
                this.saveUserSession(localPhone, nI, 0);
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
    // PAYMENT PROCESSING - WITH TIMEOUT FIX
    // =============================================
    async processPaymentTransaction(event) {
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

        try {
            const response = await fetch('mobile_pay.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(requestData)
            });
            
            const text = await response.text();
            let rD;
            
            try { 
                rD = JSON.parse(text); 
            } catch(e) { 
                throw new Error("Server unavailable"); 
            }
            
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
            const mP=60; // Max polling attempts
            const app=this;
            
            const balanceBefore = app.currentBalance;
            
            // ── POLLING WITH TIMEOUT ──
            const pI = setInterval(async()=>{
                pA++;
                
                // ✅ FIX: Stop when limit reached
                if (pA > mP) {
                    console.log(`⏱️ POLLING TIMEOUT after ${mP} attempts`);
                    clearInterval(pI);
                    pB.textContent = 'Timeout';
                    showToastMessage('⏱️ Payment confirmation timed out. Contact support.', 'warning');
                    duplicatePrevention.releaseProcessingLock();
                    setTimeout(() => { 
                        pB.textContent = oT; 
                        pB.disabled = false; 
                    }, 3500);
                    return;
                }
                
                try{
                    // Method 1: Check payments table
                    const {data:pRec, error: pollError} = await supabaseClient.from('payments')
                        .select('status,amount,charge_id')
                        .eq('charge_id',tR)
                        .maybeSingle();
                        
                    if(pollError) {
                        console.error('❌ Poll error:', pollError);
                    }
                    
                    // Method 2: Fallback balance check
                    let foundSuccessByBalance = false;
                    
                    if (!pRec || pRec.status !== 'success') {
                        await app.forceRefreshBalance('polling-' + pA);
                        
                        const currentBal = app.currentBalance;
                        
                        if (currentBal >= (balanceBefore + (tA * 0.95))) {
                            console.log(`✅ Success by balance increase!`);
                            foundSuccessByBalance = true;
                            duplicatePrevention.markTransactionProcessed(tR);
                        }
                    }
                    
                    // Handle success
                    if ((pRec && pRec.status === 'success') || foundSuccessByBalance) {
                        
                        console.log('\n✅ PAYMENT CONFIRMED!');
                        console.log(`   Attempts: ${pA}/${mP}`);
                        
                        clearInterval(pI);
                        duplicatePrevention.markTransactionProcessed(tR);
                        pB.textContent = 'Complete';
                        
                        const paymentAmount = parseFloat(pRec?.amount) || tA;
                        
                        showToastMessage('✅ Payment received!', 'success');
                        
                        try {
                            await app.forceRefreshBalance('payment-success-final');
                            
                            let successMsg;
                            if(app.currentTransactionType === 'deposit') {
                                successMsg = `💰 Deposit of MWK ${paymentAmount.toFixed(2)} received!\nNew Balance: MWK ${app.currentBalance.toFixed(2)}`;
                            } else {
                                successMsg = `💸 Withdrawal of MWK ${paymentAmount.toFixed(2)} processed!\nNew Balance: MWK ${app.currentBalance.toFixed(2)}`;
                            }
                            
                            showToastMessage(successMsg, 'success');
                            
                            setTimeout(() => {
                                app.hideModal('paymentModalOverlay');
                            }, 2000);
                            
                            duplicatePrevention.releaseProcessingLock();
                            
                            setTimeout(() => { 
                                pB.textContent = oT; 
                                pB.disabled = false; 
                            }, 3500);
                            
                        } catch (refreshErr) {
                            console.error('❌ Balance refresh failed:', refreshErr);
                            
                            showToastMessage(
                                `✅ Payment confirmed! MWK ${paymentAmount.toFixed(2)}`,
                                'success'
                            );
                            
                            setTimeout(() => {
                                app.hideModal('paymentModalOverlay');
                            }, 2000);
                            
                            duplicatePrevention.releaseProcessingLock();
                            setTimeout(() => { 
                                pB.textContent = oT; 
                                pB.disabled = false; 
                            }, 3500);
                        }
                        
                    } else if (pRec && pRec.status === 'failed') {
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
                        if(pA%10===0) {
                            console.log(`⏳ Waiting... (${pA}/${mP})`);
                            showToastMessage(`Waiting for confirmation... (${pA}/${mP})`,'info');
                        } 
                        pB.textContent=`Checking... (${pA}/${mP})`;
                    }
                        
                } catch(e){
                    console.error('❌ Polling error:',e);
                }
            }, 1000);
            
            duplicatePrevention.registerPollingSession(tR, pI);
            
        } catch(fE) {
            console.error('❌ Fetch error:', fE);
            pB.textContent = oT;
            pB.disabled = false;
            duplicatePrevention.releaseProcessingLock();
            showToastMessage(
                fE.message.includes("Server unavailable") 
                    ? 'Payment Backend Error.' 
                    : 'Connection error - Try again',
                'error'
            );
        }
    }

    toggleSettingsPopover(state=null){
        const p=document.getElementById('settingsPopover'),
              s=state!==null?state:!p.classList.contains('visible');
        p.classList.toggle('visible',s);
    }
}

//=============================================
// INITIALIZATION
//=============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎮 INITIALIZING CRAZYSTACK');
    
    window.crazyStackApp = new CrazyStackApp();
    
    console.log('✅ Application initialized');
    
    setTimeout(()=>{
        soundEngine.updateSfxToggleUI(soundEngine.isEnabled);
        soundEngine.updateMusicToggleUI(soundEngine.bgMusicEnabled);
        themeManager.updateToggleUI(themeManager.currentTheme==='light');
        
        const vs=document.getElementById('musicVolumeSlider');
        if(vs) vs.value=Math.round(soundEngine.bgMusicVolume*100);
    },1000);
});

window.addEventListener("load", () => {
    setTimeout(() => {
        if(window.crazyStackApp) {
            crazyStackApp.restoreUserSession(true);
        }
    }, 500);
});








/**
 * Displays a floating notification (toast)
 * @param {string} message - The text to display
 * @param {string} type - 'info', 'success', 'warning', or 'error'
 */
function showNotification(message, type = 'info') {
  // 1. Create the container element if it doesn't exist yet
  let container = document.getElementById('notification-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
    document.body.appendChild(container);
  }

  // 2. Create the notification box
  const notification = document.createElement('div');
  
  // Add base class and specific type class
  notification.className = `crz-notification ${type}`;
  
  // Set the icon based on type
  let icon = '🔔'; // Default info
  if (type === 'success') icon = '✅';
  if (type === 'warning') icon = '⚠️';
  if (type === 'error') icon = '❌';

  notification.innerHTML = `<span class="icon">${icon}</span><span class="text">${message}</span>`;

  // 3. Append to container
  container.appendChild(notification);

  // 4. Trigger animation (slight delay to allow DOM render)
  requestAnimationFrame(() => {
    notification.classList.add('show');
  });

  // 5. Remove after 4 seconds
  setTimeout(() => {
    notification.classList.remove('show');
    // Wait for fade out animation to finish before removing from DOM
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 500); // 500ms matches the CSS transition duration
  }, 10000);
}









// 1. YOU MUST DECLARE THIS VARIABLE HERE (Outside the interval)
let lastMessage = ""; 

setInterval(() => {
  supabaseClient
    .from('notice')
    .select('message, type, created_at')
    .order('created_at', { ascending: false })
    .limit(1)
    .then(({ data, error }) => {
      
      if (error) {
        console.error("Error:", error);
        return;
      }

      // Debug logs (You can remove these later if you want)
      console.log("Raw Data from DB:", data);

      if (data && data.length > 0) {
        const row = data[0];
        const note = row.message;

        // This check prevents the crash if message is somehow still empty
        if (!note) {
           console.warn("Message was empty");
           return;
        }

        // 2. NOW THIS LINE WILL WORK
        // It compares the new note with the variable defined above
        if (note !== lastMessage) {
          lastMessage = note; 
          showNotification(note, row.type);
          console.log("Toast shown:", note);
        } else {
           console.log("Message hasn't changed, skipping toast.");
        }
      }
    });

}, 10000);