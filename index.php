<?php
// ==============================================================================
// PASO 7: Conexión y consulta a la Base de Datos MySQL
// ==============================================================================
require_once 'conexion.php';

$resultado = $mysqli->query(
    'SELECT id, titulo, fecha, hora, categoria, descripcion 
       FROM eventos 
      ORDER BY fecha ASC, hora ASC'
);

$eventos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
$mysqli->close();

// ==============================================================================
// PASO 3: Funciones pequeñas de ayuda
// ==============================================================================

// Previene ataques XSS
if (!function_exists('e')) {
    function e(?string $texto): string
    {
        return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// Formatea fecha de 'YYYY-MM-DD' a 'DD/MM/YYYY'
function formatearFecha(string $fecha): string
{
    return date('d/m/Y', strtotime($fecha));
}

// Mapea la clave de categoría a un nombre legible
function nombreCategoria(string $clave): string
{
    $nombres = [
        'trabajo'   => 'Trabajo',
        'reunion'   => 'Reunión con cliente',
        'personal'  => 'Personal / Salud',
        'proyecto'  => 'Revisión de Proyecto',
        'academico' => 'Académico',
        'estudio'   => 'Estudio',
        'ocio'      => 'Ocio / Deporte',
    ];
    
    $claveMin = strtolower($clave);
    return $nombres[$claveMin] ?? $clave;
}

// ==============================================================================
// PASO 4: Función principal para generar el HTML de cada tarjeta
// ==============================================================================
function mostrarEvento(array $ev): string
{
    $html  = '<article class="card">';
    $html .= '<span class="card__badge">' . e(nombreCategoria($ev['categoria'])) . '</span>';
    $html .= '<h3 class="card__title">' . e($ev['titulo']) . '</h3>';

    $cuando = formatearFecha($ev['fecha']);
    if (!empty($ev['hora'])) {
        $cuando .= ' · ' . substr($ev['hora'], 0, 5); // '10:30:00' -> '10:30'
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
  
  <!-- Fuentes -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
  
  <!-- Hoja de Estilos -->
  <link rel="stylesheet" href="styles.css">
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
    
    <!-- PASO 6: Mensaje de confirmación PRG -->
    <?php if (isset($_GET['ok'])): ?>
      <div class="alert alert--ok" role="status">✅ Evento guardado correctamente.</div>
    <?php endif; ?>

    <div class="page__header">
      <div>
        <h1 class="page__title">Mis eventos</h1>
        <!-- PASO 6: Contador dinámico -->
        <p class="page__subtitle"><?= count($eventos) ?> eventos registrados</p>
      </div>
      <a href="registrar.php" class="btn-primary">+ Nuevo evento</a>
    </div>

    <!-- PASO 6: Control de Estado Vacío vs Lista -->
    <?php if (empty($eventos)): ?>
      <div class="empty-state">
        <p>Aún no tienes eventos registrados.</p>
        <a href="registrar.php" class="btn-primary">Registrar el primero</a>
      </div>
    <?php else: ?>
      <!-- PASO 5: Iteración mediante foreach -->
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