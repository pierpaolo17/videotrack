# Automatic duration suggestion 1.6.27

## Purpose

The activity form now attempts to pre-fill `durationseconds` from metadata available for the selected source. This reduces manual work without weakening the server-authoritative tracking model.

## Supported sources

- **YouTube:** the form validates the supported HTTPS URL and queries duration through a non-playing, off-screen YouTube IFrame API instance.
- **Vimeo:** the form preserves an optional privacy hash and queries the Vimeo Player SDK through a non-playing, off-screen iframe with Do Not Track enabled.
- **Local Moodle file:** after the file picker exposes the same-origin draft URL, an off-screen HTML media element reads metadata without starting playback.

Detection is best effort. Private or non-embeddable provider content, CSP/network restrictions, browser policy, unsupported media metadata or an unfinished upload may prevent a result.

## Trust and persistence

- The detector runs only in the trusted teacher activity form.
- It writes a suggestion into the normal editable field; it does not write directly to the database.
- A teacher manual edit is never overwritten by a late detector response.
- Changing the source allows a fresh suggestion for the new source.
- The value becomes authoritative only after the teacher submits the form and Moodle saves the activity.
- Learner player duration remains non-authoritative and cannot overwrite the saved field.
- `0` remains a valid explicit choice that disables watched-percentage calculation and percentage-dependent completion/end acknowledgement while retaining validated interval tracking.

## Accessibility and privacy

Status changes are announced through a polite live region associated with the duration input. Provider probes do not autoplay, are off-screen and hidden from assistive technology. Entering an external provider URL can contact that provider to obtain metadata; existing external-provider notices still apply.

## Build contract

The canonical source is `amd/src/form/duration.js`. `grunt amd --root=mod/videotrack` must generate and distribute `amd/build/form/duration.min.js` and its source map.
