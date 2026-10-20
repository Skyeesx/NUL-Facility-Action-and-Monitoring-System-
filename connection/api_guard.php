<?php
/**
 * Guard for JSON/AJAX endpoints.
 * Unlike requireAdmin()/requireFacility() (which redirect to an HTML login page),
 * this answers with a JSON 401/403 so fetch() callers get a clean error.
 *
 * Usage: require_once __DIR__ . '/../connection/api_guard.php'; requireApi('Admin');
 */
require_once __DIR__ . '/session_config.php';

function requireApi($requiredRole) {
    if (!isLoggedIn() || !isSessionValid()) {
        apiDeny(401, 'Not authenticated');
    }
    if (strtolower($_SESSION['user_role']) !== strtolower($requiredRole)) {
        apiDeny(403, 'Not authorized');
    }
}

function apiDeny($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}
