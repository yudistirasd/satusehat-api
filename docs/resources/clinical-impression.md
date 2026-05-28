# ClinicalImpression

Resource untuk anamnesis / SOAP / pemeriksaan klinis dokter.

## Kapan Dipakai

- Catat anamnesis dokter (subjective, objective, assessment, plan)
- Catat history of disorder (riwayat penyakit yang relate ke encounter saat ini)
- Default code di package: `312850006` (History of disorder, SNOMED)

## Quick Example

```php
use Satusehat\Integration\FHIR\ClinicalImpression;

$ci = new ClinicalImpression();

$ci->setIdentifier('CI-001');
$ci->setSubject('P02478375538', 'Budi Setiawan');
$ci->setEncounter('E12345');
$ci->setSummary('Pasien datang dengan keluhan demam 3 hari, batuk, pilek. Pemfis suhu 38.5C, faring hiperemis. Dx: Common cold. Plan: Paracetamol 500mg 3x1 + istirahat 3 hari.');
$ci->setEffectiveDateTime();
$ci->setAssesor('10009880728');

[$statusCode, $response] = $ci->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID internal |
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId)` | Kunjungan |
| `setSummary($text)` | **Isi anamnesis/SOAP** (free text, ini field utama) |
| `setEffectiveDateTime($dateTime)` | Default: now() UTC. Set 2 field: `effectiveDateTime` + `date`. |
| `setAssesor($practitionerId)` | Nakes yang melakukan asesmen. Note: spelling sengaja (bukan `setAssessor`). |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Catatan Penting

- **Default `status=completed`** dan `code=312850006` (History of disorder) sudah di-set di constructor. Cocok untuk anamnesis standard, edit payload manual untuk use case lain.
- **Method `setAssesor` (1 'S')** — bug spelling di package, tapi tetap dipake apa adanya. Hanya simpan reference ke practitioner, tanpa display name.
- **Field utama: `summary`** — narasi free text panjang. Idealnya struktur SOAP atau format yang konsisten di sistem kamu.
- **`json()` nggak validate field wajib** — pastikan minimal subject + encounter + summary sebelum post.

## Format SOAP (Saran)

Format standar untuk `setSummary`:

```
S: [Subjective - keluhan pasien]
O: [Objective - hasil pemeriksaan fisik + lab]
A: [Assessment - diagnosis dokter]
P: [Plan - rencana terapi]
```

## Lihat Juga

- [Encounter](encounter.md) — kunjungan parent
- [Condition](condition.md) — diagnosis (kalau ada di assessment)
- [Composition](composition.md) — resume medis lebih kompleks
