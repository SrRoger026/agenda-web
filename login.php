<?php
session_start();
if (isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/conexion.php';

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$correo = '';
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = mb_strtolower(trim($_POST['correo'] ?? ''));
    $clave  = $_POST['clave'] ?? '';

    $stmt = $mysqli->prepare('SELECT id, nombre, clave_hash FROM usuarios WHERE correo = ?');
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($usuario && password_verify($clave, $usuario['clave_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid']    = (int) $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        header('Location: index.php');
        exit;
    }

    $error = 'Correo o contraseña incorrectos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - AgendaWeb</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .auth-wrapper {
            max-width: 420px;
            margin: 2rem auto;
            background: var(--bg-surface, #fff);
            padding: 2rem;
            border-radius: var(--radius-card, 12px);
            border: 1px solid var(--border-color, #E2E8F0);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(15,23,42,0.05));
        }
        .auth-title {
            margin-bottom: 1.5rem;
            font-family: var(--font-heading, sans-serif);
            font-size: 1.5rem;
            color: var(--text-primary, #0F172A);
        }
        .form-group {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary, #0F172A);
        }
        .form-group input {
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--border-color, #E2E8F0);
            border-radius: var(--radius-input, 8px);
            font-family: var(--font-body, sans-serif);
            font-size: 0.95rem;
            outline: none;
        }
        .form-group input:focus {
            border-color: var(--brand-accent, #0F766E);
        }
        .btn-submit {
            background-color: var(--brand-accent, #0F766E);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-button, 8px);
            cursor: pointer;
            font-weight: 600;
            font-family: var(--font-body, sans-serif);
        }
        .btn-submit:hover {
            background-color: var(--brand-accent-hover, #115E59);
        }
        .auth-footer {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .auth-footer a {
            color: var(--brand-accent, #0F766E);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .alert-box {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-input, 8px);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert-box--error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
        }
        .alert-box--ok {
            background-color: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1>Agenda<span>Web</span></h1>
        </header>

        <main>
            <div class="auth-wrapper">
                <h2 class="auth-title">Iniciar Sesión</h2>

                <?php if (isset($_GET['registrado'])): ?>
                    <div class="alert-box alert-box--ok">Cuenta creada. Ya puedes iniciar sesión.</div>
                <?php endif; ?>

                <?php if (isset($_GET['salio'])): ?>
                    <div class="alert-box alert-box--ok">Cerraste sesión. ¡Hasta pronto!</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert-box alert-box--error"><?= e($error) ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" name="correo" id="correo" value="<?= e($correo) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="clave">Contraseña</label>
                        <input type="password" name="clave" id="clave" autocomplete="current-password" required>
                    </div>

                    <div class="auth-footer">
                        <button type="submit" class="btn-submit">Entrar</button>
                        <a href="registro.php">Crear cuenta</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>