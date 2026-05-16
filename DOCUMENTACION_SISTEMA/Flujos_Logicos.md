# Flujos Lógicos y Reglas de Negocio

## 1. Ciclo de Despacho Inteligente
El flujo de despacho ha sido refactorizado para soportar validaciones granulares:

1. **Identificación:** Búsqueda AJAX de pacientes con alertas automáticas de identidad (>10 años).
2. **Validación Cruzada:** Al añadir un ítem, el sistema consulta el histórico de **Principios Activos** entregados.
3. **Control Dinámico:**
   - **General:** Ventana de 24h (Advertencia).
   - **Alto Costo:** Ciclo asignado (7-180 días) con Bloqueo Estricto.
4. **Reserva de Stock:** Aplicación de lógica **FIFO** (First-In, First-Out) sobre lotes de inventario.

## 2. Gestión de Inventario y Kardex
Cada movimiento genera una traza inalterable en el Kardex:
- **Entrada:** Registro de lotes con trazabilidad logística (guía, chofer, placa).
- **Salida:** Descuento automático por fecha de vencimiento más próxima.
- **Ajuste:** Corrección auditada por administrador.
- **Anulación:** Reversión completa de stock y logs compensatorios.

## 3. Sistema de Alertas
- **Vencimiento:** Generación automática de alertas para lotes con < 30 días de vigencia.
- **Stock:** Monitoreo de niveles mínimos por medicamento.
- **Identidad:** Alertas de actualización de datos para pacientes (Cédula de Identidad).
