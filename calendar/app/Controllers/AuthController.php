<?php
/**
 * ============================================================
 * AUTH CONTROLLER
 * Handles authentication (login/logout)
 * ============================================================
 */

class AuthController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Show login page
     */
    public function showLogin() {
        // If already logged in, redirect to calendar
        if (isLoggedIn()) {
            redirect('index.php');
            return;
        }
        
        $error = '';
        $data = [
            'error' => $error,
            'pageTitle' => 'Login | Calendar System'
        ];
        
        $this->view('auth/login', $data);
    }
    
    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('login.php');
            return;
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $this->showLogin();
            return;
        }
        
        try {
            if ($this->userModel->login($email, $password)) {
                // Login successful
                loginUser([
                    'user_id' => $this->userModel->user_id,
                    'username' => $this->userModel->username,
                    'email' => $this->userModel->email,
                    'full_name' => $this->userModel->full_name,
                    'timezone' => $this->userModel->timezone
                ]);
                
                redirect('index.php');
            } else {
                // Login failed
                $data = [
                    'error' => 'Email or password incorrect',
                    'email' => $email,
                    'pageTitle' => 'Login | Calendar System'
                ];
                $this->view('auth/login', $data);
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            
            $data = [
                'error' => 'Error processing login. Please try again.',
                'email' => $email,
                'pageTitle' => 'Login | Calendar System'
            ];
            
            if (ENVIRONMENT === 'development') {
                $data['error'] .= '<br><small>Debug: ' . e($e->getMessage()) . '</small>';
            }
            
            $this->view('auth/login', $data);
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        logoutUser();
        redirect('login.php');
    }
    
    /**
     * Show register page (future implementation)
     */
    public function showRegister() {
        $data = [
            'pageTitle' => 'Register | Calendar System'
        ];
        $this->view('auth/register', $data);
    }
    
    /**
     * Process registration (future implementation)
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('register.php');
            return;
        }
        
        // TODO: Implement registration logic
        $this->showRegister();
    }
    
    /**
     * Load a view
     */
    private function view($viewName, $data = []) {
        extract($data);
        
        $viewPath = VIEWS_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewName) . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        require_once $viewPath;
    }
}