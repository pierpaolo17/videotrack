<?php

$string['pluginname'] = 'Seguimiento de vídeo';
$string['modulename'] = 'Seguimiento de vídeo';
$string['modulenameplural'] = 'Seguimientos de vídeo';
$string['pluginadministration'] = 'Administración de Seguimiento de vídeo';
$string['videotrack:addinstance'] = 'Agregar una nueva actividad de Seguimiento de vídeo';
$string['videotrack:view'] = 'Ver Seguimiento de vídeo';
$string['videotrack:viewreport'] = 'Ver informes de Seguimiento de vídeo';
$string['videotrack:viewownreport'] = 'Ver el propio informe de Seguimiento de vídeo';
$string['videoname'] = 'Nombre de la actividad';
$string['youtubeurl'] = 'URL de YouTube';
$string['youtubeurl_help'] = 'Pegue una URL estándar de YouTube, una URL corta o una URL incrustada.';
$string['showcontrols'] = 'Mostrar controles del reproductor';
$string['disablekeyboard'] = 'Desactivar atajos de teclado';
$string['showfullscreen'] = 'Mostrar botón de pantalla completa';
$string['allowseekforward'] = 'Permitir avanzar';
$string['allowseekbackward'] = 'Permitir retroceder';
$string['allowplaybackratechange'] = 'Permitir cambios en la velocidad de reproducción';
$string['countbyvideotime'] = 'Contar la cobertura según la línea de tiempo del vídeo';
$string['countbyvideotime_help'] = 'Recomendado. La finalización se basa en los segundos únicos cubiertos en la línea de tiempo del vídeo, no en visualizaciones repetidas.';
$string['completionpercent'] = 'Porcentaje de finalización requerido';
$string['completiondetail:percent'] = 'Requerir la visualización de al menos el {$a}% del vídeo';
$string['completiondetail:minreactions'] = 'Requerir al menos {$a} reacciones distintas';
$string['completiondetail:allreactiontypes'] = 'Requerir al menos una reacción para cada tipo de reacción configurado';
$string['reactionsheader'] = 'Reacciones';
$string['reactionsenabled'] = 'Habilitar reacciones';
$string['reactionsrequired'] = 'Requerir reacciones';
$string['minreactions'] = 'Número mínimo de reacciones distintas';
$string['requireallreactiontypes'] = 'Requerir al menos una reacción para cada tipo configurado';
$string['completionlogic'] = 'Lógica de finalización';
$string['logicand'] = 'Todas las condiciones habilitadas (AND)';
$string['logicor'] = 'Cualquier condición habilitada (OR)';
$string['clusterwindow'] = 'Ventana de agrupación (segundos)';
$string['showstudentreport'] = 'Mostrar informe a los estudiantes';
$string['showreactionnotice'] = 'Mostrar aviso de reacciones';
$string['reactionnotice'] = 'Aviso de reacciones';
$string['reactionlabel'] = 'Etiqueta de la reacción';
$string['reactiondescription'] = 'Descripción de la reacción';
$string['reactionicontype'] = 'Tipo de icono';
$string['reactioniconvalue'] = 'Valor del icono';
$string['reactioniconvalue_help'] = 'Para Emoji, introduce el carácter emoji. Para Font Awesome, introduce una clase compatible con el tema de Moodle, por ejemplo fa fa-smile para temas con Font Awesome 5 o fa-regular fa-face-smile para temas con Font Awesome 6. La disponibilidad de iconos depende del tema Moodle activo y de la versión de Font Awesome instalada. Deja este campo vacío cuando uses un archivo de icono subido.';
$string['reactioniconfile'] = 'Archivo de icono de reacción';
$string['reactioniconfile_help'] = 'Archivo de imagen opcional utilizado cuando el tipo de icono es “Archivo cargado”. Los formatos aceptados dependen del soporte de imágenes web de Moodle.';
$string['reactionrequired'] = 'Requerido para la finalización';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Clase de Font Awesome';
$string['icontype:file'] = 'Archivo cargado';
$string['addreaction'] = 'Añadir reacción';
$string['invalidyoutubeurl'] = 'URL de YouTube no válida.';
$string['err:minreactionsrequired'] = 'Defina un número mínimo de reacciones distintas o habilite la regla que exige todos los tipos de reacción.';
$string['notice:minreactions'] = 'Se requieren al menos {$a} reacciones distintas.';
$string['notice:requiredtypes'] = 'Tipos de reacción requeridos: {$a}.';
$string['watch'] = 'Ver';
$string['report'] = 'Informes';
$string['reportstudent'] = 'Mis reacciones';
$string['reportteacher'] = 'Informe del docente';
$string['report:cumulative'] = 'Acumulado';
$string['report:perstudent'] = 'Por estudiante';
$string['report:userid'] = 'Usuario';
$string['report:uniquecoveredseconds'] = 'Segundos únicos cubiertos';
$string['report:completionpercent'] = 'Finalización %';
$string['report:lastposition'] = 'Última posición';
$string['report:iscompleted'] = 'Completado';
$string['report:noattempts'] = 'No se encontraron datos de visualización.';
$string['report:noreactions'] = 'No se encontraron datos de reacciones.';
$string['report:timestamp'] = 'Marca temporal';
$string['report:reaction'] = 'Reacción';
$string['report:description'] = 'Descripción';
$string['report:clicks'] = 'Clics';
$string['report:students'] = 'Estudiantes';
$string['report:replay'] = 'Reproducir fragmento';
$string['report:delete'] = 'Eliminar';
$string['report:sort'] = 'Ordenar por';
$string['report:sorttime'] = 'Marca temporal';
$string['report:sortreaction'] = 'Reacción';
$string['report:sortclicks'] = 'Clics';
$string['report:aggregation'] = 'Agregación';
$string['report:aggregationtype'] = 'Misma reacción dentro de la ventana';
$string['report:aggregationpeak'] = 'Pico de cualquier reacción';
$string['report:exportcsv'] = 'Exportar CSV';
$string['progress'] = 'Progreso';
$string['uniquereactions'] = 'Reacciones distintas';
$string['removereaction'] = 'Eliminar reacción';
$string['playerunavailable'] = 'No se pudo inicializar el reproductor.';
$string['yes'] = 'Sí';
$string['no'] = 'No';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heading_performance'] = 'Rendimiento';
$string['setting:heading_defaults'] = 'Valores predeterminados para nuevas actividades';
$string['setting:heading_defaults_desc'] = 'Estos valores se utilizan como predeterminados cuando un docente crea una nueva actividad VideoTrack. Cada actividad puede seguir configurándose individualmente.';
$string['setting:default_desc'] = 'Valor predeterminado para nuevas actividades. Puede ser reemplazado por el docente para cada actividad individual.';
$string['setting:default_completionpercent_desc'] = 'Porcentaje mínimo predeterminado del video que el estudiante debe ver para completar la actividad. Establecer en 0 para dejar la regla de finalización deshabilitada de forma predeterminada.';
$string['event:segment_saved'] = 'Segmento de visualización guardado';
$string['event:reaction_saved'] = 'Reacción enviada';
$string['event:note_saved'] = 'Nota del estudiante guardada';
$string['event:reaction_deleted'] = 'Reacción eliminada';
$string['setting:heartbeatinterval'] = 'Intervalo de heartbeat (segundos)';
$string['setting:heartbeatinterval_desc'] = 'Con qué frecuencia el reproductor guarda en el servidor el segmento de visualización actual durante la reproducción continua. Los valores más bajos reducen el riesgo de pérdida de datos en caso de fallo del navegador o caída de la red, pero aumentan la carga del servidor (una solicitud AJAX + dos consultas de base de datos por estudiante por intervalo). Rango recomendado: 15–120 segundos.';
$string['setting:reactionannouncementinterval'] = 'Intervalo de anuncios accesibles de reacciones (segundos)';
$string['setting:reactionannouncementinterval_desc'] = 'Tiempo mínimo entre anuncios repetidos de “reacciones no disponibles” para lectores de pantalla. Use un valor más bajo para recibir feedback frecuente en vídeos cortos, o uno más alto para reducir anuncios repetidos. Rango recomendado: 10–60 segundos.';

$string['reactionx'] = 'Reacción {$a}';

$string['err:reactioniconfilerequired'] = 'Cargue un archivo de icono cuando el tipo de icono esté configurado como Archivo cargado.';


$string['privacy:metadata:common:timecreated'] = 'Hora en que se creó el registro.';
$string['privacy:metadata:common:timemodified'] = 'Hora en que se modificó por última vez el registro.';
$string['privacy:metadata:videotrack_seg'] = 'Almacena los segmentos de visualización registrados para un usuario en una actividad de vídeo.';
$string['privacy:metadata:videotrack_seg:userid'] = 'El usuario cuyo segmento de visualización fue registrado.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Identificador de sesión del navegador asociado al segmento de visualización.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Hora del servidor en que comenzó el segmento.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Hora del servidor en que terminó el segmento.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Posición en la línea de tiempo del vídeo al inicio del segmento.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Posición en la línea de tiempo del vídeo al final del segmento.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Velocidad de reproducción usada durante el segmento.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Motivo por el que terminó el segmento.';
$string['privacy:metadata:videotrack_state'] = 'Almacena el estado agregado de visualización de un usuario en una actividad de vídeo.';
$string['privacy:metadata:videotrack_state:userid'] = 'El usuario cuyo estado agregado fue almacenado.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Última posición conocida alcanzada por el usuario en la línea de tiempo del vídeo.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Duración del vídeo registrado en segundos.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Número de segundos únicos de la línea de tiempo cubiertos por el usuario.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Porcentaje de finalización calculado para el usuario.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Intervalos combinados usados para calcular la cobertura única.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Si la actividad está marcada actualmente como completada para el usuario.';
$string['privacy:metadata:videotrack_reactev'] = 'Almacena eventos de reacción registrados mientras el usuario ve el vídeo.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'El usuario que envió la reacción.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Identificador de sesión del navegador asociado al evento de reacción.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Clave interna de la reacción en el momento del registro.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Etiqueta de la reacción mostrada al usuario en el momento del registro.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Descripción de la reacción mostrada al usuario en el momento del registro.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Posición en la línea de tiempo del vídeo cuando se registró la reacción.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Velocidad de reproducción cuando se registró la reacción.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indica si el usuario eliminó el evento de reacción.';

$string['videotrack:viewcoursereport'] = 'Ver el informe de VideoTrack a nivel de curso';
$string['videotrack:viewcoursereport_desc'] = 'Permite ver el informe agregado de VideoTrack para todo el curso.';
$string['videotrack:overrideplayersettings'] = 'Anular configuración del reproductor de la plataforma';
$string['videotrack:overrideplayersettings_desc'] = 'Permite al docente cambiar las configuraciones del reproductor establecidas por el administrador como valores predeterminados de la plataforma.';
$string['videotrack:overridecompletionsettings'] = 'Anular configuración de finalización de la plataforma';
$string['videotrack:overridecompletionsettings_desc'] = 'Permite al docente cambiar las configuraciones de finalización establecidas por el administrador como valores predeterminados de la plataforma.';
$string['setting:lockedbyAdmin'] = 'Estas configuraciones están bloqueadas por el administrador de la plataforma y no se pueden cambiar para actividades individuales.';
$string['setting:heading_presets'] = 'Presets de reacciones';
$string['setting:heading_presets_desc'] = 'Conjuntos de reacciones a nivel de sitio que los docentes pueden usar como punto de partida.';
$string['reactionpreset'] = 'Aplicar un preset de reacciones';
$string['reactionpreset_help'] = 'Seleccione un preset para rellenar previamente los campos de reacción. Puede editar libremente los valores después.';
$string['reactionpreset:none'] = '— configurar manualmente —';
$string['presets:manage'] = 'Gestionar presets de reacciones';
$string['presets:pagetitle'] = 'VideoTrack — Presets de reacciones';
$string['presets:intro'] = 'Defina presets de reacciones a nivel de sitio como punto de partida para los docentes.';
$string['presets:addpreset'] = 'Añadir preset';
$string['presets:backtolist'] = 'Volver a la lista de presets';
$string['presets:saved'] = 'Preset guardado.';
$string['presets:deleted'] = 'Preset eliminado.';
$string['presets:notfound'] = 'Preset no encontrado.';
$string['presets:noneyet'] = 'Aún no se han configurado presets de reacciones.';
$string['presets:confirmdelete'] = '¿Está seguro de que desea eliminar este preset?';
$string['presets:presetdetails'] = 'Detalles del preset';
$string['presets:name'] = 'Nombre del preset';
$string['presets:key'] = 'Clave del preset';
$string['presets:key_help'] = 'Identificador único (solo letras, números y guiones bajos). No se puede cambiar después de la creación.';
$string['presets:reactions'] = 'Reacciones';
$string['presets:reactions_help'] = 'Deje la etiqueta vacía para omitir una fila.';
$string['presets:col_name'] = 'Nombre';
$string['presets:col_key'] = 'Clave';
$string['presets:col_reactions'] = 'Reacciones';
$string['presets:col_actions'] = 'Acciones';
$string['setting:heartbeatinterval_min'] = 'Valor mínimo aplicado: 5 segundos.';

$string['reset:userdata'] = 'Eliminar todos los datos de visualización de estudiantes (segmentos, estados, reacciones)';
$string['report:recalculate'] = 'Recalcular todos los estados de finalización';
$string['report:recalculated'] = 'Estados de finalización recalculados para {$a} usuarios.';
$string['report:heatmap_desc'] = 'Mapa de calor de reacciones en la línea de tiempo del vídeo (altura de barra = número de clics en ese punto):';
$string['report:heatmap_supplementary'] = 'El mapa de calor es una visualización complementaria. Los datos completos de los clústeres están disponibles en la tabla siguiente.';
$string['event:activity_completed'] = 'Actividad VideoTrack completada';

$string['reactioniconfile_notice'] = 'La imagen se redimensionará automáticamente a 64×64 píxeles (recorte centrado). Para mejores resultados, sube una imagen cuadrada (proporción 1:1). Formatos aceptados: JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Haz clic en un botón de reacción mientras el vídeo se está reproduciendo para registrar tu reacción en ese momento.';

$string['showgradeto'] = 'Mostrar calificación al estudiante';
$string['showgradeto_help'] = 'Si está habilitado, el estudiante verá su calificación directamente en la página de la actividad.';
$string['report:grade'] = 'Calificación';
$string['report:gradesaved'] = 'Calificación guardada correctamente.';
$string['report:gradepass_hint'] = 'Nota mínima: {$a}';
$string['report:gradenotset'] = 'Aún no calificado';

$string['videosource'] = 'Fuente de vídeo';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Subir (MP4/WebM/MP3)';
$string['vimeourl'] = 'URL de Vimeo';
$string['vimeourl_help'] = 'Pega la URL del vídeo de Vimeo.';
$string['invalidvimeourl'] = 'La URL no parece ser válida para Vimeo.';
$string['videofile'] = 'Archivo de vídeo/audio';
$string['videofile_help'] = 'Sube un archivo MP4, WebM o MP3.';
$string['videofile_notice'] = 'Formatos aceptados: MP4, WebM, MP3, M4V, MOV, AAC, M4A.';
$string['setting:heading_player'] = 'Comportamiento del reproductor';
$string['setting:playbackspeeds'] = 'Velocidades de reproducción disponibles';
$string['setting:playbackspeeds_desc'] = 'Selecciona qué velocidades están disponibles en toda la plataforma.';
$string['setting:playbackspeeds_teacher_desc'] = 'Selecciona las velocidades de reproducción para esta actividad.';
$string['setting:speed_normal'] = 'normal';
$string['setting:distractionfree'] = 'Modo sin distracciones';
$string['setting:distractionfree_desc'] = 'Oculta encabezado, pie de página y navegación al ver la actividad.';
$string['intervalbar_title'] = 'Intervalos vistos — segmentos verdes = partes ya vistas.';
$string['outline:percent'] = '{$a}% visto';
$string['outline:nodata'] = 'Sin datos de visualización.';
$string['coursereport:title'] = 'VideoTrack — Informe del curso';
$string['coursereport:navlink'] = 'Informes VideoTrack';
$string['coursereport:intro'] = 'Resumen de todas las actividades VideoTrack del curso.';
$string['coursereport:nodata'] = 'No se encontraron actividades VideoTrack.';
$string['coursereport:col_activity'] = 'Actividad';
$string['coursereport:col_source'] = 'Fuente';
$string['coursereport:col_duration'] = 'Duración';
$string['coursereport:col_students_started'] = 'Estudiantes iniciados';
$string['coursereport:col_avg_percent'] = 'Cobertura media';
$string['coursereport:col_completions'] = 'Completados';
$string['coursereport:col_reactions'] = 'Reacciones';
$string['coursereport:col_actions'] = 'Acciones';

$string['grade:pass'] = 'Aprobado';
$string['grade:fail'] = 'Suspenso';

$string['autoplay'] = 'Reproducción automática';
$string['autoplay_help'] = 'Inicia el vídeo automáticamente. Los navegadores requieren silenciar el vídeo para el autoplay.';
$string['loop'] = 'Repetir en bucle';
$string['startmuted'] = 'Iniciar silenciado';
$string['startmuted_help'] = 'Inicia la reproducción con el audio silenciado.';
$string['allowdownload'] = 'Permitir descarga (solo fuente upload)';
$string['setting:allowdownload_desc'] = 'Muestra un botón de descarga en el reproductor HTML5.';
$string['setting:heading_playerbehavior'] = 'Comportamiento predeterminado del reproductor';
$string['setting:heading_playerbehavior_desc'] = 'Valores predeterminados para autoplay, bucle, silencio y descarga.';
$string['setting:heading_html5controls'] = 'Controles del reproductor HTML5 (fuente upload)';
$string['setting:heading_html5controls_desc'] = 'Seleccione los controles disponibles en la barra del reproductor HTML5.';
$string['setting:html5controls'] = 'Controles disponibles';
$string['setting:html5controls_desc'] = 'Seleccione qué controles mostrar en el reproductor HTML5.';
$string['setting:html5controls_teacher_desc'] = 'Seleccione los controles para esta actividad.';
$string['ctrl:play'] = 'Play/Pausa';
$string['ctrl:progress'] = 'Barra de progreso';
$string['ctrl:current'] = 'Tiempo actual';
$string['ctrl:duration'] = 'Duración';
$string['ctrl:mute'] = 'Silenciar';
$string['ctrl:volume'] = 'Volumen';
$string['ctrl:speed'] = 'Velocidad';
$string['ctrl:pip'] = 'Imagen en imagen';
$string['ctrl:fullscreen'] = 'Pantalla completa';
$string['ctrl:download'] = 'Descargar';

$string['setting:playerwidth'] = 'Ancho máximo del reproductor (px)';
$string['setting:playerwidth_desc'] = 'Ancho máximo del reproductor de vídeo en píxeles.';
$string['playerwidth'] = 'Ancho máximo del reproductor (px)';
$string['playerwidth_help'] = 'Ancho máximo del reproductor para esta actividad. 0 = predeterminado.';
$string['setting:rewindstep'] = 'Paso de retroceso (segundos)';
$string['setting:rewindstep_desc'] = 'Segundos que retrocede el botón. 0 = desactivado. Predeterminado: 10. Importante: si "Permitir seek hacia atrás" está desactivado para una actividad, el botón no aparecerá aunque este valor sea > 0.';
$string['rewindstep'] = 'Paso de retroceso (segundos)';
$string['rewindstep_help'] = 'Segundos de retroceso para esta actividad. 0 = predeterminado. Nota: si "Permitir seek hacia atrás" está desactivado, el botón no aparecerá independientemente de este valor.';
$string['setting:fastforwardstep'] = 'Paso de avance rápido (segundos)';
$string['setting:fastforwardstep_desc'] = 'Segundos que avanza el botón. 0 = desactivado. Predeterminado: 10. Importante: si "Permitir seek hacia adelante" está desactivado para una actividad, el botón no aparecerá aunque este valor sea > 0.';
$string['fastforwardstep'] = 'Paso de avance rápido (segundos)';
$string['fastforwardstep_help'] = 'Segundos de avance para esta actividad. 0 = predeterminado. Nota: si "Permitir seek hacia adelante" está desactivado, el botón no aparecerá independientemente de este valor.';
$string['captionsheader'] = 'Subtítulos';
$string['captions'] = 'Activar subtítulos';
$string['captions_help'] = 'Activa subtítulos para YouTube, Vimeo o Upload (VTT).';
$string['setting:default_captions_desc'] = 'Activar subtítulos por defecto en nuevas actividades.';
$string['captionslang'] = 'Idioma de subtítulos predeterminado';
$string['captionslang_help'] = 'Código de idioma ISO 639-1 (p.ej. es, en).';
$string['setting:captionslang_desc'] = 'Idioma de subtítulos predeterminado (ISO 639-1).';
$string['vttfile'] = 'Archivo de subtítulos (.vtt)';
$string['vttfile_help'] = 'Cargar archivo WebVTT.';
$string['vttfile_notice'] = 'Formato aceptado: WebVTT (.vtt).';
$string['vimeo_captions_notice'] = 'Los subtítulos de Vimeo se gestionan en Vimeo.com.';
$string['ctrl:rewind'] = 'Botón de retroceso';
$string['ctrl:fastforward'] = 'Botón de avance rápido';

$string['playerloading'] = 'Cargando el reproductor de vídeo, por favor espere…';
$string['noreactionsyet'] = 'Aún no hay reacciones registradas. Reaccione mientras el vídeo se reproduce.';
$string['reaction:error'] = 'No se pudo guardar la reacción. Por favor, inténtelo de nuevo.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Reanudar reproducción';
$string['resumeplayback_desc'] = 'Reanuda automáticamente el vídeo desde el punto donde el estudiante lo dejó en su última sesión.';
$string['resumeplayback_help'] = 'Si está activado, el vídeo comienza desde la última posición guardada (si es superior a 5 segundos desde el inicio). El estudiante siempre puede volver manualmente al principio.';
$string['setting:resumeplayback'] = 'Reanudar reproducción (predeterminado)';
$string['setting:resumeplayback_desc'] = 'Valor predeterminado para nuevas actividades VideoTrack. El profesorado puede cambiarlo por actividad.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Velocidad máxima de reproducción';
$string['maxplaybackrate_desc'] = 'Limita la velocidad máxima que pueden seleccionar los estudiantes. 0 = sin límite.';
$string['maxplaybackrate_help'] = 'Cuando se configura, los estudiantes no pueden reproducir el vídeo más rápido que esta velocidad, aunque los controles del reproductor permitan valores superiores.';
$string['maxplaybackrate_nolimit'] = 'Sin límite';
$string['setting:maxplaybackrate'] = 'Velocidad máxima de reproducción (predeterminada)';
$string['setting:maxplaybackrate_desc'] = 'Velocidad máxima predeterminada para nuevas actividades. El profesorado puede cambiarla por actividad.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Mostrar transcripción interactiva';
$string['showtranscript_desc'] = 'Muestra junto al vídeo un panel de transcripción desplazable y clicable (requiere archivo VTT).';
$string['showtranscript_help'] = 'Analiza el archivo VTT subido y lo muestra como lista clicable. Cada entrada muestra marca temporal y texto; al hacer clic se salta a ese punto.';
$string['transcript_title'] = 'Transcripción';
$string['transcript_loading'] = 'Cargando transcripción…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Haga clic en el vídeo para iniciar la reproducción.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['vimeocspwarnlabel'] = 'No se pudo cargar el reproductor de Vimeo. Revise la conexión o pida al administrador que permita player.vimeo.com en la Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'Reanudando desde';
// ── Report: azioni studente ──
$string['report:actions'] = 'Acciones';
$string['report:resetstudent'] = 'Restablecer progreso';
$string['report:resetstudent_confirm'] = '¿Seguro que desea restablecer el progreso de este estudiante? Esto eliminará todo su historial de visualización y reacciones y no se puede deshacer.';
$string['report:studentreset'] = 'El progreso del estudiante ha sido restablecido.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Mostrar navegación por capítulos';
$string['showchapters_desc'] = 'Muestra una barra con marcadores de capítulo extraídos del archivo VTT. Los capítulos son cues VTT con texto inferior a 80 caracteres.';
$string['showchapters_help'] = 'Si el archivo VTT contiene cues cortos, se interpretan como títulos de capítulo y se muestran como navegación clicable.';
$string['chapters_label'] = 'Capítulos del vídeo';
$string['chapterslabel'] = 'Capítulos del vídeo';
$string['chapter_label'] = 'Capítulo';
$string['chapterlabel'] = 'Capítulo';
$string['studentnotesenabled'] = 'Activar notas del estudiante';
$string['studentnotesenabled_desc'] = 'Permite que los estudiantes escriban notas personales con marca temporal mientras ven el vídeo.';
$string['studentnotesenabled_help'] = 'Si está activado, aparece un área de texto junto al vídeo. Los estudiantes pueden guardar una nota en la marca temporal actual. Las notas solo son visibles para su autor y para gestores en el informe.';
$string['setting:studentnotesenabled'] = 'Activar notas del estudiante (predeterminado)';
$string['setting:studentnotesenabled_desc'] = 'Valor predeterminado para nuevas actividades VideoTrack. El profesorado puede cambiarlo por actividad.';
$string['studentnotes_title'] = 'Mis notas';
$string['studentnote_placeholder'] = 'Escribe una nota en este momento del vídeo…';
$string['studentnote_save'] = 'Guardar nota';
$string['studentnote_hint'] = 'La nota se guardará en la marca temporal actual. El vídeo debe estar reproduciéndose.';
$string['studentnotes_list_label'] = 'Notas guardadas';
$string['studentnote_label'] = 'Nota del estudiante';
$string['noteerrorlabel'] = 'No se pudo guardar la nota. Inténtalo de nuevo.';
$string['charsremaininglabel'] = 'caracteres restantes';
$string['posterimage'] = 'Imagen de portada / vista previa';
$string['posterimage_help'] = 'Sube una imagen para mostrar antes de iniciar el vídeo. Formatos aceptados: JPG, PNG, WebP, GIF. Tamaño recomendado: 1280×720 px (16:9).';
$string['posterimage_notice'] = 'La imagen de portada se muestra antes de la reproducción y se oculta automáticamente cuando empieza el vídeo.';
$string['playbutton_label'] = 'Reproducir vídeo';
$string['setting:maxplaybackrate_nolimit'] = 'Sin límite';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Texto de una nota personal escrita por el estudiante en una marca temporal concreta del vídeo.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Tipo de evento: vacío para reacciones estándar, "note" para notas personales del estudiante.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['reactionsdisabled'] = 'Las reacciones están desactivadas para esta actividad de VideoTrack. Pide a tu docente o al administrador del curso que las active si son necesarias.';
$string['studentnotesdisabled'] = 'Las notas del estudiante no están activadas para esta actividad.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'No se ha subido ningún archivo de vídeo para esta actividad.';
$string['removenote'] = 'Eliminar nota';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Ocultar notas';
$string['notes_show'] = 'Mostrar notas';
$string['report:notes_title'] = 'Notas de estudiantes';
$string['report:nonotes'] = 'No se han escrito notas para esta actividad.';
$string['report:notedate'] = 'Escrita el';
$string['report:exportnotes_csv'] = 'Exportar notas como CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Cerrar';
$string['rewindlabel'] = 'Retroceder';
$string['fastforwardlabel'] = 'Avance rápido';
$string['secondslabel'] = 'segundos';
$string['removenotelabel'] = 'Eliminar nota';
// ── Help strings ──
$string['gradepass_help'] = 'La calificación mínima requerida para superar esta actividad. Los estudiantes que alcanzan esta calificación o una superior se consideran aprobados.';


$string['completiondetail:requiredreactions'] = 'Debe incluir estas reacciones obligatorias: {$a}';

$string['error:playbackrequired'] = 'El vídeo debe estar reproduciéndose antes de guardar esta acción.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'Extensión PHP GD no disponible.';
$string['setting:gd_missing_desc'] = 'Las imágenes subidas como iconos de reacción NO se redimensionarán automáticamente a 64×64 píxeles. Se servirá el archivo original, lo que puede afectar al rendimiento si es grande. Para activar el redimensionado automático, pida al administrador que instale php-gd.';

$string['report:heatmap_legend'] = 'Leyenda de colores del mapa de calor de reacciones';

$string['report:clusterlimitreached'] = 'El informe alcanzó el número máximo de clústeres mostrados. Usa filtros o una ventana temporal más estrecha para un análisis completo.';

$string['report:showingrecentreactionsoftotal'] = 'Se muestran {$a->shown} de {$a->total} reacciones, de la más antigua a la más reciente.';

$string['report:viewfullreport'] = 'Ver el informe completo';
$string['studentnotes_view_limited'] = 'Se muestran las últimas {$a} notas. Abra el informe completo para revisar todas las notas.';
$string['report:skiptoheatmaptable'] = 'Omitir el mapa de calor e ir a la tabla de datos';
$string['report:heatmap_textsummary'] = 'El gráfico contiene {$a->clusters} clústeres; el clúster más grande contiene {$a->max} clics.';
$string['err:reactioniconvaluerequired'] = 'Introduzca un emoji o una clase de Font Awesome.';
$string['err:reactioniconvalueinvalidfa'] = 'Introduzca solo nombres de clases Font Awesome válidos, usando letras, números, espacios y guiones.';

$string['error:reactionratelimit'] = 'Se han enviado demasiadas reacciones en poco tiempo. Continúa viendo el vídeo e inténtalo de nuevo.';
$string['event:student_progress_reset'] = 'Datos VideoTrack del estudiante restablecidos';
$string['report:timefrom'] = 'Desde el segundo';
$string['report:timeto'] = 'Hasta el segundo';
$string['report:clusterlimitreached_help'] = 'El informe acumulado alcanzó el límite de clústeres visibles. Usa los filtros por usuario, reacción o tiempo del vídeo para acotar el análisis y recuperar clústeres posteriores.';
$string['report:topclusterssummary'] = 'Clústeres más relevantes en esta selección:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} clics';
$string['error:notesratelimit'] = 'Se han enviado demasiadas notas en poco tiempo. Espera antes de añadir otra nota.';

$string['privacy:segmentschunk'] = 'Segmentos de visualización - parte {$a}';

$string['privacy:reactionsactivechunk'] = 'Reacciones activas - parte {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Reacciones eliminadas - parte {$a}';

$string['privacy:notesactivechunk'] = 'Notas activas - parte {$a}';

$string['privacy:notesdeletedchunk'] = 'Notas eliminadas - parte {$a}';

$string['report:clusterlimitreached_csv'] = 'ADVERTENCIA: se alcanzó el límite de clústeres. La exportación puede estar incompleta; aplica filtros de usuario, reacción o tiempo y vuelve a exportar.';

$string['report:notecreatedfrom'] = 'Notas desde la fecha';

$string['report:notecreatedto'] = 'Notas hasta la fecha';

$string['reactionsavailableonlyduringplayback'] = 'Las reacciones solo están disponibles durante la reproducción del vídeo.';
$string['reactionsreadyannounce'] = 'Las reacciones ya están disponibles.';

$string['privacy:state'] = 'Estado de finalización';

$string['report:clusterlimitrequiresfilters'] = 'El informe acumulado es parcial. Aplique un filtro de intervalo temporal del vídeo para recuperar de forma fiable los clústeres restantes.';

$string['report:clusterlimitrequiresfilters_csv'] = 'La exportación acumulada es parcial porque no se aplicó ningún filtro de intervalo temporal del vídeo. Aplique los filtros Desde segundo/Hasta segundo y exporte de nuevo.';
$string['report:clusterexportblocked_csv'] = 'La exportación se detuvo para evitar datos incompletos. Aplique un filtro de tiempo del vídeo y exporte de nuevo.';
$string['report:clusterdisplayblocked'] = 'La tabla de agrupaciones se ha ocultado para evitar datos incompletos. Aplique un filtro de tiempo del vídeo para continuar.';
$string['unknownreaction'] = 'Reacción desconocida';

// Moodle HQ review fallback strings added in 1.0.29.
$string['externalprovider_notice'] = 'Los proveedores externos de vídeo como YouTube y Vimeo pueden procesar datos personales y establecer cookies según sus propias políticas de privacidad. Use archivos subidos cuando no esté permitida la transferencia a terceros.';
$string['privacy:metadata:youtube'] = 'Cuando se usa un vídeo de YouTube, el navegador del usuario se conecta a YouTube para cargar y reproducir el vídeo.';
$string['privacy:metadata:youtube:videoid'] = 'El identificador del vídeo de YouTube configurado para esta actividad.';
$string['privacy:metadata:youtube:url'] = 'La URL de YouTube configurada para esta actividad.';
$string['privacy:metadata:vimeo'] = 'Cuando se usa un vídeo de Vimeo, el navegador del usuario se conecta a Vimeo para cargar y reproducir el vídeo.';
$string['privacy:metadata:vimeo:videoid'] = 'El identificador del vídeo de Vimeo configurado para esta actividad.';
$string['privacy:metadata:vimeo:url'] = 'La URL de Vimeo configurada para esta actividad.';
$string['html5:controls'] = 'Controles de vídeo';
$string['html5:play'] = 'Reproducir';
$string['html5:pause'] = 'Pausa';
$string['html5:seek'] = 'Buscar';
$string['html5:volume'] = 'Volumen';
$string['html5:mute'] = 'Silenciar';
$string['html5:unmute'] = 'Activar sonido';
$string['html5:speed'] = 'Velocidad';
$string['html5:pip'] = 'Imagen en imagen';
$string['html5:fullscreen'] = 'Pantalla completa';
$string['html5:download'] = 'Descargar';
$string['setting:heading_privacy'] = 'Privacidad y conservación de datos';
$string['setting:heading_privacy_desc'] = 'Configure cómo VideoTrack almacena datos de seguimiento, notas y reacciones.';
$string['setting:retentionperioddays'] = 'Periodo de conservación de datos de seguimiento (días)';
$string['setting:retentionperioddays_desc'] = 'Número de días tras los cuales VideoTrack anonimiza datos antiguos de seguimiento, notas y reacciones. Use 0 para conservar los datos indefinidamente. Las solicitudes de supresión del usuario se gestionan siempre mediante anonimización con sal en lugar de borrar las analíticas agregadas.';
$string['setting:strictsessionvalidation'] = 'Exigir la misma sesión del navegador para validar notas y reacciones';
$string['setting:validationfallbackdays'] = 'Ventana de validación de reproducción histórica (días)';
$string['setting:validationfallbackdays_desc'] = 'Edad máxima, en días, de los segmentos ya vistos que pueden autorizar notas y reacciones después de actualizar la página o cambiar de navegador. Use 0 para permitir segmentos históricos vistos sin límite; mejora la usabilidad, pero hace más permisiva la validación de integridad académica. Las comprobaciones de misma sesión y reproducción reciente se intentan siempre primero.';
$string['setting:strictsessionvalidation_desc'] = 'Si está activado, las notas y reacciones solo pueden guardarse para marcas de tiempo vistas en la sesión actual del navegador. Si está desactivado, VideoTrack acepta marcas de tiempo ya vistas por el mismo usuario en la misma actividad, mejorando la usabilidad tras recargas o cambios de navegador y rechazando posiciones no vistas.';
$string['task:cleanup'] = 'Anonimizar datos de seguimiento caducados de VideoTrack';
$string['privacy:anonymised'] = '[anonimizado]';
$string['error:playbackpositionnotwatched'] = 'Esta posición del vídeo aún no se ha visto, por lo que la acción no puede guardarse.';

$string['setting:intrangerequired'] = 'Introduzca un número entero entre {$a->min} y {$a->max}.';
$string['setting:nonnegativeintrequired'] = 'Introduzca un número entero mayor o igual que 0.';
$string['report:anonymiseduser'] = 'Usuario anonimizado';
$string['report:exportnotes_privacywarning'] = 'Esta exportación puede contener datos personales de notas de estudiantes. Descárguela y guárdela solo cuando tenga una finalidad válida y elimínela cuando ya no sea necesaria.';
