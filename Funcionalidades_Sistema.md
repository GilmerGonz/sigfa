# Catálogo de Funcionalidades - SIGFA V2
**Fecha de Generación:** 2026-05-15

## 📋 Resumen de Módulos y Capacidades

El sistema **SIGFA_V2** ha sido refactorizado para centralizar la lógica de negocio en el **Principio Activo** y el control dinámico de las entregas, garantizando trazabilidad total y seguridad en la dispensación.

| Módulo | Funcionalidad | Descripción Técnica / Regla de Negocio |
| :--- | :--- | :--- |
| **Pacientes (Asegurados)** | **Registro y Gestión** | Registro de datos demográficos y clínicos. Búsqueda por Cédula, Historia Médica o Partida. |
| | **Alertas de Identidad** | Aviso automático para actualizar datos si el paciente es >10 años o tiene >1 año sin verificación. |
| | **Control de Indentificación** | Bloqueo o alerta si un niño con Partida de Nacimiento cumple 10 años y requiere Cédula. |
| **Medicamentos** | **Catálogo Maestro** | Clasificación por Tipo (General, Alto Costo, Controlado) y Grupo Terapéutico. |
| | **Principios Activos** | Vinculación de medicamentos a su principio activo para evitar duplicidad cruzada. |
| | **Stock Mínimo** | Alerta visual cuando el inventario total cae por debajo del umbral crítico definido. |
| **Inventario (Lotes)** | **Lógica FIFO** | Despacho automático priorizando los lotes con la fecha de vencimiento más cercana. |
| | **Gestión de Lotes** | Registro de entradas con número de guía, chofer, placa y precio unitario. |
| | **Alertas de Vencimiento** | Notificación de lotes que vencerán en 30 días o menos (Niveles: Advertencia/Crítico). |
| | **Kardex de Auditoría** | Historial inalterable de cada movimiento (Entrada, Salida, Ajuste, Anulación). |
| **Despacho (Entrega)** | **Validación 24h** | Aviso si se intenta entregar el mismo principio activo a un paciente en menos de 24h. |
| | **Ciclos Dinámicos** | Selector de período (7 a 180 días) para calcular la próxima fecha de entrega. |
| | **Bloqueo de Alto Costo** | Impedimento estricto de entrega si el tratamiento previo aún está vigente (por ítem). |
| | **Entregas Parciales** | Ajuste de la fecha de disponibilidad según la cantidad exacta entregada al paciente. |
| | **Tickets Únicos** | Generación de comprobantes con formato `DSP-YYYYMMDD-0001` y UUID de seguridad. |
| **Administración** | **Anulación Reversible** | Permite anular despachos devolviendo stock a los lotes originales y auditando en Kardex. |
| | **Ajustes de Stock** | Corrección manual de inventario con motivo obligatorio para auditoría. |
| | **Control de Roles** | Permisos granulares (Admin, Almacenista, Auxiliares, Farmacéutico, Kardista). |
| **Seguridad** | **Protección CSRF** | Tokens de seguridad en todos los formularios para prevenir ataques externos. |
| | **Registro de IPs** | Almacenamiento de la dirección IP y agente de usuario en cada transacción crítica. |

---
> [!TIP]
> Esta documentación sirve como base para el entrenamiento de usuarios y auditorías de procesos farmacéuticos.
