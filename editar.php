<?php
require_once 'conexion.php';

$errores = [];
$id = $_GET['id'] ?? $_POST['id'] ?? null;

// Redirigir si no hay ID válido
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    header('Location: index.php');
    exit;
}

$id = (int) $id;

// PROCESAR FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo      = trim($_POST['title']       ?? '');
    $fecha       = trim($_POST['date']        ?? '');
    $hora        = trim($_POST['time']        ?? '');
    $categoria   = trim($_POST['category']    ?? '');
    $descripcion = trim($_POST['description'] ?? '');

    // Validaciones
    if ($titulo === '') {
        $errores['title'] = 'El título del evento es obligatorio.';
    } elseif (mb_strlen($titulo) > 120) {
        $errores['title'] = 'El título no puede superar los 120 caracteres.';
    }

    if ($fecha === '') {
        $errores['date'] = 'La fecha es obligatoria.';
    }

    if ($categoria === '') {
        $errores['category'] = 'Selecciona una categoría válida.';
    }

    // Actualizar en MySQL si no hay errores
    if (empty($errores)) {
        $horaBD = ($hora === '') ? NULL : $hora;
        $descBD = ($descripcion === '') ? NULL : $descripcion;

        $sql = "UPDATE eventos SET titulo = ?, fecha = ?, hora = ?, categoria = ?, descripcion = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssi", $titulo, $fecha, $horaBD, $categoria, $descBD, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            header('Location: index.php?ok=1');
            exit;
        } else {
            $errores['general'] = 'No se pudo actualizar el evento.';
        }
    }
} else {
    // CONSULTAR DATOS ACTUALES DEL EVENTO (GET)
    $sql = "SELECT titulo, fecha, hora, categoria, descripcion FROM eventos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $stmt->close();
        $mysqli->close();
        header('Location: index.php');
        exit;
    }

    $evento = $res->fetch_assoc();
    $titulo      = $evento['titulo'];
    $fecha       = $evento['fecha'];
    $hora        = $evento['hora'];
    $categoria   = $evento['categoria'];
    $descripcion = $evento['descripcion'];
    $stmt->close();
}

$mysqli->close();

function e(?string $texto): string {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgendaWeb — Editar Evento</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="layout">

  <header class="site-header">
    <div class="contenedor site-header__inner">
      <a href="index.php" class="logo">Agenda<span>Web</span></a>
      <nav class="nav">
        <a href="index.php" class="nav__link">Mis eventos</a>
        <a href="registrar.php" class="nav__link">Nuevo evento</a>
      </nav>
    </div>
  </header>

  <main class="contenedor">
    <div class="page__header">
      <h1 class="page__title">Editar Evento #<?= $id ?></h1>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="alert alert--danger" style="color: #991b1b; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
        <?= e($errores['general']) ?>
      </div>
    <?php endif; ?>

    <section class="card" style="max-width: 600px; margin: 0 auto;">
      <form action="" method="POST" novalidate>
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="event-title" class="form-label">Título del evento *</label>
          <input type="text" id="event-title" name="title" class="form-control" value="<?= e($titulo) ?>">
          <?php if (isset($errores['title'])): ?>
            <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= e($errores['title']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-row" style="display: flex; gap: 16px; margin-bottom: 16px;">
          <div class="form-group" style="flex: 1;">
            <label for="event-date" class="form-label">Fecha *</label>
            <input type="date" id="event-date" name="date" class="form-control" value="<?= e($fecha) ?>">
            <?php if (isset($errores['date'])): ?>
              <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= e($errores['date']) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group" style="flex: 1;">
            <label for="event-time" class="form-label">Hora</label>
            <input type="time" id="event-time" name="time" class="form-control" value="<?= e($hora) ?>">
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="event-category" class="form-label">Categoría *</label>
          <select id="event-category" name="category" class="form-control">
            <option value="">-- Selecciona una categoría --</option>
            <option value="Trabajo" <?= $categoria === 'Trabajo' || $categoria === 'trabajo' ? 'selected' : '' ?>>Trabajo</option>
            <option value="Reunión" <?= $categoria === 'Reunión' || $categoria === 'reunion' ? 'selected' : '' ?>>Reunión con cliente</option>
            <option value="Personal" <?= $categoria === 'Personal' || $categoria === 'personal' ? 'selected' : '' ?>>Personal / Salud</option>
            <option value="Proyecto" <?= $categoria === 'Proyecto' || $categoria === 'proyecto' || $categoria === 'estudio' ? 'selected' : '' ?>>Revisión de Proyecto / Estudio</option>
            <option value="Ocio" <?= $categoria === 'Ocio' || $categoria === 'ocio' ? 'selected' : '' ?>>Ocio / Deporte</option>
          </select>
          <?php if (isset($errores['category'])): ?>
            <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= e($errores['category']) ?></span>
          <?php endif; ?>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label for="event-description" class="form-label">Notas / Descripción</label>
          <textarea id="event-description" name="description" class="form-control" rows="3"><?= e($descripcion) ?></textarea>
        </div>

        <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end;">
          <a href="index.php" class="btn btn-secondary" style="text-decoration: none; padding: 8px 16px; border-radius: 6px; background: #e2e8f0; color: #334155;">Cancelar</a>
          <button type="submit" class="btn btn-primary">Actualizar Evento</button>
        </div>
      </form>
    </section>
  </main>

  <footer class="site-footer">
    <div class="contenedor">AgendaWeb · Rogelio Cabrera Sandoval · 2026</div>
  </footer>

</body>
</html>