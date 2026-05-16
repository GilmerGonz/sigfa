# Arquitectura del Sistema - SIGFA V2

## Estructura MVC Personalizada
El sistema sigue un patrón Modelo-Vista-Controlador (MVC) diseñado para ser ligero y eficiente, sin dependencias de frameworks pesados.

### Directorios Principales
- `public/index.php`: El **Front Controller**. Centraliza todas las peticiones y las enruta al controlador correspondiente.
- `app/controllers/`: Contiene la lógica de control. 14 controladores gestionan desde la autenticación hasta los despachos complejos.
- `app/models/`: Contiene la lógica de negocio y acceso a datos. 15 modelos interactúan con la base de datos mediante PDO.
- `app/views/`: Plantillas PHP organizadas por módulos.
- `config/db.php`: Implementación del patrón **Singleton** para la conexión a la base de datos, garantizando una única instancia de conexión.

## Stack Tecnológico
- **Backend:** PHP 8.2+ con tipado estricto.
- **Frontend:** JavaScript Vanilla + CSS personalizado con estética **Glassmorphism**.
- **Base de Datos:** MySQL/MariaDB con soporte para transacciones ACID.
- **Reportes:** DomPDF para generación de documentos institucionales.
