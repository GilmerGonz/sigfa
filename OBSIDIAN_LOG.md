# Registro de Progreso - Refactorización SIGFA V2
**Fecha:** 2026-05-14
**Documento de Referencia:** [Correcciones sigfa 27/04/2026](https://docs.google.com/document/d/1O2ZNOy-TCCrCeDFY6tLRF2C1rOBVbTVE/edit#heading=h.92zegurjrtdw)

## 📋 Resumen de Requerimientos
1. **Reubicación de Ciclos:** Mover "Ciclo Tratamiento" del registro de asegurado al módulo de despacho.
2. **Selector Dual en Despacho:** Selector de rango (7-180 días) o calendario para vigencia de entrega.
3. **Validación por Principio Activo:** Bloqueo inteligente por ítem en lugar de global por paciente.
4. **Validación Temporal:**
   - **Generales:** Soft warning (24h).
   - **Alto Costo:** Hard block (según ciclo asignado).
5. **Gestión de Stock/Entregas Parciales:** Cálculo dinámico de próxima fecha disponible.
6. **Mejoras UI/UX:** Resaltado en rojo de ítems bloqueados, permitir despacho parcial.
7. **Base de Datos y Auditoría:** Nuevos campos en `medicamento` y `despacho_detalle`, logs de auditoría.

## 🛠️ Estado de Implementación (Actualizado)

| Tarea | Estado | Notas |
| :--- | :--- | :--- |
| Análisis de Estructura de BD | ✅ Finalizado | Creada migración para `principios_activos` y campos de `medicamento` |
| Eliminación campo ciclo en Asegurado | ✅ Finalizado | Eliminado del modelo y la vista de asegurados |
| Implementación Selector Dual (Frontend) | ✅ Finalizado | Selector (Dropdown + Date) integrado por cada ítem en despacho |
| Lógica de Validación (Backend) | ✅ Finalizado | Validaciones migradas a Principio Activo y ejecutadas por AJAX |
| Soporte para Entregas Parciales | ✅ Finalizado | Captura de `cantidad_recetada` y cálculo dinámico de `fecha_proxima` |
| Mejoras UI en Carrito | ✅ Finalizado | Resaltado en rojo automático y mensaje de bloqueo para ítems restringidos |
| Actualización de Esquema de BD | ✅ Finalizado | Archivo final generado en `database/migration_final_despachos.sql` |

## 📝 Notas y Observaciones Finales
- **Validación 24h:** Ahora es un "Soft Warning" por Principio Activo. El usuario decide si continuar.
- **Validación Alto Costo:** Ahora es un "Hard Block" (Resaltado en Rojo) que impide finalizar el despacho hasta que el ítem sea removido o el ciclo se cumpla.
- **Selector Dual:** Permite configuraciones rápidas (21, 30, 90 días) o fecha exacta vía calendario.
- **Población de Datos:** Se recomienda ejecutar `migrate_principios.php` para asignar los principios activos existentes antes de usar el sistema.
- **Documentación:** Se ha generado el archivo [Funcionalidades_Sistema.md](file:///c:/Users/Gilmer%20De%20Jes%C3%BAs/Desktop/SIGFA_V2-main/Funcionalidades_Sistema.md) con el catálogo detallado de capacidades del sistema.
