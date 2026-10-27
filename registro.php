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

$nombre = '';
$correo = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = mb_strtolower(trim($_POST['correo'] ?? ''));
    $clave  = $_POST['clave']  ?? '';
    $clave2 = $_POST['clave2'] ?? '';

    if ($nombre === '' || mb_strlen($nombre) > 80) {
        $errores['nombre'] = 'Escribe tu nombre (máximo 80 caracteres).';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = 'Escribe un correo válido.';
    }
    if (mb_strlen($clave) < 8) {
        $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($clave !== $clave2) {
        $errores['clave2'] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        try {
            $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, correo, clave_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $nombre, $correo, $hash);
            $stmt->execute();
            $stmt->close();
            header('Location: login.php?registrado=1');
            exit;
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() === 1062) {
                $errores['correo'] = 'Ese correo ya tiene una cuenta.';
            } else {
                $errores['general'] = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - AgendaWeb</title>
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
        .form-error {
            color: #991B1B;
            font-size: 0.8rem;
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
        .alert-box--error {
            background-color: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FCA5A5;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-input, 8px);
            margin-bottom: 1rem;
            font-size: 0.9rem;
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
                <h2 class="auth-title">Crear Cuenta</h2>

                <?php if (isset($errores['general'])): ?>
                    <div class="alert-box--error"><?= e($errores['general']) ?></div>
                <?php endif; ?>

                <form action="registro.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" name="nombre" id="nombre" maxlength="80" value="<?= e($nombre) ?>" required>
                        <?php if (isset($errores['nombre'])): ?>
                            <span class="form-error"><?= e($errores['nombre']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico *</label>
                        <input type="email" name="correo" id="correo" value="<?= e($correo) ?>" required>
                        <?php if (isset($errores['correo'])): ?>
                            <span class="form-error"><?= e($errores['correo']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="clave">Contraseña (mínimo 8 caracteres) *</label>
                        <input type="password" name="clave" id="clave" minlength="8" autocomplete="new-password" required>
                        <?php if (isset($errores['clave'])): ?>
                            <span class="form-error"><?= e($errores['clave']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="clave2">Confirmar Contraseña *</label>
                        <input type="password" name="clave2" id="clave2" minlength="8" autocomplete="new-password" required>
                        <?php if (isset($errores['clave2'])): ?>
                            <span class="form-error"><?= e($errores['clave2']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="auth-footer">
                        <button type="submit" class="btn-submit">Crear cuenta</button>
                        <a href="login.php">Ya tengo cuenta</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>