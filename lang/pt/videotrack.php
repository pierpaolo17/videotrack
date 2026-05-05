<?php

$string['pluginname'] = 'Rastreio de vídeo';
$string['modulename'] = 'Rastreio de vídeo';
$string['modulenameplural'] = 'Rastreios de vídeo';
$string['pluginadministration'] = 'Administração de Rastreio de vídeo';
$string['videotrack:addinstance'] = 'Adicionar uma nova atividade de Rastreio de vídeo';
$string['videotrack:view'] = 'Ver Rastreio de vídeo';
$string['videotrack:viewreport'] = 'Ver relatórios de Rastreio de vídeo';
$string['videotrack:viewownreport'] = 'Ver o próprio relatório de Rastreio de vídeo';
$string['videoname'] = 'Nome da atividade';
$string['youtubeurl'] = 'URL do YouTube';
$string['youtubeurl_help'] = 'Cole um URL padrão do YouTube, um URL curto ou um URL de incorporação.';
$string['showcontrols'] = 'Mostrar controlos do leitor';
$string['disablekeyboard'] = 'Desativar atalhos de teclado';
$string['showfullscreen'] = 'Mostrar botão de ecrã inteiro';
$string['allowseekforward'] = 'Permitir avanço';
$string['allowseekbackward'] = 'Permitir retrocesso';
$string['allowplaybackratechange'] = 'Permitir alteração da velocidade de reprodução';
$string['countbyvideotime'] = 'Contar a cobertura pela linha temporal do vídeo';
$string['countbyvideotime_help'] = 'Recomendado. A conclusão baseia-se nos segundos únicos cobertos na linha temporal do vídeo, e não em visualizações repetidas.';
$string['completionpercent'] = 'Percentagem de conclusão obrigatória';
$string['completiondetail:percent'] = 'Exigir a visualização de pelo menos {$a}% do vídeo';
$string['completiondetail:minreactions'] = 'Exigir pelo menos {$a} reações distintas';
$string['completiondetail:allreactiontypes'] = 'Exigir pelo menos uma reação para cada tipo de reação configurado';
$string['reactionsheader'] = 'Reações';
$string['reactionsenabled'] = 'Ativar reações';
$string['reactionsrequired'] = 'Exigir reações';
$string['minreactions'] = 'Número mínimo de reações distintas';
$string['requireallreactiontypes'] = 'Exigir pelo menos uma reação para cada tipo configurado';
$string['completionlogic'] = 'Lógica de conclusão';
$string['logicand'] = 'Todas as condições ativadas (AND)';
$string['logicor'] = 'Qualquer condição ativada (OR)';
$string['clusterwindow'] = 'Janela de agrupamento (segundos)';
$string['showstudentreport'] = 'Mostrar relatório aos estudantes';
$string['showreactionnotice'] = 'Mostrar aviso de reações';
$string['reactionnotice'] = 'Aviso de reações';
$string['reactionlabel'] = 'Rótulo da reação';
$string['reactiondescription'] = 'Descrição da reação';
$string['reactionicontype'] = 'Tipo de ícone';
$string['reactioniconvalue'] = 'Valor do ícone';
$string['reactioniconvalue_help'] = 'Para Emoji, introduza o carácter emoji. Para Font Awesome, introduza a classe CSS, por exemplo fa-regular fa-face-smile. Deixe este campo vazio quando utilizar um ficheiro de ícone carregado.';
$string['reactioniconfile'] = 'Ficheiro do ícone da reação';
$string['reactioniconfile_help'] = 'Ficheiro de imagem opcional utilizado quando o tipo de ícone é “Ficheiro carregado”. Os formatos aceites dependem do suporte de imagens web do Moodle.';
$string['reactionrequired'] = 'Obrigatória para conclusão';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Classe Font Awesome';
$string['icontype:file'] = 'Ficheiro carregado';
$string['addreaction'] = 'Adicionar reação';
$string['invalidyoutubeurl'] = 'URL do YouTube inválido.';
$string['err:minreactionsrequired'] = 'Defina um número mínimo de reações distintas ou ative a regra que exige todos os tipos de reação.';
$string['notice:minreactions'] = 'São necessárias pelo menos {$a} reações distintas.';
$string['notice:requiredtypes'] = 'Tipos de reação obrigatórios: {$a}.';
$string['watch'] = 'Ver';
$string['report'] = 'Relatórios';
$string['reportstudent'] = 'As minhas reações';
$string['reportteacher'] = 'Relatório do docente';
$string['report:cumulative'] = 'Cumulativo';
$string['report:perstudent'] = 'Por estudante';
$string['report:userid'] = 'Utilizador';
$string['report:uniquecoveredseconds'] = 'Segundos únicos cobertos';
$string['report:completionpercent'] = 'Conclusão %';
$string['report:lastposition'] = 'Última posição';
$string['report:iscompleted'] = 'Concluído';
$string['report:noattempts'] = 'Não foram encontrados dados de visualização.';
$string['report:noreactions'] = 'Não foram encontrados dados de reação.';
$string['report:timestamp'] = 'Carimbo temporal';
$string['report:reaction'] = 'Reação';
$string['report:description'] = 'Descrição';
$string['report:clicks'] = 'Cliques';
$string['report:students'] = 'Estudantes';
$string['report:replay'] = 'Rever fragmento';
$string['report:delete'] = 'Eliminar';
$string['report:sort'] = 'Ordenar por';
$string['report:sorttime'] = 'Carimbo temporal';
$string['report:sortreaction'] = 'Reação';
$string['report:sortclicks'] = 'Cliques';
$string['report:aggregation'] = 'Agregação';
$string['report:aggregationtype'] = 'Mesma reação dentro da janela';
$string['report:aggregationpeak'] = 'Pico de qualquer reação';
$string['report:exportcsv'] = 'Exportar CSV';
$string['progress'] = 'Progresso';
$string['uniquereactions'] = 'Reações distintas';
$string['removereaction'] = 'Remover reação';
$string['playerunavailable'] = 'Não foi possível inicializar o leitor.';
$string['yes'] = 'Sim';
$string['no'] = 'Não';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heading_performance'] = 'Desempenho';
$string['setting:heading_defaults'] = 'Valores padrão para novas atividades';
$string['setting:heading_defaults_desc'] = 'Estes valores são usados como padrão quando um professor cria uma nova atividade VideoTrack. Cada atividade ainda pode ser configurada individualmente.';
$string['setting:default_desc'] = 'Valor padrão para novas atividades. Pode ser substituído pelo professor para cada atividade individual.';
$string['setting:default_completionpercent_desc'] = 'Percentagem mínima padrão do vídeo que o aluno deve assistir para concluir a atividade. Definir como 0 para deixar a regra de conclusão desativada por padrão.';
$string['event:segment_saved'] = 'Segmento de visualização guardado';
$string['event:reaction_saved'] = 'Reação enviada';
$string['event:reaction_deleted'] = 'Reação eliminada';
$string['setting:heartbeatinterval'] = 'Intervalo de heartbeat (segundos)';
$string['setting:heartbeatinterval_desc'] = 'Com que frequência o player guarda no servidor o segmento de visualização atual durante a reprodução contínua. Valores mais baixos reduzem o risco de perda de dados em caso de falha do navegador ou queda de rede, mas aumentam a carga do servidor (uma solicitação AJAX + duas consultas ao banco de dados por aluno por intervalo). Intervalo recomendado: 15 a 120 segundos.';

$string['reactionx'] = 'Reação {$a}';

$string['err:reactioniconfilerequired'] = 'Carregue um ficheiro de ícone quando o tipo de ícone estiver definido como Ficheiro carregado.';


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
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indica se o evento de reação foi eliminado pelo utilizador.';

$string['cap:overrideplayersettings'] = 'Substituir configurações do reprodutor da plataforma';
$string['cap:overrideplayersettings_desc'] = 'Permite ao professor alterar as configurações do reprodutor definidas pelo administrador como padrões da plataforma.';
$string['cap:overridecompletionsettings'] = 'Substituir configurações de conclusão da plataforma';
$string['cap:overridecompletionsettings_desc'] = 'Permite ao professor alterar as configurações de conclusão definidas pelo administrador como padrões da plataforma.';
$string['setting:lockedbyAdmin'] = 'Estas configurações estão bloqueadas pelo administrador da plataforma e não podem ser alteradas para atividades individuais.';
$string['setting:heading_presets'] = 'Predefinições de reações';
$string['setting:heading_presets_desc'] = 'Conjuntos de reações a nível do site que os professores podem usar como ponto de partida.';
$string['reactionpreset'] = 'Aplicar uma predefinição de reações';
$string['reactionpreset_help'] = 'Selecione uma predefinição para pré-preencher os campos de reação. Pode editar livremente os valores depois.';
$string['reactionpreset:none'] = '— configurar manualmente —';
$string['presets:manage'] = 'Gerir predefinições de reações';
$string['presets:pagetitle'] = 'VideoTrack — Predefinições de reações';
$string['presets:intro'] = 'Defina predefinições de reações a nível do site como ponto de partida para os professores.';
$string['presets:addpreset'] = 'Adicionar predefinição';
$string['presets:backtolist'] = 'Voltar à lista de predefinições';
$string['presets:saved'] = 'Predefinição guardada.';
$string['presets:deleted'] = 'Predefinição eliminada.';
$string['presets:notfound'] = 'Predefinição não encontrada.';
$string['presets:noneyet'] = 'Ainda não foram configuradas predefinições de reações.';
$string['presets:confirmdelete'] = 'Tem a certeza de que deseja eliminar esta predefinição?';
$string['presets:presetdetails'] = 'Detalhes da predefinição';
$string['presets:name'] = 'Nome da predefinição';
$string['presets:key'] = 'Chave da predefinição';
$string['presets:key_help'] = 'Identificador único (apenas letras, números e sublinhados). Não pode ser alterado após a criação.';
$string['presets:reactions'] = 'Reações';
$string['presets:reactions_help'] = 'Deixe o rótulo vazio para ignorar uma linha.';
$string['presets:col_name'] = 'Nome';
$string['presets:col_key'] = 'Chave';
$string['presets:col_reactions'] = 'Reações';
$string['presets:col_actions'] = 'Ações';
$string['setting:heartbeatinterval_min'] = 'Valor mínimo aplicado: 5 segundos.';

$string['reset:userdata'] = 'Eliminar todos os dados de visualização dos alunos (segmentos, estados, reações)';
$string['report:recalculate'] = 'Recalcular todos os estados de conclusão';
$string['report:recalculated'] = 'Estados de conclusão recalculados para {$a} utilizadores.';
$string['report:heatmap_desc'] = 'Mapa de calor de reações na linha do tempo do vídeo (altura da barra = número de cliques nesse ponto):';
$string['event:activity_completed'] = 'Atividade VideoTrack concluída';

$string['reactioniconfile_notice'] = 'A imagem será redimensionada automaticamente para 64×64 pixels (recorte centrado). Para melhores resultados, carregue uma imagem quadrada (proporção 1:1). Formatos aceites: JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Clique num botão de reação enquanto o vídeo está a ser reproduzido para registar a sua reação nesse momento.';

$string['showgradeto'] = 'Mostrar nota ao aluno';
$string['showgradeto_help'] = 'Se ativado, o aluno verá a sua nota diretamente na página da atividade.';
$string['report:grade'] = 'Nota';
$string['report:gradesaved'] = 'Nota guardada com sucesso.';
$string['report:gradepass_hint'] = 'Nota mínima: {$a}';
$string['report:gradenotset'] = 'Ainda não avaliado';

$string['videosource'] = 'Fonte de vídeo';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Carregar (MP4/WebM/MP3)';
$string['vimeourl'] = 'URL do Vimeo';
$string['vimeourl_help'] = 'Cole o URL do vídeo Vimeo.';
$string['invalidvimeourl'] = 'O URL não parece ser um URL Vimeo válido.';
$string['videofile'] = 'Ficheiro de vídeo/áudio';
$string['videofile_help'] = 'Carregue um ficheiro MP4, WebM ou MP3.';
$string['videofile_notice'] = 'Formatos aceites: MP4, WebM, MP3, M4V, MOV, AAC, M4A.';
$string['setting:heading_player'] = 'Comportamento do reprodutor';
$string['setting:playbackspeeds'] = 'Velocidades de reprodução disponíveis';
$string['setting:playbackspeeds_desc'] = 'Selecione quais velocidades estão disponíveis em toda a plataforma.';
$string['setting:playbackspeeds_teacher_desc'] = 'Selecione as velocidades de reprodução para esta atividade.';
$string['setting:speed_normal'] = 'normal';
$string['setting:distractionfree'] = 'Modo sem distração';
$string['setting:distractionfree_desc'] = 'Oculta cabeçalho, rodapé e navegação ao visualizar a atividade.';
$string['intervalbar_title'] = 'Intervalos vistos — segmentos verdes = partes já vistas.';
$string['outline:percent'] = '{$a}% visto';
$string['outline:nodata'] = 'Sem dados de visualização.';
$string['coursereport:title'] = 'VideoTrack — Relatório do curso';
$string['coursereport:navlink'] = 'Relatórios VideoTrack';
$string['coursereport:intro'] = 'Visão geral de todas as atividades VideoTrack no curso.';
$string['coursereport:nodata'] = 'Nenhuma atividade VideoTrack encontrada.';
$string['coursereport:col_activity'] = 'Atividade';
$string['coursereport:col_source'] = 'Fonte';
$string['coursereport:col_duration'] = 'Duração';
$string['coursereport:col_students_started'] = 'Alunos iniciados';
$string['coursereport:col_avg_percent'] = 'Cobertura média';
$string['coursereport:col_completions'] = 'Conclusões';
$string['coursereport:col_reactions'] = 'Reações';
$string['coursereport:col_actions'] = 'Ações';

$string['grade:pass'] = 'Aprovado';
$string['grade:fail'] = 'Reprovado';

$string['autoplay'] = 'Reprodução automática';
$string['autoplay_help'] = 'Inicia o vídeo automaticamente. Os navegadores requerem silenciamento para a reprodução automática.';
$string['loop'] = 'Repetir em loop';
$string['startmuted'] = 'Iniciar silenciado';
$string['startmuted_help'] = 'Inicia a reprodução com o áudio silenciado.';
$string['allowdownload'] = 'Permitir download (apenas fonte upload)';
$string['setting:allowdownload_desc'] = 'Mostra um botão de download no reprodutor HTML5.';
$string['setting:heading_playerbehavior'] = 'Comportamento padrão do reprodutor';
$string['setting:heading_playerbehavior_desc'] = 'Valores padrão para reprodução automática, loop, silêncio e download.';
$string['setting:heading_html5controls'] = 'Controlos do reprodutor HTML5 (fonte upload)';
$string['setting:heading_html5controls_desc'] = 'Selecione os controlos disponíveis na barra do reprodutor HTML5.';
$string['setting:html5controls'] = 'Controlos disponíveis';
$string['setting:html5controls_desc'] = 'Selecione os controlos a mostrar no reprodutor HTML5.';
$string['setting:html5controls_teacher_desc'] = 'Selecione os controlos para esta atividade.';
$string['ctrl:play'] = 'Play/Pausa';
$string['ctrl:progress'] = 'Barra de progresso';
$string['ctrl:current'] = 'Tempo atual';
$string['ctrl:duration'] = 'Duração';
$string['ctrl:mute'] = 'Silenciar';
$string['ctrl:volume'] = 'Volume';
$string['ctrl:speed'] = 'Velocidade';
$string['ctrl:pip'] = 'Picture-in-Picture';
$string['ctrl:fullscreen'] = 'Ecrã completo';
$string['ctrl:download'] = 'Descarregar';

$string['setting:playerwidth'] = 'Largura máxima do reprodutor (px)';
$string['setting:playerwidth_desc'] = 'Largura máxima do reprodutor de vídeo em píxeis.';
$string['playerwidth'] = 'Largura máxima do reprodutor (px)';
$string['playerwidth_help'] = 'Largura máxima do reprodutor para esta atividade. 0 = padrão.';
$string['setting:rewindstep'] = 'Passo de retrocesso (segundos)';
$string['setting:rewindstep_desc'] = 'Segundos que o botão recua. 0 = desativado. Padrão: 10. Importante: se "Permitir salto para trás" estiver desativado para uma atividade, o botão não aparecerá mesmo que este valor seja > 0.';
$string['rewindstep'] = 'Passo de retrocesso (segundos)';
$string['rewindstep_help'] = 'Segundos de retrocesso para esta atividade. 0 = padrão. Nota: se "Permitir salto para trás" estiver desativado, o botão não aparecerá independentemente deste valor.';
$string['setting:fastforwardstep'] = 'Passo de avanço rápido (segundos)';
$string['setting:fastforwardstep_desc'] = 'Segundos que o botão avança. 0 = desativado. Padrão: 10. Importante: se "Permitir salto para frente" estiver desativado para uma atividade, o botão não aparecerá mesmo que este valor seja > 0.';
$string['fastforwardstep'] = 'Passo de avanço rápido (segundos)';
$string['fastforwardstep_help'] = 'Segundos de avanço para esta atividade. 0 = padrão. Nota: se "Permitir salto para frente" estiver desativado, o botão não aparecerá independentemente deste valor.';
$string['captionsheader'] = 'Legendas';
$string['captions'] = 'Ativar legendas';
$string['captions_help'] = 'Ativa legendas para YouTube, Vimeo ou Upload (VTT).';
$string['setting:default_captions_desc'] = 'Ativar legendas por padrão para novas atividades.';
$string['captionslang'] = 'Idioma de legendas padrão';
$string['captionslang_help'] = 'Código de idioma ISO 639-1 (ex. pt, en).';
$string['setting:captionslang_desc'] = 'Idioma de legendas padrão (ISO 639-1).';
$string['vttfile'] = 'Ficheiro de legendas (.vtt)';
$string['vttfile_help'] = 'Carregar ficheiro WebVTT.';
$string['vttfile_notice'] = 'Formato aceite: WebVTT (.vtt).';
$string['vimeo_captions_notice'] = 'As legendas Vimeo são geridas em Vimeo.com.';
$string['ctrl:rewind'] = 'Botão de retrocesso';
$string['ctrl:fastforward'] = 'Botão de avanço rápido';

$string['playerloading'] = 'A carregar o reprodutor de vídeo, aguarde…';
$string['noreactionsyet'] = 'Nenhuma reação registada ainda. Reaja enquanto o vídeo está a ser reproduzido.';
$string['reaction:error'] = 'Não foi possível guardar a sua reação. Por favor, tente novamente.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Retomar reprodução';
$string['resumeplayback_desc'] = 'Retoma automaticamente o vídeo no ponto em que o estudante parou na última sessão.';
$string['resumeplayback_help'] = 'Quando ativado, o vídeo começa na última posição guardada (se for mais de 5 segundos após o início). O estudante pode sempre voltar manualmente ao início.';
$string['setting:resumeplayback'] = 'Retomar reprodução (predefinição)';
$string['setting:resumeplayback_desc'] = 'Definição predefinida para novas atividades VideoTrack. Os docentes podem alterá-la por atividade.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Velocidade máxima de reprodução';
$string['maxplaybackrate_desc'] = 'Limita a velocidade máxima que os estudantes podem selecionar. 0 = sem limite.';
$string['maxplaybackrate_help'] = 'Quando definido, os estudantes não podem reproduzir o vídeo acima desta velocidade, mesmo que os controlos do player permitam valores superiores.';
$string['maxplaybackrate_nolimit'] = 'Sem limite';
$string['setting:maxplaybackrate'] = 'Velocidade máxima de reprodução (predefinição)';
$string['setting:maxplaybackrate_desc'] = 'Velocidade máxima predefinida para novas atividades. Os docentes podem alterá-la por atividade.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Mostrar transcrição interativa';
$string['showtranscript_desc'] = 'Mostra junto ao vídeo um painel de transcrição deslocável e clicável (requer ficheiro VTT).';
$string['showtranscript_help'] = 'Analisa o ficheiro VTT carregado e mostra-o como lista clicável. Cada entrada apresenta timestamp e texto; o clique salta o vídeo para esse ponto.';
$string['transcript_title'] = 'Transcrição';
$string['transcript_loading'] = 'A carregar transcrição…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Clique no vídeo para iniciar a reprodução.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['vimeocspwarnlabel'] = 'Não foi possível carregar o player Vimeo. Verifique a ligação de rede ou peça ao administrador para permitir player.vimeo.com na Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'A retomar de';
// ── Report: azioni studente ──
$string['report:actions'] = 'Actions';
$string['report:resetstudent'] = 'Reset progress';
$string['report:resetstudent_confirm'] = 'Are you sure you want to reset this student\'s progress? This will delete all their viewing history and reactions and cannot be undone.';
$string['report:studentreset'] = 'Student progress has been reset.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Mostrar navegação por capítulos';
$string['showchapters_desc'] = 'Mostra uma barra com marcadores de capítulo extraídos do ficheiro VTT. Os capítulos são cues VTT com texto inferior a 80 caracteres.';
$string['showchapters_help'] = 'Se o ficheiro VTT carregado contiver cues curtos, estes são interpretados como títulos de capítulos e mostrados numa barra clicável.';
$string['chapters_label'] = 'Capítulos do vídeo';
$string['chapterslabel'] = 'Capítulos do vídeo';
$string['chapter_label'] = 'Capítulo';
$string['chapterlabel'] = 'Capítulo';
$string['studentnotesenabled'] = 'Ativar notas dos estudantes';
$string['studentnotesenabled_desc'] = 'Permite que os estudantes escrevam notas pessoais com timestamp enquanto veem o vídeo.';
$string['studentnotesenabled_help'] = 'Quando ativado, surge uma área de texto junto ao vídeo. Os estudantes podem guardar uma nota no timestamp atual. As notas são visíveis apenas para o autor e para gestores através do relatório.';
$string['setting:studentnotesenabled'] = 'Ativar notas dos estudantes (predefinição)';
$string['setting:studentnotesenabled_desc'] = 'Definição predefinida para novas atividades VideoTrack. Os docentes podem alterá-la por atividade.';
$string['studentnotes_title'] = 'As minhas notas';
$string['studentnote_placeholder'] = 'Escreva uma nota neste momento do vídeo…';
$string['studentnote_save'] = 'Guardar nota';
$string['studentnote_hint'] = 'A nota será guardada no timestamp atual. O vídeo deve estar em reprodução.';
$string['studentnotes_list_label'] = 'Notas guardadas';
$string['studentnote_label'] = 'Nota do estudante';
$string['noteerrorlabel'] = 'Não foi possível guardar a nota. Tente novamente.';
$string['charsremaininglabel'] = 'caracteres restantes';
$string['posterimage'] = 'Imagem de poster / pré-visualização';
$string['posterimage_help'] = 'Carregue uma imagem para mostrar antes do início do vídeo. Formatos aceites: JPG, PNG, WebP, GIF. Tamanho recomendado: 1280×720 px (16:9).';
$string['posterimage_notice'] = 'A imagem de poster é mostrada antes da reprodução e ocultada automaticamente quando o vídeo começa.';
$string['playbutton_label'] = 'Reproduzir vídeo';
$string['setting:maxplaybackrate_nolimit'] = 'Sem limite';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Texto de uma nota pessoal escrita pelo estudante num timestamp específico do vídeo.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Tipo de evento: vazio para reações padrão, "note" para notas pessoais dos estudantes.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['studentnotesdisabled'] = 'As notas dos estudantes não estão ativadas para esta atividade.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'Nenhum ficheiro de vídeo foi carregado para esta atividade.';
$string['removenote'] = 'Remover nota';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Ocultar notas';
$string['notes_show'] = 'Mostrar notas';
$string['report:notes_title'] = 'Notas dos estudantes';
$string['report:nonotes'] = 'Nenhuma nota foi escrita para esta atividade.';
$string['report:notedate'] = 'Escrita em';
$string['report:exportnotes_csv'] = 'Exportar notas como CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Fechar';
$string['rewindlabel'] = 'Retroceder';
$string['fastforwardlabel'] = 'Avanço rápido';
$string['secondslabel'] = 'segundos';
$string['removenotelabel'] = 'Remover nota';
// ── Help strings ──
$string['gradepass_help'] = 'A nota mínima necessária para concluir com sucesso esta atividade. Estudantes que atinjam esta nota ou superior são considerados aprovados.';


$string['completiondetail:requiredreactions'] = 'Deve incluir estas reações obrigatórias: {$a}';

$string['error:playbackrequired'] = 'O vídeo deve estar em reprodução antes de esta ação poder ser guardada.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'Extensão PHP GD não disponível.';
$string['setting:gd_missing_desc'] = 'As imagens carregadas como ícones de reação NÃO serão redimensionadas automaticamente para 64×64 pixels. O ficheiro original será servido como está, o que pode afetar o desempenho se for grande. Para ativar o redimensionamento automático, peça ao administrador para instalar php-gd.';

$string['report:heatmap_legend'] = 'Legenda de cores do mapa de calor das reações';

$string['report:clusterlimitreached'] = 'O relatório atingiu o número máximo de clusters apresentados. Utilize filtros ou uma janela temporal mais reduzida para uma análise completa.';

$string['report:showingrecentreactions'] = 'São apresentadas apenas as primeiras {$a} reações.';

$string['report:viewfullreport'] = 'Ver o relatório completo';
$string['studentnotes_view_limited'] = 'São apresentadas as últimas {$a} notas. Abra o relatório completo para rever todas as notas.';
$string['report:skiptoheatmaptable'] = 'Ignorar o mapa de calor e ir para a tabela de dados';
$string['report:heatmap_textsummary'] = 'O gráfico contém {$a->clusters} grupos; o maior grupo contém {$a->max} cliques.';
$string['err:reactioniconvaluerequired'] = 'Introduza um emoji ou uma classe Font Awesome.';

$string['error:reactionratelimit'] = 'Foram enviadas demasiadas reações em pouco tempo. Continue a ver o vídeo e tente novamente.';
$string['event:student_progress_reset'] = 'Dados VideoTrack do estudante reiniciados';
$string['report:timefrom'] = 'A partir do segundo';
$string['report:timeto'] = 'Até ao segundo';
$string['report:clusterlimitreached_help'] = 'O relatório cumulativo atingiu o limite de clusters apresentados. Use os filtros por utilizador, reação ou tempo do vídeo para restringir a análise e recuperar clusters posteriores.';
$string['report:topclusterssummary'] = 'Clusters mais relevantes nesta seleção:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} cliques';
$string['error:notesratelimit'] = 'Foram enviadas demasiadas notas num curto período. Aguarde antes de adicionar outra nota.';

$string['privacy:segmentschunk'] = 'Segmentos de visualização - parte {$a}';

$string['privacy:reactionsactivechunk'] = 'Reações ativas - parte {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Reações eliminadas - parte {$a}';

$string['privacy:notesactivechunk'] = 'Notas ativas - parte {$a}';

$string['privacy:notesdeletedchunk'] = 'Notas eliminadas - parte {$a}';

$string['report:clusterlimitreached_csv'] = 'AVISO: o limite de clusters foi atingido. A exportação pode estar incompleta; aplique filtros de utilizador, reação ou tempo e exporte novamente.';

$string['report:notecreatedfrom'] = 'Notas desde a data';

$string['report:notecreatedto'] = 'Notas até à data';

$string['reactionsavailableonlyduringplayback'] = 'As reações estão disponíveis apenas durante a reprodução do vídeo.';
