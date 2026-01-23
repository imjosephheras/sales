/**
 * ============================================================
 * THEME TOGGLE - DARK/LIGHT MODE
 * Fixed version with proper CSS variable application
 * ============================================================ */

// Toggle between light and dark theme
function toggleTheme() {
    const html = document.documentElement;
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    // Apply to both html and body
    html.setAttribute('data-theme', newTheme);
    body.setAttribute('data-theme', newTheme);
    
    // Save to localStorage
    localStorage.setItem('theme', newTheme);
    
    console.log('✅ Theme changed to:', newTheme);
    
    // Force CSS recalculation
    void body.offsetHeight;
}

// Load saved theme on page load
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const html = document.documentElement;
    const body = document.body;
    
    // Apply immediately to prevent flash
    html.setAttribute('data-theme', savedTheme);
    body.setAttribute('data-theme', savedTheme);
    
    console.log('✅ Theme loaded:', savedTheme);
}

// Load theme IMMEDIATELY (before DOMContentLoaded to prevent flash)
loadSavedTheme();

// Also load on DOMContentLoaded as backup
document.addEventListener('DOMContentLoaded', () => {
    loadSavedTheme();
    console.log('✅ Theme system initialized');
});