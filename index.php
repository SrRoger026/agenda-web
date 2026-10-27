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

// 1. Resumen por categoría filtrado por usuario (conserva ceros)
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

// 2. Eventos del usuario autenticado
$stmt = $mysqli->prepare(
    'SELECT e.id, e.titulo, e.fecha, e.hora, e.descripcion, c.nombre AS categoria
       FROM eventos e
       JOIN categorias c ON c.id = e.categoria_id
      WHERE e.usuario_id = ?
      ORDER BY e.fecha, e.hora'
);
$stmt->bind_param('i', $usuarioId);
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
        /* Ajustes específicos para la barra superior con sesión */
        .top-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-greeting {
            color: var(--text-secondary, #475569);
            font-size: 0.95rem;
        }
        .user-greeting strong {
            color: var(--text-primary, #0F172A);
        }
        .btn-logout {
            color: #ef4444;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-button, 8px);
            border: 1px solid #fecaca;
            background: #fff;
            transition: all 0.2s ease;
        }
        .btn-logout:hover {
            background: #fee2e2;
        }
        /* Contenedor en cuadrícula para que las tarjetas no se extiendan a todo lo ancho */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .summary-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0 2rem 0;
        }
        .category-pill {
            background: #e2e8f0;
            color: var(--text-primary, #0F172A);
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .badge-cat {
            display: inline-block;
            background: var(--brand-accent-light, #CCFBF1);
            color: var(--brand-accent, #0F766E);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <header class="app-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color, #E2E8F0); padding-bottom: 1rem; margin-bottom: 2rem;">
            <h1>Agenda<span>Web</span></h1>
            <div class="top-nav">
                <span class="user-greeting">Hola, <strong><?= e($usuarioNombre) ?></strong></span>
                <a href="registrar.php" class="btn" style="background: var(--brand-accent, #0F766E); color: #fff; padding: 0.5rem 1rem; border-radius: var(--radius-button, 8px); text-decoration: none; font-weight: 500;">+ Nuevo evento</a>
                <a href="logout.php" class="btn-logout">Salir</a>
            </div>
        </header>

        <main>
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div>
                    <h2 style="font-family: var(--font-heading, sans-serif); color: var(--text-primary, #0F172A); margin-bottom: 0.25rem;">Mis eventos</h2>
                    <p style="color: var(--text-secondary, #475569); font-size: 0.95rem;"><?= count($eventos) ?> eventos registrados</p>
                </div>
            </div>

            <!-- Resumen de Categorías en píldoras -->
            <div class="summary-bar">
                <?php foreach ($resumen as $cat): ?>
                    <span class="category-pill"><?= e($cat['nombre']) ?>: <?= (int)$cat['total'] ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Grid de eventos -->
            <div class="events-grid">
                <?php if (empty($eventos)): ?>
                    <p style="color: var(--text-secondary, #475569);">No tienes eventos registrados aún.</p>
                <?php else: ?>
                    <?php foreach ($eventos as $ev): ?>
                        <?php 
                            $fecha = !empty($ev['fecha']) ? date('d/m/Y', strtotime($ev['fecha'])) : 'Sin fecha';
                            $hora  = !empty($ev['hora']) ? date('H:i', strtotime($ev['hora'])) : '';
                        ?>
                        <article class="card" style="background: var(--bg-surface, #fff); border: 1px solid var(--border-color, #E2E8F0); border-radius: var(--radius-card, 12px); padding: 1.5rem; box-shadow: var(--shadow-sm, 0 1px 3px rgba(15,23,42,0.05));">
                            <span class="badge-cat"><?= e($ev['categoria']) ?></span>
                            <h3 style="font-family: var(--font-heading, sans-serif); color: var(--text-primary, #0F172A); margin-bottom: 0.5rem;"><?= e($ev['titulo']) ?></h3>
                            <p style="color: var(--text-secondary, #475569); font-size: 0.875rem; margin-bottom: 0.75rem;">
                                📅 <?= e($fecha) ?> <?= $hora ? '· ⏰ ' . e($hora) : '' ?>
                            </p>
                            <?php if (!empty($ev['descripcion'])): ?>
                                <p style="color: var(--text-primary, #0F172A); font-size: 0.95rem; margin-bottom: 1.25rem;"><?= e($ev['descripcion']) ?></p>
                            <?php endif; ?>
                            <div style="display: flex; gap: 0.75rem; border-top: 1px solid var(--border-color, #E2E8F0); padding-top: 1rem;">
                                <a href="editar.php?id=<?= (int)$ev['id'] ?>" style="color: var(--brand-accent, #0F766E); text-decoration: none; font-weight: 500; font-size: 0.9rem;">Editar</a>
                                <a href="borrar.php?id=<?= (int)$ev['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este evento?');" style="color: #ef4444; text-decoration: none; font-weight: 500; font-size: 0.9rem;">Borrar</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <footer style="margin-top: 4rem; text-align: center; color: var(--text-secondary, #475569); font-size: 0.875rem; padding: 2rem 0; border-top: 1px solid var(--border-color, #E2E8F0);">
            <p>AgendaWeb · Rogelio Cabrera Sandoval · 2026</p>
        </footer>
    </div>
</body>
</html>