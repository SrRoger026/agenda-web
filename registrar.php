<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
$usuarioId = (int)$_SESSION['uid'];

require __DIR__ . '/conexion.php';

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$resCategorias = $mysqli->query('SELECT id, nombre FROM categorias ORDER BY nombre ASC');
$categorias = $resCategorias->fetch_all(MYSQLI_ASSOC);

$titulo = $fecha = $hora = $descripcion = '';
$categoriaId = 0;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo       = trim($_POST['titulo'] ?? '');
    $fecha        = trim($_POST['fecha'] ?? '');
    $hora         = trim($_POST['hora'] ?? '');
    $categoriaId  = (int)($_POST['categoria_id'] ?? 0);
    $descripcion  = trim($_POST['descripcion'] ?? '');

    if ($titulo === '') {
        $errores['titulo'] = 'El título es obligatorio.';
    }
    if ($categoriaId <= 0) {
        $errores['categoria_id'] = 'Selecciona una categoría válida.';
    }

    if (empty($errores)) {
        $horaBD = !empty($hora) ? $hora : null;
        $descBD = !empty($descripcion) ? $descripcion : null;

        // INSERT con el usuario_id del usuario logueado
        $stmt = $mysqli->prepare('INSERT INTO eventos (usuario_id, titulo, fecha, hora, categoria_id, descripcion) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssis', $usuarioId, $titulo, $fecha, $horaBD, $categoriaId, $descBD);
        $stmt->execute();
        $stmt->close();

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Evento - AgendaWeb</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-box {
            max-width: 500px;
            margin: 2rem auto;
            background: var(--bg-surface, #fff);
            padding: 2rem;
            border-radius: var(--radius-card, 12px);
            border: 1px solid var(--border-color, #E2E8F0);
            box-shadow: var(--shadow-sm, 0 1px 3px rgba(15,23,42,0.05));
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
        .form-group input, .form-group select, .form-group textarea {
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--border-color, #E2E8F0);
            border-radius: var(--radius-input, 8px);
            font-family: var(--font-body, sans-serif);
            font-size: 0.95rem;
            outline: none;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--brand-accent, #0F766E);
        }
        .form-error {
            color: #991B1B;
            font-size: 0.8rem;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }
        .btn-submit {
            background-color: var(--brand-accent, #0F766E);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-button, 8px);
            cursor: pointer;
            font-weight: 600;
        }
        .btn-submit:hover {
            background-color: var(--brand-accent-hover, #115E59);
        }
        .btn-cancel {
            color: var(--text-secondary, #475569);
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header" style="border-bottom: 1px solid var(--border-color, #E2E8F0); padding-bottom: 1rem; margin-bottom: 2rem;">
            <h1>Agenda<span>Web</span></h1>
        </header>

        <main>
            <div class="form-box">
                <h2 style="margin-bottom: 1.5rem; font-family: var(--font-heading, sans-serif); color: var(--text-primary, #0F172A);">Nuevo Evento</h2>

                <form action="registrar.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="titulo">Título *</label>
                        <input type="text" name="titulo" id="titulo" value="<?= e($titulo) ?>" required>
                        <?php if (isset($errores['titulo'])): ?>
                            <span class="form-error"><?= e($errores['titulo']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="<?= e($fecha) ?>">
                    </div>

                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <input type="time" name="hora" id="hora" value="<?= e($hora) ?>">
                    </div>

                    <div class="form-group">
                        <label for="categoria_id">Categoría *</label>
                        <select name="categoria_id" id="categoria_id" required>
                            <option value="">-- Selecciona una categoría --</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= $categoriaId === (int)$cat['id'] ? 'selected' : '' ?>>
                                    <?= e($cat['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores['categoria_id'])): ?>
                            <span class="form-error"><?= e($errores['categoria_id']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="4"><?= e($descripcion) ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Guardar evento</button>
                        <a href="index.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>