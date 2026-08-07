# Videotrack 1.4.123 - Revisione sendBeacon

## Scopo

Valutazione del percorso `sendBeacon` usato quando la pagina viene chiusa o nascosta.

## Decisione

Il comportamento è conservativo: se `navigator.sendBeacon` non è disponibile, il codice evita fallback automatici che potrebbero duplicare o alterare il tracking.

## Rationale

La priorità è non compromettere la semantica didattica del tracciamento. Eventuali fallback più complessi richiedono audit specifici.
