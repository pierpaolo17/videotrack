# Videotrack 1.3 Stable Release

## Stable status

The 1.3 line has passed the planned adapter, tracker, hardening, accessibility and static testing checkpoints and is packaged with `MATURITY_STABLE`.

## Runtime caveat

These package checks do not replace Moodle runtime validation. Before deployment, validate at least one course activity with each configured provider:

- HTML5
- YouTube
- Vimeo

## Stable maintenance rule

Maintenance patches after `1.3.80` should stay conservative: documentation, static checks, packaging corrections, or narrowly scoped bug fixes only. Any schema, backup/restore, capability, privacy or player behaviour change requires a dedicated review.
