<?php
/**
 * Register View
 * Uses auth-layout.php
 */

// Start output buffering to capture content
ob_start();
?>

<h1>Create Account</h1>
<p class="subtitle">Join Calendar System today</p>

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

<form method="POST" action="register.php">
    <div class="form-group">
        <label for="full_name">Full Name</label>
        <input 
            type="text" 
            id="full_name" 
            name="full_name" 
            placeholder="John Doe"
            value="<?= e($full_name ?? '') ?>"
            required 
            autofocus
        >
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="your@email.com"
            value="<?= e($email ?? '') ?>"
            required
        >
    </div>
    
    <div class="form-group">
        <label for="username">Username</label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            placeholder="johndoe"
            value="<?= e($username ?? '') ?>"
            required
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
    
    <div class="form-group">
        <label for="password_confirm">Confirm Password</label>
        <input 
            type="password" 
            id="password_confirm" 
            name="password_confirm" 
            placeholder="••••••••"
            required
        >
    </div>
    
    <button type="submit" class="btn-primary">
        Create Account
    </button>
</form>

<style>
    .form-group + .form-group {
        margin-top: 1rem;
    }
    
    .help-text {
        font-size: 0.75rem;
        color: var(--color-text-muted);
        margin-top: 0.25rem;
    }
</style>

<?php
// Capture content
$content = ob_get_clean();

// Set footer text
$footerText = 'Already have an account? <a href="login.php">Sign in</a>';

// Include layout
include VIEWS_PATH . '/layouts/auth-layout.php';
?>