<?php
/**
 * ============================================================
 * WEB ROUTES
 * Define all web routes for the application
 * Future scalability feature - not yet implemented
 * ============================================================
 */

/*
 * This file is prepared for future implementation of a routing system.
 * Currently, the application uses direct PHP files (index.php, login.php, etc.)
 * 
 * Future implementation would use a router like:
 * 
 * Route::get('/', 'CalendarController@index');
 * Route::get('/calendar/{year}/{month}', 'CalendarController@index');
 * Route::get('/login', 'AuthController@showLogin');
 * Route::post('/login', 'AuthController@login');
 * Route::get('/logout', 'AuthController@logout');
 * Route::get('/register', 'AuthController@showRegister');
 * Route::post('/register', 'AuthController@register');
 * 
 * API Routes:
 * Route::get('/api/events/{id}', 'EventController@getById');
 * Route::get('/api/categories', 'CategoryController@getAll');
 * Route::post('/api/events', 'EventController@save');
 * Route::delete('/api/events/{id}', 'EventController@delete');
 * 
 * For now, access files directly:
 * - index.php (calendar)
 * - login.php
 * - logout.php
 * - api/events.php?id=123
 * - actions/event/save.php
 */

// ============================================================
// ROUTE DEFINITIONS (for future use)
// ============================================================

$routes = [
    // Public routes
    'GET /' => 'CalendarController@index',
    'GET /calendar' => 'CalendarController@index',
    'GET /calendar/{year}/{month}' => 'CalendarController@index',
    'GET /login' => 'AuthController@showLogin',
    'POST /login' => 'AuthController@login',
    'GET /logout' => 'AuthController@logout',
    'GET /register' => 'AuthController@showRegister',
    'POST /register' => 'AuthController@register',
    
    // API routes (JSON responses)
    'GET /api/events/{id}' => 'EventController@getById',
    'GET /api/events' => 'EventController@getAll',
    'POST /api/events' => 'EventController@save',
    'PUT /api/events/{id}' => 'EventController@update',
    'DELETE /api/events/{id}' => 'EventController@delete',
    
    'GET /api/categories' => 'CategoryController@getAll',
    'GET /api/categories/{id}' => 'CategoryController@getById',
    'POST /api/categories' => 'CategoryController@create',
    'PUT /api/categories/{id}' => 'CategoryController@update',
    'DELETE /api/categories/{id}' => 'CategoryController@delete',
    
    'GET /api/calendar-data' => 'CalendarController@getMonthData',
    'GET /api/check-series' => 'EventController@checkSeries',
    
    // Action routes (form submissions)
    'POST /actions/event/save' => 'EventController@save',
    'POST /actions/event/delete' => 'EventController@delete',
    'POST /actions/event/reschedule' => 'EventController@reschedule',
    'POST /actions/event/toggle-status' => 'EventController@toggleStatus',
    'POST /actions/task/toggle' => 'TaskController@toggle',
];

// ============================================================
// MIDDLEWARE GROUPS (for future use)
// ============================================================

$middleware = [
    'auth' => [
        'check' => 'requireAuth',
        'redirect' => '/login'
    ],
    'guest' => [
        'check' => 'isGuest',
        'redirect' => '/'
    ],
    'api' => [
        'headers' => ['Content-Type: application/json'],
        'auth' => 'requireAuth'
    ]
];

// ============================================================
// ROUTE GROUPS (for future use)
// ============================================================

$routeGroups = [
    'web' => [
        'middleware' => ['auth'],
        'routes' => [
            'GET /' => 'CalendarController@index',
            'GET /calendar' => 'CalendarController@index',
        ]
    ],
    'api' => [
        'prefix' => '/api',
        'middleware' => ['api', 'auth'],
        'routes' => [
            'GET /events' => 'EventController@getAll',
            'POST /events' => 'EventController@save',
        ]
    ]
];

// ============================================================
// NOTES FOR FUTURE IMPLEMENTATION
// ============================================================

/*
 * To implement this routing system:
 * 
 * 1. Create a Router class in app/Core/Router.php
 * 2. Create a Request class in app/Core/Request.php
 * 3. Create a Response class in app/Core/Response.php
 * 4. Modify index.php to use the router
 * 5. Add .htaccess to redirect all requests to index.php
 * 
 * Example Router usage:
 * 
 * $router = new Router();
 * $router->loadRoutes('routes/web.php');
 * $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
 * 
 * Benefits:
 * - Clean URLs
 * - RESTful API
 * - Middleware support
 * - Route parameters
 * - Route naming
 * - Route caching
 */