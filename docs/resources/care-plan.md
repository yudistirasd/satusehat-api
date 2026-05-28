# CarePlan

Resource untuk rencana rawat pasien (treatment plan).

## Kapan Dipakai

- Catat rencana terapi/perawatan dari dokter
- Default category: Outpatient care plan (SNOMED 736271009)
- Default status: `active`, intent: `plan`

## Quick Example

```php
use Satusehat\Integration\FHIR\CarePlan;

$plan = new CarePlan();

$plan->setSubject('P02478375538', 'Budi Setiawan');
$plan->setEncounter('E12345');
$plan->setAuthor('10009880728', 'dr. Ahmad');
$plan->setDescription(
    'Pasien dianjurkan istirahat 3 hari, minum obat sesuai resep, kontrol ulang jika gejala memburuk atau tidak membaik dalam 5 hari.'
);

[$statusCode, $response] = $plan->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $display)` | Pasien |
| `setEncounter($encounterId)` | Kunjungan |
| `setAuthor($practitionerId, $display)` | Dokter pembuat plan |
| `setDescription($text)` | **Isi rencana rawat** (free text) |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Default Constructor

Auto-set:

- **`status`**: `active`
- **`intent`**: `plan`
- **`category`**: SNOMED 736271009 (Outpatient care plan)
- **`title`**: `Rencana Rawat Pasien`

Untuk inpatient/discharge care plan, edit payload manual.

## Catatan Penting

- **CarePlan sederhana** — package ini cuma support description text. Untuk goal/activity terstruktur (misal target tekanan darah, jadwal obat), butuh modify payload manual.
- **`json()` nggak validate field wajib** — pastikan setSubject + setEncounter + setAuthor + setDescription sebelum post.
- **Default title hardcoded** "Rencana Rawat Pasien". Edit `$plan->carePlan['title']` manual kalau perlu kustom.

## Lihat Juga

- [Encounter](encounter.md)
- [Composition](composition.md) — resume medis lebih lengkap
- [ClinicalImpression](clinical-impression.md) — anamnesis dengan plan
