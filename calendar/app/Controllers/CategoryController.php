<?php
/**
 * ============================================================
 * CATEGORY CONTROLLER
 * Handles category CRUD operations
 * ============================================================
 */

class CategoryController {
    
    private $categoryModel;
    private $userId;
    
    public function __construct() {
        $this->categoryModel = new Category();
        $this->userId = getCurrentUserId();
    }
    
    /**
     * Get all categories for current user (AJAX)
     */
    public function getAll() {
        header('Content-Type: application/json');
        
        try {
            $categories = $this->categoryModel->getAllByUser($this->userId);
            echo json_encode($categories);
            
        } catch (Exception $e) {
            error_log("Error loading categories: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
    
    /**
     * Get category by ID (AJAX)
     */
    public function getById($categoryId) {
        header('Content-Type: application/json');
        
        if (!$categoryId) {
            http_response_code(400);
            echo json_encode(['error' => 'Category ID required']);
            return;
        }
        
        try {
            $category = $this->categoryModel->getById($categoryId);
            
            if (!$category) {
                http_response_code(404);
                echo json_encode(['error' => 'Category not found']);
                return;
            }
            
            echo json_encode($category);
            
        } catch (Exception $e) {
            error_log("Error loading category: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
    
    /**
     * Create new category
     */
    public function create() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($input['category_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Category name is required']);
            return;
        }
        
        try {
            $categoryName = sanitize($input['category_name']);
            $colorHex = $input['color_hex'] ?? $this->generateRandomColor();
            $icon = $input['icon'] ?? '📁';
            $isDefault = $input['is_default'] ?? false;
            
            // Validate color hex
            if (!validateColorHex($colorHex)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid color format']);
                return;
            }
            
            $categoryId = $this->categoryModel->create(
                $this->userId,
                $categoryName,
                $colorHex,
                $icon,
                $isDefault
            );
            
            if ($categoryId) {
                $category = $this->categoryModel->getById($categoryId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Category created successfully',
                    'category' => $category
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create category']);
            }
            
        } catch (Exception $e) {
            error_log("Error creating category: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
    
    /**
     * Update category
     */
    public function update($categoryId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        if (!$categoryId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Category ID required']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Verify category exists and belongs to user
            $category = $this->categoryModel->getById($categoryId);
            
            if (!$category || $category['user_id'] != $this->userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }
            
            $updateData = [];
            
            if (isset($input['category_name'])) {
                $updateData['category_name'] = sanitize($input['category_name']);
            }
            
            if (isset($input['color_hex'])) {
                if (!validateColorHex($input['color_hex'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid color format']);
                    return;
                }
                $updateData['color_hex'] = $input['color_hex'];
            }
            
            if (isset($input['icon'])) {
                $updateData['icon'] = $input['icon'];
            }
            
            if (empty($updateData)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No data to update']);
                return;
            }
            
            $success = $this->categoryModel->update($categoryId, $updateData);
            
            if ($success) {
                $updatedCategory = $this->categoryModel->getById($categoryId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Category updated successfully',
                    'category' => $updatedCategory
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update category']);
            }
            
        } catch (Exception $e) {
            error_log("Error updating category: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
    
    /**
     * Delete category
     */
    public function delete($categoryId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }
        
        if (!$categoryId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Category ID required']);
            return;
        }
        
        try {
            // Verify category exists and belongs to user
            $category = $this->categoryModel->getById($categoryId);
            
            if (!$category || $category['user_id'] != $this->userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                return;
            }
            
            // Don't allow deleting default category
            if ($category['is_default']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cannot delete default category']);
                return;
            }
            
            $success = $this->categoryModel->delete($categoryId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Category deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete category']);
            }
            
        } catch (Exception $e) {
            error_log("Error deleting category: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
    
    /**
     * Create default categories for user
     */
    public function createDefaults() {
        header('Content-Type: application/json');
        
        try {
            $defaultCategories = [
                [
                    'name' => 'JWO',
                    'color' => '#3b82f6',
                    'icon' => '📋',
                    'is_default' => true
                ],
                [
                    'name' => 'Contract',
                    'color' => '#10b981',
                    'icon' => '📄',
                    'is_default' => false
                ],
                [
                    'name' => 'Proposal',
                    'color' => '#f59e0b',
                    'icon' => '📊',
                    'is_default' => false
                ],
                [
                    'name' => 'Hoodvent',
                    'color' => '#ef4444',
                    'icon' => '🔥',
                    'is_default' => false
                ],
                [
                    'name' => 'Janitorial',
                    'color' => '#8b5cf6',
                    'icon' => '🧹',
                    'is_default' => false
                ],
                [
                    'name' => 'Installation',
                    'color' => '#f97316',
                    'icon' => '🔧',
                    'is_default' => false
                ],
                [
                    'name' => 'Kitchen',
                    'color' => '#ec4899',
                    'icon' => '🍳',
                    'is_default' => false
                ],
                [
                    'name' => 'Staff',
                    'color' => '#14b8a6',
                    'icon' => '👥',
                    'is_default' => false
                ]
            ];
            
            $created = [];
            $errors = [];
            
            foreach ($defaultCategories as $cat) {
                $categoryId = $this->categoryModel->create(
                    $this->userId,
                    $cat['name'],
                    $cat['color'],
                    $cat['icon'],
                    $cat['is_default']
                );
                
                if ($categoryId) {
                    $created[] = [
                        'id' => $categoryId,
                        'name' => $cat['name']
                    ];
                } else {
                    $errors[] = $cat['name'];
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => count($created) . ' categories created',
                'created' => $created,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            error_log("Error creating default categories: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
    
    /**
     * Show category management page
     */
    public function manage() {
        try {
            $categories = $this->categoryModel->getAllByUser($this->userId);
            $currentUser = getCurrentUser();
            
            $data = [
                'categories' => $categories,
                'currentUser' => $currentUser,
                'pageTitle' => 'Manage Categories | Calendar System'
            ];
            
            $this->view('categories/manage', $data);
            
        } catch (Exception $e) {
            error_log("Error loading categories page: " . $e->getMessage());
            setFlashMessage('Error loading categories', 'error');
            redirect('index.php');
        }
    }
    
    /**
     * Generate random color
     */
    private function generateRandomColor() {
        $colors = [
            '#3b82f6', // Blue
            '#10b981', // Green
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#8b5cf6', // Purple
            '#ec4899', // Pink
            '#14b8a6', // Teal
            '#f97316', // Orange
            '#06b6d4', // Cyan
            '#84cc16'  // Lime
        ];
        
        return $colors[array_rand($colors)];
    }
    
    /**
     * Load a view
     */
    private function view($viewName, $data = []) {
        extract($data);
        
        $viewPath = VIEWS_PATH . '/' . $viewName . '.php';
        
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        
        require_once $viewPath;
    }
}