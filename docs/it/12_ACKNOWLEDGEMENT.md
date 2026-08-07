# Dichiarazione di presa visione

La funzione è disabilitata per default e ha una sezione autonoma nel form. Il docente inserisce testo formattato e sceglie “in qualsiasi momento” oppure “dopo l’ultimo secondo”. L’hash versione include testo, formato e modalità.

La conferma usa un form POST con `sesskey`. La modalità fine video è verificata due volte: il player abilita i controlli dopo il salvataggio del segmento finale e il server controlla il progresso persistito con tolleranza di un secondo. Un POST manuale non aggira il controllo server.

`videotrack_acknowledge` salva utente/attività, hash, versione attività, data e fotografia immutabile di secondi/percentuale. I record storici senza fotografia sono “non disponibili”, non zero. Cambiare testo/modalità richiede una nuova conferma corrente.

Il completamento può richiedere la conferma corrente. I report individuali mostrano stato, data e fotografia. Analytics/export mostrano conferme correnti, studenti distinti, medie e conteggio legacy, con soppressione separata per conteggi e contributori alle medie.

Privacy API, retention, reset e backup/restore con dati utente includono le prese visione. Il testo completo non viene duplicato in ogni riga utente.
