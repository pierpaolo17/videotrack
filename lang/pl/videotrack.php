<?php

$string['pluginname'] = 'VideoTrack';
$string['modulename'] = 'VideoTrack';
$string['modulenameplural'] = 'VideoTracki';
$string['pluginadministration'] = 'Administracja VideoTrack';
$string['videotrack:addinstance'] = 'Dodaj nową aktywność VideoTrack';
$string['videotrack:viewcoursereport'] = 'Wyświetlanie raportu VideoTrack na poziomie kursu';
$string['videotrack:viewcoursereport_desc'] = 'Pozwala użytkownikowi wyświetlać zagregowany raport VideoTrack dla całego kursu.';
$string['videotrack:view'] = 'Wyświetl VideoTrack';
$string['videotrack:viewreport'] = 'Wyświetl raporty VideoTrack';
$string['videotrack:viewownreport'] = 'Wyświetl własny raport VideoTrack';
$string['videoname'] = 'Nazwa aktywności';
$string['youtubeurl'] = 'Adres URL YouTube';
$string['youtubeurl_help'] = 'Wklej standardowy adres oglądania YouTube, krótki adres URL albo adres osadzenia.';
$string['showcontrols'] = 'Pokaż elementy sterujące odtwarzacza';
$string['disablekeyboard'] = 'Wyłącz skróty klawiaturowe';
$string['showfullscreen'] = 'Pokaż przycisk pełnego ekranu';
$string['allowseekforward'] = 'Zezwalaj na przewijanie do przodu';
$string['allowseekbackward'] = 'Zezwalaj na przewijanie do tyłu';
$string['allowplaybackratechange'] = 'Zezwalaj na zmianę szybkości odtwarzania';
$string['countbyvideotime'] = 'Licz pokrycie według osi czasu wideo';
$string['countbyvideotime_help'] = 'Zalecane. Ukończenie jest obliczane na podstawie unikalnych obejrzanych sekund osi czasu wideo, a nie ponownego oglądania tych samych fragmentów.';
$string['completionpercent'] = 'Wymagany procent ukończenia';
$string['completiondetail:percent'] = 'Wymagane obejrzenie co najmniej {$a}% wideo';
$string['completiondetail:minreactions'] = 'Wymagane co najmniej {$a} różne reakcje';
$string['completiondetail:allreactiontypes'] = 'Wymagana co najmniej jedna reakcja dla każdego skonfigurowanego typu reakcji';
$string['reactionsheader'] = 'Reakcje';
$string['reactionsenabled'] = 'Włącz reakcje';
$string['reactionsrequired'] = 'Wymagaj reakcji';
$string['minreactions'] = 'Minimalna liczba różnych reakcji';
$string['requireallreactiontypes'] = 'Wymagaj co najmniej jednej reakcji dla każdego skonfigurowanego typu';
$string['completionlogic'] = 'Logika ukończenia';
$string['logicand'] = 'Wszystkie włączone warunki (AND)';
$string['logicor'] = 'Dowolny włączony warunek (OR)';
$string['clusterwindow'] = 'Okno klastrowania (sekundy)';
$string['showstudentreport'] = 'Pokaż raport studentom';
$string['showreactionnotice'] = 'Pokaż informację o reakcjach';
$string['reactionnotice'] = 'Informacja o reakcjach';
$string['reactionlabel'] = 'Etykieta reakcji';
$string['reactiondescription'] = 'Opis reakcji';
$string['reactionicontype'] = 'Typ ikony';
$string['reactioniconvalue'] = 'Wartość ikony';
$string['reactioniconvalue_help'] = 'Dla emoji wpisz znak emoji. Dla Font Awesome wpisz klasę CSS, na przykład fa-regular fa-face-smile. Pozostaw puste, gdy używasz przesłanego pliku ikony.';
$string['reactioniconfile'] = 'Plik ikony reakcji';
$string['reactioniconfile_help'] = 'Opcjonalny plik obrazu używany, gdy typ ikony to „Przesłany plik”. Obsługiwane formaty zależą od obsługi obrazów WWW w Moodle.';
$string['reactionrequired'] = 'Wymagana do ukończenia';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Klasa Font Awesome';
$string['icontype:file'] = 'Przesłany plik';
$string['addreaction'] = 'Dodaj reakcję';
$string['invalidyoutubeurl'] = 'Nieprawidłowy adres URL YouTube.';
$string['err:minreactionsrequired'] = 'Ustaw minimalną liczbę różnych reakcji albo włącz regułę wymagającą wszystkich typów reakcji.';
$string['notice:minreactions'] = 'Wymagane są co najmniej {$a} różne reakcje.';
$string['notice:requiredtypes'] = 'Wymagane typy reakcji: {$a}.';
$string['watch'] = 'Oglądaj';
$string['report'] = 'Raporty';
$string['reportstudent'] = 'Moje reakcje';
$string['reportteacher'] = 'Raport nauczyciela';
$string['report:cumulative'] = 'Skumulowany';
$string['report:perstudent'] = 'Według studenta';
$string['report:userid'] = 'Użytkownik';
$string['report:uniquecoveredseconds'] = 'Unikalne obejrzane sekundy';
$string['report:completionpercent'] = '% ukończenia';
$string['report:lastposition'] = 'Ostatnia pozycja';
$string['report:iscompleted'] = 'Ukończono';
$string['report:noattempts'] = 'Nie znaleziono danych oglądania.';
$string['report:noreactions'] = 'Nie znaleziono danych reakcji.';
$string['report:timestamp'] = 'Znacznik czasu';
$string['report:reaction'] = 'Reakcja';
$string['report:description'] = 'Opis';
$string['report:clicks'] = 'Kliknięcia';
$string['report:students'] = 'Studenci';
$string['report:replay'] = 'Odtwórz fragment';
$string['report:delete'] = 'Usuń';
$string['report:sort'] = 'Sortuj według';
$string['report:sorttime'] = 'Znacznik czasu';
$string['report:sortreaction'] = 'Reakcja';
$string['report:sortclicks'] = 'Kliknięcia';
$string['report:aggregation'] = 'Agregacja';
$string['report:aggregationtype'] = 'Ta sama reakcja w oknie czasu';
$string['report:aggregationpeak'] = 'Szczyt dowolnej reakcji';
$string['report:exportcsv'] = 'Eksportuj CSV';
$string['progress'] = 'Postęp';
$string['uniquereactions'] = 'Różne reakcje';
$string['removereaction'] = 'Usuń reakcję';
$string['playerunavailable'] = 'Nie można zainicjować odtwarzacza.';
$string['yes'] = 'Tak';
$string['no'] = 'Nie';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heartbeatinterval'] = 'Interwał heartbeat (sekundy)';
$string['setting:heartbeatinterval_desc'] = 'Jak często odtwarzacz zapisuje bieżący segment oglądania na serwerze podczas ciągłego odtwarzania. Niższe wartości zmniejszają ryzyko utraty danych przy awarii przeglądarki lub sieci, ale zwiększają obciążenie serwera. Zalecany zakres: 15–120 sekund. Minimalna wymuszona wartość: 5 sekund.';
$string['setting:heading_performance'] = 'Wydajność';
$string['setting:heading_defaults'] = 'Domyślne wartości dla nowych aktywności';
$string['setting:heading_defaults_desc'] = 'Te wartości są używane jako domyślne, gdy nauczyciel tworzy nową aktywność VideoTrack. Każdą aktywność można nadal skonfigurować indywidualnie.';
$string['setting:default_desc'] = 'Domyślna wartość dla nowych aktywności. Nauczyciel może ją nadpisać w każdej aktywności.';
$string['setting:default_completionpercent_desc'] = 'Domyślny minimalny procent wideo, który student musi obejrzeć, aby ukończyć aktywność. Ustaw 0, aby domyślnie wyłączyć tę regułę ukończenia.';
$string['event:segment_saved'] = 'Zapisano segment oglądania';
$string['event:reaction_saved'] = 'Przesłano reakcję';
$string['event:note_saved'] = 'Zapisano notatkę studenta';
$string['event:reaction_deleted'] = 'Usunięto reakcję';

$string['reactionx'] = 'Reakcja {$a}';

$string['err:reactioniconfilerequired'] = 'Prześlij plik ikony, gdy typ ikony jest ustawiony na Przesłany plik.';


$string['privacy:metadata:common:timecreated'] = 'Czas utworzenia rekordu.';
$string['privacy:metadata:common:timemodified'] = 'Czas ostatniej modyfikacji rekordu.';
$string['privacy:metadata:videotrack_seg'] = 'Przechowuje segmenty oglądania zapisane dla użytkownika w aktywności wideo.';
$string['privacy:metadata:videotrack_seg:userid'] = 'Użytkownik, którego segment oglądania został zapisany.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Identyfikator sesji przeglądarki powiązany z segmentem oglądania.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Czas serwera rozpoczęcia segmentu.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Czas serwera zakończenia segmentu.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Pozycja na osi czasu wideo na początku segmentu.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Pozycja na osi czasu wideo na końcu segmentu.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Szybkość odtwarzania użyta podczas segmentu.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Powód zakończenia segmentu.';
$string['privacy:metadata:videotrack_state'] = 'Przechowuje zagregowany stan oglądania użytkownika w aktywności wideo.';
$string['privacy:metadata:videotrack_state:userid'] = 'Użytkownik, którego stan agregowany został zapisany.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Ostatnia znana pozycja użytkownika na osi czasu wideo.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Czas trwania śledzonego wideo w sekundach.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Liczba unikalnych sekund osi czasu obejrzanych przez użytkownika.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Procent ukończenia obliczony dla użytkownika.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Scalone interwały używane do obliczenia unikalnego pokrycia.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Czy aktywność jest obecnie oznaczona jako ukończona dla użytkownika.';
$string['privacy:metadata:videotrack_reactev'] = 'Przechowuje zdarzenia reakcji zapisane podczas oglądania wideo przez użytkownika.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'Użytkownik, który przesłał reakcję.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Identyfikator sesji przeglądarki powiązany ze zdarzeniem reakcji.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Wewnętrzny klucz reakcji w chwili jej zapisania.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Etykieta reakcji pokazana użytkownikowi w chwili zapisania zdarzenia.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Opis reakcji pokazany użytkownikowi w chwili zapisania zdarzenia.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Pozycja na osi czasu wideo, gdy reakcja została zapisana.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Szybkość odtwarzania w chwili zapisania reakcji.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Czy zdarzenie reakcji zostało usunięte przez użytkownika.';

$string['videotrack:overrideplayersettings'] = 'Nadpisywanie ustawień odtwarzacza platformy';
$string['videotrack:overrideplayersettings_desc'] = 'Pozwala nauczycielowi zmieniać ustawienia odtwarzacza (przewijanie, szybkość, kontrolki, klawiatura, pełny ekran), które administrator ustawił jako domyślne dla całej platformy. Cofnij to uprawnienie, aby wymusić jednolitą politykę odtwarzacza w całym serwisie.';
$string['videotrack:overridecompletionsettings'] = 'Nadpisywanie ustawień ukończenia platformy';
$string['videotrack:overridecompletionsettings_desc'] = 'Pozwala nauczycielowi zmieniać ustawienia ukończenia (wymagany procent, okno klastra), które administrator ustawił jako domyślne dla platformy. Cofnij to uprawnienie, aby wymusić jednolite progi ukończenia w całym serwisie.';
$string['setting:lockedbyAdmin'] = 'To ustawienie jest zablokowane przez administratora witryny.';
$string['setting:heading_presets'] = 'Presety reakcji';
$string['setting:heading_presets_desc'] = 'Zarządzaj zestawami reakcji, które nauczyciele mogą ponownie wykorzystywać w aktywnościach VideoTrack.';
$string['reactionpreset'] = 'Preset reakcji';
$string['reactionpreset_help'] = 'Wybierz zapisany preset, aby wstępnie wypełnić listę reakcji.';
$string['reactionpreset:none'] = 'Brak presetu';
$string['presets:manage'] = 'Zarządzaj presetami reakcji';
$string['presets:pagetitle'] = 'Presety reakcji VideoTrack';
$string['presets:intro'] = 'Twórz i edytuj zestawy reakcji wielokrotnego użytku.';
$string['presets:addpreset'] = 'Dodaj preset';
$string['presets:backtolist'] = 'Powrót do listy presetów';
$string['presets:saved'] = 'Preset zapisany.';
$string['presets:deleted'] = 'Preset usunięty.';
$string['presets:notfound'] = 'Nie znaleziono presetu.';
$string['presets:noneyet'] = 'Nie utworzono jeszcze presetów.';
$string['presets:confirmdelete'] = 'Czy na pewno chcesz usunąć ten preset?';
$string['presets:presetdetails'] = 'Szczegóły presetu';
$string['presets:name'] = 'Nazwa';
$string['presets:key'] = 'Klucz';
$string['presets:key_help'] = 'Unikalny klucz techniczny presetu. Używaj małych liter, cyfr i podkreśleń.';
$string['presets:reactions'] = 'Reakcje';
$string['presets:reactions_help'] = 'Reakcje zawarte w tym presecie.';
$string['presets:col_name'] = 'Nazwa';
$string['presets:col_key'] = 'Klucz';
$string['presets:col_reactions'] = 'Reakcje';
$string['presets:col_actions'] = 'Akcje';
$string['setting:heartbeatinterval_min'] = 'Minimalna wymuszana wartość: 5 sekund.';

$string['reset:userdata'] = 'Delete all student viewing data (segments, states, reactions)';
$string['report:recalculate'] = 'Przelicz raport';
$string['report:recalculated'] = 'Stany ukończenia przeliczono dla {$a} użytkowników.';
$string['report:heatmap_desc'] = 'Mapa cieplna pokazuje miejsca wideo, w których studenci najczęściej reagowali.';
$string['report:heatmap_supplementary'] = 'Dane mapy cieplnej są dostępne także w tabeli poniżej.';
$string['event:activity_completed'] = 'Aktywność VideoTrack ukończona';

$string['reactioniconfile_notice'] = 'Przesłane ikony są przechowywane przez Moodle w obszarze plików aktywności.';
$string['reactions_hint'] = 'Reakcje są dostępne podczas odtwarzania wideo.';

$string['showgradeto'] = 'Pokaż ocenę';
$string['showgradeto_help'] = 'Określa, komu wyświetlać ocenę aktywności.';
$string['report:grade'] = 'Ocena';
$string['report:gradesaved'] = 'Ocena zapisana.';
$string['report:gradepass_hint'] = 'Próg zaliczenia: {$a}';
$string['report:gradenotset'] = 'Nie ustawiono oceny.';

$string['videosource'] = 'Źródło wideo';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Przesłany plik';
$string['vimeourl'] = 'Adres URL Vimeo';
$string['vimeourl_help'] = 'Wklej adres URL publicznego lub niepublicznego filmu Vimeo.';
$string['invalidvimeourl'] = 'Nieprawidłowy adres URL Vimeo.';
$string['videofile'] = 'Plik wideo';
$string['videofile_help'] = 'Prześlij plik wideo lub audio obsługiwany przez przeglądarkę.';
$string['videofile_notice'] = 'Przesłane pliki są odtwarzane za pomocą natywnego odtwarzacza HTML5.';
$string['setting:heading_player'] = 'Odtwarzacz';
$string['setting:playbackspeeds'] = 'Dostępne szybkości odtwarzania';
$string['setting:playbackspeeds_desc'] = 'Lista dozwolonych szybkości oddzielonych przecinkami, np. 0.75,1,1.25,1.5.';
$string['setting:playbackspeeds_teacher_desc'] = 'Szybkości odtwarzania dostępne dla studentów.';
$string['setting:speed_normal'] = 'Normalna';
$string['setting:distractionfree'] = 'Tryb bez rozpraszania';
$string['setting:distractionfree_desc'] = 'Ukrywa elementy interfejsu, które mogą rozpraszać podczas oglądania.';
$string['intervalbar_title'] = 'Obejrzane fragmenty';
$string['outline:percent'] = '{$a}% ukończenia';
$string['outline:nodata'] = 'Brak danych';
$string['coursereport:title'] = 'Raport kursu VideoTrack';
$string['coursereport:navlink'] = 'Raport VideoTrack';
$string['coursereport:intro'] = 'Podsumowanie aktywności VideoTrack w tym kursie.';
$string['coursereport:nodata'] = 'Brak danych VideoTrack dla tego kursu.';
$string['coursereport:col_activity'] = 'Aktywność';
$string['coursereport:col_source'] = 'Źródło';
$string['coursereport:col_duration'] = 'Czas trwania';
$string['coursereport:col_students_started'] = 'Studenci, którzy rozpoczęli';
$string['coursereport:col_avg_percent'] = 'Średni %';
$string['coursereport:col_completions'] = 'Ukończenia';
$string['coursereport:col_reactions'] = 'Reakcje';
$string['coursereport:col_actions'] = 'Akcje';

$string['grade:pass'] = 'Zaliczone';
$string['grade:fail'] = 'Niezaliczone';

$string['autoplay'] = 'Autoodtwarzanie';
$string['autoplay_help'] = 'Próbuje automatycznie rozpocząć odtwarzanie, jeśli przeglądarka na to pozwala.';
$string['loop'] = 'Zapętlaj';
$string['startmuted'] = 'Start z wyciszeniem';
$string['startmuted_help'] = 'Rozpoczyna odtwarzanie z wyciszonym dźwiękiem.';
$string['allowdownload'] = 'Zezwalaj na pobieranie';
$string['setting:allowdownload_desc'] = 'Pozwala studentom pobierać przesłane pliki, jeśli odtwarzacz i przeglądarka to obsługują.';
$string['setting:heading_playerbehavior'] = 'Zachowanie odtwarzacza';
$string['setting:heading_playerbehavior_desc'] = 'Ustawienia sterujące sposobem interakcji studentów z odtwarzaczem.';
$string['setting:heading_html5controls'] = 'Kontrolki HTML5';
$string['setting:heading_html5controls_desc'] = 'Wybierz kontrolki widoczne w niestandardowym odtwarzaczu HTML5.';
$string['setting:html5controls'] = 'Kontrolki HTML5';
$string['setting:html5controls_desc'] = 'Kontrolki dostępne w odtwarzaczu HTML5.';
$string['setting:html5controls_teacher_desc'] = 'Kontrolki HTML5 widoczne dla studentów.';
$string['ctrl:play'] = 'Odtwarzaj/Pauza';
$string['ctrl:progress'] = 'Pasek postępu';
$string['ctrl:current'] = 'Bieżący czas';
$string['ctrl:duration'] = 'Czas trwania';
$string['ctrl:mute'] = 'Wycisz';
$string['ctrl:volume'] = 'Głośność';
$string['ctrl:speed'] = 'Szybkość';
$string['ctrl:pip'] = 'Obraz w obrazie';
$string['ctrl:fullscreen'] = 'Pełny ekran';
$string['ctrl:download'] = 'Pobierz';

$string['setting:playerwidth'] = 'Szerokość odtwarzacza';
$string['setting:playerwidth_desc'] = 'Domyślna szerokość odtwarzacza.';
$string['playerwidth'] = 'Szerokość odtwarzacza';
$string['playerwidth_help'] = 'Szerokość obszaru wideo.';
$string['setting:rewindstep'] = 'Krok przewijania wstecz';
$string['setting:rewindstep_desc'] = 'Liczba sekund dla przycisku przewijania wstecz.';
$string['rewindstep'] = 'Krok przewijania wstecz';
$string['rewindstep_help'] = 'Ile sekund cofa przycisk przewijania wstecz.';
$string['setting:fastforwardstep'] = 'Krok przewijania do przodu';
$string['setting:fastforwardstep_desc'] = 'Liczba sekund dla przycisku przewijania do przodu.';
$string['fastforwardstep'] = 'Krok przewijania do przodu';
$string['fastforwardstep_help'] = 'Ile sekund przesuwa przycisk przewijania do przodu.';
$string['captionsheader'] = 'Napisy';
$string['captions'] = 'Napisy';
$string['captions_help'] = 'Włącz napisy dla przesłanego pliku wideo, używając pliku WebVTT.';
$string['setting:default_captions_desc'] = 'Domyślnie włącz napisy dla nowych aktywności.';
$string['captionslang'] = 'Język napisów';
$string['captionslang_help'] = 'Kod języka napisów, np. pl, en, it.';
$string['setting:captionslang_desc'] = 'Domyślny kod języka napisów.';
$string['vttfile'] = 'Plik napisów WebVTT';
$string['vttfile_help'] = 'Prześlij plik .vtt z napisami.';
$string['vttfile_notice'] = 'Napisy WebVTT są używane tylko dla przesłanych plików HTML5.';
$string['vimeo_captions_notice'] = 'Napisy Vimeo są obsługiwane przez sam odtwarzacz Vimeo.';
$string['ctrl:rewind'] = 'Przewiń do tyłu';
$string['ctrl:fastforward'] = 'Przewiń do przodu';

$string['playerloading'] = 'Ładowanie odtwarzacza…';
$string['noreactionsyet'] = 'Brak reakcji.';
$string['reaction:error'] = 'Nie można zapisać reakcji. Spróbuj ponownie.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Wznów odtwarzanie';
$string['resumeplayback_desc'] = 'Pozwala studentom wznowić oglądanie od ostatniej pozycji.';
$string['resumeplayback_help'] = 'Gdy włączone, odtwarzacz proponuje powrót do ostatnio zapisanej pozycji.';
$string['setting:resumeplayback'] = 'Wznawianie odtwarzania';
$string['setting:resumeplayback_desc'] = 'Domyślne ustawienie wznawiania odtwarzania dla nowych aktywności.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Maksymalna szybkość odtwarzania';
$string['maxplaybackrate_desc'] = 'Najwyższa szybkość odtwarzania dostępna studentom.';
$string['maxplaybackrate_help'] = 'Ogranicza maksymalną szybkość odtwarzania.';
$string['maxplaybackrate_nolimit'] = 'Bez limitu';
$string['setting:maxplaybackrate'] = 'Maksymalna szybkość odtwarzania';
$string['setting:maxplaybackrate_desc'] = 'Domyślny limit maksymalnej szybkości odtwarzania.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Pokaż transkrypcję';
$string['showtranscript_desc'] = 'Wyświetla transkrypcję, jeśli jest dostępna.';
$string['showtranscript_help'] = 'Pozwala studentom czytać transkrypcję podczas oglądania.';
$string['transcript_title'] = 'Transkrypcja';
$string['transcript_loading'] = 'Ładowanie transkrypcji…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Autoodtwarzanie zostało zablokowane przez przeglądarkę.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['vimeocspwarnlabel'] = 'Odtwarzacz Vimeo może wymagać zezwolenia w Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'Wznów';
// ── Report: azioni studente ──
$string['report:actions'] = 'Akcje';
$string['report:resetstudent'] = 'Resetuj dane studenta';
$string['report:resetstudent_confirm'] = 'Czy na pewno chcesz zresetować dane tego studenta?';
$string['report:studentreset'] = 'Dane studenta zostały zresetowane.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Pokaż rozdziały';
$string['showchapters_desc'] = 'Włącz listę rozdziałów wideo.';
$string['showchapters_help'] = 'Rozdziały pomagają studentom poruszać się po treści wideo.';
$string['chapters_label'] = 'Rozdziały wideo';
$string['chapterslabel'] = 'Rozdziały';
$string['chapter_label'] = 'Rozdział';
$string['chapterlabel'] = 'Rozdział';
$string['studentnotesenabled'] = 'Włącz notatki studentów';
$string['studentnotesenabled_desc'] = 'Pozwól studentom pisać osobiste notatki z przypisanym znacznikiem czasu podczas oglądania.';
$string['studentnotesenabled_help'] = 'Po włączeniu obok wideo pojawia się pole tekstowe. Studenci mogą wpisać notatkę i zapisać ją przy bieżącym znaczniku czasu. Notatki są widoczne tylko dla autora oraz dla osób uprawnionych w raporcie.';
$string['setting:studentnotesenabled'] = 'Włącz notatki studentów (domyślnie)';
$string['setting:studentnotesenabled_desc'] = 'Domyślne ustawienie dla nowych aktywności VideoTrack. Nauczyciele mogą je nadpisać w aktywności.';
$string['studentnotes_title'] = 'Moje notatki';
$string['studentnote_placeholder'] = 'Napisz notatkę w tym momencie wideo…';
$string['studentnote_save'] = 'Zapisz notatkę';
$string['studentnote_hint'] = 'Notatka zostanie zapisana przy bieżącym znaczniku czasu. Wideo musi być odtwarzane.';
$string['studentnotes_list_label'] = 'Zapisane notatki';
$string['studentnote_label'] = 'Notatka studenta';
$string['noteerrorlabel'] = 'Nie można zapisać notatki. Spróbuj ponownie.';
$string['charsremaininglabel'] = 'pozostałych znaków';
$string['posterimage'] = 'Plakat / obraz podglądu';
$string['posterimage_help'] = 'Prześlij obraz wyświetlany jako podgląd przed rozpoczęciem odtwarzania. Zalecany rozmiar: 1280×720 px (16:9).';
$string['posterimage_notice'] = 'Obraz plakatu jest pokazywany przed rozpoczęciem odtwarzania i ukrywany automatycznie po starcie wideo.';
$string['playbutton_label'] = 'Odtwórz wideo';
$string['setting:maxplaybackrate_nolimit'] = 'Bez limitu';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Tekst osobistej notatki napisanej przez studenta przy określonym znaczniku czasu wideo.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Typ zdarzenia: puste dla standardowych reakcji, „note” dla osobistych notatek studenta.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['studentnotesdisabled'] = 'Notatki studentów nie są włączone dla tej aktywności.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'Dla tej aktywności nie przesłano pliku wideo.';
$string['removenote'] = 'Usuń notatkę';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Ukryj notatki';
$string['notes_show'] = 'Pokaż notatki';
$string['report:notes_title'] = 'Notatki studentów';
$string['report:nonotes'] = 'Nie napisano notatek dla tej aktywności.';
$string['report:notedate'] = 'Napisano';
$string['report:exportnotes_csv'] = 'Eksportuj notatki jako CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Zamknij';
$string['rewindlabel'] = 'Przewiń wstecz';
$string['fastforwardlabel'] = 'Przewiń do przodu';
$string['secondslabel'] = 'sekund';
$string['removenotelabel'] = 'Usuń notatkę';
// ── Help strings ──
$string['gradepass_help'] = 'Minimalna ocena wymagana do zaliczenia tej aktywności.';


$string['completiondetail:requiredreactions'] = 'Musi zawierać te wymagane reakcje: {$a}';

$string['error:playbackrequired'] = 'Wideo musi być odtwarzane, zanim ta akcja może zostać zapisana.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'Rozszerzenie PHP GD jest niedostępne.';
$string['setting:gd_missing_desc'] = 'Ikony reakcji przesłane przez nauczycieli NIE będą automatycznie zmieniane do 64×64 pikseli. Oryginalny plik będzie serwowany bez zmian, co może wpłynąć na wydajność.';

$string['report:heatmap_legend'] = 'Legenda kolorów mapy cieplnej reakcji';

$string['report:clusterlimitreached'] = 'Raport osiągnął maksymalną liczbę wyświetlanych klastrów. Użyj filtrów lub węższego zakresu czasu.';

$string['report:showingrecentreactions'] = 'Pokazywane jest tylko pierwsze {$a} reakcji.';

$string['report:viewfullreport'] = 'Wyświetl pełny raport';
$string['studentnotes_view_limited'] = 'Pokazywane są najnowsze notatki: {$a}. Otwórz pełny raport, aby przejrzeć wszystkie.';
$string['report:skiptoheatmaptable'] = 'Pomiń mapę cieplną i przejdź do tabeli danych';
$string['report:heatmap_textsummary'] = 'Wykres zawiera {$a->clusters} klastrów; największy klaster zawiera {$a->max} kliknięć.';
$string['err:reactioniconvaluerequired'] = 'Wpisz emoji albo klasę Font Awesome.';

$string['error:reactionratelimit'] = 'Przesłano zbyt wiele reakcji w krótkim czasie. Kontynuuj oglądanie i spróbuj ponownie.';
$string['event:student_progress_reset'] = 'Zresetowano dane studenta VideoTrack';
$string['report:timefrom'] = 'Od sekundy';
$string['report:timeto'] = 'Do sekundy';
$string['report:clusterlimitreached_help'] = 'Raport skumulowany osiągnął limit klastrów. Użyj filtrów użytkownika, reakcji lub czasu wideo, aby zawęzić analizę.';
$string['report:topclusterssummary'] = 'Najważniejsze klastry w tym wyborze:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, kliknięcia: {$a->clicks}';
$string['error:notesratelimit'] = 'Przesłano zbyt wiele notatek w krótkim czasie. Poczekaj przed dodaniem kolejnej.';

$string['privacy:segmentschunk'] = 'Segmenty oglądania wideo - część {$a}';

$string['privacy:reactionsactivechunk'] = 'Aktywne reakcje - część {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Usunięte reakcje - część {$a}';

$string['privacy:notesactivechunk'] = 'Aktywne notatki - część {$a}';

$string['privacy:notesdeletedchunk'] = 'Usunięte notatki - część {$a}';

$string['report:clusterlimitreached_csv'] = 'OSTRZEŻENIE: osiągnięto limit klastrów. Eksport może być niepełny; zastosuj filtry użytkownika, reakcji lub czasu i wyeksportuj ponownie.';

$string['report:notecreatedfrom'] = 'Notatki od daty';

$string['report:notecreatedto'] = 'Notatki do daty';

$string['reactionsdisabled'] = 'Reakcje są wyłączone dla tej aktywności VideoTrack. Poproś nauczyciela lub administratora kursu o ich włączenie, jeśli są wymagane.';
$string['reactionsavailableonlyduringplayback'] = 'Reakcje są dostępne tylko podczas odtwarzania wideo.';
$string['reactionsreadyannounce'] = 'Reakcje są teraz dostępne.';

$string['privacy:state'] = 'Stan ukończenia';

$string['report:clusterlimitrequiresfilters'] = 'Raport skumulowany jest częściowy. Zastosuj filtr zakresu czasu wideo, aby niezawodnie pobrać pozostałe klastry.';

$string['report:clusterlimitrequiresfilters_csv'] = 'Eksport skumulowany jest częściowy, ponieważ nie zastosowano filtru zakresu czasu wideo. Zastosuj filtry Od sekundy/Do sekundy i wyeksportuj ponownie.';
$string['report:clusterexportblocked_csv'] = 'Eksport został zatrzymany, aby uniknąć zwrócenia niepełnych danych. Zastosuj filtr zakresu czasu wideo i wyeksportuj ponownie.';
$string['report:clusterdisplayblocked'] = 'Tabela klastrów została ukryta, aby uniknąć pokazania niepełnych danych. Zastosuj filtr zakresu czasu wideo, aby kontynuować.';
$string['unknownreaction'] = 'Nieznana reakcja';
$string['externalprovider_notice'] = 'Zewnętrzni dostawcy wideo, tacy jak YouTube i Vimeo, mogą przetwarzać dane osobowe i ustawiać pliki cookie zgodnie z własnymi politykami prywatności. Używaj przesłanych plików, gdy przekazywanie danych stronom trzecim nie jest dozwolone.';
$string['privacy:metadata:youtube'] = 'Gdy używane jest wideo YouTube, przeglądarka użytkownika łączy się z YouTube, aby załadować i odtworzyć wideo.';
$string['privacy:metadata:youtube:videoid'] = 'Identyfikator wideo YouTube skonfigurowany dla tej aktywności.';
$string['privacy:metadata:youtube:url'] = 'Adres URL YouTube skonfigurowany dla tej aktywności.';
$string['privacy:metadata:vimeo'] = 'Gdy używane jest wideo Vimeo, przeglądarka użytkownika łączy się z Vimeo, aby załadować i odtworzyć wideo.';
$string['privacy:metadata:vimeo:videoid'] = 'Identyfikator wideo Vimeo skonfigurowany dla tej aktywności.';
$string['privacy:metadata:vimeo:url'] = 'Adres URL Vimeo skonfigurowany dla tej aktywności.';
$string['html5:controls'] = 'Kontrolki wideo';
$string['html5:play'] = 'Odtwórz';
$string['html5:pause'] = 'Pauza';
$string['html5:seek'] = 'Przewijanie';
$string['html5:volume'] = 'Głośność';
$string['html5:mute'] = 'Wycisz';
$string['html5:unmute'] = 'Wyłącz wyciszenie';
$string['html5:speed'] = 'Szybkość';
$string['html5:pip'] = 'Obraz w obrazie';
$string['html5:fullscreen'] = 'Pełny ekran';
$string['html5:download'] = 'Pobierz';

// GDPR retention and academic-integrity.
$string['setting:heading_privacy'] = 'Prywatność i przechowywanie danych';
$string['setting:heading_privacy_desc'] = 'Skonfiguruj sposób przechowywania danych śledzenia, notatek i reakcji przez VideoTrack.';
$string['setting:retentionperioddays'] = 'Okres przechowywania danych śledzenia (dni)';
$string['setting:retentionperioddays_desc'] = 'Liczba dni, po których VideoTrack anonimizuje stare dane śledzenia, notatki i reakcje. Ustaw 0, aby przechowywać dane bezterminowo. Żądania usunięcia danych użytkownika są zawsze obsługiwane przez soloną anonimizację zamiast usuwania analityki agregowanej.';
$string['setting:strictsessionvalidation'] = 'Wymagaj tej samej sesji przeglądarki do walidacji notatek i reakcji';
$string['setting:validationfallbackdays'] = 'Okno walidacji historycznego odtwarzania (dni)';
$string['setting:validationfallbackdays_desc'] = 'Maksymalny wiek, w dniach, wcześniej obejrzanych segmentów, które mogą autoryzować notatki i reakcje po odświeżeniu strony lub zmianie przeglądarki. Ustaw 0, aby zezwolić na historyczne obejrzane segmenty bez ograniczenia czasu; poprawia to użyteczność, ale czyni walidację integralności akademickiej bardziej liberalną. Kontrole tej samej sesji i ostatniego odtwarzania są zawsze wykonywane najpierw.';
$string['setting:strictsessionvalidation_desc'] = 'Gdy włączone, notatki i reakcje mogą być zapisane tylko dla znaczników czasu obejrzanych w bieżącej sesji przeglądarki. Gdy wyłączone, VideoTrack akceptuje znaczniki czasu już obejrzane przez tego samego użytkownika w tej samej aktywności.';
$string['task:cleanup'] = 'Anonimizuj przeterminowane dane śledzenia VideoTrack';
$string['privacy:anonymised'] = '[zanonimizowano]';
$string['error:playbackpositionnotwatched'] = 'Ta pozycja wideo nie została jeszcze obejrzana, więc akcja nie może zostać zapisana.';

$string['setting:intrangerequired'] = 'Wprowadź liczbę całkowitą od {$a->min} do {$a->max}.';
$string['setting:nonnegativeintrequired'] = 'Wpisz liczbę całkowitą większą lub równą 0.';

$string['report:anonymiseduser'] = 'Użytkownik zanonimizowany';

$string['report:exportnotes_privacywarning'] = 'Ten eksport może zawierać dane osobowe z notatek studentów. Pobieraj i przechowuj go tylko wtedy, gdy masz ważny cel, i usuń, gdy nie jest już potrzebny.';
