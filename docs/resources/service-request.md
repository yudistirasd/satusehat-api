# ServiceRequest

Resource untuk permintaan pemeriksaan lab/radiologi/diagnostik.

## Kapan Dipakai

- Dokter request pemeriksaan lab ke unit lab
- Default kategori: Laboratory procedure (SNOMED 108252007)
- Sebagai parent dari `Specimen`, `Observation` (hasil), dan `DiagnosticReport`

## Quick Example

```php
use Satusehat\Integration\FHIR\ServiceRequest;

$sr = new ServiceRequest();

$sr->setIdentifier('SR-2026-001');
$sr->setCode('CBC', 'Complete Blood Count', 'http://loinc.org');
$sr->setSubject('P02478375538');
$sr->setEncounter('E12345');
$sr->setRequester('10009880728', 'dr. Ahmad');
$sr->setPerformer('10009880728', 'dr. Ahmad'); // bisa nakes lain (analis lab)
$sr->setAuthored();

[$statusCode, $response] = $sr->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID internal request |
| `setCode($code, $display, $codeSystem, $text=null)` | Jenis pemeriksaan. Default `text`: `"Permintaan Pemeriksaan {display}"` |
| `setSubject($patientId)` | Pasien (note: cuma 1 parameter, nggak ada display) |
| `setEncounter($encounterId)` | Kunjungan |
| `setRequester($practitionerId, $display)` | Dokter yang request |
| `setPerformer($practitionerId, $display)` | Nakes yang akan kerjain (analis lab/radiografer) |
| `setAuthored($dateTime)` | Default: now() UTC. Set `authoredOn` + `occurrenceDateTime`. |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Default Constructor

Auto-set:

- **`status`**: `active`
- **`intent`**: `original-order`
- **`priority`**: `routine`
- **`category`**: SNOMED 108252007 (Laboratory procedure)

Untuk priority urgent, status berbeda, atau kategori radiologi/patologi, edit payload manual.

## Catatan Penting

- **`setSubject` cuma 1 param** — beda dari resource lain yang `($id, $name)`. Display di subject di-skip.
- **`setAuthored` set 2 field** — `authoredOn` (kapan request dibuat) + `occurrenceDateTime` (kapan diharapkan dilakukan). Nilai sama.
- **Default LOINC system** dipake untuk parameter `$codeSystem` di `setCode`. Bisa pake SNOMED atau code system lain.
- **`json()` nggak validate field wajib** — pastikan minimal subject + encounter + code + requester sebelum post.

## Lihat Juga

- [Specimen](specimen.md) — sampel yang dikumpulkan
- [Observation](observation.md) — hasil detail
- [DiagnosticReport](diagnostic-report.md) — summary report
- [Workflow: Alur Laboratorium](../workflows/alur-laboratorium.md)
