# DiagnosticReport

Resource untuk laporan hasil pemeriksaan diagnostik (lab, radiologi, patologi).

## Kapan Dipakai

- Bikin summary report dari pemeriksaan lab/radiologi
- Bind 1 report ke multiple `Observation` (hasil detail) + `Specimen` (sampel)
- Wajib reference ke `ServiceRequest` (permintaan pemeriksaan)

## Quick Example

```php
use Satusehat\Integration\FHIR\DiagnosticReport;

$dr = new DiagnosticReport();

$dr->setStatus('final')
   ->setSubject('P02478375538', 'Budi Setiawan')
   ->setEncounter('E12345')
   ->setPerformer('10009880728', 'dr. Ahmad');

$dr->setCode('CBC', 'Complete Blood Count', 'http://loinc.org');
$dr->setSpecimen('SPEC001');
$dr->setServiceRequest('SR001');
$dr->setResult('OBS001');             // ID Observation hasil
$dr->setEffectiveDateTime();

[$statusCode, $response] = $dr->post();
```

## Method Reference

Beberapa setter return `DiagnosticReport` instance untuk chaining.

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId, $display=null)` | Kunjungan |
| `setPerformer($practitionerId, $name)` | Dokter pembuat report |
| `setCode($code, $display, $codeSystem)` | Jenis pemeriksaan (LOINC code recommended) |
| `setServiceRequest($srId)` | Reference ke ServiceRequest (permintaan lab) |
| `setResult($obsId)` | Reference ke Observation (hasil pemeriksaan) |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setStatus($status)` | `final` | registered / preliminary / final / amended / corrected / cancelled / entered-in-error / unknown |
| `setSpecimen($specimenId)` | — | Reference ke Specimen |
| `setEffectiveDateTime($dateTime)` | now() UTC | Set 2 field: `effectiveDateTime` + `issued` |

### Output Method

- `json()` / `post()` / `put($id)`

## Catatan Penting

- **Default `conclusionCode=Group A`** sudah di-set di constructor (LOINC LA19710-5). Edit payload manual kalau butuh conclusion lain.
- **`setResult` cuma terima 1 observation ID** — kalau ada multiple hasil (misal CBC = WBC + RBC + Hb + dll), butuh modify `result` array manual. Default cuma override dengan ID terakhir.
- **`setStatus` switch case** — value di luar daftar default ke `final`. Tidak throw exception (beda dari MedicationRequest).
- **`json()` nggak validate field wajib** — pastikan semua field di atas di-set sebelum post.

## Lihat Juga

- [ServiceRequest](service-request.md) — permintaan parent
- [Specimen](specimen.md) — sampel
- [Observation](observation.md) — hasil detail
- [Workflow: Alur Laboratorium](../workflows/alur-laboratorium.md)
