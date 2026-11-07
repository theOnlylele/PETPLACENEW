
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PetPlace | Your Pet's Care, In One Place</title>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #7652c8;
            --primary-dark: #6240ad;
            --primary-light: #f0eaff;
            --background: #faf9fd;
            --text: #29243a;
            --muted: #81798e;
            --white: #ffffff;
            --border: #eeeaf5;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            background: var(--background);
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* HEADER */

        header {
            height: 78px;
            padding: 0 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid var(--border);
            position: relative;
            z-index: 10;
        }

        .logo {
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--text);
        }

        .logo span {
            color: var(--primary);
        }

        .header-label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
        }

        /* HERO */

        .hero {
            min-height: calc(100vh - 78px);
            padding: 75px 8%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 65px;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 85% 15%, rgba(118,82,200,0.12), transparent 28%),
                radial-gradient(circle at 5% 90%, rgba(118,82,200,0.08), transparent 30%),
                #f6f3ff;
        }

        .hero-content {
            max-width: 580px;
            position: relative;
            z-index: 2;
            animation: fadeUp 0.7s ease;
        }

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: var(--white);
            color: var(--primary);
            border: 1px solid #e7defb;
            border-radius: 30px;
            padding: 9px 16px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 27px;
            box-shadow: 0 4px 15px rgba(70,45,120,0.05);
        }

        .welcome-dot {
            width: 8px;
            height: 8px;
            background: #8b6fd4;
            border-radius: 50%;
        }

        .hero-title {
            font-size: clamp(48px, 5.5vw, 76px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -3px;
            margin-bottom: 22px;
        }

        .hero-title span {
            color: var(--primary);
        }

        .hero-subtitle {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .hero-description {
            font-size: 15px;
            line-height: 1.9;
            color: var(--muted);
            max-width: 460px;
            margin-bottom: 32px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .primary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            background: var(--primary);
            color: var(--white);
            padding: 15px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 8px 22px rgba(118,82,200,0.22);
            transition: 0.25s;
        }

        .primary-button:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 12px 26px rgba(118,82,200,0.3);
        }

        .button-arrow {
            font-size: 19px;
        }

        .secondary-text {
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        /* FEATURES */

        .features {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            margin-top: 45px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 600;
            color: #655d75;
        }

        .feature-icon {
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 9px;
            font-size: 15px;
        }

        /* RIGHT SIDE PREVIEW */

        .preview-area {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 490px;
            animation: fadeUp 0.9s ease;
        }

        .preview-glow {
            position: absolute;
            width: 420px;
            height: 420px;
            background: rgba(118,82,200,0.12);
            border-radius: 50%;
            filter: blur(5px);
        }

        .dashboard-preview {
            position: relative;
            width: 100%;
            max-width: 440px;
            background: var(--white);
            padding: 25px;
            border-radius: 22px;
            border: 1px solid #eeeaf5;
            box-shadow: 0 25px 70px rgba(55,35,100,0.13);
            z-index: 2;
        }

        .preview-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .preview-brand {
            font-size: 18px;
            font-weight: 800;
        }

        .preview-brand span {
            color: var(--primary);
        }

        .preview-status {
            background: #eaf8ef;
            color: #247344;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .preview-heading {
            font-size: 19px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .preview-subheading {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 20px;
        }

        .pet-card {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f7f4ff;
            padding: 17px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .pet-avatar {
            width: 55px;
            height: 55px;
            background: #e8ddff;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
        }

        .pet-info h3 {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .pet-info p {
            color: var(--muted);
            font-size: 11px;
        }

        .preview-section-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .appointment-card {
            border: 1px solid var(--border);
            padding: 15px;
            border-radius: 13px;
            margin-bottom: 13px;
        }

        .appointment-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 9px;
        }

        .appointment-top strong {
            font-size: 12px;
        }

        .appointment-tag {
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 20px;
            padding: 5px 9px;
            font-size: 9px;
            font-weight: 700;
        }

        .appointment-card p {
            font-size: 11px;
            color: var(--muted);
            line-height: 1.7;
        }

        .preview-footer {
            display: flex;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border);
            font-size: 10px;
            color: var(--muted);
        }

        .preview-footer strong {
            color: var(--primary);
        }

        /* FLOATING DECORATIONS */

        .floating-decoration {
            position: absolute;
            background: white;
            padding: 13px 17px;
            border-radius: 13px;
            box-shadow: 0 10px 30px rgba(55,35,100,0.1);
            font-size: 12px;
            font-weight: 700;
            z-index: 3;
        }

        .floating-one {
            top: 75px;
            right: -15px;
            color: #247344;
        }

        .floating-two {
            bottom: 65px;
            left: -30px;
            color: var(--primary);
        }

        /* FOOTER */

        footer {
            padding: 22px 8%;
            background: white;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            color: var(--muted);
            font-size: 12px;
        }

        footer strong {
            color: var(--primary);
        }

        /* ANIMATION */

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */

        @media (max-width: 950px) {
            .hero {
                grid-template-columns: 1fr;
                padding-top: 65px;
                gap: 30px;
            }

            .hero-content {
                max-width: 650px;
            }

            .preview-area {
                min-height: 450px;
            }

            .floating-one {
                right: 0;
            }

            .floating-two {
                left: 0;
            }
        }

        @media (max-width: 600px) {
            header {
                height: 68px;
                padding: 0 6%;
            }

            .header-label {
                font-size: 11px;
            }

            .hero {
                padding: 55px 6% 45px;
            }

            .hero-title {
                letter-spacing: -2px;
            }

            .hero-subtitle {
                font-size: 16px;
            }

            .hero-description {
                font-size: 13px;
            }

            .features {
                gap: 15px;
            }

            .preview-area {
                min-height: 390px;
            }

            .dashboard-preview {
                padding: 18px;
            }

            .floating-decoration {
                font-size: 10px;
                padding: 10px;
            }

            footer {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <header>
        <a href="{{ route('choose') }}" class="logo">
            Pet<span>Place.</span>
        </a>

        <div class="header-label">
            Your pet's care, all in one place.
        </div>
    </header>

    <!-- HERO -->
    <main class="hero">

        <section class="hero-content">

            <div class="welcome-badge">
                <span class="welcome-dot"></span>
                WELCOME TO PETPLACE
            </div>

            <h1 class="hero-title">
                Better care.<br>
                Happier <span>pets.</span>
            </h1>

            <p class="hero-subtitle">
                Everything your pet needs, in one place.
            </p>

            <p class="hero-description">
                PetPlace makes veterinary care easier.
                Book appointments, manage pet profiles, track vaccinations,
                and keep important health records organized in one convenient portal.
            </p>

            <div class="hero-actions">
                <a href="{{ route('choose') }}" class="primary-button">
                    Get Started
                    <span class="button-arrow">→</span>
                </a>

                <span class="secondary-text">
                    Simple. Organized. Pet-friendly.
                </span>
            </div>

            <div class="features">
                <div class="feature">
                    <span class="feature-icon">♥</span>
                    Pet Profiles
                </div>

                <div class="feature">
                    <span class="feature-icon">＋</span>
                    Appointments
                </div>

                <div class="feature">
                    <span class="feature-icon">✓</span>
                    Health Records
                </div>
            </div>

        </section>

        <!-- DASHBOARD PREVIEW -->
        <section class="preview-area">

            <div class="preview-glow"></div>

            <div class="floating-decoration floating-one">
                ✓ Organized Records
            </div>

            <div class="floating-decoration floating-two">
                ♥ Care Made Simple
            </div>

            <div class="dashboard-preview">

                <div class="preview-top">
                    <div class="preview-brand">
                        Pet<span>Place.</span>
                    </div>

                    <span class="preview-status">
                        Pet Care Portal
                    </span>
                </div>

                <h2 class="preview-heading">
                    Your Pet Dashboard
                </h2>

                <p class="preview-subheading">
                    Everything important, right where you need it.
                </p>

                <div class="pet-card">
                    <div class="pet-avatar">🐶</div>

                    <div class="pet-info">
                        <h3>Your Pet's Profile</h3>
                        <p>Keep pet information organized</p>
                    </div>
                </div>

                <h3 class="preview-section-title">
                    Manage Your Pet's Care
                </h3>

                <div class="appointment-card">
                    <div class="appointment-top">
                        <strong>Appointments</strong>
                        <span class="appointment-tag">Booking</span>
                    </div>

                    <p>
                        Request veterinary consultations,
                        vaccinations, and grooming services.
                    </p>
                </div>

                <div class="appointment-card">
                    <div class="appointment-top">
                        <strong>Vaccination Records</strong>
                        <span class="appointment-tag">Tracking</span>
                    </div>

                    <p>
                        Keep vaccination history and upcoming
                        schedules easy to access.
                    </p>
                </div>

                <div class="preview-footer">
                    <span>One convenient portal</span>
                    <strong>PetPlace</strong>
                </div>

            </div>

        </section>

    </main>

    <!-- FOOTER -->
    <footer>
        <span>© {{ date('Y') }} <strong>PetPlace</strong>. All rights reserved.</span>
        <span>Made for pets and the people who care for them.</span>
    </footer>

</body>
</html>
