<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Pet Owner Login — Pet Place</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:#f0ecff;
  --white:#ffffff;
  --border:#ddd5f5;
  --text:#1a1523;
  --text2:#6b6880;
  --text3:#a09daf;
  --accent:#6c4fc7;
  --accent2:#5a3eb5;
  --accent-light:#ede9ff;
}
html,body{
  min-height:100vh;
  background:var(--bg);
  font-family:'DM Sans',sans-serif;
  color:var(--text);
  background-image:
    radial-gradient(circle at 15% 15%, rgba(108,79,199,0.1) 0%, transparent 50%),
    radial-gradient(circle at 85% 85%, rgba(108,79,199,0.06) 0%, transparent 50%);
}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;}

/* BACK */
.back-btn{
  position:fixed;top:24px;left:24px;
  display:flex;align-items:center;gap:6px;
  font-size:13px;font-weight:500;color:var(--text2);
  text-decoration:none;padding:8px 14px;border-radius:40px;
  background:var(--white);border:1px solid var(--border);
  transition:all 0.15s;box-shadow:0 2px 8px rgba(0,0,0,0.05);
  z-index:100;
}
.back-btn:hover{color:var(--accent);border-color:var(--accent);}

.container{width:100%;max-width:420px;animation:fadeUp 0.3s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:none;}}

/* LOGO */
.logo{text-align:center;margin-bottom:24px;}
.paw{font-size:26px;display:block;margin-bottom:6px;}
.logo-text{font-size:24px;font-weight:700;letter-spacing:-0.03em;color:var(--text);}
.logo-text span{color:var(--accent);}
.logo-sub{font-size:13px;color:var(--text3);margin-top:4px;}

/* ROLE BADGE */
.role-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--accent-light);
  border:1px solid var(--border);
  border-radius:40px;padding:8px 16px;
  font-size:13px;font-weight:600;color:var(--accent);
  margin-bottom:20px;
}

/* CARD */
.card{
  background:var(--white);border:1px solid var(--border);
  border-radius:20px;padding:36px;
  box-shadow:0 4px 24px rgba(108,79,199,0.1);
}

.form-group{margin-bottom:16px;}
label{display:block;font-size:12px;font-weight:600;color:var(--text2);margin-bottom:7px;letter-spacing:0.04em;text-transform:uppercase;}
input[type="email"],input[type="password"]{
  width:100%;background:#faf8ff;border:1.5px solid var(--border);
  border-radius:10px;padding:11px 14px;color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:14px;outline:none;
  transition:border-color 0.2s,box-shadow 0.2s;
}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(108,79,199,0.08);}
input::placeholder{color:var(--text3);}

.remember-row{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:20px;margin-top:-4px;
}
.remember-row label{
  display:flex;align-items:center;gap:7px;
  font-size:13px;color:var(--text2);
  text-transform:none;letter-spacing:0;cursor:pointer;margin:0;font-weight:400;
}
.remember-row input[type="checkbox"]{width:15px;height:15px;accent-color:var(--accent);cursor:pointer;}
.remember-row a{font-size:12px;color:var(--accent);text-decoration:none;font-weight:500;}
.remember-row a:hover{text-decoration:underline;}

.btn{
  width:100%;background:var(--accent);color:#fff;
  border:none;border-radius:10px;padding:13px;
  font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;
  cursor:pointer;transition:all 0.2s;letter-spacing:-0.01em;
}
.btn:hover{background:var(--accent2);transform:translateY(-1px);box-shadow:0 4px 16px rgba(108,79,199,0.3);}
.btn:active{transform:scale(0.98);}

.error-list{
  background:#fff5f5;border:1px solid #fed7d7;border-radius:9px;
  padding:10px 13px;font-size:13px;color:#e53e3e;
  margin-bottom:16px;list-style:none;
}

.divider{border:none;border-top:1px solid var(--border);margin:20px 0;}
.card-footer{text-align:center;font-size:13px;color:var(--text2);}
.card-footer a{color:var(--accent);text-decoration:none;font-weight:600;}
.card-footer a:hover{text-decoration:underline;}

.pw-wrap{position:relative;}
.pw-toggle{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--text3);cursor:pointer;font-size:15px;
  transition:color 0.15s;
}
.pw-toggle:hover{color:var(--accent);}

/* Keep input styling consistent between password and text type */
input[type="text"],
input[type="password"] {
    -webkit-appearance: none;
    appearance: none;
}

.pw-wrap input {
    width: 100%;
    background: #faf8ff;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 11px 44px 11px 14px !important;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
</style>
</head>
<body>

<a href="{{ route('choose') }}" class="back-btn">← Back</a>

<div class="container">
  <div class="logo">
    <span class="paw">🐾</span>
    <div class="logo-text">Pet <span>Place.</span></div>
    <div class="logo-sub">Pet Owner Portal</div>
  </div>

  <div style="text-align:center;">
    <div class="role-badge">🐶 Signing in as Pet Owner</div>
  </div>

  <div class="card">

    @if ($errors->any())
      <ul class="error-list">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif

    @if (session('status'))
      <div style="background:#f0fff4;border:1px solid #c6f6d5;border-radius:9px;padding:10px 13px;font-size:13px;color:#276749;margin-bottom:16px;">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <input type="hidden" name="role" value="owner"/>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com"
               value="{{ old('email') }}" required autofocus autocomplete="username"/>
      </div>

      <div class="form-group">
        <label>Password</label>
        <div class="pw-wrap">
          <input type="password" name="password" id="pwField"
                 placeholder="Enter your password" required autocomplete="new-password"/>
          <button type="button" class="pw-toggle" onclick="togglePw()">👁</button>
        </div>
      </div>

      <div class="remember-row">
        <label>
          <input type="checkbox" name="remember"/>
          Remember me
        </label>
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}">Forgot password?</a>
        @endif
      </div>

      <button type="submit" class="btn">Sign In as Pet Owner</button>
    </form>

    <hr class="divider"/>
    <div class="card-footer">
      Don't have an account? <a href="{{ route('register.owner') }}">Create one</a>
    </div>
  </div>
</div>

<script>
function togglePw() {
  const f = document.getElementById('pwField');
  f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>