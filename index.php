<?php
require_once 'conexion.php';

// 1. Consulta de eventos con JOIN
$sqlEventos = 'SELECT e.id, e.titulo, e.fecha, e.hora, e.descripcion,
                      c.nombre AS categoria
                 FROM eventos e
                 JOIN categorias c ON c.id = e.categoria_id
                ORDER BY e.fecha ASC, e.hora ASC';

$resEventos = $mysqli->query($sqlEventos);
$eventos = $resEventos ? $resEventos->fetch_all(MYSQLI_ASSOC) : [];

// 2. Consulta de resumen por categoría con GROUP BY
$sqlResumen = 'SELECT c.nombre, COUNT(e.id) AS total
                 FROM categorias c
                 LEFT JOIN eventos e ON e.categoria_id = c.id
                GROUP BY c.id, c.nombre
                ORDER BY c.nombre ASC';

$resResumen = $mysqli->query($sqlResumen);
$resumen = $resResumen ? $resResumen->fetch_all(MYSQLI_ASSOC) : [];

$mysqli->close();

if (!function_exists('e')) {
    function e(?string $texto): string {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

function formatearFecha(string $fecha): string {
    return date('d/m/Y', strtotime($fecha));
}

function mostrarEvento(array $ev): string {
    $html  = '<article class="card">';
    $html .= '<span class="card__badge">' . e($ev['categoria']) . '</span>';
    $html .= '<h3 class="card__title">' . e($ev['titulo']) . '</h3>';

    $cuando = formatearFecha($ev['fecha']);
    if (!empty($ev['hora'])) {
        $cuando .= ' · ' . substr($ev['hora'], 0, 5);
    }
    $html .= '<p class="card__meta"><time datetime="' . e($ev['fecha']) . '">📅 ' . e($cuando) . '</time></p>';

    if (!empty($ev['descripcion'])) {
        $html .= '<p class="card__text">' . e($ev['descripcion']) . '</p>';
    }

    $id = (int) $ev['id'];
    $html .= '<div class="card__actions">'
           . '<a href="editar.php?id=' . $id . '" class="btn-sm btn-secondary">Editar</a>'
           . '<form method="post" action="borrar.php" class="form-inline" style="display:inline;">'
           . '<input type="hidden" name="id" value="' . $id . '">'
           . '<button type="submit" class="btn-sm btn-danger">Borrar</button>'
           . '</form></div>';

    return $html . '</article>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgendaWeb - Mis Eventos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    .resumen { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0 20px; }
    .resumen .card__badge { background: #e2e8f0; color: #334155; font-size: 0.85rem; padding: 4px 10px; border-radius: 12px; }
  </style>
</head>
<body class="layout">

  <header class="site-header">
    <div class="contenedor site-header__inner">
      <a href="index.php" class="logo">Agenda<span>Web</span></a>
      <nav class="nav">
        <a href="index.php" class="nav__link is-active">Mis eventos</a>
        <a href="registrar.php" class="nav__link">Nuevo evento</a>
      </nav>
    </div>
  </header>

  <main class="contenedor">
    
    <?php if (isset($_GET['ok'])): ?>
      <div class="alert alert--ok" role="status">✅ Evento guardado correctamente.</div>
    <?php endif; ?>

    <div class="page__header">
      <div>
        <h1 class="page__title">Mis eventos</h1>
        <p class="page__subtitle"><?= count($eventos) ?> eventos registrados</p>
      </div>
      <a href="registrar.php" class="btn-primary">+ Nuevo evento</a>
    </div>

    <!-- PASO 6: Resumen dinámico por categoría -->
    <div class="resumen">
      <?php foreach ($resumen as $r): ?>
        <span class="card__badge"><strong><?= e($r['nombre']) ?>:</strong> <?= (int) $r['total'] ?></span>
      <?php endforeach; ?>
    </div>

    <?php if (empty($eventos)): ?>
      <div class="empty-state">
        <p>Aún no tienes eventos registrados.</p>
        <a href="registrar.php" class="btn-primary">Registrar el primero</a>
      </div>
    <?php else: ?>
      <section class="card-list" aria-label="Lista de eventos">
        <?php foreach ($eventos as $ev): ?>
          <?= mostrarEvento($ev) ?>
        <?php endforeach; ?>
      </section>
    <?php endif; ?>

  </main>

  <footer class="site-footer">
    <div class="contenedor">AgendaWeb · Rogelio Cabrera Sandoval · 2026</div>
  </footer>

</body>
</html>