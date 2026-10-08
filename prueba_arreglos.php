<?php
// Arreglo Indexado: valores por posición (inicia en 0)
$categorias = ['trabajo', 'personal', 'estudio', 'ocio'];
echo $categorias[0] . '<br>';     // Imprime: trabajo
echo count($categorias) . '<br>';  // Imprime: 4

// Arreglo Asociativo: valores por clave
$evento = ['titulo' => 'Examen de Cálculo', 'fecha' => '2026-10-08'];
echo $evento['titulo'];              // Imprime: Examen de Cálculo