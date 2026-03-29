// API Configuration
const API_BASE = '/primebank/php/';
let currentUser = null;
let loginOtpRequired = false;

// Toast Notification
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'toast' + (isError ? ' error' : '');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// API Request
async function apiRequest(endpoint, data) {
    try {
        const response = await fetch(API_BASE + endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        console.log(endpoint + ' response:', result);
        return result;
    } catch (error) {
        console.error('API Error:', error);
        showToast('Network error: ' + error.message, true);
        return null;
    }
}

// Send OTP
async function sendOTP(email, purpose) {
    const result = await apiRequest('send_otp.php', { email, purpose });
    if (result && result.success) {
        showToast(result.message);
        return true;
    }
    if (result) showToast(result.message, true);
    return false;
}

// ========== LOGIN - FIXED VERSION ==========
document.getElementById('loginBtn').addEventListener('click', async function() {
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;
    const otp = document.getElementById('loginOtp').value.trim();
    
    console.log('=== LOGIN DEBUG ===');
    console.log('Email:', email);
    console.log('loginOtpRequired flag:', loginOtpRequired);
    console.log('OTP entered:', otp);
    
    if (!email || !password) {
        showToast('Enter email and password', true);
        return;
    }
    
    const btn = this;
    const otpSection = document.getElementById('loginOtpSection');
    const otpInput = document.getElementById('loginOtp');
    
    // CASE 1: OTP is required AND user has entered OTP
    if (loginOtpRequired && otp) {
        btn.disabled = true;
        btn.textContent = 'Verifying...';
        
        const result = await apiRequest('login.php', { email, password, otp });
        
        if (result && result.success) {
            currentUser = result.data;
            localStorage.setItem('primebank_session', JSON.stringify(currentUser));
            showToast('Login successful!');
            await loadDashboard();
        } else if (result) {
            showToast(result.message, true);
            if (otpInput) otpInput.value = '';
            btn.disabled = false;
            btn.textContent = 'Verify OTP';
        }
        return;
    }
    
    // CASE 2: First step - Request OTP
    btn.disabled = true;
    btn.textContent = 'Sending OTP...';
    
    const result = await apiRequest('login.php', { email, password });
    console.log('Login API result:', result);
    
    if (result && result.require_otp) {
        // CRITICAL: Show the OTP section
        loginOtpRequired = true;
        
        // Method 1: Remove hidden class
        if (otpSection) {
            otpSection.classList.remove('hidden');
            console.log('Removed hidden class - OTP section should now show');
        }
        
        // Method 2: Direct style override (backup)
        if (otpSection) {
            otpSection.style.display = 'block';
            console.log('Set display to block');
        }
        
        // Change button text
        btn.textContent = 'Verify OTP';
        btn.disabled = false;
        
        // Clear and focus OTP input
        if (otpInput) {
            otpInput.value = '';
            otpInput.focus();
        }
        
        showToast('OTP sent to your email! Check spam folder.', false);
        
    } else if (result && result.success) {
        // Demo account login (no OTP required)
        currentUser = result.data;
        localStorage.setItem('primebank_session', JSON.stringify(currentUser));
        showToast('Login successful!');
        await loadDashboard();
    } else if (result) {
        showToast(result.message, true);
        btn.disabled = false;
        btn.textContent = 'Login';
    } else {
        btn.disabled = false;
        btn.textContent = 'Login';
    }
});

// Resend OTP Button
document.getElementById('resendOtpBtn').addEventListener('click', async () => {
    const email = document.getElementById('loginEmail').value.trim();
    if (!email) {
        showToast('Enter email first', true);
        return;
    }
    await sendOTP(email, 'login');
    document.getElementById('loginOtp').value = '';
    document.getElementById('loginOtp').focus();
});

// ========== REGISTER ==========
document.getElementById('sendRegOtpBtn').addEventListener('click', async () => {
    const email = document.getElementById('regEmail').value.trim();
    if (!email) {
        showToast('Enter email first', true);
        return;
    }
    await sendOTP(email, 'register');
});

document.getElementById('registerBtn').addEventListener('click', async () => {
    const name = document.getElementById('regName').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const phone = document.getElementById('regPhone').value.trim();
    const password = document.getElementById('regPassword').value;
    const otp = document.getElementById('regOtp').value.trim();
    
    if (!name || !email || !phone || !password || !otp) {
        showToast('Please fill all fields', true);
        return;
    }
    
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Registering...';
    
    const result = await apiRequest('register.php', { name, email, phone, password, otp });
    
    if (result && result.success) {
        showToast(result.message);
        document.getElementById('showLoginLink').click();
        // Clear form
        document.getElementById('regName').value = '';
        document.getElementById('regEmail').value = '';
        document.getElementById('regPhone').value = '';
        document.getElementById('regPassword').value = '';
        document.getElementById('regOtp').value = '';
    } else if (result) {
        showToast(result.message, true);
    }
    
    btn.disabled = false;
    btn.textContent = 'Register';
});

// ========== DASHBOARD ==========
async function loadDashboard() {
    const session = localStorage.getItem('primebank_session');
    if (!session) {
        showAuth();
        return;
    }
    
    const userResult = await apiRequest('get_user.php', {});
    if (!userResult || !userResult.success) {
        localStorage.removeItem('primebank_session');
        showAuth();
        return;
    }
    
    currentUser = userResult.data;
    
    document.getElementById('dashUserName').innerHTML = currentUser.name;
    document.getElementById('balanceAmount').innerHTML = `₹${Number(currentUser.balance).toFixed(2)}`;
    
    document.getElementById('profileInfo').innerHTML = `
        <p><i class="fas fa-user"></i> ${currentUser.name}</p>
        <p><i class="fas fa-envelope"></i> ${currentUser.email}</p>
        <p><i class="fas fa-phone"></i> ${currentUser.phone}</p>
        <p><i class="fas fa-credit-card"></i> ${currentUser.account_number}</p>
        <p><i class="fas fa-qrcode"></i> ${currentUser.upi_id}</p>
        <p><i class="fas fa-key"></i> ${currentUser.has_txn_pin ? '✓ PIN Set' : '✗ Not set'}</p>
    `;
    
    const transResult = await apiRequest('get_transactions.php', {});
    const container = document.getElementById('transactionsList');
    
    if (transResult && transResult.success && transResult.data.length > 0) {
        container.innerHTML = transResult.data.slice(0, 20).map(t => `
            <div class="transaction-item">
                <div>
                    <small style="color:#64748b;">${t.date}</small>
                    <div style="font-size:13px;">${t.description}</div>
                </div>
                <div class="transaction-${t.type.toLowerCase()}">
                    ${t.type === 'Credit' ? '+' : '-'} ₹${Number(t.amount).toFixed(2)}
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = '<div style="text-align:center; padding:20px;">No transactions yet</div>';
    }
    
    document.getElementById('authContainer').style.display = 'none';
    document.getElementById('dashboard').style.display = 'block';
}

function showAuth() {
    document.getElementById('authContainer').style.display = 'block';
    document.getElementById('dashboard').style.display = 'none';
    
    // Reset login state
    loginOtpRequired = false;
    const otpSection = document.getElementById('loginOtpSection');
    if (otpSection) {
        otpSection.classList.add('hidden');
        otpSection.style.display = 'none';
    }
    
    const loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.disabled = false;
        loginBtn.textContent = 'Login';
    }
    
    // Clear inputs
    document.getElementById('loginEmail').value = '';
    document.getElementById('loginPassword').value = '';
    const otpInput = document.getElementById('loginOtp');
    if (otpInput) otpInput.value = '';
}

// ========== ACTION BUTTONS ==========
document.getElementById('addMoneyBtn').addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3 style="margin-bottom:16px;">Add Money</h3>
            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" id="addAmount" min="1" max="100000" placeholder="Enter amount">
            </div>
            <button class="btn btn-primary" id="confirmAdd" style="margin-bottom:10px;">Add Money</button>
            <button class="btn btn-outline" id="closeModal">Cancel</button>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('confirmAdd').onclick = async () => {
        const amount = parseFloat(document.getElementById('addAmount').value);
        if (isNaN(amount) || amount <= 0) {
            showToast('Invalid amount', true);
            return;
        }
        const result = await apiRequest('add_money.php', { amount });
        if (result && result.success) {
            showToast(result.message);
            modal.remove();
            loadDashboard();
        } else if (result) {
            showToast(result.message, true);
        }
    };
    document.getElementById('closeModal').onclick = () => modal.remove();
});

document.getElementById('sendMoneyBtn').addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3 style="margin-bottom:16px;">Send Money</h3>
            <div class="form-group">
                <label>Send via</label>
                <select id="sendMethod">
                    <option value="account">Account Number</option>
                    <option value="phone">Phone Number</option>
                    <option value="upi">UPI ID</option>
                </select>
            </div>
            <div class="form-group">
                <label>Recipient</label>
                <input type="text" id="recipientId" placeholder="Enter account/phone/UPI">
            </div>
            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" id="sendAmount" min="1" placeholder="Enter amount">
            </div>
            <div class="form-group">
                <label>Transaction PIN</label>
                <input type="password" id="txnPin" maxlength="6" placeholder="Enter PIN">
            </div>
            <button class="btn btn-primary" id="confirmSend" style="margin-bottom:10px;">Send Money</button>
            <button class="btn btn-outline" id="closeModal">Cancel</button>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('confirmSend').onclick = async () => {
        const method = document.getElementById('sendMethod').value;
        const recipient = document.getElementById('recipientId').value.trim();
        const amount = parseFloat(document.getElementById('sendAmount').value);
        const txnPin = document.getElementById('txnPin').value;
        
        if (!recipient || isNaN(amount) || !txnPin) {
            showToast('Fill all fields', true);
            return;
        }
        
        const sendBtn = document.getElementById('confirmSend');
        sendBtn.disabled = true;
        sendBtn.textContent = 'Processing...';
        
        const result = await apiRequest('send_money.php', { method, recipient, amount, txn_pin: txnPin });
        
        if (result && result.success) {
            showToast(result.message);
            modal.remove();
            loadDashboard();
        } else if (result) {
            showToast(result.message, true);
        }
        
        sendBtn.disabled = false;
        sendBtn.textContent = 'Send Money';
    };
    document.getElementById('closeModal').onclick = () => modal.remove();
});

document.getElementById('changePasswordBtn').addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3 style="margin-bottom:16px;">Change Password</h3>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" id="currentPwd" placeholder="Enter current password">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" id="newPwd" placeholder="Min 4 characters">
            </div>
            <button class="btn btn-primary" id="updatePwd" style="margin-bottom:10px;">Update</button>
            <button class="btn btn-outline" id="closeModal">Cancel</button>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('updatePwd').onclick = async () => {
        const current = document.getElementById('currentPwd').value;
        const newPwd = document.getElementById('newPwd').value;
        
        if (!current || !newPwd) {
            showToast('Fill all fields', true);
            return;
        }
        
        const result = await apiRequest('change_password.php', { current_password: current, new_password: newPwd });
        if (result && result.success) {
            showToast(result.message);
            modal.remove();
        } else if (result) {
            showToast(result.message, true);
        }
    };
    document.getElementById('closeModal').onclick = () => modal.remove();
});

document.getElementById('setTxnPinBtn').addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3 style="margin-bottom:16px;">Set Transaction PIN</h3>
            <div class="form-group">
                <label>PIN (4-6 digits)</label>
                <input type="password" id="newPin" maxlength="6" placeholder="Enter 4-6 digit PIN">
            </div>
            <div class="form-group">
                <label>Confirm PIN</label>
                <input type="password" id="confirmPin" maxlength="6" placeholder="Confirm PIN">
            </div>
            <button class="btn btn-primary" id="setPin" style="margin-bottom:10px;">Set PIN</button>
            <button class="btn btn-outline" id="closeModal">Cancel</button>
        </div>
    `;
    document.body.appendChild(modal);
    
// Set Transaction PIN - FIXED VERSION
document.getElementById('setTxnPinBtn').addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3 style="margin-bottom:16px;">Set Transaction PIN</h3>
            <div class="form-group">
                <label>Enter New PIN (4-6 digits)</label>
                <input type="password" id="newPin" maxlength="6" placeholder="Enter 4-6 digit PIN" autocomplete="off">
                <small style="color: #666; display: block; margin-top: 5px;">Example: 1234</small>
            </div>
            <div class="form-group">
                <label>Confirm PIN</label>
                <input type="password" id="confirmPin" maxlength="6" placeholder="Confirm your PIN" autocomplete="off">
            </div>
            <button class="btn btn-primary" id="setPin" style="margin-bottom:10px;">Set Transaction PIN</button>
            <button class="btn btn-outline" id="closeModal">Cancel</button>
        </div>
    `;
    document.body.appendChild(modal);
    
    document.getElementById('setPin').onclick = async () => {
        const pin = document.getElementById('newPin').value;
        const confirm = document.getElementById('confirmPin').value;
        
        if (!pin) {
            showToast('Please enter a PIN', true);
            return;
        }
        
        if (pin !== confirm) {
            showToast('PINs do not match', true);
            return;
        }
        
        if (!/^\d{4,6}$/.test(pin)) {
            showToast('PIN must be 4-6 digits only (0-9)', true);
            return;
        }
        
        const setBtn = document.getElementById('setPin');
        setBtn.disabled = true;
        setBtn.textContent = 'Setting PIN...';
        
        console.log('Setting PIN:', pin);
        
        const result = await apiRequest('set_txn_pin.php', { txn_pin: pin });
        
        console.log('Set PIN result:', result);
        
        if (result && result.success) {
            showToast(result.message);
            modal.remove();
            // Reload dashboard to show PIN is set
            setTimeout(() => loadDashboard(), 1000);
        } else if (result) {
            showToast(result.message, true);
        } else {
            showToast('Failed to set PIN. Please try again.', true);
        }
        
        setBtn.disabled = false;
        setBtn.textContent = 'Set Transaction PIN';
    };
    
    document.getElementById('closeModal').onclick = () => modal.remove();
});

// ========== LOGOUT & NAVIGATION ==========
document.getElementById('logoutBtn').addEventListener('click', async () => {
    await apiRequest('logout.php', {});
    localStorage.removeItem('primebank_session');
    currentUser = null;
    showToast('Logged out');
    showAuth();
});

document.getElementById('showRegisterLink').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('loginFormDiv').classList.add('hidden');
    document.getElementById('registerFormDiv').classList.remove('hidden');
});

document.getElementById('showLoginLink').addEventListener('click', (e) => {
    e.preventDefault();
    document.getElementById('loginFormDiv').classList.remove('hidden');
    document.getElementById('registerFormDiv').classList.add('hidden');
    
    // Reset login state
    loginOtpRequired = false;
    const otpSection = document.getElementById('loginOtpSection');
    if (otpSection) {
        otpSection.classList.add('hidden');
        otpSection.style.display = 'none';
    }
    const loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.disabled = false;
        loginBtn.textContent = 'Login';
    }
});

// ========== INITIALIZE ==========
document.addEventListener('DOMContentLoaded', async () => {
    console.log('Page loaded - checking session');
    const session = localStorage.getItem('primebank_session');
    if (session) {
        await loadDashboard();
    } else {
        showAuth();
    }
    
    // Debug check
    const otpSection = document.getElementById('loginOtpSection');
    console.log('OTP Section exists in DOM:', !!otpSection);
    if (otpSection) {
        console.log('OTP Section classes:', otpSection.className);
    }
});