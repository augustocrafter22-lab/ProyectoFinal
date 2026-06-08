# Reglamentación de Código Limpio

## Principios generales
- Escribir código claro, legible y autoexplicativo.
- Usar nombres descriptivos para variables, funciones, clases y selectores.
- Evitar abreviaturas oscuras y nombres genéricos como `data`, `temp` o `x`.
- Mantener funciones cortas y con una sola responsabilidad.
- Documentar solo cuando sea necesario; el código bien escrito debe explicar su intención.
- Usar consistencia en estilo y formato a lo largo del proyecto.

## JavaScript
- Declarar variables con `const` por defecto; usar `let` solo si el valor cambia.
- Evitar `var` y preferir la sintaxis moderna de ES6+.
- Escribir funciones pequeñas y reutilizables.
- Usar nombres de funciones que indiquen claramente la acción, por ejemplo `obtenerUsuarios()` o `mostrarAlerta()`.
- Evitar anidar demasiados `if`, `for` o callbacks; preferir funciones auxiliares y manejo de errores claro.
- Usar plantillas literales para concatenar cadenas cuando corresponda.
- Manejar errores con `try/catch` cuando se trabaje con operaciones asíncronas o procesos susceptibles a fallos.
- Declarar y exportar módulos de manera clara si se usa modularización.

## HTML
- Escribir una estructura semántica: `header`, `main`, `section`, `article`, `footer`, etc.
- Usar atributos `alt` descriptivos en imágenes.
- Evitar etiquetas innecesarias; mantener el HTML limpio y ligero.
- Ordenar el contenido con indentación consistente de dos espacios o cuatro espacios.
- Mantener el HTML accesible: usar `label` junto a `input`, roles cuando sea necesario y atributos ARIA básicos.
- No mezclar contenido con estilos o comportamiento; usar CSS para diseño y JavaScript para lógica.

## CSS
- Usar selectores claros y específicos sin abusar de selectores muy complejos.
- Utilizar nombres de clases legibles y consistentes, preferiblemente en minúsculas y con guiones (`.formulario-principal`).
- Separar las reglas por bloques lógicos: tipografía, colores, diseño y componentes.
- Evitar estilos en línea y preferir hojas de estilo externas.
- Usar variables CSS (`:root { --color-primario: #...; }`) para colores, espacios y valores repetidos.
- Mantener el orden de propiedades de forma coherente, por ejemplo agrupando diseño, caja y tipografía.
- Usar reset o normalización mínima para asegurar consistencia entre navegadores.

## Buenas prácticas del proyecto
- Mantener todos los archivos organizados en carpetas claras: `js/`, `css/`, `images/`, `components/`, etc.
- Usar un archivo de entrada principal para JavaScript y CSS que importe o vincule los módulos necesarios.
- Hacer revisiones periódicas de código para asegurar consistencia y detectar mejoras.
- Probar las funcionalidades en al menos dos navegadores modernos.
- Mantener el repositorio limpio de archivos temporales o compilados.
- Actualizar la documentación básica del proyecto cuando se cambie la estructura o comportamiento importante.

## Estilo de código
- Respetar la indentación elegida y no mezclar espacios con tabulaciones.
- Dejar una línea en blanco entre bloques de código o secciones relevantes.
- No dejar código comentado innecesario en el proyecto final.
- Usar comentarios breves solo para explicar decisiones no evidentes.

## Legibilidad y mantenimiento
- Priorizar claridad sobre cleverness; el código debe ser fácil de entender para otros desarrolladores.
- Refactorizar cuando el código crezca y se vuelva repetitivo.
- Usar funciones y componentes reutilizables para reducir duplicación.
- Mantener el foco en el usuario y la funcionalidad al escribir y revisar el código.
