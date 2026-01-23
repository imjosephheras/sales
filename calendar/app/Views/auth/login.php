<?php
/**
 * Login View
 * Uses auth-layout.php
 */

// Start output buffering to capture content
ob_start();
?>

<h1>Welcome Back</h1>
<p class="subtitle">Sign in to continue</p>

<?php if (isset($error) && $error): ?>
    <div class="error-message">
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
    <div class="success-message">
        <?= e($success) ?>
    </div>
<?php endif; ?>

<form method="POST" action="login.php">
    <div class="form-group">
        <label for="email">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="your@email.com"
            value="<?= e($email ?? '') ?>"
            required 
            autofocus
        >
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="••••••••"
            required
        >
    </div>
    
    <button type="submit" class="btn-primary">
        Sign In
    </button>
</form>

<!-- Demo Credentials -->
<div class="demo-credentials">
    <strong>🔑 Demo Credentials:</strong>
    <div>Email: <code>admin@example.com</code></div>
    <div>Password: <code>password</code></div>
</div>

<?php
// Capture content
$content = ob_get_clean();

// Set footer text
$footerText = 'Calendar System v2.0';

// Include layout
include VIEWS_PATH . '/layouts/auth-layout.php';
?>