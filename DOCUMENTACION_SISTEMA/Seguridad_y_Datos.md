# Seguridad y Estructura de Datos

## Modelo de Datos
El esquema consta de 20 tablas core que garantizan la integridad referencial:
- **Catálogos:** Medicamentos, Principios Activos, Proveedores, Almacenes, Médicos, Patologías.
- **Transaccional:** Despachos, Transferencias, Devoluciones.
- **Auditoría:** Kardex, Alertas, Logs de Auditoría, Logs de Descargas.

## Seguridad Implementada
1. **Acceso:** Sistema de 6 roles con permisos granulares (RBAC). 
2. **Criptografía:** Contraseñas protegidas mediante Bcrypt (`password_hash`).
3. **Integridad:** Protección contra SQL Injection mediante sentencias preparadas (PDO).
4. **Protección Web:** Tokens CSRF para validación de origen en formularios críticos.
5. **Forense:** Registro de IP, Session ID y User Agent en cada movimiento de inventario (Kardex).

## Control de Roles Especiales
- **Administrador:** Único rol con capacidad de anular transacciones, ajustar stock manualmente y gestionar usuarios.
- **Alto Costo:** Permisos específicos para despachar grupos controlados (Psicotrópicos/Estupefacientes).
