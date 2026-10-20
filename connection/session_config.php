<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access to session cookie
    ini_set('session.use_only_cookies', 1); // Only use cookies, not URL parameters
    ini_set('session.cookie_secure', 0);    // Set to 1 if using HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Prevent CSRF attacks
    
    ini_set('session.gc_maxlifetime', 7200);
    
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['username']) && 
           isset($_SESSION['user_role']);
}

/**
 * Check if session is still valid (not expired)
 * @return bool
 */
function isSessionValid() {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    $timeout_duration = 7200;
    
    if ((time() - $_SESSION['last_activity']) > $timeout_duration) {
        return false; 
    }
    
    $_SESSION['last_activity'] = time(); 
    return true;
}

/**
 * Require user to be logged in, redirect to login if not
 * @param string $userType - 'admin' or 'facility' (optional)
 */
function requireLogin($userType = null) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: /FAMS/Log_in_page/Log_in_faci.php");
        exit;
    }
    
    if (!isSessionValid()) {
        destroySession();
        header("Location: /FAMS/Log_in_page/Log_in_faci.php?error=session_expired");
        exit;
    }
    
    if ($userType !== null) {
        $currentRole = strtolower($_SESSION['user_role']);
        $requiredRole = strtolower($userType);
        
        if ($currentRole !== $requiredRole) {
            header("Location: /FAMS/index.php?error=access_denied");
            exit;
        }
    }
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireLogin('admin');
}

/**
 * Require facility staff access
 */
function requireFacility() {
    requireLogin('Facility Management Staff');
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * @return string|null
 */
function getUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && strtolower($_SESSION['user_role']) === 'admin';
}

/**
 * Check if current user is facility staff
 * @return bool
 */
function isFacility() {
    return isLoggedIn() && strtolower($_SESSION['user_role']) === 'facility management staff';
}

/**
 * Set user session data after successful login
 * @param int $userId
 * @param string $username
 * @param string $role
 */
function setUserSession($userId, $username, $role) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['user_role'] = $role;
    $_SESSION['last_activity'] = time();
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

/**
 * Destroy session and logout user
 */
function destroySession() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Logout user and redirect
 * @param string $redirectUrl
 */
function logout($redirectUrl = '/FAMS/index.php') {
    destroySession();
    header("Location: $redirectUrl");
    exit;
}

/**
 * Get session info for debugging (only in development)
 * @return array
 */
function getSessionInfo() {
    if (!isLoggedIn()) {
        return ['logged_in' => false];
    }
    
    return [
        'logged_in' => true,
        'user_id' => getUserId(),
        'username' => getUsername(),
        'role' => getUserRole(),
        'login_time' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'Unknown',
        'last_activity' => isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'Unknown',
        'session_age' => isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) : 0,
        'time_until_timeout' => isset($_SESSION['last_activity']) ? (7200 - (time() - $_SESSION['last_activity'])) : 0
    ];
}
?>
