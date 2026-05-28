# Specimen

Resource untuk sampel pemeriksaan lab (darah, urine, dll).

## Kapan Dipakai

- Catat pengambilan sampel pasien untuk pemeriksaan lab
- Wajib ada sebelum bisa kirim `Observation` kategori `laboratory`
- Reference dari `DiagnosticReport`

## Quick Example

```php
use Satusehat\Integration\FHIR\Specimen;

$spec = new Specimen();

$spec->setIdentifier('SPEC-2026-001');
$spec->setStatus('available');
$spec->setType('Darah');
$spec->setSubject('P02478375538', 'Budi Setiawan');
$spec->setServiceRequest('SR001');
$spec->setProcessing();

[$statusCode, $response] = $spec->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID sampel internal. Auto-set assigner ke organization_id. |
| `setSubject($patientId, $display)` | Pasien sumber sampel |
| `setServiceRequest($srId)` | Reference ke ServiceRequest |
| `setType($type)` | Jenis sampel (lihat tabel di bawah) |
| `setStatus($status)` | Default `available`. Pilihan: available / unavailable / unsatisfactory / entered-in-error |
| `setProcessing($dateTime)` | Default: now() UTC. Set processing time + receivedTime. |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Tipe Sampel (`setType`)

| Input | SNOMED Code | Display |
|---|---|---|
| `Darah` | 119297000 | Blood specimen |
| `Urine` | 122575003 | Urine specimen |
| `Feses` | 119339001 | Stool specimen |
| `Jaringan tubuh` | 119376003 | Tissue specimen |
| `Serum` | 119364003 | Serum specimen |
| (lainnya) | 74964007 | Other |

Input case-sensitive. Kalau nggak match, fallback ke `Other`.

## Catatan Penting

- **`setProcessing` set 2 field** — `processing` (array dengan procedure code) + `receivedTime` (timestamp).
- **`setStatus` nggak validate** — kalau pass status invalid, akan tetep di-set tapi SATUSEHAT mungkin reject.
- **`json()` nggak validate field wajib** — pastikan minimal setIdentifier + setSubject + setType sebelum post.

## Lihat Juga

- [ServiceRequest](service-request.md) — permintaan lab parent
- [Observation](observation.md) — hasil pemeriksaan dari sampel
- [DiagnosticReport](diagnostic-report.md) — laporan summary
