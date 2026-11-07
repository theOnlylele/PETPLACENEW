<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Staff Register — Pet Place</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:#eef4fd;
  --white:#ffffff;
  --border:#c8dff5;
  --text:#0f2137;
  --text2:#4a6080;
  --text3:#8aa0b8;
  --accent:#4f86c6;
  --accent2:#3d75b5;
  --accent-light:#deeaf8;
}
html,body{
  min-height:100vh;
  background:var(--bg);
  font-family:'DM Sans',sans-serif;
  color:var(--text);
  background-image:
    radial-gradient(circle at 15% 15%, rgba(79,134,198,0.1) 0%, transparent 50%),
    radial-gradient(circle at 85% 85%, rgba(79,134,198,0.06) 0%, transparent 50%);
}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}

.back-btn{
  position:fixed;top:24px;left:24px;
  display:flex;align-items:center;gap:6px;
  font-size:13px;font-weight:500;color:var(--text2);
  text-decoration:none;padding:8px 14px;border-radius:40px;
  background:var(--white);border:1px solid var(--border);
  transition:all 0.15s;box-shadow:0 2px 8px rgba(0,0,0,0.05);z-index:100;
}
.back-btn:hover{color:var(--accent);border-color:var(--accent);}

.container{width:100%;max-width:460px;animation:fadeUp 0.3s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:none;}}

.logo{text-align:center;margin-bottom:20px;}
.paw{font-size:26px;display:block;margin-bottom:6px;}
.logo-text{font-size:24px;font-weight:700;letter-spacing:-0.03em;color:var(--text);}
.logo-text span{color:var(--accent);}
.logo-sub{font-size:13px;color:var(--text3);margin-top:4px;}

.role-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--accent-light);border:1px solid var(--border);
  border-radius:40px;padding:8px 16px;
  font-size:13px;font-weight:600;color:var(--accent);margin-bottom:20px;
}

.card{
  background:var(--white);border:1px solid var(--border);
  border-radius:20px;padding:36px;
  box-shadow:0 4px 24px rgba(79,134,198,0.1);
}

.form-row{display:flex;gap:12px;}
.form-row .form-group{flex:1;}

.form-group{margin-bottom:15px;}
label{display:block;font-size:12px;font-weight:600;color:var(--text2);margin-bottom:7px;letter-spacing:0.04em;text-transform:uppercase;}
input[type="text"],input[type="email"],input[type="password"],select{
  width:100%;background:#f5f9ff;border:1.5px solid var(--border);
  border-radius:10px;padding:11px 14px;color:var(--text);
  font-family:'DM Sans',sans-serif;font-size:14px;outline:none;
  transition:border-color 0.2s,box-shadow 0.2s;
}
input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,134,198,0.1);}
input::placeholder{color:var(--text3);}
select{cursor:pointer;color:var(--text);}

.field-error{font-size:12px;color:#e53e3e;margin-top:5px;}

.btn{
  width:100%;background:var(--accent);color:#fff;
  border:none;border-radius:10px;padding:13px;
  font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;
  cursor:pointer;margin-top:6px;transition:all 0.2s;
}
.btn:hover{background:var(--accent2);transform:translateY(-1px);box-shadow:0 4px 16px rgba(79,134,198,0.3);}
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
}
.pw-toggle:hover{color:var(--accent);}

.section-title{
  font-size:11px;font-weight:700;color:var(--text3);
  letter-spacing:0.08em;text-transform:uppercase;
  margin:18px 0 12px;padding-bottom:8px;
  border-bottom:1px solid var(--border);
}
</style>
</head>
<body>

<a href="{{ route('login.vet') }}" class="back-btn">← Back</a>

<div class="container">
  <div class="logo">
    <span class="paw">🐾</span>
    <div class="logo-text">Pet <span>Place.</span></div>
    <div class="logo-sub">Staff Registration</div>
  </div>

  <div style="text-align:center;">
    <div class="role-badge">🩺 Create Staff Account</div>
  </div>

  <div class="card">

    @if ($errors->any())
      <ul class="error-list">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif

    <form method="POST" action="{{ route('register') }}">
      @csrf
      <input type="hidden" name="role" value="vet"/>

      <div class="section-title">Personal Information</div>

      <div class="form-row">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" placeholder="Juan"
                 value="{{ old('first_name') }}" required autofocus/>
          @error('first_name')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" placeholder="Dela Cruz"
                 value="{{ old('last_name') }}" required/>
          @error('last_name')<div class="field-error">{{ $message }}</div>@enderror
        </div>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="vet@example.com"
               value="{{ old('email') }}" required autocomplete="username"/>
        @error('email')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label for="date_of_birth">Date of Birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" max="{{ today()->toDateString() }}"
               value="{{ old('date_of_birth') }}" required/>
        @error('date_of_birth')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="section-title">Professional Information</div>

      <div class="form-group">
        <label>Staff role</label>
        <select name="staff_type" id="staffType" required>
          <option value="">Select staff role</option>
          <option value="veterinarian" {{ old('staff_type', 'veterinarian') === 'veterinarian' ? 'selected' : '' }}>Veterinarian</option>
          <option value="groomer" {{ old('staff_type') === 'groomer' ? 'selected' : '' }}>Groomer</option>
        </select>
        @error('staff_type')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group" id="specialtyField">
        <label for="specialty">Veterinary specialty</label>
        <select name="specialty" id="specialty" required>
          <option value="">Select animal specialty</option>
          <option value="dog" {{ old('specialty') === 'dog' ? 'selected' : '' }}>Dogs</option>
          <option value="cat" {{ old('specialty') === 'cat' ? 'selected' : '' }}>Cats</option>
          <option value="rabbit" {{ old('specialty') === 'rabbit' ? 'selected' : '' }}>Rabbits</option>
          <option value="fish" {{ old('specialty') === 'fish' ? 'selected' : '' }}>Fish</option>
          <option value="bird" {{ old('specialty') === 'bird' ? 'selected' : '' }}>Birds</option>
          <option value="reptile" {{ old('specialty') === 'reptile' ? 'selected' : '' }}>Reptiles</option>
        </select>
        @error('specialty')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group" id="licenseField">
        <label for="license_number">PRC Registration No.</label>
        <input type="text" id="license_number" name="license_number" placeholder="7 digits, e.g. 0012345"
               value="{{ old('license_number') }}" inputmode="numeric" pattern="[0-9]{7}" minlength="7" maxlength="7"
               title="Enter exactly 7 digits, as shown on your PRC ID." autocomplete="off" required/>
        @error('license_number')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="section-title">Account Security</div>

      <div class="form-group">
        <label>Password</label>
        <div class="pw-wrap">
          <input type="password" name="password" id="pwField"
                 placeholder="At least 8 characters" required autocomplete="new-password"/>
          <button type="button" class="pw-toggle" onclick="togglePw('pwField','pwBtn')" id="pwBtn">👁</button>
        </div>
        @error('password')<div class="field-error">{{ $message }}</div>@enderror
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <div class="pw-wrap">
          <input type="password" name="password_confirmation" id="confirmField"
                 placeholder="Repeat password" required autocomplete="new-password"/>
          <button type="button" class="pw-toggle" onclick="togglePw('confirmField','confirmBtn')" id="confirmBtn">👁</button>
        </div>
      </div>

      <button type="submit" class="btn">Create Staff Account</button>
    </form>

    <hr class="divider"/>
    <div class="card-footer">
      Already have an account? <a href="{{ route('login.vet') }}">Sign in</a>
    </div>
  </div>
</div>

<script>
function togglePw(fieldId, btnId) {
  const f = document.getElementById(fieldId);
  const b = document.getElementById(btnId);
  f.type = f.type === 'password' ? 'text' : 'password';
  b.textContent = f.type === 'password' ? '👁' : '🙈';
}

const staffType = document.getElementById('staffType');
const specialtyField = document.getElementById('specialtyField');
const specialtySelect = document.getElementById('specialty');
const licenseField = document.getElementById('licenseField');
const licenseInput = document.getElementById('license_number');

function toggleSpecialty() {
  const isVeterinarian = staffType.value === 'veterinarian';
  specialtyField.hidden = !isVeterinarian;
  specialtySelect.required = isVeterinarian;
  specialtySelect.disabled = !isVeterinarian;
  licenseField.hidden = !isVeterinarian;
  licenseInput.required = isVeterinarian;
  licenseInput.disabled = !isVeterinarian;
}

licenseInput.addEventListener('input', () => {
  licenseInput.value = licenseInput.value.replace(/[^0-9]/g, '').slice(0, 7);
});

staffType.addEventListener('change', toggleSpecialty);
toggleSpecialty();
</script>
</body>
</html>