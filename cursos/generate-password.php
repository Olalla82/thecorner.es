<?php
/**
 * GENERADOR DE HASHES DE CONTRASEÑAS
 * 
 * Usa este archivo para generar hashes seguros de contraseñas
 * que puedes copiar al archivo inc/auth.php
 * 
 * ⚠️ ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO (SEGURIDAD)
 */

require_once __DIR__ . '/inc/auth.php';

// Si se envió el formulario, generar hash
$hash = '';
$password_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['password'])) {
    $password_input = $_POST['password'];
    $hash = auth_generate_password($password_input);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Contraseñas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            color: #856404;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .result {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #667eea;
        }
        
        .result h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .hash-output {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        
        .copy-btn {
            padding: 8px 16px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .copy-btn:hover {
            background: #218838;
        }
        
        .instructions {
            margin-top: 20px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }
        
        .instructions h4 {
            color: #1976D2;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            margin-left: 20px;
            color: #555;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #c7254e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Generador de Contraseñas</h1>
        <p style="color: #666; margin-bottom: 20px;">Genera hashes seguros para el sistema de autenticación</p>
        
        <div class="warning">
            ⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo después de usarlo por seguridad.
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Contraseña a hashear:</label>
                <input 
                    type="text" 
                    id="password" 
                    name="password" 
                    placeholder="Ingresa la contraseña"
                    value="<?= htmlspecialchars($password_input) ?>"
                    required
                    autofocus
                >
                <small style="color: #999;">Esta contraseña se mostrará en pantalla. Usa una contraseña de prueba o cambiala después.</small>
            </div>
            
            <button type="submit" class="btn">
                🔒 Generar Hash
            </button>
        </form>
        
        <?php if ($hash): ?>
            <div class="result">
                <h3>✅ Hash Generado</h3>
                
                <p style="margin-bottom: 10px;"><strong>Contraseña:</strong> <code><?= htmlspecialchars($password_input) ?></code></p>
                
                <p style="margin-bottom: 10px;"><strong>Hash:</strong></p>
                <div class="hash-output" id="hash-output"><?= htmlspecialchars($hash) ?></div>
                
                <button class="copy-btn" onclick="copyHash()">
                    📋 Copiar Hash
                </button>
                
                <div class="instructions">
                    <h4>📝 Cómo usar este hash:</h4>
                    <ol>
                        <li>Copia el hash generado (botón de arriba)</li>
                        <li>Abre el archivo <code>inc/auth.php</code></li>
                        <li>Busca el array <code>AUTH_USERS</code></li>
                        <li>Agrega o modifica un usuario con el hash copiado:</li>
                    </ol>
                    <pre style="background: #fff; padding: 10px; border-radius: 5px; margin-top: 10px; overflow-x: auto;"><code>'nuevo_usuario' => [
    'password' => '<?= htmlspecialchars($hash) ?>',
    'name' => 'Nombre del Usuario'
]</code></pre>
                </div>
            </div>
            
            <script>
                function copyHash() {
                    const hashText = document.getElementById('hash-output').textContent;
                    navigator.clipboard.writeText(hashText).then(() => {
                        const btn = event.target;
                        const originalText = btn.textContent;
                        btn.textContent = '✅ ¡Copiado!';
                        setTimeout(() => {
                            btn.textContent = originalText;
                        }, 2000);
                    });
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
