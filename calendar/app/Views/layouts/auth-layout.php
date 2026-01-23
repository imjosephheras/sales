<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Calendar System' ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@400;600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/calendar.css') ?>">
    
    <style>
        :root {
            --color-bg: #fafaf9;
            --color-surface: #ffffff;
            --color-border: #e7e5e4;
            --color-text: #1c1917;
            --color-text-muted: #78716c;
            --color-accent: #dc2626;
            --color-accent-light: #fef2f2;
            --font-display: 'Crimson Pro', serif;
            --font-body: 'DM Sans', sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-body);
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .auth-container {
            width: 100%;
            max-width: 420px;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .logo svg {
            color: var(--color-accent);
        }
        
        .logo-text {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--color-text);
        }
        
        .logo-subtitle {
            color: var(--color-text-muted);
            font-size: 0.9375rem;
        }
        
        .auth-card {
            background: var(--color-surface);
            border-radius: 1rem;
            padding: 2.5rem;
            border: 1px solid var(--color-border);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--color-text);
        }
        
        .subtitle {
            color: var(--color-text-muted);
            margin-bottom: 2rem;
            font-size: 0.9375rem;
        }
        
        .error-message {
            background: var(--color-accent-light);
            color: var(--color-accent);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #fca5a5;
        }
        
        .success-message {
            background: #ecfdf5;
            color: #065f46;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid #86efac;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--color-text);
            font-size: 0.875rem;
        }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--color-border);
            border-radius: 0.5rem;
            font-family: var(--font-body);
            font-size: 0.9375rem;
            transition: all 200ms;
        }
        
        input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px var(--color-accent-light);
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: var(--color-accent);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 200ms;
            margin-top: 1rem;
        }
        
        .btn-primary:hover {
            background: #b91c1c;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: var(--color-text-muted);
            font-size: 0.875rem;
        }
        
        .footer-text a {
            color: var(--color-accent);
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
        
        .demo-credentials {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 2rem;
            font-size: 0.8125rem;
        }
        
        .demo-credentials strong {
            color: #065f46;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .demo-credentials code {
            background: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-family: monospace;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        
        <!-- Logo -->
        <div class="logo-section">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 32 32" fill="none">
                    <rect x="4" y="8" width="24" height="20" rx="2" stroke="currentColor" stroke-width="2"/>
                    <line x1="4" y1="14" x2="28" y2="14" stroke="currentColor" stroke-width="2"/>
                    <line x1="10" y1="5" x2="10" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <line x1="22" y1="5" x2="22" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="logo-text">Calendar</span>
            </div>
            <p class="logo-subtitle">Your professional calendar</p>
        </div>
        
        <!-- Auth Card Content (injected from child view) -->
        <div class="auth-card">
            <?php if (isset($content)) echo $content; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            <?php if (isset($footerText)) echo $footerText; ?>
        </div>
    </div>
    
    <?php if (ENVIRONMENT === 'development'): ?>
    <script>
        console.log('🔐 Auth page loaded');
        console.log('Environment:', '<?= ENVIRONMENT ?>');
    </script>
    <?php endif; ?>
</body>
</html>