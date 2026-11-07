<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Choose Account — Pet Place</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --bg:#f0ecff;
  --white:#ffffff;
  --border:#e0d9f7;
  --text:#1a1523;
  --text2:#6b6880;
  --text3:#a09daf;
  --accent:#6c4fc7;
  --vet:#4f86c6;
  --vet-light:#eef4fd;
  --vet-border:#b8d4f0;
  --owner:#6c4fc7;
  --owner-light:#ede9ff;
  --owner-border:#c4b3f0;
}

html,body{
  min-height:100vh;
  background:var(--bg);
  font-family:'DM Sans',sans-serif;
  color:var(--text);
  background-image:
    radial-gradient(circle at 15% 15%, rgba(108,79,199,0.1) 0%, transparent 50%),
    radial-gradient(circle at 85% 85%, rgba(79,134,198,0.1) 0%, transparent 50%);
}

body{
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  min-height:100vh;
  padding:24px;
}

/* BACK BUTTON */
.back-btn{
  position:absolute;
  top:24px;left:24px;
  display:flex;align-items:center;gap:6px;
  font-size:13px;font-weight:500;
  color:var(--text2);
  text-decoration:none;
  padding:8px 14px;
  border-radius:40px;
  background:var(--white);
  border:1px solid var(--border);
  transition:all 0.15s;
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
}
.back-btn:hover{color:var(--accent);border-color:var(--accent);}

/* LOGO */
.logo{
  font-size:20px;font-weight:700;
  color:var(--text);letter-spacing:-0.02em;
  margin-bottom:36px;
  text-align:center;
}
.logo span{color:var(--accent);}
.paw{font-size:28px;display:block;margin-bottom:6px;text-align:center;}

/* HEADING */
.heading{
  text-align:center;
  margin-bottom:36px;
}
.heading h1{
  font-size:28px;font-weight:800;
  letter-spacing:-0.03em;
  margin-bottom:8px;
}
.heading p{
  font-size:14px;color:var(--text2);
  line-height:1.6;
}

/* CARDS */
.cards{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:16px;
  width:100%;
  max-width:520px;
}

.type-card{
  background:var(--white);
  border:2px solid var(--border);
  border-radius:20px;
  padding:32px 24px;
  cursor:pointer;
  text-align:center;
  text-decoration:none;
  display:block;
  transition:all 0.25s;
  box-shadow:0 2px 12px rgba(0,0,0,0.04);
  position:relative;
  overflow:hidden;
}

.type-card::before{
  content:'';
  position:absolute;
  inset:0;
  opacity:0;
  transition:opacity 0.25s;
  border-radius:18px;
}

.type-card.vet::before{
  background:linear-gradient(135deg, rgba(79,134,198,0.05), rgba(79,134,198,0.1));
}
.type-card.owner::before{
  background:linear-gradient(135deg, rgba(108,79,199,0.05), rgba(108,79,199,0.1));
}

.type-card:hover{
  transform:translateY(-4px);
}
.type-card:hover::before{opacity:1;}

.type-card.vet:hover{
  border-color:var(--vet);
  box-shadow:0 12px 32px rgba(79,134,198,0.2);
}
.type-card.owner:hover{
  border-color:var(--owner);
  box-shadow:0 12px 32px rgba(108,79,199,0.2);
}

.type-card:active{transform:scale(0.97);}

/* Icon circle */
.icon-wrap{
  width:72px;height:72px;
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:34px;
  margin:0 auto 16px;
  transition:transform 0.2s;
}
.type-card:hover .icon-wrap{transform:scale(1.1);}
.vet .icon-wrap{background:var(--vet-light);}
.owner .icon-wrap{background:var(--owner-light);}

.type-name{
  font-size:18px;font-weight:700;
  margin-bottom:6px;
  transition:color 0.2s;
}
.vet:hover .type-name{color:var(--vet);}
.owner:hover .type-name{color:var(--owner);}

.type-desc{
  font-size:12px;color:var(--text2);
  line-height:1.5;
  margin-bottom:20px;
}

/* Arrow button */
.card-arrow{
  display:inline-flex;align-items:center;gap:6px;
  font-size:13px;font-weight:600;
  padding:8px 18px;
  border-radius:40px;
  transition:all 0.2s;
  opacity:0;
  transform:translateY(4px);
}
.type-card:hover .card-arrow{
  opacity:1;
  transform:translateY(0);
}
.vet .card-arrow{background:var(--vet-light);color:var(--vet);}
.owner .card-arrow{background:var(--owner-light);color:var(--owner);}

/* Features list */
.features{
  list-style:none;
  margin-bottom:20px;
  text-align:left;
}
.features li{
  font-size:12px;color:var(--text2);
  padding:4px 0;
  display:flex;align-items:center;gap:6px;
}
.features li::before{
  content:'✓';
  font-size:10px;font-weight:700;
  width:16px;height:16px;
  border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.vet .features li::before{background:var(--vet-light);color:var(--vet);}
.owner .features li::before{background:var(--owner-light);color:var(--owner);}

/* Divider */
.or-divider{
  text-align:center;
  font-size:12px;
  color:var(--text3);
  margin-top:24px;
}

/* Responsive */
@media(max-width:480px){
  .cards{grid-template-columns:1fr;}
  .heading h1{font-size:22px;}
}
</style>
</head>
<body>

<!-- BACK -->
<a href="{{ url('/') }}" class="back-btn">← Back</a>

<!-- LOGO -->
<span class="paw">🐾</span>
<div class="logo">Pet <span>Place.</span></div>

<!-- HEADING -->
<div class="heading">
  <h1>Who are you?</h1>
  <p>Choose your account type to continue.<br/>Each has its own dashboard tailored for you.</p>
</div>

<!-- CARDS -->
<div class="cards">

  <!-- STAFF CARD -->
  <a href="{{ route('login.vet') }}" class="type-card vet">
    <div class="icon-wrap">🧑‍⚕️</div>
    <div class="type-name">Staff</div>
    <div class="type-desc">For veterinarians and pet groomers</div>
    <ul class="features">
      <li>Provide veterinary care</li>
      <li>Offer grooming services</li>
      <li>Manage appointment requests</li>
    </ul>
    <div class="card-arrow">Get Started →</div>
  </a>

  <!-- PET OWNER CARD -->
  <a href="{{ route('login.owner') }}" class="type-card owner">
    <div class="icon-wrap">🐶</div>
    <div class="type-name">Pet Owner</div>
    <div class="type-desc">For pet owners who want to track their pet's health</div>
    <ul class="features">
      <li>View pet health records</li>
      <li>Book vet appointments</li>
      <li>Get reminders</li>
    </ul>
    <div class="card-arrow">Get Started →</div>
  </a>

</div>

</body>
</html>