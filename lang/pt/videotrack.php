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
$string['reactioniconvalue_help'] = 'Para Emoji, introduza o carácter emoji. Para Font Awesome, introduza uma classe suportada pelo tema Moodle, por exemplo fa fa-smile para temas Font Awesome 5 ou fa-regular fa-face-smile para temas Font Awesome 6. A disponibilidade dos ícones depende do tema Moodle ativo e da versão do Font Awesome instalada. Deixe este campo vazio quando usar um ficheiro de ícone carregado.';
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
$string['setting:heading_accessibility'] = 'Acessibilidade';
$string['setting:heading_accessibility_desc'] = 'Definições para anúncios de tecnologias de apoio e feedback de teclado/leitor de ecrã.';
$string['setting:heading_defaults'] = 'Valores padrão para novas atividades';
$string['setting:heading_defaults_desc'] = 'Estes valores são usados como padrão quando um professor cria uma nova atividade VideoTrack. Cada atividade ainda pode ser configurada individualmente.';
$string['setting:default_desc'] = 'Valor padrão para novas atividades. Pode ser substituído pelo professor para cada atividade individual.';
$string['setting:default_completionpercent_desc'] = 'Percentagem mínima padrão do vídeo que o aluno deve assistir para concluir a atividade. Definir como 0 para deixar a regra de conclusão desativada por padrão.';
$string['event:segment_saved'] = 'Segmento de visualização guardado';
$string['event:reaction_saved'] = 'Reação enviada';
$string['event:note_saved'] = 'Nota do estudante salva';
$string['event:note_deleted'] = 'Nota pessoal apagada';
$string['event:reaction_deleted'] = 'Reação eliminada';
$string['setting:heartbeatinterval'] = 'Intervalo de heartbeat (segundos)';
$string['setting:heartbeatinterval_desc'] = 'Com que frequência o player guarda no servidor o segmento de visualização atual durante a reprodução contínua. Valores mais baixos reduzem o risco de perda de dados em caso de falha do navegador ou queda de rede, mas aumentam a carga do servidor (uma solicitação AJAX + duas consultas ao banco de dados por aluno por intervalo). Intervalo recomendado: 15–120 segundos. Valor mínimo aplicado: 5 segundos (valores abaixo de 5 são automaticamente elevados para 5 pelo servidor).';
$string['setting:reactionannouncementinterval'] = 'Intervalo dos anúncios acessíveis das reações (milissegundos)';
$string['setting:reactionannouncementinterval_desc'] = 'Intervalo mínimo, em milissegundos, entre anúncios repetidos “reações indisponíveis” para leitores de ecrã. Use um valor mais baixo para feedback frequente em vídeos curtos ou mais alto para reduzir anúncios repetidos. Defina 0 para desativar os anúncios repetidos. Intervalo recomendado quando ativo: 10000–60000 milissegundos. Exemplos: 10000 = 10 segundos, 30000 = 30 segundos, 60000 = 1 minuto.';
$string['setting:reactionreadydebouncems'] = 'Debounce das reações prontas (milissegundos)';
$string['setting:reactionreadydebouncems_desc'] = 'Atraso mínimo, em milissegundos, antes de repetir o anúncio “reações disponíveis” após uma pausa e retoma rápidas. Defina 0 para desativar este debounce.';

$string['reactionx'] = 'Reação {$a}';

$string['err:reactioniconfilerequired'] = 'Carregue um ficheiro de ícone quando o tipo de ícone estiver definido como Ficheiro carregado.';


$string['privacy:metadata:common:timecreated'] = 'Hora em que o registo foi criado.';
$string['privacy:metadata:common:timemodified'] = 'Hora da última alteração do registo.';
$string['privacy:metadata:videotrack_seg'] = 'Armazena segmentos de visualização registados para um utilizador numa atividade de vídeo.';
$string['privacy:metadata:videotrack_seg:userid'] = 'O utilizador cujo segmento de visualização foi registado.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Identificador de sessão do navegador associado ao segmento de visualização.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Hora do servidor em que o segmento começou.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Hora do servidor em que o segmento terminou.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Posição na linha temporal do vídeo no início do segmento.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Posição na linha temporal do vídeo no fim do segmento.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Velocidade de reprodução usada durante o segmento.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Motivo pelo qual o segmento terminou.';
$string['privacy:metadata:videotrack_state'] = 'Armazena o estado agregado de visualização de um utilizador numa atividade de vídeo.';
$string['privacy:metadata:videotrack_state:userid'] = 'O utilizador cujo estado agregado foi armazenado.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Última posição conhecida alcançada pelo utilizador na linha temporal do vídeo.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Duração do vídeo acompanhado em segundos.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Número de segundos únicos da linha temporal cobertos pelo utilizador.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Percentagem de conclusão calculada para o utilizador.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Intervalos combinados usados para calcular a cobertura única.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Se a atividade está atualmente marcada como concluída para o utilizador.';
$string['privacy:metadata:videotrack_reactev'] = 'Armazena eventos de reação registados enquanto o utilizador vê o vídeo.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'O utilizador que enviou a reação.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Identificador de sessão do navegador associado ao evento de reação.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Chave interna da reação no momento do registo.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Rótulo da reação apresentado ao utilizador no momento do registo.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Descrição da reação apresentada ao utilizador no momento do registo.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Posição na linha temporal do vídeo quando a reação foi registada.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Velocidade de reprodução quando a reação foi registada.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indica se o evento de reação foi eliminado pelo utilizador.';

$string['videotrack:viewcoursereport'] = 'Ver relatório VideoTrack ao nível do curso';
$string['videotrack:viewcoursereport_desc'] = 'Permite ver o relatório agregado do VideoTrack para todo o curso.';
$string['videotrack:overrideplayersettings'] = 'Substituir configurações do reprodutor da plataforma';
$string['videotrack:overrideplayersettings_desc'] = 'Permite ao professor alterar as configurações do reprodutor definidas pelo administrador como padrões da plataforma.';
$string['videotrack:overridecompletionsettings'] = 'Substituir configurações de conclusão da plataforma';
$string['videotrack:overridecompletionsettings_desc'] = 'Permite ao professor alterar as configurações de conclusão definidas pelo administrador como padrões da plataforma.';
$string['setting:lockedbyAdmin'] = 'Estas configurações estão bloqueadas pelo administrador da plataforma e não podem ser alteradas para atividades individuais.';
$string['setting:heading_presets'] = 'Predefinições de reações';
$string['setting:heading_presets_desc'] = 'Conjuntos de reações a nível do site que os professores podem usar como ponto de partida.';
$string['reactionpreset'] = 'Aplicar uma predefinição de reações';
$string['reactionpreset_help'] = 'Selecione uma predefinição para pré-preencher os campos de reação. Pode editar livremente os valores depois.';
$string['reactionpreset:none'] = '— configurar manualmente —';
$string['presets:manage'] = 'Gerir predefinições de reações';
$string['presets:pagetitle'] = 'VideoTrack — Predefinições de reações';
$string['presets:intro'] = 'Defina predefinições de reações ao nível do site que os docentes podem usar como ponto de partida ao criar uma atividade VideoTrack. As reações são copiadas para a atividade e podem ser editadas livremente pelo docente.';
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

$string['reset:userdata'] = 'Eliminar todos os dados de visualização dos alunos (segmentos, estados, reações)';
$string['report:recalculate'] = 'Recalcular todos os estados de conclusão';
$string['report:recalculated'] = 'Estados de conclusão recalculados para {$a} utilizadores.';
$string['report:heatmap_desc'] = 'Mapa de calor de reações na linha do tempo do vídeo (altura da barra = número de cliques nesse ponto):';
$string['report:heatmap_supplementary'] = 'O mapa de calor é uma visualização complementar. Os dados completos dos agrupamentos estão disponíveis na tabela abaixo.';
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
$string['vimeourl_help'] = 'Cole o URL do vídeo Vimeo (por exemplo, https://vimeo.com/123456789).';
$string['invalidvimeourl'] = 'O URL não parece ser um URL Vimeo válido.';
$string['videofile'] = 'Ficheiro de vídeo/áudio';
$string['videofile_help'] = 'Carregue um ficheiro MP4, WebM ou MP3.';
$string['videofile_notice'] = 'Formatos aceites: MP4, WebM, MP3, M4V, MOV, AAC, M4A.';
$string['setting:heading_player'] = 'Comportamento do reprodutor';
$string['setting:playbackspeeds'] = 'Velocidades de reprodução disponíveis';
$string['setting:playbackspeeds_desc'] = 'Selecione quais velocidades de reprodução estão disponíveis em toda a plataforma. Os docentes podem restringir esta lista para atividades individuais (se tiverem a capacidade de substituição). O valor 1× (normal) é sempre recomendado.';
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
$string['setting:playerwidth_desc'] = 'Largura máxima do reprodutor de vídeo em píxeis (1–4096). Os docentes podem alterar este valor para atividades individuais (valor 0 na atividade = usar o valor predefinido do site). Recomendado: 960.';
$string['playerwidth'] = 'Largura máxima do reprodutor (px)';
$string['playerwidth_help'] = 'Largura máxima do reprodutor para esta atividade. 0 = padrão.';
$string['playerwidth_zero_note'] = 'Insira 0 para herdar o padrão da plataforma, ou um valor de 1 a 4096 píxeis para esta atividade.';
$string['setting:rewindstep'] = 'Passo de retrocesso (segundos)';
$string['setting:rewindstep_desc'] = 'Quantos segundos o botão de retroceder recua por padrão. Os docentes podem alterar isso em atividades individuais. Defina 0 para ocultar o botão por padrão; substituições da atividade ainda podem reativá-lo. Padrão: 10. Importante: se "Permitir salto para trás" estiver desativado para uma atividade, o botão não aparecerá mesmo que este valor seja > 0.';
$string['rewindstep'] = 'Passo de retrocesso (segundos)';
$string['rewindstep_help'] = 'Quantos segundos o botão retrocede nesta atividade. Deixe 0 para usar o padrão da plataforma. Se o padrão da plataforma for 0, o botão ficará oculto a menos que esta atividade defina seu próprio valor. Nota: se "Permitir salto para trás" estiver desativado nesta atividade, o botão não aparecerá independentemente deste valor — as duas configurações funcionam juntas.';
$string['setting:fastforwardstep'] = 'Passo de avanço rápido (segundos)';
$string['setting:fastforwardstep_desc'] = 'Quantos segundos o botão de avanço rápido avança por padrão. Os docentes podem alterar isso em atividades individuais. Defina 0 para ocultar o botão por padrão; substituições da atividade ainda podem reativá-lo. Padrão: 10. Importante: se "Permitir salto para a frente" estiver desativado para uma atividade, o botão não aparecerá mesmo que este valor seja > 0.';
$string['fastforwardstep'] = 'Passo de avanço rápido (segundos)';
$string['fastforwardstep_help'] = 'Quantos segundos o botão avança nesta atividade. Deixe 0 para usar o padrão da plataforma. Se o padrão da plataforma for 0, o botão ficará oculto a menos que esta atividade defina seu próprio valor. Nota: se "Permitir salto para a frente" estiver desativado nesta atividade, o botão não aparecerá independentemente deste valor — as duas configurações funcionam juntas.';
$string['captionsheader'] = 'Legendas';
$string['captions'] = 'Ativar legendas';
$string['captions_help'] = 'Quando ativado: YouTube — as legendas são mostradas por defeito; Vimeo — a faixa correspondente ao código de idioma é ativada (deve estar pré-carregada em Vimeo.com); Upload — o ficheiro VTT anexado é utilizado.';
$string['setting:default_captions_desc'] = 'Ativar legendas por padrão para novas atividades.';
$string['captionslang'] = 'Idioma de legendas padrão';
$string['captionslang_help'] = 'Código de idioma ISO 639-1 (ex. pt, en, de). Para YouTube, define o idioma preferido das legendas. Para Vimeo, ativa a faixa correspondente (deve estar carregada em Vimeo.com). Para Upload: campo informativo.';
$string['setting:captionslang_desc'] = 'Idioma de legendas padrão (ISO 639-1).';
$string['vttfile'] = 'Ficheiro de legendas (.vtt)';
$string['vttfile_help'] = 'Carregue um ficheiro de legendas WebVTT (.vtt). O ficheiro será enviado ao navegador do estudante e exibido como legendas no reprodutor de vídeo.';
$string['vttfile_notice'] = 'Formato aceite: WebVTT (.vtt). Apenas um ficheiro é suportado. O ficheiro deve corresponder ao código de idioma indicado acima.';
$string['vimeo_captions_notice'] = 'As legendas Vimeo são geridas em Vimeo.com. Carregue as suas faixas de legendas lá. O código de idioma indicado acima será usado para ativar automaticamente a faixa correspondente.';
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
$string['transcript_unavailable'] = 'A transcrição não está disponível para este vídeo.';
$string['transcript_loading'] = 'A carregar transcrição…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Clique no vídeo para iniciar a reprodução.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['sdkerrorlabel'] = 'Não foi possível carregar o leitor de vídeo. Isto pode ser causado por bloqueador de conteúdos, política de segurança de conteúdo ou restrição de rede. Desative bloqueadores ou contacte o administrador.';
$string['vimeocspwarnlabel'] = 'Não foi possível carregar o player Vimeo. Verifique a ligação de rede ou peça ao administrador para permitir player.vimeo.com na Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'A retomar de';
// ── Report: azioni studente ──
$string['report:actions'] = 'Ações';
$string['report:resetstudent'] = 'Repor progresso';
$string['report:resetstudent_confirm'] = 'Tem a certeza de que pretende repor o progresso deste estudante? Isto eliminará todo o histórico de visualização e reações e não pode ser desfeito.';
$string['report:studentreset'] = 'O progresso do estudante foi reposto.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Mostrar navegação por capítulos';
$string['showchapters_desc'] = 'Mostra uma barra com marcadores de capítulo extraídos do ficheiro VTT. Os capítulos são cues VTT com texto inferior a 80 caracteres.';
$string['showchapters_help'] = 'Se o ficheiro VTT carregado contiver cues curtos (menos de 80 caracteres), estes são interpretados como títulos de capítulos e apresentados como uma barra de navegação clicável acima dos controlos de vídeo. Clicar num capítulo salta para esse ponto.';
$string['chapters_label'] = 'Capítulos do vídeo';
$string['chapters_unavailable'] = 'Os capítulos não estão disponíveis para este vídeo.';
$string['chapter_label'] = 'Capítulo';
$string['studentnotesenabled'] = 'Ativar notas dos estudantes';
$string['studentnotesenabled_desc'] = 'Permite que os estudantes escrevam notas pessoais com timestamp enquanto veem o vídeo.';
$string['studentnotesenabled_help'] = 'Quando ativado, surge uma área de texto junto ao vídeo. Os estudantes podem guardar uma nota no timestamp atual. As notas são visíveis apenas para o autor e para gestores através do relatório.';
$string['setting:studentnotesenabled'] = 'Ativar notas dos estudantes (predefinição)';
$string['setting:studentnotesenabled_desc'] = 'Definição predefinida para novas atividades VideoTrack. Os docentes podem alterá-la por atividade.';
$string['setting:notemaxlength'] = 'Comprimento máximo das notas';
$string['setting:notemaxlength_desc'] = 'Número máximo de caracteres permitidos para cada nota pessoal do estudante. Predefinição: 2000.';
$string['studentnotes_title'] = 'As minhas notas';
$string['studentnote_placeholder'] = 'Escreva uma nota neste momento do vídeo…';
$string['studentnote_save'] = 'Guardar nota';
$string['studentnote_hint'] = 'A nota será guardada no timestamp atual. O vídeo deve estar em reprodução.';
$string['studentnotes_list_label'] = 'Notas guardadas';
$string['studentnote_label'] = 'Nota do estudante';
$string['noteerrorlabel'] = 'Não foi possível guardar a nota. Tente novamente.';
$string['notesavedlabel'] = 'Nota guardada.';
$string['notedeletedlabel'] = 'Nota removida.';
$string['noteplaybackrequiredlabel'] = 'Inicie a reprodução antes de guardar uma nota.';
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
$string['reactionsdisabled'] = 'As reações estão desativadas para esta atividade VideoTrack. Peça ao professor ou administrador do curso para ativá-las se forem necessárias.';
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

$string['report:showingrecentreactionsoftotal'] = 'São apresentadas {$a->shown} de {$a->total} reações, da mais antiga para a mais recente.';

$string['report:viewfullreport'] = 'Ver o relatório completo';
$string['studentnotes_view_limited'] = 'São apresentadas as últimas {$a} notas. Abra o relatório completo para rever todas as notas.';
$string['report:skiptoheatmaptable'] = 'Ignorar o mapa de calor e ir para a tabela de dados';
$string['report:heatmap_textsummary'] = 'O gráfico contém {$a->clusters} grupos; o maior grupo contém {$a->max} cliques.';
$string['err:reactioniconvaluerequired'] = 'Introduza um emoji ou uma classe Font Awesome.';
$string['err:reactioniconvalueinvalidfa'] = 'Introduza apenas nomes de classes Font Awesome válidos, usando letras, números, espaços e hífens.';

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
$string['reactionsreadyannounce'] = 'As reações estão agora disponíveis.';

$string['privacy:state'] = 'Estado de conclusão';

$string['report:clusterlimitrequiresfilters'] = 'O relatório cumulativo está parcial. Aplique um filtro de intervalo temporal do vídeo para recuperar de forma fiável os clusters restantes.';

$string['report:clusterlimitrequiresfilters_csv'] = 'A exportação cumulativa está parcial porque não foi aplicado um filtro de intervalo temporal do vídeo. Aplique os filtros De segundo/Até segundo e exporte novamente.';
$string['report:clusterexportblocked_csv'] = 'A exportação foi interrompida para evitar dados incompletos. Aplique um filtro de tempo do vídeo e exporte novamente.';
$string['report:clusterdisplayblocked'] = 'A tabela de agrupamentos foi ocultada para evitar dados incompletos. Aplique um filtro de tempo do vídeo para continuar.';
$string['unknownreaction'] = 'Reação desconhecida';

// Moodle HQ review fallback strings added in 1.0.29.
$string['externalprovider_notice'] = 'Fornecedores externos de vídeo como YouTube e Vimeo podem processar dados pessoais e definir cookies de acordo com as suas próprias políticas de privacidade. Use ficheiros carregados quando a transferência para terceiros não for permitida.';
$string['privacy:metadata:youtube'] = 'Quando é usado um vídeo do YouTube, o navegador do utilizador liga-se ao YouTube para carregar e reproduzir o vídeo.';
$string['privacy:metadata:youtube:videoid'] = 'O identificador do vídeo do YouTube configurado para esta atividade.';
$string['privacy:metadata:youtube:url'] = 'O URL do YouTube configurado para esta atividade.';
$string['privacy:metadata:vimeo'] = 'Quando é usado um vídeo do Vimeo, o navegador do utilizador liga-se ao Vimeo para carregar e reproduzir o vídeo.';
$string['privacy:metadata:vimeo:videoid'] = 'O identificador do vídeo Vimeo configurado para esta atividade.';
$string['privacy:metadata:vimeo:url'] = 'O URL do Vimeo configurado para esta atividade.';

$string['html5:controls'] = 'Controlos de vídeo';
$string['html5:play'] = 'Reproduzir';
$string['html5:pause'] = 'Pausa';
$string['html5:seek'] = 'Procurar';
$string['html5:volume'] = 'Volume';
$string['html5:mute'] = 'Silenciar';
$string['html5:unmute'] = 'Ativar som';
$string['html5:speed'] = 'Velocidade';
$string['html5:pip'] = 'Imagem em imagem';
$string['html5:fullscreen'] = 'Ecrã inteiro';
$string['html5:download'] = 'Transferir';
$string['setting:heading_privacy'] = 'Privacidade e conservação de dados';
$string['setting:heading_privacy_desc'] = 'Configure como o VideoTrack armazena dados de acompanhamento, notas e reações.';
$string['setting:retentionperioddays'] = 'Período de conservação dos dados de acompanhamento (dias)';
$string['setting:retentionperioddays_desc'] = 'Numero de dias apos os quais o VideoTrack anonimiza dados antigos de acompanhamento, notas e reacoes (incluindo etiquetas de reacao em texto livre) para a retencao automatica. Defina 0 para conservar os dados indefinidamente. Os pedidos de apagamento tratados pela Privacy API do Moodle eliminam permanentemente os registos de acompanhamento, estado, reacoes e notas do utilizador no contexto selecionado.';
$string['setting:strictsessionvalidation'] = 'Exigir a mesma sessão do navegador para validar notas e reações';
$string['setting:validationfallbackdays'] = 'Janela de validação de reprodução histórica (dias)';
$string['setting:validationfallbackdays_desc'] = 'Idade máxima, em dias, dos segmentos já assistidos que podem autorizar notas e reações após atualizar a página ou trocar de navegador. Defina 0 para permitir segmentos históricos assistidos sem limite; isso melhora a usabilidade, mas torna a validação de integridade académica mais permissiva. As verificações da mesma sessão e de reprodução recente são sempre tentadas primeiro.';
$string['setting:strictsessionvalidation_desc'] = 'Quando ativado, notas e reações só podem ser guardadas para tempos vistos na sessão atual do navegador. Quando desativado, o VideoTrack aceita tempos já vistos pelo mesmo utilizador na mesma atividade, melhorando a usabilidade após atualizações ou alterações de navegador e continuando a rejeitar posições não vistas.';
$string['task:cleanup'] = 'Anonimizar dados de acompanhamento expirados do VideoTrack';
$string['privacy:anonymised'] = '[anonimizado]';
$string['error:playbackpositionnotwatched'] = 'Esta posição do vídeo ainda não foi vista, por isso a ação não pode ser guardada.';

$string['setting:intrangerequired'] = 'Digite um número inteiro entre {$a->min} e {$a->max}.';
$string['err:playerwidthrequired'] = 'Introduza 0 para usar o valor predefinido da plataforma ou um número inteiro de 1 a 4096 píxeis.';
$string['err:playbacksteprequired'] = 'Digite um número inteiro de 0 a 300 segundos. Use 0 para o padrão da plataforma.';
$string['setting:nonnegativeintrequired'] = 'Introduza um número inteiro maior ou igual a 0.';
$string['report:anonymiseduser'] = 'Utilizador anonimizado';
$string['report:exportnotes_privacywarning'] = 'Esta exportação pode conter dados pessoais das notas dos estudantes. Transfira e armazene apenas quando houver uma finalidade válida e elimine quando já não for necessária.';

$string['privacy:videoid_export_note'] = 'Video/content identifier: {$a}';
$string['privacy:anonymisedreaction'] = 'Reação anonimizada';
