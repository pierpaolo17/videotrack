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
$string['reactioniconvalue_help'] = 'Dla emoji wpisz znak emoji. Dla Font Awesome wpisz klasę obsługiwaną przez motyw Moodle, np. fa fa-smile dla motywów Font Awesome 5 lub fa-regular fa-face-smile dla motywów Font Awesome 6. Dostępność ikon zależy od aktywnego motywu Moodle i zainstalowanej wersji Font Awesome. Pozostaw puste, gdy używasz przesłanego pliku ikony.';
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
$string['setting:heartbeatinterval_desc'] = 'Jak często odtwarzacz zapisuje bieżący segment oglądania na serwerze podczas ciągłego odtwarzania. Niższe wartości zmniejszają ryzyko utraty danych przy awarii przeglądarki lub sieci, ale zwiększają obciążenie serwera (jedno żądanie AJAX + dwa zapytania bazy danych na studenta na interwał). Zalecany zakres: 15–120 sekund. Minimalna wymuszona wartość: 5 sekund (wartości poniżej 5 są automatycznie podnoszone do 5 przez serwer).';
$string['setting:reactionannouncementinterval'] = 'Interwał dostępnych komunikatów reakcji (milisekundy)';
$string['setting:reactionannouncementinterval_desc'] = 'Minimalny interwał, w milisekundach, między powtarzanymi komunikatami czytnika ekranu „reakcje niedostępne”. Użyj niższej wartości dla częstszej informacji zwrotnej w krótkich filmach albo wyższej, aby ograniczyć powtarzane komunikaty. Ustaw 0, aby wyłączyć powtarzane komunikaty. Zalecany zakres po włączeniu: 10000–60000 milisekund. Przykłady: 10000 = 10 sekund, 30000 = 30 sekund, 60000 = 1 minuta.';
$string['setting:reactionreadydebouncems'] = 'Opóźnienie gotowości reakcji (milisekundy)';
$string['setting:reactionreadydebouncems_desc'] = 'Minimalne opóźnienie, w milisekundach, przed ponownym ogłoszeniem „reakcje dostępne” po szybkiej pauzie i wznowieniu. Ustaw 0, aby wyłączyć ten debounce.';
$string['setting:heading_performance'] = 'Wydajność';
$string['setting:heading_accessibility'] = 'Dostępność';
$string['setting:heading_accessibility_desc'] = 'Ustawienia komunikatów technologii wspomagających oraz informacji zwrotnej dla klawiatury i czytników ekranu.';
$string['setting:heading_defaults'] = 'Domyślne wartości dla nowych aktywności';
$string['setting:heading_defaults_desc'] = 'Te wartości są używane jako domyślne, gdy nauczyciel tworzy nową aktywność VideoTrack. Każdą aktywność można nadal skonfigurować indywidualnie.';
$string['setting:default_desc'] = 'Domyślna wartość dla nowych aktywności. Nauczyciel może ją nadpisać w każdej aktywności.';
$string['setting:default_completionpercent_desc'] = 'Domyślny minimalny procent wideo, który student musi obejrzeć, aby ukończyć aktywność. Ustaw 0, aby domyślnie wyłączyć tę regułę ukończenia.';
$string['event:segment_saved'] = 'Zapisano segment oglądania';
$string['event:reaction_saved'] = 'Przesłano reakcję';
$string['event:note_saved'] = 'Zapisano notatkę studenta';
$string['event:note_deleted'] = 'Usunięto osobistą notatkę';
$string['event:reaction_deleted'] = 'Usunięto reakcję';

$string['reactionx'] = 'Reakcja {$a}';

$string['err:reactioniconfilerequired'] = 'Prześlij plik ikony, gdy typ ikony jest ustawiony na Przesłany plik.';


$string['privacy:metadata:common:timecreated'] = 'Czas utworzenia rekordu.';
$string['privacy:metadata:common:timemodified'] = 'Czas ostatniej modyfikacji rekordu.';

$string['privacy:metadata:common:videotrackid'] = 'Wewnetrzny identyfikator aktywnosci VideoTrack powiazanej z rekordem.';
$string['privacy:metadata:common:courseid'] = 'Identyfikator kursu powiazanego z aktywnoscia.';
$string['privacy:metadata:common:cmid'] = 'Identyfikator modulu kursu powiazanego z aktywnoscia.';
$string['privacy:metadata:common:videoid'] = 'Identyfikator wideo lub tresci skonfigurowanej dla aktywnosci.';
$string['privacy:metadata:videotrack_reactev:reactionid'] = 'Wewnetrzny identyfikator definicji reakcji uzytej podczas zapisu zdarzenia.';
$string['privacy:metadata:external:ipaddress'] = 'Zewnetrzny dostawca moze otrzymac adres IP osoby ogladajacej w ramach standardowych zadan przegladarki.';
$string['privacy:metadata:external:cookies'] = 'Zewnetrzny dostawca moze ustawiac lub odczytywac pliki cookie zgodnie z wlasna polityka prywatnosci i ustawieniami przegladarki.';
$string['privacy:metadata:external:useragent'] = 'Zewnetrzny dostawca moze otrzymac informacje o przegladarce i urzadzeniu, takie jak naglowek user-agent.';
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
$string['presets:intro'] = 'Definiuj ogólnowitrynowe zestawy reakcji, które prowadzący mogą używać jako punkt startowy przy tworzeniu aktywności VideoTrack. Reakcje są kopiowane do aktywności i mogą być swobodnie edytowane przez prowadzącego.';
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

$string['reset:userdata'] = 'Usuń wszystkie dane oglądania studentów (segmenty, stany, reakcje)';
$string['report:recalculate'] = 'Przelicz raport';
$string['report:recalculated'] = 'Stany ukończenia przeliczono dla {$a} użytkowników.';
$string['report:heatmap_desc'] = 'Mapa cieplna pokazuje miejsca wideo, w których studenci najczęściej reagowali.';
$string['report:heatmap_supplementary'] = 'Dane mapy cieplnej są dostępne także w tabeli poniżej.';
$string['event:activity_completed'] = 'Aktywność VideoTrack ukończona';

$string['reactioniconfile_notice'] = 'Obraz zostanie automatycznie przeskalowany do 64×64 pikseli (wycinanie ze środka). Najlepsze wyniki daje kwadratowy obraz (proporcje 1:1). Akceptowane formaty: JPG, PNG, GIF, WebP.';
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
$string['vimeourl_help'] = 'Wklej adres URL filmu Vimeo (np. https://vimeo.com/123456789). Film musi być publiczny albo niepubliczny (unlisted), aby studenci z odpowiednimi uprawnieniami mogli go odtworzyć.';
$string['invalidvimeourl'] = 'Nieprawidłowy adres URL Vimeo.';
$string['videofile'] = 'Plik wideo';
$string['videofile_help'] = 'Prześlij plik wideo lub audio obsługiwany przez przeglądarkę.';
$string['videofile_notice'] = 'Przesłane pliki są odtwarzane za pomocą natywnego odtwarzacza HTML5.';
$string['setting:heading_player'] = 'Odtwarzacz';
$string['setting:playbackspeeds'] = 'Dostępne szybkości odtwarzania';
$string['setting:playbackspeeds_desc'] = 'Wybierz, które prędkości odtwarzania są dostępne w całej witrynie. Prowadzący mogą ograniczyć tę listę dla poszczególnych aktywności (jeśli mają odpowiednie uprawnienia). Wartość 1× (normalna) jest zawsze zalecana.';
$string['setting:playbackspeeds_teacher_desc'] = 'Wybierz, które prędkości odtwarzania mają być dostępne dla tej aktywności. Wyświetlane są tylko prędkości włączone na poziomie witryny. Pozostaw wszystkie zaznaczone, aby użyć domyślnych ustawień witryny.';
$string['setting:speed_normal'] = 'Normalna';
$string['setting:distractionfree'] = 'Tryb bez rozpraszania';
$string['setting:distractionfree_desc'] = 'Ukrywa elementy interfejsu, które mogą rozpraszać podczas oglądania.';
$string['intervalbar_title'] = 'Obejrzane fragmenty — zielone segmenty oznaczają części wideo, które już obejrzałeś(-aś).';
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
$string['setting:html5controls_teacher_desc'] = 'Wybierz, które kontrolki mają być widoczne w odtwarzaczu. Wyświetlane są tylko kontrolki włączone na poziomie witryny.';
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
$string['setting:playerwidth_desc'] = 'Maksymalna szerokość odtwarzacza wideo w pikselach (1–4096). Prowadzący mogą to zmienić dla poszczególnych aktywności (wartość 0 w aktywności = domyślna witryny). Zalecana: 960.';
$string['playerwidth'] = 'Szerokość odtwarzacza';
$string['playerwidth_help'] = 'Ustawia maksymalną szerokość odtwarzacza wideo dla tej aktywności w pikselach. Pozostaw 0, aby użyć domyślnej wartości platformy.';
$string['playerwidth_zero_note'] = 'Wpisz 0, aby odziedziczyć domyślną wartość platformy, albo wartość od 1 do 4096 pikseli dla tej aktywności.';
$string['setting:rewindstep'] = 'Krok przewijania wstecz';
$string['setting:rewindstep_desc'] = 'Liczba sekund, o jaką przycisk przewijania wstecz cofa domyślnie. Prowadzący mogą nadpisać tę wartość w pojedynczych aktywnościach. Ustaw 0, aby domyślnie ukryć przycisk; ustawienie aktywności może go ponownie włączyć. Domyślnie: 10. Ważne: jeśli opcja "Zezwól na przewijanie wstecz" jest wyłączona dla aktywności, przycisk przewijania wstecz nie pojawi się nawet wtedy, gdy ta wartość jest > 0.';
$string['rewindstep'] = 'Krok przewijania wstecz';
$string['rewindstep_help'] = 'Liczba sekund cofania w tej aktywności (0–300). Pozostaw 0, aby użyć domyślnej wartości platformy. Jeśli domyślna wartość platformy wynosi 0, przycisk pozostaje ukryty, chyba że ta aktywność ustawi własną wartość większą niż 0. Uwaga: jeśli opcja „Zezwalaj na przewijanie wstecz” jest wyłączona dla tej aktywności, przycisk nie pojawi się niezależnie od tej wartości.';
$string['setting:fastforwardstep'] = 'Krok przewijania do przodu';
$string['setting:fastforwardstep_desc'] = 'Liczba sekund, o jaką przycisk przewijania do przodu przesuwa domyślnie. Prowadzący mogą nadpisać tę wartość w pojedynczych aktywnościach. Ustaw 0, aby domyślnie ukryć przycisk; ustawienie aktywności może go ponownie włączyć. Domyślnie: 10. Ważne: jeśli opcja "Zezwól na przewijanie do przodu" jest wyłączona dla aktywności, przycisk przewijania do przodu nie pojawi się nawet wtedy, gdy ta wartość jest > 0.';
$string['fastforwardstep'] = 'Krok przewijania do przodu';
$string['fastforwardstep_help'] = 'Liczba sekund przewijania do przodu w tej aktywności (0–300). Pozostaw 0, aby użyć domyślnej wartości platformy. Jeśli domyślna wartość platformy wynosi 0, przycisk pozostaje ukryty, chyba że ta aktywność ustawi własną wartość większą niż 0. Uwaga: jeśli opcja „Zezwalaj na przewijanie do przodu” jest wyłączona dla tej aktywności, przycisk nie pojawi się niezależnie od tej wartości.';
$string['captionsheader'] = 'Napisy i podpisy';
$string['captions'] = 'Włącz napisy / podpisy';
$string['captions_help'] = 'Gdy włączone: YouTube — napisy są domyślnie wyświetlane; Vimeo — ścieżka odpowiadająca kodowi języka jest aktywowana (musi być wcześniej załadowana na Vimeo.com); Upload — używany jest załączony plik VTT.';
$string['setting:default_captions_desc'] = 'Domyślnie włącz napisy dla nowych aktywności.';
$string['captionslang'] = 'Język napisów';
$string['captionslang_help'] = 'Kod języka ISO 639-1 (np. pl, en, de). Dla YouTube ustawia preferowany język napisów. Dla Vimeo aktywuje pasującą ścieżkę (musi być załadowana na Vimeo.com). Dla przesłanych filmów: pole informacyjne.';
$string['setting:captionslang_desc'] = 'Domyślny kod języka napisów (ISO 639-1, np. en, it). Prowadzący mogą zmieniać to ustawienie dla poszczególnych aktywności.';
$string['vttfile'] = 'Plik napisów WebVTT';
$string['vttfile_help'] = 'Prześlij plik napisów WebVTT (.vtt). Plik zostanie wysłany do przeglądarki studenta i wyświetlony jako napisy w odtwarzaczu wideo.';
$string['vttfile_notice'] = 'Akceptowany format: WebVTT (.vtt). Obsługiwany jest tylko jeden plik. Plik musi odpowiadać kodowi języka podanemu powyżej.';
$string['vimeo_captions_notice'] = 'Napisy Vimeo są zarządzane na Vimeo.com. Prześlij tam swoje ścieżki napisów. Podany wyżej kod języka zostanie użyty do automatycznego aktywowania pasującej ścieżki.';
$string['ctrl:rewind'] = 'Przewiń do tyłu';
$string['ctrl:fastforward'] = 'Przewiń do przodu';

$string['playerloading'] = 'Ładowanie odtwarzacza…';
$string['noreactionsyet'] = 'Nie zarejestrowano jeszcze żadnych reakcji. Reaguj podczas odtwarzania wideo.';
$string['reaction:error'] = 'Nie można zapisać reakcji. Spróbuj ponownie.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Wznów odtwarzanie';
$string['resumeplayback_desc'] = 'Pozwala studentom wznowić oglądanie od ostatniej pozycji.';
$string['resumeplayback_help'] = 'Gdy włączone, odtwarzacz proponuje powrót do ostatnio zapisanej pozycji (jeśli oglądano więcej niż 5 sekund). Studenci zawsze mogą ręcznie wrócić na początek.';
$string['setting:resumeplayback'] = 'Wznawianie odtwarzania';
$string['setting:resumeplayback_desc'] = 'Domyślne ustawienie wznawiania odtwarzania dla nowych aktywności.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Maksymalna szybkość odtwarzania';
$string['maxplaybackrate_desc'] = 'Najwyższa szybkość odtwarzania dostępna studentom. Ustaw 0, aby nie ograniczać szybkości.';
$string['maxplaybackrate_help'] = 'Gdy ustawiono, studenci nie mogą odtwarzać wideo szybciej niż ta prędkość, nawet jeśli kontrolki odtwarzacza umożliwiają wyższe wartości. Zniechęca do zbyt szybkiego przeglądania treści.';
$string['maxplaybackrate_nolimit'] = 'Bez limitu';
$string['setting:maxplaybackrate'] = 'Maksymalna szybkość odtwarzania';
$string['setting:maxplaybackrate_desc'] = 'Domyślny limit maksymalnej szybkości odtwarzania.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Pokaż transkrypcję';
$string['showtranscript_desc'] = 'Wyświetla transkrypcję, jeśli jest dostępna.';
$string['showtranscript_help'] = 'Analizuje przesłany plik VTT i wyświetla go jako listę z możliwością klikania. Każdy wpis pokazuje znacznik czasu i tekst; kliknięcie przenosi wideo do tego miejsca. Aktywny cue jest podświetlony i automatycznie przewijany do widoku.';
$string['transcript_title'] = 'Transkrypcja';
$string['transcript_unavailable'] = 'Transkrypcja nie jest dostępna dla tego wideo.';
$string['transcript_loading'] = 'Ładowanie transkrypcji…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Autoodtwarzanie zostało zablokowane przez przeglądarkę.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['sdkerrorlabel'] = 'Nie można załadować odtwarzacza wideo. Przyczyną może być bloker treści, Content Security Policy lub ograniczenie sieciowe. Wyłącz blokery albo skontaktuj się z administratorem.';
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
$string['showchapters_desc'] = 'Wyświetla pasek nawigacji z markerami rozdziałów wyodrębnionymi z pliku VTT. Rozdziałami są cue VTT z tekstem krótszym niż 80 znaków.';
$string['showchapters_help'] = 'Jeśli przesłany plik VTT zawiera krótkie cue (poniżej 80 znaków), są one interpretowane jako tytuły rozdziałów i renderowane jako pasek nawigacji z możliwością klikania powyżej kontrolek wideo. Kliknięcie rozdziału przenosi do tego miejsca.';
$string['chapters_label'] = 'Rozdziały wideo';
$string['chapters_unavailable'] = 'Rozdziały nie są dostępne dla tego filmu.';
$string['chapter_label'] = 'Rozdział';
$string['studentnotesenabled'] = 'Włącz notatki studentów';
$string['studentnotesenabled_desc'] = 'Pozwól studentom pisać osobiste notatki z przypisanym znacznikiem czasu podczas oglądania.';
$string['studentnotesenabled_help'] = 'Po włączeniu obok wideo pojawia się pole tekstowe. Studenci mogą wpisać notatkę i zapisać ją przy bieżącym znaczniku czasu. Notatki są widoczne tylko dla autora oraz dla osób uprawnionych w raporcie.';
$string['setting:studentnotesenabled'] = 'Włącz notatki studentów (domyślnie)';
$string['setting:studentnotesenabled_desc'] = 'Domyślne ustawienie dla nowych aktywności VideoTrack. Nauczyciele mogą je nadpisać w aktywności.';
$string['setting:notemaxlength'] = 'Maksymalna długość notatek';
$string['setting:notemaxlength_desc'] = 'Maksymalna liczba znaków dozwolona dla każdej osobistej notatki studenta. Domyślnie: 2000.';
$string['studentnotes_title'] = 'Moje notatki';
$string['studentnote_placeholder'] = 'Napisz notatkę w tym momencie wideo…';
$string['studentnote_save'] = 'Zapisz notatkę';
$string['studentnote_hint'] = 'Notatka zostanie zapisana przy bieżącym znaczniku czasu. Wideo musi być odtwarzane.';
$string['studentnotes_list_label'] = 'Zapisane notatki';
$string['studentnote_label'] = 'Notatka studenta';
$string['noteerrorlabel'] = 'Nie można zapisać notatki. Spróbuj ponownie.';
$string['notesavedlabel'] = 'Notatka zapisana.';
$string['notedeletedlabel'] = 'Notatka usunięta.';
$string['noteplaybackrequiredlabel'] = 'Uruchom odtwarzanie przed zapisaniem notatki.';
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
$string['status:default'] = 'Aktualizacja stanu.';
$string['status:error'] = 'Wystąpił błąd. Spróbuj ponownie.';
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

$string['report:showingrecentreactionsoftotal'] = 'Pokazywane jest {$a->shown} z {$a->total} reakcji, od najstarszej do najnowszej.';

$string['report:viewfullreport'] = 'Wyświetl pełny raport';
$string['studentnotes_view_limited'] = 'Pokazywane są najnowsze notatki: {$a}. Otwórz pełny raport, aby przejrzeć wszystkie.';
$string['report:skiptoheatmaptable'] = 'Pomiń mapę cieplną i przejdź do tabeli danych';
$string['report:heatmap_textsummary'] = 'Wykres zawiera {$a->clusters} klastrów; największy klaster zawiera {$a->max} kliknięć.';
$string['err:reactioniconvaluerequired'] = 'Wpisz emoji albo klasę Font Awesome.';
$string['err:reactioniconvalueinvalidfa'] = 'Wpisz tylko prawidłowe nazwy klas Font Awesome, używając liter, cyfr, spacji i myślników.';

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
$string['setting:retentionperioddays_desc'] = 'Liczba dni, po ktorych VideoTrack anonimizuje stare dane sledzenia, notatki i reakcje (w tym etykiety reakcji wpisywane jako tekst) w ramach automatycznej retencji. Ustaw 0, aby przechowywac dane bezterminowo. Zadania usuniecia danych obslugiwane przez Moodle Privacy API trwale usuwaja rekordy sledzenia, stanu, reakcji i notatek uzytkownika w wybranym kontekscie.';
$string['setting:retentionprivacynotice'] = 'Dane sledzenia, notatki i reakcje sa danymi osobowymi. Zapewnij wazna podstawe prawna, aktualizuj informacje o prywatnosci witryny i unikaj bezterminowego przechowywania, chyba ze jest ono uzasadnione.';
$string['setting:strictsessionvalidation'] = 'Wymagaj tej samej sesji przeglądarki do walidacji notatek i reakcji';
$string['setting:validationfallbackdays'] = 'Okno walidacji historycznego odtwarzania (dni)';
$string['setting:validationfallbackdays_desc'] = 'Maksymalny wiek, w dniach, wcześniej obejrzanych segmentów, które mogą autoryzować notatki i reakcje po odświeżeniu strony lub zmianie przeglądarki. Ustaw 0, aby zezwolić na historyczne obejrzane segmenty bez ograniczenia czasu; poprawia to użyteczność, ale czyni walidację integralności akademickiej bardziej liberalną. Kontrole tej samej sesji i ostatniego odtwarzania są zawsze wykonywane najpierw.';
$string['setting:strictsessionvalidation_desc'] = 'Gdy włączone, notatki i reakcje mogą być zapisane tylko dla znaczników czasu obejrzanych w bieżącej sesji przeglądarki. Gdy wyłączone, VideoTrack akceptuje znaczniki czasu już obejrzane przez tego samego użytkownika w tej samej aktywności.';
$string['task:cleanup'] = 'Anonimizuj przeterminowane dane śledzenia VideoTrack';
$string['privacy:anonymised'] = '[zanonimizowano]';
$string['error:playbackpositionnotwatched'] = 'Ta pozycja wideo nie została jeszcze obejrzana, więc akcja nie może zostać zapisana.';

$string['setting:intrangerequired'] = 'Wprowadź liczbę całkowitą od {$a->min} do {$a->max}.';
$string['err:playerwidthrequired'] = 'Wpisz 0, aby użyć domyślnej wartości platformy, albo liczbę całkowitą od 1 do 4096 pikseli.';
$string['err:playbacksteprequired'] = 'Wprowadź liczbę całkowitą od 0 do 300 sekund. Użyj 0, aby zastosować domyślną wartość platformy.';
$string['setting:nonnegativeintrequired'] = 'Wpisz liczbę całkowitą większą lub równą 0.';

$string['report:anonymiseduser'] = 'Użytkownik zanonimizowany';

$string['report:exportnotes_privacywarning'] = 'Ten eksport może zawierać dane osobowe z notatek studentów. Pobieraj i przechowuj go tylko wtedy, gdy masz ważny cel, i usuń, gdy nie jest już potrzebny.';

$string['privacy:videoid_export_note'] = 'Identyfikator wideo/treści: {$a}';
$string['privacy:anonymisedreaction'] = 'Zanonimizowana reakcja';

// 1.3.87 accessibility and privacy confirmation strings.
$string['invalidvideosource'] = 'Nieprawidłowe źródło wideo.';
$string['report:gradeinputfor'] = 'Ocena dla {$a}';
$string['report:savegradefor'] = 'Zapisz ocenę dla {$a}';
$string['report:gradepassed'] = 'Zaliczone';
$string['report:gradefailed'] = 'Niezaliczone';
$string['report:exportnotes_confirm'] = 'Potwierdzam, że ten eksport notatek może zawierać dane osobowe i że mam ważny cel ich pobrania.';
$string['report:exportnotes_confirmrequired'] = 'Potwierdź informację o eksporcie danych osobowych przed pobraniem notatek.';
$string['coursereport:avgcoverage'] = 'Średnie pokrycie: {$a}%';

$string['report:exportnotes_csv_personaldata'] = 'Eksportuj notatki jako CSV, w tym możliwe dane osobowe';

$string['presets:deletearia'] = 'Usuń preset {$a}';
$string['presets:reactionlabelaria'] = 'Reakcja {$a}: etykieta';
$string['presets:reactiondescriptionaria'] = 'Reakcja {$a}: opis';
$string['presets:reactionicontypearia'] = 'Reakcja {$a}: typ ikony';
$string['presets:reactioniconvaluearia'] = 'Reakcja {$a}: wartość ikony';
$string['presets:reactionrequiredaria'] = 'Reakcja {$a}: wymagana do ukończenia';
$string['err:reactionpresetjson'] = 'Dane presetu reakcji są nieprawidłowe. Odśwież stronę i spróbuj ponownie.';
$string['presets:reactionstablecaption'] = 'Wiersze presetu reakcji';
$string['privacy:intervals_none'] = 'Nie zarejestrowano interwałów oglądania.';
$string['privacy:intervals_unavailable'] = 'Interwały oglądania są niedostępne lub nieprawidłowe.';

$string['warning:suspicioussegment'] = 'Segment oglądania nie został zapisany, ponieważ przekroczył oczekiwane okno odtwarzania. Kontynuuj oglądanie normalnie i spróbuj ponownie.';

$string['event:notes_exported'] = 'Wyeksportowano notatki osobiste';

$string['externalproviderprivacy_notice'] = 'Ta aktywność wczytuje wideo z {$a}. Przeglądarka może wysyłać do tego dostawcy dane techniczne, takie jak adres IP, user agent i pliki cookie, zgodnie z informacją o prywatności witryny.';

$string['setting:retentionunlimitedwarning_title'] = 'Włączono bezterminowe przechowywanie VideoTrack.';

$string['setting:retentionunlimitedwarning_desc'] = 'Wartość 0 przechowuje dane śledzenia, notatki i reakcje bezterminowo. Potwierdź, że jest to uzasadnione zgodnie z polityką GDPR/prywatności, albo ustaw skończony okres, np. 730 dni.';

$string['warning:notetruncated'] = 'Notatka została zapisana, ale skrócono ją do maksymalnej długości dozwolonej w witrynie.';

$string['error:securetokenunavailable'] = 'Bezpieczny generator losowych tokenów nie jest dostępny. VideoTrack nie może bezpiecznie utworzyć kluczy anonimizacji.';
