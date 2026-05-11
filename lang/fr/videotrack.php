<?php

$string['pluginname'] = 'Suivi vidéo';
$string['modulename'] = 'Suivi vidéo';
$string['modulenameplural'] = 'Suivis vidéo';
$string['pluginadministration'] = 'Administration de Suivi vidéo';
$string['videotrack:addinstance'] = 'Ajouter une activité Suivi vidéo';
$string['videotrack:view'] = 'Afficher Suivi vidéo';
$string['videotrack:viewreport'] = 'Afficher les rapports de Suivi vidéo';
$string['videotrack:viewownreport'] = 'Afficher son propre rapport Suivi vidéo';
$string['videoname'] = 'Nom de l’activité';
$string['youtubeurl'] = 'URL YouTube';
$string['youtubeurl_help'] = 'Collez une URL YouTube standard, courte ou d’intégration.';
$string['showcontrols'] = 'Afficher les contrôles du lecteur';
$string['disablekeyboard'] = 'Désactiver les raccourcis clavier';
$string['showfullscreen'] = 'Afficher le bouton plein écran';
$string['allowseekforward'] = 'Autoriser l’avance';
$string['allowseekbackward'] = 'Autoriser le retour arrière';
$string['allowplaybackratechange'] = 'Autoriser la modification de la vitesse de lecture';
$string['countbyvideotime'] = 'Compter la couverture sur la chronologie de la vidéo';
$string['countbyvideotime_help'] = 'Recommandé. L’achèvement repose sur les secondes uniques couvertes dans la chronologie de la vidéo, et non sur des visionnages répétés.';
$string['completionpercent'] = 'Pourcentage d’achèvement requis';
$string['completiondetail:percent'] = 'Exiger le visionnage d’au moins {$a}% de la vidéo';
$string['completiondetail:minreactions'] = 'Exiger au moins {$a} réactions distinctes';
$string['completiondetail:allreactiontypes'] = 'Exiger au moins une réaction pour chaque type de réaction configuré';
$string['reactionsheader'] = 'Réactions';
$string['reactionsenabled'] = 'Activer les réactions';
$string['reactionsrequired'] = 'Exiger des réactions';
$string['minreactions'] = 'Nombre minimal de réactions distinctes';
$string['requireallreactiontypes'] = 'Exiger au moins une réaction pour chaque type configuré';
$string['completionlogic'] = 'Logique d’achèvement';
$string['logicand'] = 'Toutes les conditions activées (AND)';
$string['logicor'] = 'N’importe quelle condition activée (OR)';
$string['clusterwindow'] = 'Fenêtre de regroupement (secondes)';
$string['showstudentreport'] = 'Afficher le rapport aux étudiants';
$string['showreactionnotice'] = 'Afficher l’avis sur les réactions';
$string['reactionnotice'] = 'Avis sur les réactions';
$string['reactionlabel'] = 'Libellé de la réaction';
$string['reactiondescription'] = 'Description de la réaction';
$string['reactionicontype'] = 'Type d’icône';
$string['reactioniconvalue'] = 'Valeur de l’icône';
$string['reactioniconvalue_help'] = 'Pour Emoji, saisissez le caractère emoji. Pour Font Awesome, saisissez la classe CSS, par exemple fa-regular fa-face-smile. Laissez ce champ vide si vous utilisez un fichier d’icône téléversé.';
$string['reactioniconfile'] = 'Fichier d’icône de réaction';
$string['reactioniconfile_help'] = 'Fichier image facultatif utilisé lorsque le type d’icône est « Fichier téléversé ». Les formats acceptés dépendent de la prise en charge des images web par Moodle.';
$string['reactionrequired'] = 'Requis pour l’achèvement';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Classe Font Awesome';
$string['icontype:file'] = 'Fichier téléversé';
$string['addreaction'] = 'Ajouter une réaction';
$string['invalidyoutubeurl'] = 'URL YouTube invalide.';
$string['err:minreactionsrequired'] = 'Définissez un nombre minimal de réactions distinctes ou activez la règle exigeant tous les types de réaction.';
$string['notice:minreactions'] = 'Au moins {$a} réactions distinctes sont requises.';
$string['notice:requiredtypes'] = 'Types de réaction requis : {$a}.';
$string['watch'] = 'Regarder';
$string['report'] = 'Rapports';
$string['reportstudent'] = 'Mes réactions';
$string['reportteacher'] = 'Rapport enseignant';
$string['report:cumulative'] = 'Cumulatif';
$string['report:perstudent'] = 'Par étudiant';
$string['report:userid'] = 'Utilisateur';
$string['report:uniquecoveredseconds'] = 'Secondes uniques couvertes';
$string['report:completionpercent'] = 'Achèvement %';
$string['report:lastposition'] = 'Dernière position';
$string['report:iscompleted'] = 'Achevé';
$string['report:noattempts'] = 'Aucune donnée de visionnage trouvée.';
$string['report:noreactions'] = 'Aucune donnée de réaction trouvée.';
$string['report:timestamp'] = 'Horodatage';
$string['report:reaction'] = 'Réaction';
$string['report:description'] = 'Description';
$string['report:clicks'] = 'Clics';
$string['report:students'] = 'Étudiants';
$string['report:replay'] = 'Revoir l’extrait';
$string['report:delete'] = 'Supprimer';
$string['report:sort'] = 'Trier par';
$string['report:sorttime'] = 'Horodatage';
$string['report:sortreaction'] = 'Réaction';
$string['report:sortclicks'] = 'Clics';
$string['report:aggregation'] = 'Agrégation';
$string['report:aggregationtype'] = 'Même réaction dans la fenêtre';
$string['report:aggregationpeak'] = 'Pic de toute réaction';
$string['report:exportcsv'] = 'Exporter en CSV';
$string['progress'] = 'Progression';
$string['uniquereactions'] = 'Réactions distinctes';
$string['removereaction'] = 'Supprimer la réaction';
$string['playerunavailable'] = 'Le lecteur n’a pas pu être initialisé.';
$string['yes'] = 'Oui';
$string['no'] = 'Non';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heading_performance'] = 'Performance';
$string['setting:heading_defaults'] = 'Valeurs par défaut pour les nouvelles activités';
$string['setting:heading_defaults_desc'] = 'Ces valeurs sont utilisées par défaut lorsqu\'un enseignant crée une nouvelle activité VideoTrack. Chaque activité peut toujours être configurée individuellement.';
$string['setting:default_desc'] = 'Valeur par défaut pour les nouvelles activités. Peut être remplacée par l\'enseignant pour chaque activité individuelle.';
$string['setting:default_completionpercent_desc'] = 'Pourcentage minimum par défaut de la vidéo que l\'étudiant doit regarder pour terminer l\'activité. Définir sur 0 pour laisser la règle d\'achèvement désactivée par défaut.';
$string['event:segment_saved'] = 'Segment de visionnage enregistré';
$string['event:reaction_saved'] = 'Réaction soumise';
$string['event:note_saved'] = 'Note de l\'étudiant enregistrée';
$string['event:reaction_deleted'] = 'Réaction supprimée';
$string['setting:heartbeatinterval'] = 'Intervalle de heartbeat (secondes)';
$string['setting:heartbeatinterval_desc'] = 'À quelle fréquence le lecteur enregistre le segment de visionnage en cours sur le serveur pendant la lecture continue. Des valeurs plus faibles réduisent le risque de perte de données en cas de plantage du navigateur ou de panne réseau, mais augmentent la charge serveur (une requête AJAX + deux requêtes de base de données par étudiant et par intervalle). Plage recommandée : 15 à 120 secondes.';

$string['reactionx'] = 'Réaction {$a}';

$string['err:reactioniconfilerequired'] = 'Téléversez un fichier d’icône lorsque le type d’icône est défini sur Fichier téléversé.';


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
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indique si l\'événement de réaction a été supprimé par l\'utilisateur.';

$string['cap:overrideplayersettings'] = 'Remplacer les paramètres du lecteur de la plateforme';
$string['cap:overrideplayersettings_desc'] = 'Permet à l\'enseignant de modifier les paramètres du lecteur définis par l\'administrateur comme valeurs par défaut de la plateforme.';
$string['cap:overridecompletionsettings'] = 'Remplacer les paramètres d\'achèvement de la plateforme';
$string['cap:overridecompletionsettings_desc'] = 'Permet à l\'enseignant de modifier les paramètres d\'achèvement définis par l\'administrateur comme valeurs par défaut de la plateforme.';
$string['setting:lockedbyAdmin'] = 'Ces paramètres sont verrouillés par l\'administrateur de la plateforme et ne peuvent pas être modifiés pour les activités individuelles.';
$string['setting:heading_presets'] = 'Préréglages de réactions';
$string['setting:heading_presets_desc'] = 'Ensembles de réactions à l\'échelle du site que les enseignants peuvent utiliser comme point de départ.';
$string['reactionpreset'] = 'Appliquer un préréglage de réactions';
$string['reactionpreset_help'] = 'Sélectionnez un préréglage pour préremplir les champs de réaction. Vous pouvez modifier librement les valeurs après.';
$string['reactionpreset:none'] = '— configurer manuellement —';
$string['presets:manage'] = 'Gérer les préréglages de réactions';
$string['presets:pagetitle'] = 'VideoTrack — Préréglages de réactions';
$string['presets:intro'] = 'Définissez des préréglages de réactions à l\'échelle du site comme point de départ pour les enseignants.';
$string['presets:addpreset'] = 'Ajouter un préréglage';
$string['presets:backtolist'] = 'Retour à la liste des préréglages';
$string['presets:saved'] = 'Préréglage enregistré.';
$string['presets:deleted'] = 'Préréglage supprimé.';
$string['presets:notfound'] = 'Préréglage introuvable.';
$string['presets:noneyet'] = 'Aucun préréglage de réaction n\'a encore été configuré.';
$string['presets:confirmdelete'] = 'Êtes-vous sûr de vouloir supprimer ce préréglage ?';
$string['presets:presetdetails'] = 'Détails du préréglage';
$string['presets:name'] = 'Nom du préréglage';
$string['presets:key'] = 'Clé du préréglage';
$string['presets:key_help'] = 'Identifiant unique (lettres, chiffres et tirets bas uniquement). Ne peut pas être modifié après la création.';
$string['presets:reactions'] = 'Réactions';
$string['presets:reactions_help'] = 'Laissez l\'étiquette vide pour ignorer une ligne.';
$string['presets:col_name'] = 'Nom';
$string['presets:col_key'] = 'Clé';
$string['presets:col_reactions'] = 'Réactions';
$string['presets:col_actions'] = 'Actions';
$string['setting:heartbeatinterval_min'] = 'Valeur minimale appliquée : 5 secondes.';

$string['reset:userdata'] = 'Supprimer toutes les données de visionnage des étudiants (segments, états, réactions)';
$string['report:recalculate'] = 'Recalculer tous les états d\'achèvement';
$string['report:recalculated'] = 'États d\'achèvement recalculés pour {$a} utilisateurs.';
$string['report:heatmap_desc'] = 'Carte de chaleur des réactions sur la timeline vidéo (hauteur de barre = nombre de clics à ce point) :';
$string['report:heatmap_supplementary'] = 'La carte thermique est une visualisation complémentaire. Les données complètes des clusters sont disponibles dans le tableau ci-dessous.';
$string['event:activity_completed'] = 'Activité VideoTrack terminée';

$string['reactioniconfile_notice'] = 'L\'image sera automatiquement redimensionnée à 64×64 pixels (recadrage centré). Pour de meilleurs résultats, téléversez une image carrée (ratio 1:1). Formats acceptés : JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Cliquez sur un bouton de réaction pendant la lecture de la vidéo pour enregistrer votre réaction à cet instant.';

$string['showgradeto'] = 'Afficher la note à l\'étudiant';
$string['showgradeto_help'] = 'Si activé, l\'étudiant verra sa note directement sur la page de l\'activité.';
$string['report:grade'] = 'Note';
$string['report:gradesaved'] = 'Note enregistrée avec succès.';
$string['report:gradepass_hint'] = 'Note de passage : {$a}';
$string['report:gradenotset'] = 'Pas encore noté';

$string['videosource'] = 'Source vidéo';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Téléverser (MP4/WebM/MP3)';
$string['vimeourl'] = 'URL Vimeo';
$string['vimeourl_help'] = 'Collez l\'URL de la vidéo Vimeo.';
$string['invalidvimeourl'] = 'L\'URL ne semble pas être une URL Vimeo valide.';
$string['videofile'] = 'Fichier vidéo/audio';
$string['videofile_help'] = 'Téléversez un fichier MP4, WebM ou MP3.';
$string['videofile_notice'] = 'Formats acceptés : MP4, WebM, MP3, M4V, MOV, AAC, M4A.';
$string['setting:heading_player'] = 'Comportement du lecteur';
$string['setting:playbackspeeds'] = 'Vitesses de lecture disponibles';
$string['setting:playbackspeeds_desc'] = 'Sélectionnez les vitesses disponibles sur toute la plateforme.';
$string['setting:playbackspeeds_teacher_desc'] = 'Sélectionnez les vitesses de lecture pour cette activité.';
$string['setting:speed_normal'] = 'normale';
$string['setting:distractionfree'] = 'Mode sans distraction';
$string['setting:distractionfree_desc'] = 'Cache l\'en-tête, le pied de page et la navigation lors du visionnage.';
$string['intervalbar_title'] = 'Intervalles vus — segments verts = parties déjà visionnées.';
$string['outline:percent'] = '{$a}% visionné';
$string['outline:nodata'] = 'Aucune donnée de visionnage.';
$string['coursereport:title'] = 'VideoTrack — Rapport de cours';
$string['coursereport:navlink'] = 'Rapports VideoTrack';
$string['coursereport:intro'] = 'Vue d\'ensemble de toutes les activités VideoTrack du cours.';
$string['coursereport:nodata'] = 'Aucune activité VideoTrack trouvée.';
$string['coursereport:col_activity'] = 'Activité';
$string['coursereport:col_source'] = 'Source';
$string['coursereport:col_duration'] = 'Durée';
$string['coursereport:col_students_started'] = 'Étudiants commencés';
$string['coursereport:col_avg_percent'] = 'Couverture moy.';
$string['coursereport:col_completions'] = 'Achèvements';
$string['coursereport:col_reactions'] = 'Réactions';
$string['coursereport:col_actions'] = 'Actions';

$string['grade:pass'] = 'Réussi';
$string['grade:fail'] = 'Échoué';

$string['autoplay'] = 'Lecture automatique';
$string['autoplay_help'] = 'Démarre la vidéo automatiquement. Les navigateurs nécessitent la mise en sourdine pour la lecture automatique.';
$string['loop'] = 'Lecture en boucle';
$string['startmuted'] = 'Démarrer en sourdine';
$string['startmuted_help'] = 'Démarre la lecture avec le son coupé.';
$string['allowdownload'] = 'Autoriser le téléchargement (source upload uniquement)';
$string['setting:allowdownload_desc'] = 'Affiche un bouton de téléchargement dans le lecteur HTML5.';
$string['setting:heading_playerbehavior'] = 'Comportement par défaut du lecteur';
$string['setting:heading_playerbehavior_desc'] = 'Valeurs par défaut pour la lecture automatique, la boucle, le son coupé et le téléchargement.';
$string['setting:heading_html5controls'] = 'Contrôles du lecteur HTML5 (source upload)';
$string['setting:heading_html5controls_desc'] = 'Sélectionnez les contrôles disponibles dans la barre du lecteur HTML5.';
$string['setting:html5controls'] = 'Contrôles disponibles';
$string['setting:html5controls_desc'] = 'Sélectionnez les contrôles à afficher dans le lecteur HTML5.';
$string['setting:html5controls_teacher_desc'] = 'Sélectionnez les contrôles pour cette activité.';
$string['ctrl:play'] = 'Lecture/Pause';
$string['ctrl:progress'] = 'Barre de progression';
$string['ctrl:current'] = 'Temps actuel';
$string['ctrl:duration'] = 'Durée';
$string['ctrl:mute'] = 'Sourdine';
$string['ctrl:volume'] = 'Volume';
$string['ctrl:speed'] = 'Vitesse';
$string['ctrl:pip'] = 'Image dans l\'image';
$string['ctrl:fullscreen'] = 'Plein écran';
$string['ctrl:download'] = 'Télécharger';

$string['setting:playerwidth'] = 'Largeur maximale du lecteur (px)';
$string['setting:playerwidth_desc'] = 'Largeur maximale du lecteur vidéo en pixels.';
$string['playerwidth'] = 'Largeur maximale du lecteur (px)';
$string['playerwidth_help'] = 'Largeur maximale du lecteur pour cette activité. 0 = valeur par défaut.';
$string['setting:rewindstep'] = 'Pas de rembobinage (secondes)';
$string['setting:rewindstep_desc'] = 'Secondes reculées par le bouton de rembobinage. 0 = désactivé. Par défaut : 10. Important : si "Autoriser le saut en arrière" est désactivé pour une activité, le bouton n\'apparaîtra pas même si cette valeur est > 0.';
$string['rewindstep'] = 'Pas de rembobinage (secondes)';
$string['rewindstep_help'] = 'Secondes de rembobinage pour cette activité. 0 = valeur par défaut. Note : si "Autoriser le saut en arrière" est désactivé, le bouton n\'apparaîtra pas quelle que soit cette valeur.';
$string['setting:fastforwardstep'] = 'Pas d\'avance rapide (secondes)';
$string['setting:fastforwardstep_desc'] = 'Secondes avancées par le bouton d\'avance rapide. 0 = désactivé. Par défaut : 10. Important : si "Autoriser le saut en avant" est désactivé pour une activité, le bouton n\'apparaîtra pas même si cette valeur est > 0.';
$string['fastforwardstep'] = 'Pas d\'avance rapide (secondes)';
$string['fastforwardstep_help'] = 'Secondes d\'avance pour cette activité. 0 = valeur par défaut. Note : si "Autoriser le saut en avant" est désactivé, le bouton n\'apparaîtra pas quelle que soit cette valeur.';
$string['captionsheader'] = 'Sous-titres';
$string['captions'] = 'Activer les sous-titres';
$string['captions_help'] = 'Active les sous-titres pour YouTube, Vimeo ou Upload (VTT).';
$string['setting:default_captions_desc'] = 'Activer les sous-titres par défaut pour les nouvelles activités.';
$string['captionslang'] = 'Langue de sous-titres par défaut';
$string['captionslang_help'] = 'Code de langue ISO 639-1 (ex. fr, en).';
$string['setting:captionslang_desc'] = 'Langue de sous-titres par défaut (ISO 639-1).';
$string['vttfile'] = 'Fichier de sous-titres (.vtt)';
$string['vttfile_help'] = 'Téléverser un fichier WebVTT.';
$string['vttfile_notice'] = 'Format accepté : WebVTT (.vtt).';
$string['vimeo_captions_notice'] = 'Les sous-titres Vimeo sont gérés sur Vimeo.com.';
$string['ctrl:rewind'] = 'Bouton de rembobinage';
$string['ctrl:fastforward'] = 'Bouton d\'avance rapide';

$string['playerloading'] = 'Chargement du lecteur vidéo, veuillez patienter…';
$string['noreactionsyet'] = 'Aucune réaction enregistrée. Réagissez pendant la lecture de la vidéo.';
$string['reaction:error'] = 'Impossible de sauvegarder votre réaction. Veuillez réessayer.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Reprendre la lecture';
$string['resumeplayback_desc'] = 'Reprend automatiquement la vidéo à l\'endroit où l\'étudiant s\'est arrêté lors de sa dernière session.';
$string['resumeplayback_help'] = 'Si cette option est activée, la vidéo démarre à la dernière position enregistrée (si elle est située à plus de 5 secondes du début). L\'étudiant peut toujours revenir manuellement au début.';
$string['setting:resumeplayback'] = 'Reprendre la lecture (par défaut)';
$string['setting:resumeplayback_desc'] = 'Paramètre par défaut pour les nouvelles activités VideoTrack. Les enseignants peuvent le modifier par activité.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Vitesse maximale de lecture';
$string['maxplaybackrate_desc'] = 'Limite la vitesse maximale que les étudiants peuvent sélectionner. 0 = aucune limite.';
$string['maxplaybackrate_help'] = 'Lorsque ce paramètre est défini, les étudiants ne peuvent pas lire la vidéo plus rapidement que cette vitesse, même si les contrôles du lecteur proposent des valeurs plus élevées.';
$string['maxplaybackrate_nolimit'] = 'Aucune limite';
$string['setting:maxplaybackrate'] = 'Vitesse maximale de lecture (par défaut)';
$string['setting:maxplaybackrate_desc'] = 'Vitesse maximale par défaut pour les nouvelles activités. Les enseignants peuvent la modifier par activité.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Afficher la transcription interactive';
$string['showtranscript_desc'] = 'Affiche un panneau de transcription défilant et cliquable à côté de la vidéo (nécessite un fichier de sous-titres VTT).';
$string['showtranscript_help'] = 'Analyse le fichier VTT téléversé et l\'affiche sous forme de liste cliquable. Chaque entrée affiche l\'horodatage et le texte ; un clic déplace la vidéo à ce point. Le segment actif est mis en évidence et affiché automatiquement.';
$string['transcript_title'] = 'Transcription';
$string['transcript_loading'] = 'Chargement de la transcription…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Cliquez sur la vidéo pour démarrer la lecture.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['vimeocspwarnlabel'] = 'Le lecteur Vimeo n\'a pas pu être chargé. Vérifiez la connexion réseau ou demandez à l\'administrateur d\'autoriser player.vimeo.com dans la Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'Reprise à partir de';
// ── Report: azioni studente ──
$string['report:actions'] = 'Actions';
$string['report:resetstudent'] = 'Réinitialiser la progression';
$string['report:resetstudent_confirm'] = 'Voulez-vous vraiment réinitialiser la progression de cet étudiant ? Tout son historique de visionnage et ses réactions seront supprimés. Cette opération est irréversible.';
$string['report:studentreset'] = 'La progression de l\'étudiant a été réinitialisée.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Afficher la navigation par chapitres';
$string['showchapters_desc'] = 'Affiche une barre de navigation avec les marqueurs de chapitres extraits du fichier VTT. Les chapitres sont des cues VTT dont le texte fait moins de 80 caractères.';
$string['showchapters_help'] = 'Si le fichier VTT téléversé contient des cues courts (moins de 80 caractères), ils sont interprétés comme des titres de chapitres et affichés sous forme de barre de navigation cliquable au-dessus des contrôles vidéo.';
$string['chapters_label'] = 'Chapitres vidéo';
$string['chapterslabel'] = 'Chapitres vidéo';
$string['chapter_label'] = 'Chapitre';
$string['chapterlabel'] = 'Chapitre';
$string['studentnotesenabled'] = 'Activer les notes des étudiants';
$string['studentnotesenabled_desc'] = 'Permet aux étudiants d\'écrire des notes personnelles horodatées pendant le visionnage.';
$string['studentnotesenabled_help'] = 'Si cette option est activée, une zone de texte apparaît à côté de la vidéo. Les étudiants peuvent saisir une note et l\'enregistrer à l\'horodatage actuel. Les notes sont visibles uniquement par l\'étudiant qui les a écrites et par les gestionnaires via le rapport.';
$string['setting:studentnotesenabled'] = 'Activer les notes des étudiants (par défaut)';
$string['setting:studentnotesenabled_desc'] = 'Paramètre par défaut pour les nouvelles activités VideoTrack. Les enseignants peuvent le modifier par activité.';
$string['studentnotes_title'] = 'Mes notes';
$string['studentnote_placeholder'] = 'Écrire une note à ce moment de la vidéo…';
$string['studentnote_save'] = 'Enregistrer la note';
$string['studentnote_hint'] = 'La note sera enregistrée à l\'horodatage actuel de la vidéo. La vidéo doit être en cours de lecture.';
$string['studentnotes_list_label'] = 'Notes enregistrées';
$string['studentnote_label'] = 'Note de l\'étudiant';
$string['noteerrorlabel'] = 'Impossible d\'enregistrer la note. Veuillez réessayer.';
$string['charsremaininglabel'] = 'caractères restants';
$string['posterimage'] = 'Image d\'aperçu / poster';
$string['posterimage_help'] = 'Téléversez une image à afficher avant le démarrage de la vidéo. Elle reste visible jusqu\'à ce que l\'étudiant clique sur lecture. Formats acceptés : JPG, PNG, WebP, GIF. Taille recommandée : 1280×720 px (16:9).';
$string['posterimage_notice'] = 'L\'image d\'aperçu est affichée avant la lecture et masquée automatiquement lorsque la vidéo démarre.';
$string['playbutton_label'] = 'Lire la vidéo';
$string['setting:maxplaybackrate_nolimit'] = 'Aucune limite';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Texte d\'une note personnelle écrite par l\'étudiant à un horodatage vidéo précis.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Type d\'événement : vide pour les réactions standard, "note" pour les notes personnelles des étudiants.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['studentnotesdisabled'] = 'Les notes des étudiants ne sont pas activées pour cette activité.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'Aucun fichier vidéo n\'a été téléversé pour cette activité.';
$string['removenote'] = 'Supprimer la note';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Masquer les notes';
$string['notes_show'] = 'Afficher les notes';
$string['report:notes_title'] = 'Notes des étudiants';
$string['report:nonotes'] = 'Aucune note n\'a été écrite pour cette activité.';
$string['report:notedate'] = 'Écrite le';
$string['report:exportnotes_csv'] = 'Exporter les notes en CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Fermer';
$string['rewindlabel'] = 'Retour rapide';
$string['fastforwardlabel'] = 'Avance rapide';
$string['secondslabel'] = 'secondes';
$string['removenotelabel'] = 'Supprimer la note';
// ── Help strings ──
$string['gradepass_help'] = 'Note minimale requise pour réussir cette activité. Les étudiants qui atteignent cette note ou une note supérieure sont considérés comme ayant réussi.';


$string['completiondetail:requiredreactions'] = 'Doit inclure ces réactions obligatoires : {$a}';

$string['error:playbackrequired'] = 'La vidéo doit être en cours de lecture avant que cette action puisse être enregistrée.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'Extension PHP GD non disponible.';
$string['setting:gd_missing_desc'] = 'Les images téléversées comme icônes de réaction ne seront PAS redimensionnées automatiquement à 64×64 pixels. Le fichier original sera servi tel quel, ce qui peut affecter les performances pour les grandes images. Pour activer le redimensionnement automatique, demandez à l\'administrateur du serveur d\'installer le paquet php-gd.';

$string['report:heatmap_legend'] = 'Légende des couleurs de la heatmap des réactions';

$string['report:clusterlimitreached'] = 'Le rapport a atteint le nombre maximal de clusters affichés. Utilisez les filtres ou une fenêtre temporelle plus restreinte pour une analyse complète.';

$string['report:showingrecentreactions'] = 'Seules les {$a} premières réactions sont affichées.';

$string['report:viewfullreport'] = 'Afficher le rapport complet';
$string['studentnotes_view_limited'] = 'Les {$a} notes les plus récentes sont affichées. Ouvrez le rapport complet pour consulter toutes les notes.';
$string['report:skiptoheatmaptable'] = 'Ignorer la carte thermique et aller au tableau des données';
$string['report:heatmap_textsummary'] = 'Le graphique contient {$a->clusters} groupes ; le groupe le plus important contient {$a->max} clics.';
$string['err:reactioniconvaluerequired'] = 'Saisir un emoji ou une classe Font Awesome.';

$string['error:reactionratelimit'] = 'Trop de réactions ont été envoyées en peu de temps. Continuez à regarder la vidéo puis réessayez.';
$string['event:student_progress_reset'] = 'Données VideoTrack de l’étudiant réinitialisées';
$string['report:timefrom'] = 'Depuis la seconde';
$string['report:timeto'] = 'Jusqu’à la seconde';
$string['report:clusterlimitreached_help'] = 'Le rapport cumulatif a atteint la limite de clusters affichables. Utilisez les filtres par utilisateur, réaction ou temps vidéo pour affiner l’analyse et accéder aux clusters suivants.';
$string['report:topclusterssummary'] = 'Clusters les plus significatifs dans cette sélection :';
$string['report:topclusteritem'] = '{$a->time} : {$a->reaction}, {$a->clicks} clics';
$string['error:notesratelimit'] = 'Trop de notes ont été envoyées en peu de temps. Veuillez attendre avant d’ajouter une autre note.';

$string['privacy:segmentschunk'] = 'Segments de visionnage - partie {$a}';

$string['privacy:reactionsactivechunk'] = 'Réactions actives - partie {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Réactions supprimées - partie {$a}';

$string['privacy:notesactivechunk'] = 'Notes actives - partie {$a}';

$string['privacy:notesdeletedchunk'] = 'Notes supprimées - partie {$a}';

$string['report:clusterlimitreached_csv'] = 'AVERTISSEMENT : la limite des clusters a été atteinte. L’export peut être incomplet ; appliquez des filtres utilisateur, réaction ou temps puis exportez à nouveau.';

$string['report:notecreatedfrom'] = 'Notes à partir de la date';

$string['report:notecreatedto'] = 'Notes jusqu’à la date';

$string['reactionsavailableonlyduringplayback'] = 'Les réactions sont disponibles uniquement pendant la lecture de la vidéo.';
$string['reactionsreadyannounce'] = 'Les réactions sont maintenant disponibles.';

$string['privacy:state'] = 'État d’achèvement';

$string['report:clusterlimitrequiresfilters'] = 'Le rapport cumulatif est partiel. Appliquez un filtre de plage temporelle vidéo pour récupérer les clusters restants de manière fiable.';

$string['report:clusterlimitrequiresfilters_csv'] = 'L’export cumulatif est partiel car aucun filtre de plage temporelle vidéo n’a été appliqué. Appliquez les filtres Début/Fin en secondes puis relancez l’export.';
$string['report:clusterexportblocked_csv'] = 'L’export a été arrêté afin d’éviter des données incomplètes. Appliquez un filtre de temps vidéo puis relancez l’export.';
$string['report:clusterdisplayblocked'] = 'Le tableau des regroupements a été masqué afin d’éviter des données incomplètes. Appliquez un filtre de temps vidéo pour continuer.';
$string['unknownreaction'] = 'Réaction inconnue';

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

$string['setting:nonnegativeintrequired'] = 'Saisissez un nombre entier supérieur ou égal à 0.';
$string['report:anonymiseduser'] = 'Utilisateur anonymisé';
$string['report:exportnotes_privacywarning'] = 'Cet export peut contenir des données personnelles issues des notes des étudiants. Téléchargez-le et conservez-le uniquement pour un motif valable, puis supprimez-le lorsqu’il n’est plus nécessaire.';
