<?php
// ==============================================================================
// FASES 3, 4, 5 y 6: Procesamiento, Validación, Sanitización y Guardado
// ==============================================================================
$errores = [];
$titulo = '';
$fecha = '';
$hora = '';
$categoria = '';
$descripcion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fase 3: Recibir y limpiar espacios (trim y coalescencia nula ??)
    $titulo      = trim($_POST['title']       ?? '');
    $fecha       = trim($_POST['date']        ?? '');
    $hora        = trim($_POST['time']        ?? '');
    $categoria   = trim($_POST['category']    ?? '');
    $descripcion = trim($_POST['description'] ?? '');

    // Fase 4: Validaciones en el Servidor
    // Lista blanca para categoría
    $categoriasOK = ['trabajo', 'reunion', 'personal', 'proyecto', 'academico', 'ocio'];

    // Validar Título
    if ($titulo === '') {
        $errores['title'] = 'El título del evento es obligatorio.';
    } elseif (mb_strlen($titulo) > 120) {
        $errores['title'] = 'El título no puede superar los 120 caracteres.';
    }

    // Validar Fecha
    if ($fecha === '') {
        $errores['date'] = 'La fecha es obligatoria.';
    } elseif (!DateTime::createFromFormat('Y-m-d', $fecha)) {
        $errores['date'] = 'Ingresa una fecha válida.';
    }

    // Validar Categoría
    $catNormalizada = strtolower(str_replace([' ', 'ñ', '/'], ['', 'n', ''], $categoria));
    if ($categoria === '' || (!in_array($catNormalizada, $categoriasOK, true) && !in_array($categoria, ['Trabajo', 'Reunión', 'Personal', 'Proyecto'], true))) {
        $errores['category'] = 'Selecciona una categoría válida.';
    }

    // Fase 6: Guardar en MySQL si no hay errores
    if (empty($errores)) {
        require_once 'conexion.php';

        $horaBD = ($hora === '') ? NULL : $hora;
        $descBD = ($descripcion === '') ? NULL : $descripcion;

        $sql = "INSERT INTO eventos (titulo, fecha, hora, categoria, descripcion) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssss", $titulo, $fecha, $horaBD, $categoria, $descBD);

        if ($stmt->execute()) {
            $stmt->close();
            $mysqli->close();

            // Fase 7: Redirección PRG
            header('Location: index.php?ok=1');
            exit;
        } else {
            $errores['general'] = 'Ocurrió un error al intentar guardar el evento en la base de datos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgendaWeb — Registro de Eventos</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
  
  <!-- Hoja de Estilos CSS -->
  <link rel="stylesheet" href="styles.css">
</head>
<body class="layout">
  
  <header class="site-header">
    <div class="contenedor site-header__inner">
      <a href="index.php" class="logo">Agenda<span>Web</span></a>
      <nav class="nav">
        <a href="index.php" class="nav__link">Mis eventos</a>
        <a href="registrar.php" class="nav__link is-active">Nuevo evento</a>
      </nav>
    </div>
  </header>

  <main class="contenedor">
    <div class="page__header">
      <h1 class="page__title">Agendar Evento</h1>
    </div>

    <?php if (isset($errores['general'])): ?>
      <div class="alert alert--danger" style="color: #991b1b; background: #fef2f2; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
        <?= htmlspecialchars($errores['general']) ?>
      </div>
    <?php endif; ?>

    <div class="main-layout" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
      
      <!-- Formulario principal -->
      <section class="card">
        <div class="card-header" style="margin-bottom: 16px;">
          <h2 class="card-title" style="font-size: 1.25rem; margin: 0 0 4px 0;">Agendar Evento</h2>
          <p class="card-description" style="color: #64748b; margin: 0; font-size: 0.9rem;">Completa los datos requeridos para registrar un nuevo compromiso.</p>
        </div>

        <form id="event-form" class="event-form" action="" method="POST" novalidate>
          
          <div class="form-group" style="margin-bottom: 16px;">
            <label for="event-title" class="form-label">
              Título del evento <span class="required" style="color: #e11d48;">*</span>
            </label>
            <input type="text" id="event-title" name="title" class="form-control <?= isset($errores['title']) ? 'is-invalid' : '' ?>" 
                   placeholder="Ej. Reunión de Estrategia Trimestral" value="<?= htmlspecialchars($titulo) ?>">
            <?php if (isset($errores['title'])): ?>
              <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= htmlspecialchars($errores['title']) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-row" style="display: flex; gap: 16px; margin-bottom: 16px;">
            <div class="form-group" style="flex: 1;">
              <label for="event-date" class="form-label">
                Fecha <span class="required" style="color: #e11d48;">*</span>
              </label>
              <input type="date" id="event-date" name="date" class="form-control <?= isset($errores['date']) ? 'is-invalid' : '' ?>" 
                     value="<?= htmlspecialchars($fecha) ?>">
              <?php if (isset($errores['date'])): ?>
                <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= htmlspecialchars($errores['date']) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-group" style="flex: 1;">
              <label for="event-time" class="form-label">Hora</label>
              <input type="time" id="event-time" name="time" class="form-control" value="<?= htmlspecialchars($hora) ?>">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label for="event-category" class="form-label">Categoría <span class="required" style="color: #e11d48;">*</span></label>
            <select id="event-category" name="category" class="form-control <?= isset($errores['category']) ? 'is-invalid' : '' ?>">
              <option value="">-- Selecciona una categoría --</option>
              <option value="Trabajo" <?= $categoria === 'Trabajo' ? 'selected' : '' ?>>Trabajo</option>
              <option value="Reunión" <?= $categoria === 'Reunión' ? 'selected' : '' ?>>Reunión con cliente</option>
              <option value="Personal" <?= $categoria === 'Personal' ? 'selected' : '' ?>>Personal / Salud</option>
              <option value="Proyecto" <?= $categoria === 'Proyecto' ? 'selected' : '' ?>>Revisión de Proyecto</option>
            </select>
            <?php if (isset($errores['category'])): ?>
              <span class="error-message" style="color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;"><?= htmlspecialchars($errores['category']) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-group" style="margin-bottom: 16px;">
            <label for="event-description" class="form-label">Notas / Descripción</label>
            <textarea id="event-description" name="description" class="form-control" rows="3" placeholder="Añade detalles clave..."><?= htmlspecialchars($descripcion) ?></textarea>
          </div>

          <div class="form-actions" style="display: flex; gap: 12px; justify-content: flex-end;">
            <button type="reset" class="btn btn-secondary">Limpiar</button>
            <button type="submit" class="btn btn-primary">Guardar Cita</button>
          </div>
        </form>
      </section>

      <!-- Panel derecho de previsualización -->
      <section class="card">
        <div class="card-header" style="margin-bottom: 16px;">
          <h2 class="card-title" style="font-size: 1.25rem; margin: 0 0 4px 0;">Próximos Eventos</h2>
          <p class="card-description" style="color: #64748b; margin: 0; font-size: 0.9rem;">Tus citas y compromisos guardados en el sistema.</p>
        </div>

        <div class="events-list">
          <article class="event-item">
            <div class="card__badge">Trabajo</div>
            <h3 class="card__title" style="font-size: 1.05rem; margin-top: 8px;">Revisión de Diseño UI con Cliente</h3>
            <div class="card__meta">
              <span>📅 <?= date('d/m/Y'); ?></span>
              <span>⏰ 10:30 AM</span>
            </div>
            <p class="card__text">Presentación del sistema de diseño Serene Corporate.</p>
          </article>
        </div>
      </section>

    </div>
  </main>

  <footer class="site-footer">
    <div class="contenedor">AgendaWeb · Rogelio Cabrera Sandoval · 2026</div>
  </footer>

</body>
</html>