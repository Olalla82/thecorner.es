<?php
/**
 * SISTEMA DE GESTIÓN DE USUARIOS
 * Panel administrativo para crear, editar y eliminar usuarios
 */

require_once __DIR__ . '/inc/auth.php';
auth_require(); // Solo usuarios autenticados

// Archivo de usuarios adicionales (además de los hardcodeados)
$users_file = __DIR__ . '/data/users.json';

// Crear directorio data si no existe
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// Cargar usuarios desde archivo JSON
function load_users() {
    global $users_file;
    if (!file_exists($users_file)) {
        return [];
    }
    $content = file_get_contents($users_file);
    return json_decode($content, true) ?: [];
}

// Guardar usuarios en archivo JSON
function save_users($users) {
    global $users_file;
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
}

// Obtener todos los usuarios (hardcodeados + JSON)
function get_all_users() {
    $hardcoded = AUTH_USERS;
    $json_users = load_users();
    return array_merge($hardcoded, $json_users);
}

// Mensajes
$success = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!auth_csrf_verify($_POST['csrf_token'] ?? '')) {
        $error = 'Token de seguridad inválido';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $name = trim($_POST['name'] ?? '');
            
            if (empty($username) || empty($password) || empty($name)) {
                $error = 'Todos los campos son obligatorios';
            } else if (array_key_exists($username, get_all_users())) {
                $error = 'El usuario ya existe';
            } else if (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                $users = load_users();
                $users[$username] = [
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'name' => $name,
                    'created' => date('Y-m-d H:i:s')
                ];
                save_users($users);
                $success = "Usuario '$username' creado correctamente";
            }
        } 
        else if ($action === 'delete') {
            $username = $_POST['username'] ?? '';
            
            // No permitir eliminar usuarios hardcodeados
            if (array_key_exists($username, AUTH_USERS)) {
                $error = 'No se pueden eliminar usuarios del sistema';
            } else {
                $users = load_users();
                if (array_key_exists($username, $users)) {
                    unset($users[$username]);
                    save_users($users);
                    $success = "Usuario '$username' eliminado correctamente";
                } else {
                    $error = 'Usuario no encontrado';
                }
            }
        }
        else if ($action === 'change_password') {
            $username = $_POST['username'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            
            if (empty($new_password)) {
                $error = 'Debes especificar una contraseña';
            } else if (strlen($new_password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres';
            } else if (array_key_exists($username, AUTH_USERS)) {
                $error = 'No se puede cambiar contraseña de usuarios del sistema. Modifica inc/auth.php';
            } else {
                $users = load_users();
                if (array_key_exists($username, $users)) {
                    $users[$username]['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    $users[$username]['password_changed'] = date('Y-m-d H:i:s');
                    save_users($users);
                    $success = "Contraseña de '$username' actualizada correctamente";
                } else {
                    $error = 'Usuario no encontrado';
                }
            }
        }
    }
}

$all_users = get_all_users();
$json_users = load_users();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - The Corner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #1e1545 0%, #2a1d5f 50%, #1e1545 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            height: 50px;
            width: auto;
        }

        .header-title h1 {
            color: #1e1545;
            font-size: 28px;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #C2D500, #a3c62e);
            color: #1e1545;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(194, 213, 0, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4757, #ff6348);
            color: white;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .card h2 {
            color: #1e1545;
            margin-bottom: 20px;
            font-size: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Roboto', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #C2D500;
            box-shadow: 0 0 0 3px rgba(194, 213, 0, 0.1);
        }

        .users-list {
            grid-column: 1 / -1;
        }

        .user-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #C2D500;
        }

        .user-item.system {
            border-left-color: #1e1545;
            background: #e8e6f0;
        }

        .user-info {
            flex: 1;
        }

        .user-info h3 {
            color: #1e1545;
            margin-bottom: 5px;
            font-size: 18px;
        }

        .user-info p {
            color: #666;
            font-size: 14px;
        }

        .user-meta {
            display: flex;
            gap: 15px;
            margin-top: 8px;
            font-size: 13px;
            color: #888;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 10px;
        }

        .badge-system {
            background: #1e1545;
            color: white;
        }

        .badge-custom {
            background: #C2D500;
            color: #1e1545;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-actions {
                flex-direction: column;
            }

            .user-item {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .user-actions {
                flex-direction: column;
            }
        }

        /* Modal para cambiar contraseña */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-header h3 {
            color: #1e1545;
            font-size: 22px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-title">
                <img src="https://thecorner.es/wp-content/uploads/2025/09/logo.png" alt="The Corner" class="logo">
                <h1>👥 Gestión de Usuarios</h1>
            </div>
            <div class="header-actions">
                <a href="/cursos/admin-dashboard" class="btn btn-secondary">← Dashboard</a>
                <a href="/cursos/logout" class="btn btn-danger">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <span>✅</span>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <span>❌</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <!-- Formulario Añadir Usuario -->
            <div class="card">
                <h2>➕ Añadir Usuario</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= auth_csrf_token() ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Usuario</label>
                        <input type="text" name="username" placeholder="usuario_nuevo" required pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guión bajo">
                    </div>
                    
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="name" placeholder="Juan Pérez" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        ➕ Crear Usuario
                    </button>
                </form>
            </div>

            <!-- Info del sistema -->
            <div class="card">
                <h2>ℹ️ Información</h2>
                <div style="line-height: 1.8; color: #555;">
                    <p style="margin-bottom: 15px;">
                        <strong>Total de usuarios:</strong> <?= count($all_users) ?>
                    </p>
                    <p style="margin-bottom: 15px;">
                        <strong>Usuarios del sistema:</strong> <?= count(AUTH_USERS) ?>
                        <br><small style="color: #888;">Definidos en <code>inc/auth.php</code></small>
                    </p>
                    <p style="margin-bottom: 15px;">
                        <strong>Usuarios personalizados:</strong> <?= count($json_users) ?>
                        <br><small style="color: #888;">Guardados en <code>data/users.json</code></small>
                    </p>
                    
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #ffc107;">
                        <strong style="color: #856404;">⚠️ Nota de seguridad</strong>
                        <p style="color: #856404; margin-top: 8px; font-size: 14px;">
                            Los usuarios del sistema no pueden ser eliminados ni modificados desde aquí. Para cambiarlos, edita directamente <code>inc/auth.php</code>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Lista de usuarios -->
            <div class="card users-list">
                <h2>📋 Lista de Usuarios</h2>
                
                <?php if (empty($all_users)): ?>
                    <div class="empty-state">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <p>No hay usuarios configurados</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_users as $username => $data): ?>
                        <?php 
                        $is_system = array_key_exists($username, AUTH_USERS);
                        ?>
                        <div class="user-item <?= $is_system ? 'system' : '' ?>">
                            <div class="user-info">
                                <h3>
                                    <?php if ($is_system): ?>
                                        <span class="badge badge-system">SISTEMA</span>
                                    <?php else: ?>
                                        <span class="badge badge-custom">PERSONALIZADO</span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($username) ?>
                                </h3>
                                <p><?= htmlspecialchars($data['name']) ?></p>
                                <div class="user-meta">
                                    <?php if (isset($data['created'])): ?>
                                        <span>📅 Creado: <?= htmlspecialchars($data['created']) ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($data['password_changed'])): ?>
                                        <span>🔑 Contraseña cambiada: <?= htmlspecialchars($data['password_changed']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="user-actions">
                                <?php if (!$is_system): ?>
                                    <button class="btn btn-secondary btn-small" onclick="openPasswordModal('<?= htmlspecialchars($username) ?>')">
                                        🔑 Cambiar contraseña
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
                                        <input type="hidden" name="csrf_token" value="<?= auth_csrf_token() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                                        <button type="submit" class="btn btn-danger btn-small">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #888; font-size: 13px; font-style: italic;">
                                        Usuario protegido del sistema
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal cambiar contraseña -->
    <div class="modal" id="passwordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔑 Cambiar Contraseña</h3>
            </div>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?= auth_csrf_token() ?>">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="username" id="modalUsername">
                
                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <input type="password" name="new_password" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePasswordModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💾 Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPasswordModal(username) {
            document.getElementById('modalUsername').value = username;
            document.getElementById('passwordModal').classList.add('active');
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.remove('active');
            document.getElementById('passwordForm').reset();
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        });
    </script>
</body>
</html>
