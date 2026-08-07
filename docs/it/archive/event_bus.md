# Videotrack - Event Bus

## Scopo

Il bus eventi interno coordina moduli AMD senza legarli direttamente tra loro.

## Convenzioni

Gli eventi usano nomi brevi e neutrali, con namespace controllati. Il pattern accettato limita lunghezza e caratteri per evitare input arbitrari.

## Namespace

- `player:*` per eventi del player.
- `tracker:*` per eventi di tracciamento.
- `notes:*` per note.
- `reactions:*` per reazioni.

## Rationale

La regex resta volutamente generale ma documentata: l'elenco dei namespace definisce l'uso previsto senza irrigidire eccessivamente il layer.
