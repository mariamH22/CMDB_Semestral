# Hallazgos Funcionales

## H1 - Flujos CRUD requieren validacion por navegador

- Estado: PARCIAL.
- Evidencia: controladores y modelos existen para usuarios, categorias, colaboradores, inventario, asignaciones, necesidades y reportes.
- Resultado real: lint y tests OK; MySQL real bloqueado en esta sesion.
- Impacto: no se puede afirmar ejecucion completa de formularios, redirecciones y vistas con datos reales.
- Recomendacion: ejecutar `docs/PRUEBAS_MANUALES_ENTREGA.md` en XAMPP/WampServer.
- Prioridad: alta.

## H2 - Enlaces externos de entrega pendientes

- Estado: NO VERIFICABLE.
- Evidencia: `README.md`, `docs/ENTREGA_FINAL.md`.
- Resultado real: no hay remoto GitHub, URL de backup ni video publicado configurados.
- Impacto: afecta rubrica de video y URL de backup/repositorio.
- Recomendacion: crear remoto `CMDB_Semestral`, subir commit, generar backup externo y actualizar README con URLs reales.
- Prioridad: alta.

## H3 - Repositorio local corregido

- Estado: CUMPLE.
- Evidencia: `git branch --show-current` devuelve `main`.
- Resultado real: proyecto ya puede recibir commit inicial.
- Impacto: elimina bloqueo previo de versionado local.
- Recomendacion: conservar este repositorio como fuente de verdad para la entrega.
- Prioridad: media.
