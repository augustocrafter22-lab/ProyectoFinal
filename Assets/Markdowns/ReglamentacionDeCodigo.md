Entendido. Aunque volvemos a recalcar que **esto no pertenece al libro *Código Limpio* de Robert C. Martin** (ya que la obra original no trata sobre HTML ni desarrollo web), lo integro a tu documento como una regla adoptada para el desarrollo frontend.

He añadido esta norma en el **Capítulo 1 (como el punto 1.7)** para que mantenga la coherencia con el resto de tus principios generales. Aquí tienes la reglamentación completa y actualizada:

---

# REGLAMENTACIÓN DE CÓDIGO LIMPIO

### CAPÍTULO 1: PRINCIPIOS GENERALES

* **1.1. Regla del Boy Scout:** Todo desarrollador debe dejar el código que modifica más limpio de como lo encontró.
* **1.2. Legibilidad:** El código se escribe para las personas y sólo incidentalmente para las computadoras. La legibilidad tiene prioridad sobre la brevedad.
* **1.3. Simplicidad:** La solución más simple que resuelva correctamente el problema debe ser la preferida.
* **1.4. Duplicación:** Se prohíbe la duplicación de lógica, conocimiento o comportamiento. Toda duplicación identificada debe eliminarse mediante abstracción adecuada.
* **1.5. Expresividad:** El código debe expresar claramente la intención del desarrollador. Toda construcción que dificulte comprender el propósito del código debe ser refactorizada.
* **1.6. Pruebas:** Todo código debe estar respaldado por pruebas automatizadas. Un código sin pruebas no puede considerarse limpio.
* **1.7. Semántica Web:** El código de maquetación debe ser estrictamente semántico, utilizando las etiquetas HTML5 adecuadas según su significado e intención (como `<header>`, `<nav>`, `<main>`, `<article>`, `<footer>`). Se prohíbe el uso de etiquetas no semánticas (como `<div>` o `<span>`) cuando exista una alternativa semántica válida; estas solo se permitirán con fines puramente de diseño, estructura técnica o estilos CSS.

### CAPÍTULO 2: NOMBRES CON SENTIDO

* **2.1. Revelar intención:** Todo nombre debe indicar: Por qué existe, Qué representa y Cómo se utiliza.
* **2.2. Evitar desinformación:** Se prohíbe utilizar nombres que sugieran significados incorrectos.
* **2.3. Distinciones significativas:** Las diferencias entre nombres deben reflejar diferencias reales de concepto.
* **2.4. Nombres pronunciables:** Todo nombre debe poder pronunciarse naturalmente en una conversación.
* **2.5. Nombres buscables:** Los nombres deben ser fáciles de localizar mediante búsquedas. Se deben evitar variables de una sola letra salvo contadores de bucles muy locales.
* **2.6. Evitar codificaciones:** Se prohíbe incluir información de tipos, prefijos húngaros o notaciones similares dentro de los nombres.
* **2.7. Clases:** Los nombres de clases deben ser sustantivos o grupos nominales.
* **2.8. Métodos:** Los nombres de métodos deben ser verbos o frases verbales.
* **2.9. Un concepto, una palabra:** Un mismo concepto debe recibir siempre el mismo nombre.
* **2.10. Evitar juegos de palabras:** No se debe reutilizar una palabra conocida para representar conceptos distintos.
* **2.11. Contexto significativo:** Los nombres deben proporcionar suficiente contexto para comprender su función.

### CAPÍTULO 3: FUNCIONES

* **3.1. Tamaño:** Las funciones deben ser pequeñas.
* **3.2. Bloques:** Los bloques dentro de `if`, `else`, `while` y `for` deben ser preferentemente una única invocación de función.
* **3.3. Una sola responsabilidad:** Cada función debe realizar una sola tarea.
* **3.4. Un único nivel de abstracción:** Todas las instrucciones de una función deben encontrarse en el mismo nivel conceptual.
* **3.5. Lectura descendente:** El código debe leerse de arriba hacia abajo como una narración.
* **3.6. Argumentos:** Se debe minimizar la cantidad de argumentos.
* **3.7. Argumentos booleanos:** Deben evitarse porque indican múltiples responsabilidades.
* **3.8. Efectos secundarios:** Las funciones no deben producir efectos secundarios ocultos.
* **3.9. Separación entre comandos y consultas:** Una función debe modificar estado o devolver información, pero no ambas cosas simultáneamente.
* **3.10. Excepciones:** Las funciones deben preferir excepciones antes que códigos de error.
* **3.11. Evitar parámetros de salida:** Los parámetros de salida están prohibidos.

### CAPÍTULO 4: COMENTARIOS

* **4.1. Regla general:** Los comentarios son un recurso secundario. Siempre se debe intentar expresar la intención mediante código.
* **4.2. Comentarios aceptables:** Se permiten: Comentarios legales, Advertencias, Explicaciones de intención difíciles de expresar en código y Documentación pública.
* **4.3. Comentarios prohibidos:** Se prohíben: Comentarios redundantes, Comentarios engañosos, Comentarios obsoletos, Comentarios históricos, Código comentado y Comentarios de autoría.

### CAPÍTULO 5: FORMATO

* **5.1. Organización vertical:** Los conceptos relacionados deben permanecer próximos.
* **5.2. Variables:** Las variables deben declararse cerca de su punto de uso.
* **5.3. Funciones privadas:** Las funciones auxiliares deben situarse debajo de las funciones que las utilizan.
* **5.4. Tamaño de archivo:** Los archivos deben mantenerse pequeños y enfocados.
* **5.5. Espaciado:** El espacio horizontal debe utilizarse para mejorar la legibilidad.
* **5.6. Alineación horizontal:** La alineación en columnas debe evitarse.
* **5.7. Sangrado:** El sangrado debe ser consistente en todo el proyecto.

### CAPÍTULO 6: OBJETOS Y ESTRUCTURAS DE DATOS

* **6.1. Ocultamiento de datos:** Los detalles internos deben permanecer ocultos.
* **6.2. Objetos:** Los objetos exponen comportamiento.
* **6.3. Estructuras de datos:** Las estructuras de datos exponen información.
* **6.4. Ley de Demeter:** Los módulos no deben conocer detalles internos de otros módulos.

### CAPÍTULO 7: MANEJO DE ERRORES

* **7.1. Excepciones:** Se deben utilizar excepciones en lugar de códigos de error.
* **7.2. Separación de lógica:** La lógica de negocio y la gestión de errores deben permanecer separadas.
* **7.3. Contexto:** Toda excepción debe proporcionar información suficiente para diagnosticar el problema.
* **7.4. Null:** Se prohíbe devolver `null` cuando exista una alternativa razonable.
* **7.5. Parámetros null:** Se prohíbe pasar `null` como argumento.

### CAPÍTULO 8: CLASES

* **8.1. Tamaño:** Las clases deben ser pequeñas.
* **8.2. Responsabilidad única:** Cada clase debe tener una única razón para cambiar.
* **8.3. Cohesión:** Los métodos deben trabajar sobre los mismos datos de la clase.
* **8.4. Dependencias:** Las dependencias deben mantenerse mínimas y explícitas.
* **8.5. Organización por cambio:** Las clases deben diseñarse alrededor de responsabilidades estables.

### CAPÍTULO 9: SISTEMAS

* **9.1. Separación de construcción y uso:** La creación de objetos debe separarse de su utilización.
* **9.2. Dependencias:** Las dependencias deben inyectarse en lugar de construirse internamente cuando sea posible.
* **9.3. Modularidad:** El sistema debe organizarse en componentes claramente delimitados.

### CAPÍTULO 10: PRUEBAS

* **10.1. Execution completa:** Las pruebas deben ejecutarse completamente.
* **10.2. Independencia:** Las pruebas no deben depender unas de otras.
* **10.3. Repetibilidad:** Las pruebas deben producir el mismo resultado en cualquier entorno.
* **10.4. Autoevaluación:** Las pruebas deben indicar automáticamente éxito o fracaso.
* **10.5. Oportunidad:** Las pruebas deben escribirse junto con el código de producción.
* **10.6. Legibilidad:** El código de prueba debe cumplir los mismos estándares de limpieza que el código de producción.

### CAPÍTULO 11: CONCURRENCIA

* **11.1. Separación:** La lógica concurrente debe separarse de la lógica de negocio.
* **11.2. Compartición:** Debe minimizarse el estado compartido.
* **11.3. Sincronización:** La sincronización debe mantenerse encapsulada.
* **11.4. Tamaño:** Las secciones críticas deben ser lo más pequeñas posible.

### CAPÍTULO 12: REFACTORIZACIÓN CONTINUA

* **12.1. Código muerto:** Todo código muerto debe eliminarse.
* **12.2. Variables innecesarias:** Toda variable innecesaria debe eliminarse.
* **12.3. Complejidad:** Toda complejidad accidental debe reducirse.
* **12.4. Consistencia:** Las convenciones deben mantenerse uniformes en todo el sistema.
* **12.5. Mejora continua:** Toda modificación del sistema debe ser una oportunidad para mejorar su diseño.