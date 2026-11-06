<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
$usuarioId     = (int) $_SESSION['uid'];
$usuarioNombre = $_SESSION['nombre'] ?? '';

require __DIR__ . '/conexion.php';

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

$stmt = $mysqli->prepare(
    'SELECT c.nombre, COUNT(e.id) AS total
       FROM categorias c
       LEFT JOIN eventos e ON e.categoria_id = c.id AND e.usuario_id = ?
      GROUP BY c.id, c.nombre
      ORDER BY c.nombre'
);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$resumen = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$q = mb_substr(trim($_GET['q'] ?? ''), 0, 60);
$categoriaId = (int) ($_GET['categoria'] ?? 0);

$categorias = $mysqli->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
$idsValidos = array_map('intval', array_column($categorias, 'id'));
if (!in_array($categoriaId, $idsValidos, true)) {
    $categoriaId = 0;
}
$buscando = ($q !== '' || $categoriaId !== 0);

$condiciones = ['e.usuario_id = ?'];
$tipos       = 'i';
$valores     = [$usuarioId];

if ($q !== '') {
    $patron = '%' . addcslashes($q, '%_\\') . '%';
    $condiciones[] = '(e.titulo LIKE ? OR e.descripcion LIKE ?)';
    $tipos       .= 'ss';
    $valores[]   = $patron;
    $valores[]   = $patron;
}

if ($categoriaId !== 0) {
    $condiciones[] = 'e.categoria_id = ?';
    $tipos       .= 'i';
    $valores[]   = $categoriaId;
}

$sql = 'SELECT e.id, e.titulo, e.fecha, e.hora, e.descripcion,
               c.nombre AS categoria
          FROM eventos e
          JOIN categorias c ON c.id = e.categoria_id
         WHERE ' . implode(' AND ', $condiciones) . '
         ORDER BY e.fecha, e.hora';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($tipos, ...$valores);
$stmt->execute();
$eventos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgendaWeb - Mis eventos</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .top-nav { display: flex; align-items: center; gap: 1rem; }
        .user-greeting { color: var(--text-secondary, #475569); font-size: 0.95rem; }
        .user-greeting strong { color: var(--text-primary, #0F172A); }
        .btn-logout {
            color: #ef4444; text-decoration: none; font-weight: 500; font-size: 0.9rem;
            padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid #fecaca;
            background: #fff; transition: all 0.2s ease;
        }
        .btn-logout:hover { background: #fee2e2; }
        .events-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem; margin-top: 1.5rem;
        }
        .summary-bar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin: 1rem 0 1.5rem 0; }
        .category-pill {
            background: #e2e8f0; color: #0F172A; padding: 0.35rem 0.75rem;
            border-radius: 9999px; font-size: 0.85rem; font-weight: 500;
        }
        .badge-cat {
            display: inline-block; background: #CCFBF1; color: #0F766E;
            font-weight: 600; font-size: 0.75rem; padding: 0.2rem 0.6rem;
            border-radius: 9999px; text-transform: uppercase; margin-bottom: 0.75rem;
        }
        .buscador { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 18px; }
        .buscador .form-input {
            flex: 1 1 220px; width: auto; padding: 0.5rem 0.75rem;
            border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.95rem;
        }
        .buscador .form-select {
            flex: 0 1 210px; width: auto; padding: 0.5rem 0.75rem;
            border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.95rem;
        }
        .btn-primary {
            background: #0F766E; color: #fff; border: none; padding: 0.5rem 1rem;
            border-radius: 8px; font-weight: 500; cursor: pointer;
        }
        .btn-secondary {
            background: #e2e8f0; color: #0F172A; text-decoration: none;
            display: inline-flex; align-items: center; padding: 0.5rem 1rem;
            border-radius: 8px; font-weight: 500; font-size: 0.95rem;
        }
        .empty-state { padding: 2rem 0; color: #475569; }
        .empty-state p { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #E2E8F0; padding-bottom: 1rem; margin-bottom: 2rem;">
            <h1>Agenda<span>Web</span></h1>
            <div class="top-nav">
                <span class="user-greeting">Hola, <strong><?= e($usuarioNombre) ?></strong></span>
                <a href="registrar.php" class="btn" style="background: #0F766E; color: #fff; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 500;">+ Nuevo evento</a>
                <a href="logout.php" class="btn-logout">Salir</a>
            </div>
        </header>

        <main>
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div>
                    <h2 style="color: #0F172A; margin-bottom: 0.25rem;">Mis eventos</h2>
                    <p class="page__subtitle" style="color: #475569; font-size: 0.95rem;">
                        <?php if ($buscando): ?>
                            <?= count($eventos) ?> resultado<?= count($eventos) === 1 ? '' : 's' ?><?= $q !== '' ? ' para «' . e($q) . '»' : '' ?>
                        <?php else: ?>
                            <?= count($eventos) ?> eventos registrados
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="summary-bar">
                <?php foreach ($resumen as $cat): ?>
                    <span class="category-pill"><?= e($cat['nombre']) ?>: <?= (int)$cat['total'] ?></span>
                <?php endforeach; ?>
            </div>

            <form class="buscador" method="get" action="index.php" role="search">
                <input class="form-input" type="search" name="q" value="<?= e($q) ?>" maxlength="60"
                       placeholder="Buscar en título o descripción…" aria-label="Buscar eventos">
                <select class="form-select" name="categoria" aria-label="Filtrar por categoría">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $categoriaId === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary">Buscar</button>
                <?php if ($buscando): ?>
                    <a href="index.php" class="btn-secondary">Limpiar</a>
                <?php endif; ?>
            </form>

            <div class="events-grid">
                <?php if (empty($eventos) && $buscando): ?>
                    <div class="empty-state">
                        <p>Sin resultados para tu búsqueda.</p>
                        <a href="index.php" class="btn-secondary">Ver todos mis eventos</a>
                    </div>
                <?php elseif (empty($eventos)): ?>
                    <div class="empty-state">
                        <p>No tienes eventos registrados aún.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($eventos as $ev): ?>
                        <?php 
                            $fecha = !empty($ev['fecha']) ? date('d/m/Y', strtotime($ev['fecha'])) : 'Sin fecha';
                            $hora  = !empty($ev['hora']) ? date('H:i', strtotime($ev['hora'])) : '';
                        ?>
                        <article class="card" style="background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(15,23,42,0.05);">
                            <span class="badge-cat"><?= e($ev['categoria']) ?></span>
                            <h3 style="color: #0F172A; margin-bottom: 0.5rem;"><?= e($ev['titulo']) ?></h3>
                            <p style="color: #475569; font-size: 0.875rem; margin-bottom: 0.75rem;">
                                📅 <?= e($fecha) ?> <?= $hora ? '· ⏰ ' . e($hora) : '' ?>
                            </p>
                            <?php if (!empty($ev['descripcion'])): ?>
                                <p style="color: #0F172A; font-size: 0.95rem; margin-bottom: 1.25rem;"><?= e($ev['descripcion']) ?></p>
                            <?php endif; ?>
                            <div style="display: flex; gap: 0.75rem; border-top: 1px solid #E2E8F0; padding-top: 1rem;">
                                <a href="editar.php?id=<?= (int)$ev['id'] ?>" style="color: #0F766E; text-decoration: none; font-weight: 500; font-size: 0.9rem;">Editar</a>
                                <a href="borrar.php?id=<?= (int)$ev['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este evento?');" style="color: #ef4444; text-decoration: none; font-weight: 500; font-size: 0.9rem;">Borrar</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <footer style="margin-top: 4rem; text-align: center; color: #475569; font-size: 0.875rem; padding: 2rem 0; border-top: 1px solid #E2E8F0;">
            <p>AgendaWeb · Rogelio Cabrera Sandoval · 2026</p>
        </footer>
    </div>
</body>
</html>
