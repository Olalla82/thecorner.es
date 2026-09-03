<?php
require_once __DIR__ . '/inc/auth.php';

// Si ya está autenticado, redirigir
if (auth_check()) {
    $redirect = $_SESSION['auth_redirect'] ?? '/cursos/admin-dashboard.php';
    unset($_SESSION['auth_redirect']);
    header('Location: ' . $redirect);
    exit;
}

$error = '';
$success = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Verificar CSRF token
    if (!auth_csrf_verify($csrf_token)) {
        $error = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $result = auth_login($username, $password);
        
        if ($result['success']) {
            $redirect = $_SESSION['auth_redirect'] ?? '/cursos/admin-dashboard.php';
            unset($_SESSION['auth_redirect']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Administración</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e1545 0%, #2a1d5f 50%, #1e1545 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(194, 213, 0, 0.15) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.3s ease-out;
            position: relative;
            z-index: 1;
            border-top: 4px solid #C2D500;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .icon-lock {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #C2D500, #a3c62e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            box-shadow: 0 4px 15px rgba(194, 213, 0, 0.3);
        }
        
        .login-header h1 {
            color: #1e1545;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #C2D500;
            box-shadow: 0 0 0 3px rgba(194, 213, 0, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #C2D500, #a3c62e);
            color: #1e1545;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(194, 213, 0, 0.4);
            background: linear-gradient(135deg, #d4e815, #C2D500);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #1e1545;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner - Centre d'Estudis">
        </div>
        
        <div class="login-header">
            <div class="icon-lock">🔒</div>
            <h1>Acceso al Sistema</h1>
            <p>Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= auth_csrf_token() ?>">
            
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="back-link">
            <a href="/cursos/">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>
