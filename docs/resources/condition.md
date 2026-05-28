# Condition

Resource untuk diagnosis pasien (ICD-10).

## Kapan Dipakai

- Catat diagnosis hasil pemeriksaan dokter
- Tracking riwayat penyakit pasien
- Wajib reference ke `Encounter` (dibuat saat kunjungan tertentu)

## Quick Example

```php
use Satusehat\Integration\FHIR\Condition;

$condition = new Condition();

$condition->setSubject('P02478375538', 'Budi Setiawan');
$condition->setEncounter('E12345');
$condition->addCode('J00', 'Acute nasopharyngitis [common cold]');

[$statusCode, $response] = $condition->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId, $display=null, $bundle=false)` | Kunjungan terkait |
| `addCode($icd10Code, $display=null)` | **Validasi ICD-10 otomatis**. Kalau `$display` null, ambil dari database. |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `addClinicalStatus($status)` | `active` | `active` / `recurrence` / `inactive` / `remission` / `resolved` |
| `addCategory($category)` | `diagnosis` | `diagnosis` (encounter-diagnosis) / `keluhan` (problem-list-item) |
| `setOnsetDateTime($dateTime)` | now() | Kapan kondisi mulai dirasain |
| `setRecordedDate($date)` | now() | Kapan dicatat |

### Output Method

- `json()` — Generate JSON
- `post()` — Create condition baru
- `put($id)` — Update full

## Catatan Penting

- **`addCode` strict ICD-10** — code di-cek ke tabel `icd10` (sudah seeded di package). Kode invalid → throw `FHIRException("Kode ICD10 tidak ditemukan")`.
- **Default kategori `diagnosis`** kalau nggak set, jadi otomatis encounter-diagnosis. Pake `keluhan` kalau ini problem list pasien (nggak related ke encounter spesifik).
- **`$bundle=true`** dipakai khusus saat resource ini dimasukkan ke `Bundle` — referensi encounter pakai `urn:uuid:` prefix bukan `Encounter/`.
- Field wajib di `json()`: `subject`, `encounter`, `code`. Tapi clinicalStatus, category, onsetDateTime, recordedDate auto-default kalau belum di-set.

## Status Klinis

| Input | FHIR Code |
|---|---|
| `active` | active (Active) |
| `recurrence` | recurrence (Recurrence) |
| `inactive` | inactive (Inactive) |
| `remission` | remission (Remission) |
| `resolved` | resolved (Resolved) |

## Lihat Juga

- [Encounter](encounter.md) — kunjungan parent
- [Bundle](bundle.md) — Encounter + Condition dalam 1 transaction
