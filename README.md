# Linea 3 Estudio Legal - Child Theme

Tema hijo basado en Twenty Twenty-Five para "Linea 3 Estudio Legal". Diseñado para soportar Full Site Editing (FSE) puro.

## Estándares
- **PHP**: 8.2+ estricto.
- **WP Version**: 6.9.4+ 
- Arquitectura pura FSE (`.html` templates), accesibilidad AA, y variables nativas vía `theme.json`.

## Estructura de Directorios
- `/templates`: Plantillas principales FSE (`home.html`, `index.html`, etc.)
- `/parts`: Componentes estructurales (`header.html`, `footer.html`, `sidebar.html`)
- `/patterns`: Patrones listos para inyectar diseños Figma (`hero-legal`, `servicios`, `testimonios`, `contacto`)
- `/assets`: Contendrá CSS/JS/Imágenes en el futuro según diseño.

## Instalación
1. Empaqueta el directorio `linea3-legal-child` en un zip: `zip -r linea3-legal-child.zip linea3-legal-child`
2. Ve a tu instalación WordPress (versión 6.9.4 preferida).
3. Asegúrate de tener instalado el tema padre **Twenty Twenty-Five**.
4. Dirígete a **Apariencia > Temas > Añadir nuevo > Subir**.
5. Sube el `.zip` y Actívate.

## Flujo de Trabajo Iterativo
Este tema está preparado como base. El siguiente paso consiste en integrar los diseños de Figma editando directamente `theme.json` para definir la paleta real y luego los patrones en `/patterns/` para armar la interfaz.
