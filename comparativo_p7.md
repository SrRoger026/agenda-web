# P7 · Comparativo: WordPress vs. Desarrollo a Medida

## 1. Evidencia del Sitio en WordPress
- **URL pública de prueba:** https://sixdestruction.s6-tastewp.com/
- **Captura:** Ver archivo adjunto evidencia_wordpress.png (muestra tema activo, menú con página "Acerca de nosotros" y entrada publicada).

---

## 2. Tabla Comparativa por Criterios

| Criterio | WordPress (CMS) | Desarrollo a Medida (PHP + MySQL nativo) |
| :--- | :--- | :--- |
| **1. Tiempo y costo inicial** | **Muy bajo / Rápido.** Prototipo listo en horas o días reutilizando temas y plugins existentes. | **Medio a Alto.** Requiere diseñar base de datos, rutas, sesiones, CRUD y frontend desde cero. |
| **2. Flexibilidad y personalización** | **Media a Limitada.** Eficiente en blogs y páginas estándar; rígido para flujos de negocio complejos sin hooks avanzados. | **Total / Ilimitada.** Lógica de negocio, consultas SQL y modelos de datos diseñados con precisión a la medida. |
| **3. Curva de aprendizaje** | **Baja.** Panel de administración estándar e intuitivo para usuarios no técnicos. | **Variable.** Depende del panel creado por el desarrollador; requiere capacitación al usuario final. |
| **4. Seguridad y mantenimiento** | **Dependiente de terceros.** Objetivo frecuente de ataques automáticos; requiere actualizar core, plugins y temas constantemente. | **Superficie de ataque reducida.** Sin código público de terceros; la seguridad depende de buenas prácticas (consultas preparadas, hash seguro). |
| **5. Rendimiento y recursos** | **Moderado / Carga pesada.** Carga scripts y hojas de estilo globales que a veces no se usan, requiriendo plugins de caché. | **Ligero / Alto rendimiento.** Ejecuta únicamente el código y las consultas SQL estrictamente necesarias. |

---

## 3. Conclusión Argumentada: Dos Escenarios

### Escenario A — Dónde conviene WordPress:
- **Caso:** Sitio web corporativo, blog de contenidos o catálogo informativo para un negocio que necesita salir en producción en dos semanas con presupuesto ajustado y personal no técnico administrando publicaciones.
- **Justificación:** Crear desarrollo a medida resultaría costoso e innecesario. WordPress resuelve nativamente el flujo editorial, SEO básico y formularios sin programar desde cero.

### Escenario B — Dónde conviene Desarrollo a Medida (PHP/MySQL):
- **Caso:** Plataformas transaccionales como **AgendaWeb**, sistemas SaaS con lógica propietaria, aislamiento estricto por usuario (`usuario_id`), procesamiento de métricas en tiempo real y alta concurrencia.
- **Justificación:** Forzar la arquitectura de WordPress para manejar aislamiento de datos genera sobrecarga innecesaria y deuda técnica. Desarrollar a medida garantiza transacciones SQL directas, menor superficie de ataque y óptimo consumo de recursos.