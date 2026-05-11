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
$string['reactioniconvalue_help'] = 'Para Emoji, introduzca el carácter emoji. Para Font Awesome, introduzca la clase CSS, por ejemplo fa-regular fa-face-smile. Deje este campo vacío cuando utilice un archivo de icono cargado.';
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

$string['reactionx'] = 'Reacción {$a}';

$string['err:reactioniconfilerequired'] = 'Cargue un archivo de icono cuando el tipo de icono esté configurado como Archivo cargado.';


$string['privacy:metadata:common:timecreated'] = 'Time when the record was created.';
$string['privacy:metadata:common:timemodified'] = 'Time when the record was last modified.';
$string['privacy:metadata:videotrack_seg'] = 'Stores viewing segments recorded for a user in a video activity.';
$string['privacy:metadata:videotrack_seg:userid'] = 'The user whose viewing segment was recorded.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Browser session identifier associated with the viewing segment.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Server time when the segment started.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Server time when the segment ended.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Video timeline position at the start of the segment.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Video timeline position at the end of the segment.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Playback speed used during the segment.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Reason why the segment ended.';
$string['privacy:metadata:videotrack_state'] = 'Stores the aggregated viewing state for a user in a video activity.';
$string['privacy:metadata:videotrack_state:userid'] = 'The user whose aggregate state was stored.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Last known position reached by the user in the video timeline.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Duration of the tracked video in seconds.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Number of unique timeline seconds covered by the user.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Completion percentage calculated for the user.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Merged intervals used to calculate unique coverage.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Whether the activity is currently marked complete for the user.';
$string['privacy:metadata:videotrack_reactev'] = 'Stores reaction events recorded while a user watches the video.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'The user who submitted the reaction.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Browser session identifier associated with the reaction event.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Internal key of the reaction at the time it was recorded.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Reaction label shown to the user when the event was recorded.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Reaction description shown to the user when the event was recorded.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Video timeline position when the reaction was recorded.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Playback speed when the reaction was recorded.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indica si el usuario eliminó el evento de reacción.';

$string['cap:overrideplayersettings'] = 'Anular configuración del reproductor de la plataforma';
$string['cap:overrideplayersettings_desc'] = 'Permite al docente cambiar las configuraciones del reproductor establecidas por el administrador como valores predeterminados de la plataforma.';
$string['cap:overridecompletionsettings'] = 'Anular configuración de finalización de la plataforma';
$string['cap:overridecompletionsettings_desc'] = 'Permite al docente cambiar las configuraciones de finalización establecidas por el administrador como valores predeterminados de la plataforma.';
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
$string['report:actions'] = 'Actions';
$string['report:resetstudent'] = 'Reset progress';
$string['report:resetstudent_confirm'] = 'Are you sure you want to reset this student\'s progress? This will delete all their viewing history and reactions and cannot be undone.';
$string['report:studentreset'] = 'Student progress has been reset.';
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

$string['report:showingrecentreactions'] = 'Solo se muestran las primeras {$a} reacciones.';

$string['report:viewfullreport'] = 'Ver el informe completo';
$string['studentnotes_view_limited'] = 'Se muestran las últimas {$a} notas. Abra el informe completo para revisar todas las notas.';
$string['report:skiptoheatmaptable'] = 'Omitir el mapa de calor e ir a la tabla de datos';
$string['report:heatmap_textsummary'] = 'El gráfico contiene {$a->clusters} clústeres; el clúster más grande contiene {$a->max} clics.';
$string['err:reactioniconvaluerequired'] = 'Introduzca un emoji o una clase de Font Awesome.';

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
$string['externalprovider_notice'] = 'External video providers such as YouTube and Vimeo may process personal data and set cookies according to their own privacy policies. Use uploaded files when third-party transfer is not allowed.';
$string['privacy:metadata:youtube'] = 'When a YouTube video is used, the user browser connects to YouTube to load and play the video.';
$string['privacy:metadata:youtube:videoid'] = 'The YouTube video identifier configured for this activity.';
$string['privacy:metadata:youtube:url'] = 'The YouTube URL configured for this activity.';
$string['privacy:metadata:vimeo'] = 'When a Vimeo video is used, the user browser connects to Vimeo to load and play the video.';
$string['privacy:metadata:vimeo:videoid'] = 'The Vimeo video identifier configured for this activity.';
$string['privacy:metadata:vimeo:url'] = 'The Vimeo URL configured for this activity.';
$string['html5:controls'] = 'Video controls';
$string['html5:play'] = 'Play';
$string['html5:pause'] = 'Pause';
$string['html5:seek'] = 'Seek';
$string['html5:volume'] = 'Volume';
$string['html5:mute'] = 'Mute';
$string['html5:unmute'] = 'Unmute';
$string['html5:speed'] = 'Speed';
$string['html5:pip'] = 'Picture-in-picture';
$string['html5:fullscreen'] = 'Fullscreen';
$string['html5:download'] = 'Download';
$string['setting:heading_privacy'] = 'Privacy and data retention';
$string['setting:heading_privacy_desc'] = 'Configure how VideoTrack stores tracking, notes and reaction data.';
$string['setting:retentionperioddays'] = 'Retention period for tracking data (days)';
$string['setting:retentionperioddays_desc'] = 'Number of days after which VideoTrack anonymises old tracking, notes and reaction data. Set to 0 to retain data indefinitely. User erasure requests are always handled by salted anonymisation rather than deleting aggregate analytics.';
$string['setting:strictsessionvalidation'] = 'Require same browser session for note and reaction validation';
$string['setting:strictsessionvalidation_desc'] = 'When enabled, notes and reactions can only be saved for timestamps watched in the current browser session. When disabled, VideoTrack accepts timestamps already watched by the same user in the same activity, improving usability after refreshes or browser changes while still rejecting unwatched timestamps.';
$string['task:cleanup'] = 'Anonymise expired VideoTrack tracking data';
$string['privacy:anonymised'] = '[anonymised]';
$string['error:playbackpositionnotwatched'] = 'This video position has not been watched yet, so the action cannot be saved.';
