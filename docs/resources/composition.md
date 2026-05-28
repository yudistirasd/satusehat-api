# Composition

Resource untuk resume medis pasien (discharge summary).

## Kapan Dipakai

- Generate resume medis akhir kunjungan (rawat jalan)
- Compile semua data klinis (diagnosis, tindakan, obat) jadi 1 dokumen
- Untuk e-MR / RME yang butuh dokumentasi lengkap per encounter

## Quick Example

```php
use Satusehat\Integration\FHIR\Composition;

$comp = new Composition();

$comp->setIdentifier('COMP-001');
$comp->setSubject('P02478375538', 'Budi Setiawan');
$comp->setEncounter('E12345');
$comp->setAuthor('10009880728');

[$statusCode, $response] = $comp->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID composition. Auto-set `custodian` ke organization_id. |
| `setSubject($patientId, $display)` | Pasien. Auto-set `date` (now UTC) + `title` (`Resume Medis Pasien Rawat Jalan {nama} pada {date}`). |
| `setEncounter($encounterId)` | Kunjungan |
| `setAuthor($practitionerId)` | Dokter yang membuat resume |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Default Coding

Constructor auto-set:

- **`status`**: `final`
- **`category`**: LP173421-1 (Report) — LOINC
- **`type`**: 88645-7 (Outpatient hospital Discharge summary) — LOINC

Edit payload manual kalau butuh type berbeda (misal inpatient discharge summary, lab report, dll).

## Catatan Penting

- **`setSubject` set 3 field** — `subject`, `date` (now UTC), dan `title` auto-format. Kalau mau title custom, edit payload setelah `setSubject()`.
- **`setIdentifier` set custodian otomatis** ke organization_id kamu.
- **Resume sederhana** — package ini cuma simpan reference ke pasien/encounter/author. Untuk compose section detail (misal "Anamnesis", "Diagnosis", "Tindakan", "Obat" sebagai sub-section), butuh modify payload manual.
- **`json()` nggak validate field wajib** — pastikan setIdentifier + setSubject + setEncounter + setAuthor sebelum post.

## Lihat Juga

- [Encounter](encounter.md)
- [ClinicalImpression](clinical-impression.md) — anamnesis (lebih sederhana dari Composition)
