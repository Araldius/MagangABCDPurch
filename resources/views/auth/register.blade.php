<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --navy-dark: #0f2942;
  --sky-blue: #0ea5e9;
  --bg: #F1F5F9;
  --surface: #FFFFFF;
  --surface2: #F9F8F6;
  --border: #E2E8F0;
  --border-focus: #0ea5e9;
  --text: #1A1C1E;
  --text-secondary: #64748B;
  --text-muted: #64748B;
  --accent: #0f2942;
  --accent-light: #EEF2FD;
  --danger: #EF4444;
  --danger-light: #FEF0EF;
  --success: #10B981;
  --success-light: #EDFAF3;
  --warning: #F59E0B;
  --warning-light: #FEF3C7;
  --shadow: 0 1px 2px rgba(0,0,0,0.05), 0 4px 16px rgba(0,0,0,0.10);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
}

/* ── LEFT PANEL ── */
.left-panel {
  width: 50%;
  min-height: 100vh;
  background: var(--navy-dark);
  position: sticky;
  top: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 2.5rem;
  overflow: hidden;
  flex-shrink: 0;
}
.left-panel::before {
  content: '';
  position: absolute;
  top: -120px; right: -120px;
  width: 420px; height: 420px;
  border-radius: 50%;
  background: rgba(14,165,233,0.10);
}
.left-panel::after {
  content: '';
  position: absolute;
  top: -60px; right: -180px;
  width: 360px; height: 360px;
  border-radius: 50%;
  border: 1px solid rgba(14,165,233,0.15);
}
.brand-row { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
.brand-logo-img { width: 46px; height: 46px; object-fit: contain; border-radius: 10px; }
.brand-name { font-size: 16px; font-weight: 600; color: white; }
.left-content { position: relative; z-index: 1; }
.left-accent-line { width: 36px; height: 3px; background: var(--sky-blue); border-radius: 2px; margin-bottom: 1.5rem; }
.left-heading { font-size: clamp(28px, 3vw, 38px); font-weight: 700; color: white; line-height: 1.2; letter-spacing: -0.02em; margin-bottom: 1rem; }
.left-heading span { color: var(--sky-blue); }
.left-sub { font-size: 14px; color: rgba(255,255,255,0.55); line-height: 1.6; max-width: 320px; }
.left-features { margin-top: 2rem; display: flex; flex-direction: column; gap: 12px; }
.left-feature { display: flex; align-items: center; gap: 10px; }
.left-feature-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--sky-blue); flex-shrink: 0; }
.left-feature-text { font-size: 13px; color: rgba(255,255,255,0.5); }
.left-footer { font-size: 12px; color: rgba(255,255,255,0.3); position: relative; z-index: 1; }

/* ── RIGHT PANEL ── */
.right-panel {
  flex: 1;
  min-height: 100vh;
  background: var(--bg);
  display: flex;
  justify-content: center;
  padding: 3rem 2.5rem;
}

.reg-box { width: 100%; max-width: 480px; }
.reg-header { margin-bottom: 2rem; }
.reg-title { font-size: 22px; font-weight: 600; letter-spacing: -0.02em; margin-bottom: 0.4rem; }
.reg-sub { font-size: 14px; color: var(--text-secondary); }

.card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 1.25rem; overflow: hidden;
}
.card-header {
  padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 10px; background: var(--surface2);
}
.card-icon { display: flex; align-items: center; justify-content: center; color: var(--accent); width: 20px; height: 20px; }
.card-title { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; color: var(--text-secondary); }
.card-body { padding: 1.5rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 1rem; }
.form-group:last-child { margin-bottom: 0; }
label { font-size: 11.5px; font-weight: 600; color: var(--text-secondary); letter-spacing: 0.04em; text-transform: uppercase; }
.req { color: var(--danger); margin-left: 2px; }

input, select {
  font-family: 'DM Sans', sans-serif; font-size: 14px;
  color: var(--text); background: var(--surface);
  border: 1px solid var(--border); border-radius: 8px;
  padding: 10px 12px; outline: none; width: 100%; transition: all 0.15s;
}
input:focus, select:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }

.input-wrap { position: relative; }
.input-wrap input { padding-right: 40px; }
.eye-btn {
  position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: var(--text-muted); display: flex; align-items: center; padding: 4px;
}

.role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 6px; }
.role-card {
  border: 1px solid var(--border); border-radius: 8px; padding: 12px;
  cursor: pointer; transition: all 0.15s;
  display: flex; align-items: center; gap: 10px; background: var(--surface);
}
.role-card:hover { border-color: var(--accent); background: var(--surface2); }
.role-card.selected { border-color: var(--accent); background: var(--accent-light); box-shadow: inset 0 0 0 1px var(--accent); }
.role-icon-box { color: var(--text-muted); transition: color 0.15s; }
.role-card.selected .role-icon-box { color: var(--accent); }
.role-name { font-size: 13px; font-weight: 600; display: block; }
.role-desc { font-size: 11px; color: var(--text-muted); }
.role-check { margin-left: auto; color: var(--accent); opacity: 0; }
.role-card.selected .role-check { opacity: 1; }

.pass-strength { margin-top: 8px; }
.strength-bar { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; margin-bottom: 6px; }
.strength-fill { height: 100%; width: 0%; transition: all 0.3s; }
.strength-label { font-size: 11px; font-weight: 500; }

.terms-row {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: 13px; color: var(--text-secondary); margin: 1.5rem 0;
}
.terms-row input[type="checkbox"] { width: 16px; height: 16px; margin-top: 1px; cursor: pointer; }
.terms-row a { color: var(--accent); text-decoration: none; font-weight: 500; }

.btn-register {
  width: 100%; padding: 12px;
  background: var(--accent); color: white;
  border: none; border-radius: 8px;
  font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 600;
  cursor: pointer; transition: all 0.2s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-register:hover { background: #1a3a5c; box-shadow: 0 4px 12px rgba(15,41,66,0.25); }

.login-link-row { text-align: center; font-size: 13.5px; color: var(--text-secondary); margin-top: 1.5rem; margin-bottom: 3rem; }
.login-link-row a { color: var(--accent); font-weight: 600; text-decoration: none; }

.err-msg {
  background: var(--danger-light); border: 1px solid #F5C6C3;
  border-radius: 8px; padding: 10px 14px;
  font-size: 13px; color: var(--danger);
  display: flex; align-items: center; gap: 8px; margin-bottom: 1.25rem;
}

@media (max-width: 768px) {
  body { flex-direction: column; }
  .left-panel { width: 100%; min-height: auto; height: auto; position: relative; padding: 2rem; }
  .left-panel::before, .left-panel::after { display: none; }
  .left-content { padding: 2rem 0 1rem; }
  .left-features { display: none; }
  .left-footer { display: none; }
  .right-panel { padding: 2rem 1.25rem; }
}
@media(max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="brand-row">
    <img src="{{ asset('img/Logo_DKJ.jpeg') }}" alt="DKJ Logo" class="brand-logo-img">
    <div class="brand-name">PT. Dunia Kimia Jaya</div>
  </div>

  <div class="left-content">
    <div class="left-accent-line"></div>
    <div class="left-heading">Create Your<br><span>Account</span></div>
    <div class="left-sub">Register to get access to the purchasing system and manage your procurement activities.</div>
    <div class="left-features">
      <div class="left-feature">
        <div class="left-feature-dot"></div>
        <div class="left-feature-text">Submit and track purchase requests</div>
      </div>
      <div class="left-feature">
        <div class="left-feature-dot"></div>
        <div class="left-feature-text">Manage purchase orders</div>
      </div>
      <div class="left-feature">
        <div class="left-feature-dot"></div>
        <div class="left-feature-text">Monitor procurement data in real-time</div>
      </div>
    </div>
  </div>

  <div class="left-footer">© 2026 PT. Dunia Kimia Jaya</div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <div class="reg-box">
    <div class="reg-header">
      <div class="reg-title">Create Account</div>
      <div class="reg-sub">Please complete the form to access the purchasing system</div>
    </div>

    @if($errors->any())
    <div class="err-msg">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
      @csrf
      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          <div class="card-title">Account Credentials</div>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label>Email Address <span class="req">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Password <span class="req">*</span></label>
              <div class="input-wrap">
                <input type="password" name="password" id="regPass" placeholder="Min. 8 characters" oninput="checkStrength()">
                <button class="eye-btn" type="button" onclick="togglePass('regPass',this)">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
              </div>
              <div class="pass-strength">
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                <div class="strength-label" id="strengthLabel">Password Strength</div>
              </div>
            </div>
            <div class="form-group">
              <label>Confirm Password <span class="req">*</span></label>
              <div class="input-wrap">
                <input type="password" name="password_confirmation" id="regPass2" placeholder="Repeat password">
                <button class="eye-btn" type="button" onclick="togglePass('regPass2',this)">
                  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
          <div class="card-title">Employee Profile</div>
        </div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name <span class="req">*</span></label>
              <input type="text" name="name" value="{{ old('name') }}" placeholder="As per ID card">
            </div>
            <div class="form-group">
              <label>Employee ID</label>
              <input type="text" name="emp_id" value="{{ old('emp_id') }}" placeholder="e.g. EMP-001">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Department <span class="req">*</span></label>
              <select name="dept">
                <option value="">Select Department</option>
                <option {{ old('dept')=='IT & Digital'?'selected':'' }}>IT & Digital</option>
                <option {{ old('dept')=='Operations'?'selected':'' }}>Operations</option>
                <option {{ old('dept')=='Finance'?'selected':'' }}>Finance</option>
                <option {{ old('dept')=='HR & GA'?'selected':'' }}>HR & GA</option>
                <option {{ old('dept')=='Marketing'?'selected':'' }}>Marketing</option>
                <option {{ old('dept')=='Production'?'selected':'' }}>Production</option>
                <option {{ old('dept')=='Maintenance'?'selected':'' }}>Maintenance</option>
                <option {{ old('dept')=='Other'?'selected':'' }}>Other</option>
              </select>
            </div>
            <div class="form-group">
              <label>Job Title</label>
              <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Staff / Manager">
            </div>
          </div>
          <div class="form-group">
            <label>Work Location (Plant)</label>
            <select name="plant">
              <option value="">Select Plant</option>
              <option {{ old('plant')=='Cikarang'?'selected':'' }}>Cikarang</option>
              <option {{ old('plant')=='Cibitung'?'selected':'' }}>Cibitung</option>
              <option {{ old('plant')=='Slipi'?'selected':'' }}>Slipi</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div class="card-title">System Access</div>
        </div>
        <div class="card-body">
          <div class="form-group" style="margin-bottom:0;">
            <label>User Role <span class="req">*</span></label>
            <div class="role-grid">
              <div class="role-card selected" onclick="selectRole(this,'user')" data-role="user">
                <div class="role-icon-box">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                  <span class="role-name">User</span>
                  <span class="role-desc">PR Submission</span>
                </div>
                <svg class="role-check" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              </div>
              <div class="role-card" onclick="selectRole(this,'purchasing')" data-role="purchasing">
                <div class="role-icon-box">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                  <span class="role-name">Purchasing</span>
                  <span class="role-desc">PO Management</span>
                </div>
                <svg class="role-check" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
              </div>
            </div>
            <input type="hidden" name="role" id="regRole" value="user">
          </div>
        </div>
      </div>

      <div class="terms-row">
        <input type="checkbox" id="termsCheck" required>
        <span>I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.</span>
      </div>

      <button class="btn-register" type="submit">
        Create Account
      </button>

      <div class="login-link-row">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
      </div>
    </form>
  </div>
</div>

<script>
  function selectRole(el, role) {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('regRole').value = role;
  }

  function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass ? 
      '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>' : 
      '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
  }

  function checkStrength() {
    const pass = document.getElementById('regPass').value;
    const fill = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (pass.length >= 8) score++;
    if (/[A-Z]/.test(pass)) score++;
    if (/[0-9]/.test(pass)) score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;
    const levels = [
      { w: '0%', c: 'transparent', l: 'Password Strength' },
      { w: '25%', c: '#D93025', l: 'Very Weak' },
      { w: '50%', c: '#F59E0B', l: 'Weak' },
      { w: '75%', c: '#3B82F6', l: 'Moderate' },
      { w: '100%', c: '#1A7A4A', l: 'Strong' },
    ];
    const lv = pass.length === 0 ? levels[0] : levels[score] || levels[1];
    fill.style.width = lv.w; fill.style.background = lv.c; label.textContent = lv.l;
    label.style.color = lv.c === 'transparent' ? 'var(--text-muted)' : lv.c;
  }
</script>
</body>
</html>