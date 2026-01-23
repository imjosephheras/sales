<?php
/**
 * ============================================================
 * CALENDAR CONTROLLER
 * Handles calendar display and data loading
 * ============================================================
 */

class CalendarController {
    
    private $eventModel;
    private $categoryModel;
    private $userId;
    
    public function __construct() {
        $this->eventModel = new Event();
        $this->categoryModel = new Category();
        $this->userId = getCurrentUserId();
    }
    
    /**
     * Display main calendar view
     */
    public function index($month, $year) {
        // Load data
        $data = $this->loadCalendarData($month, $year);
        
        // Add month/year info
        $data['month'] = $month;
        $data['year'] = $year;
        $data['monthName'] = date('F', strtotime("$year-$month-01"));
        $data['firstDay'] = getFirstDayOfMonth($month, $year);
        $data['daysInMonth'] = getDaysInMonth($month, $year);
        
        // Navigation
        $data['prevMonth'] = $month - 1;
        $data['prevYear'] = $year;
        $data['nextMonth'] = $month + 1;
        $data['nextYear'] = $year;
        
        if ($data['prevMonth'] < 1) { 
            $data['prevMonth'] = 12; 
            $data['prevYear']--; 
        }
        if ($data['nextMonth'] > 12) { 
            $data['nextMonth'] = 1; 
            $data['nextYear']++; 
        }
        
        // User info
        $data['currentUser'] = getCurrentUser();
        
        // Flash message
        $data['flash'] = getFlashMessage();
        
        // Load view
        $this->view('calendar/index', $data);
    }
    
    /**
     * Load all calendar data
     */
    private function loadCalendarData($month, $year) {
        try {
            // Load events for the month
            $events = $this->eventModel->getByMonth($this->userId, $year, $month);
            
            // Load categories
            $categories = $this->categoryModel->getAllByUser($this->userId);
            
            // Load today's events
            $todayEvents = $this->eventModel->getToday($this->userId);
            
            // Filter work items (JWO, Contract, Proposal, etc.)
            $workItems = array_filter($events, function($evt) {
                $categoryName = strtoupper($evt['category_name'] ?? '');
                return in_array($categoryName, ['JWO', 'CONTRACT', 'PROPOSAL', 'HOODVENT', 'JANITORIAL']);
            });
            
            // Group work items by type
            $jwos = array_filter($workItems, fn($item) => stripos($item['category_name'], 'JWO') !== false);
            $contracts = array_filter($workItems, fn($item) => stripos($item['category_name'], 'CONTRACT') !== false);
            $proposals = array_filter($workItems, fn($item) => stripos($item['category_name'], 'PROPOSAL') !== false);
            
            return [
                'events' => $events,
                'categories' => $categories,
                'todayEvents' => $todayEvents,
                'workItems' => $workItems,
                'jwos' => $jwos,
                'contracts' => $contracts,
                'proposals' => $proposals
            ];
            
        } catch (Exception $e) {
            error_log("Error loading calendar data: " . $e->getMessage());
            
            return [
                'events' => [],
                'categories' => [],
                'todayEvents' => [],
                'workItems' => [],
                'jwos' => [],
                'contracts' => [],
                'proposals' => []
            ];
        }
    }
    
    /**
     * Load a view
     */
    private function view($viewName, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Build view path
        $viewPath = VIEWS_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $viewName) . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        // Load view
        require_once $viewPath;
    }
}