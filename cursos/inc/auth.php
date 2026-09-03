<?php
/**
 * Sistema de Autenticación para Panel de Caché
 * Protege el acceso a herramientas administrativas
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de usuarios (CAMBIAR ESTAS CREDENCIALES)
define('AUTH_USERS', [
    'admin' => [
        'password' => '$2y$10$.1GzMs68Je6n.Mu9S4QYsuoUWMuq1oVB7ZRL2G03ajIVrtQaU2ZBa', // password: "admin123"
        'name' => 'Administrador'
    ],
    'thecorner' => [
        'password' => '$2y$10$3JObHImYqnRHmAPSy/Qg6eOxhitkc3wR0vJdKvWpzGISNwPlyGLyK', // password: "thecorner2026"
        'name' => 'The Corner Admin'
    ]
]);

/**
 * Verificar si el usuario está autenticado
 */
function auth_check() {
    return isset($_SESSION['auth_user']) && 
           isset($_SESSION['auth_time']) && 
           (time() - $_SESSION['auth_time']) < 7200; // 2 horas de sesión
}

/**
 * Requerir autenticación - Redirige a login si no está autenticado
 */
function auth_require() {
    if (!auth_check()) {
        // Guardar la URL a la que quería acceder
        $_SESSION['auth_redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: /cursos/login.php');
        exit;
    }
    // Renovar tiempo de sesión
    $_SESSION['auth_time'] = time();
}

/**
 * Cargar usuarios adicionales desde JSON
 */
function auth_load_json_users() {
    $users_file = __DIR__ . '/../data/users.json';
    if (!file_exists($users_file)) {
        return [];
    }
    $content = file_get_contents($users_file);
    return json_decode($content, true) ?: [];
}

/**
 * Obtener todos los usuarios (hardcodeados + JSON)
 */
function auth_get_all_users() {
    return array_merge(AUTH_USERS, auth_load_json_users());
}

/**
 * Intentar login con usuario y contraseña
 */
function auth_login($username, $password) {
    // Protección contra fuerza bruta
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last_attempt'] = 0;
    }
    
    // Bloqueo temporal después de 5 intentos fallidos
    if ($_SESSION['login_attempts'] >= 5) {
        $time_since_last = time() - $_SESSION['login_last_attempt'];
        if ($time_since_last < 300) { // 5 minutos
            return [
                'success' => false,
                'message' => 'Demasiados intentos fallidos. Espera ' . (300 - $time_since_last) . ' segundos.'
            ];
        } else {
            // Reset después del tiempo de bloqueo
            $_SESSION['login_attempts'] = 0;
        }
    }
    
    // Verificar credenciales (hardcodeados + JSON)
    $users = auth_get_all_users();
    
    if (!isset($users[$username])) {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last_attempt'] = time();
        return [
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ];
    }
    
    // Verificar contraseña
    if (!password_verify($password, $users[$username]['password'])) {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last_attempt'] = time();
        return [
            'success' => false,
            'message' => 'Usuario o contraseña incorrectos.'
        ];
    }
    
    // Login exitoso
    $_SESSION['auth_user'] = $username;
    $_SESSION['auth_name'] = $users[$username]['name'];
    $_SESSION['auth_time'] = time();
    $_SESSION['login_attempts'] = 0;
    
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
    
    return [
        'success' => true,
        'message' => 'Login exitoso'
    ];
}

/**
 * Cerrar sesión
 */
function auth_logout() {
    $_SESSION = [];
    
    // Destruir cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Obtener nombre del usuario autenticado
 */
function auth_user_name() {
    return $_SESSION['auth_name'] ?? 'Usuario';
}

/**
 * Generar token CSRF
 */
function auth_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function auth_csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar hash de contraseña (útil para crear nuevos usuarios)
 */
function auth_generate_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
