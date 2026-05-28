# AllergyIntolerance

Resource untuk catat riwayat alergi pasien (obat, makanan, lingkungan).

## Kapan Dipakai

- Pasien punya riwayat alergi yang harus di-track lintas kunjungan
- Penting untuk safety check sebelum prescribe obat
- Update saat ada alergi baru ditemukan atau status berubah (active → resolved)

## Quick Example

```php
use Satusehat\Integration\FHIR\AllergyIntolerance;

$allergy = new AllergyIntolerance();

$allergy->setIdentifier('ALG-001');
$allergy->setPatient('P02478375538', 'Budi Setiawan');
$allergy->setEncounter('E12345');
$allergy->setStatus('active');
$allergy->setCategory(['medication']);
$allergy->setCoding(
    'http://www.whocc.no/atc',
    'J01CA04',
    'Amoxicillin',
    'Alergi Amoxicillin (gatal-gatal seluruh badan)'
);
$allergy->setRecorder('10009880728', 'dr. Ahmad');

[$statusCode, $response] = $allergy->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setPatient($patientId, $name)` | Pasien (catatan: pake `setPatient`, bukan `setSubject`) |
| `setCoding($system, $code, $display, $text)` | Kode alergi (ATC untuk obat, SNOMED untuk lainnya) |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setIdentifier($id)` | — | ID internal di sistem kamu |
| `setEncounter($encounterId)` | — | Kunjungan saat alergi dicatat |
| `setCategory(array $categories)` | — | Array of `food` / `medication` / `environment` / `biologic` |
| `setStatus($status)` | `active` (default tidak auto-set) | `active` / `inactive` / `resolved` |
| `setRecorder($practId, $name)` | — | Nakes yang catat. Auto-set `recordedDate` ke now (UTC). |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Catatan Penting

- **Method-nya `setPatient`, bukan `setSubject`** — beda dari resource lain. FHIR spec untuk AllergyIntolerance pake field `patient`, bukan `subject`.
- **`setStatus` pake PHP `match`** — kalau pass value di luar `active`/`inactive`/`resolved` akan throw `UnhandledMatchError` (PHP 8.0+).
- **`verificationStatus=confirmed` default** sudah di-set di constructor, nggak bisa diubah via method. Edit payload manual kalau butuh.
- **Kategori multiple** — `setCategory(['medication', 'food'])` valid untuk pasien yang alergi obat + makanan.
- **`setRecorder` set 2 field** — `recorder` (reference ke practitioner) + `recordedDate` (timestamp now UTC).
- **`json()` nggak validate field wajib** — pastikan minimal `setPatient` + `setCoding` sebelum post.

## Lihat Juga

- [Patient](patient.md) — pasien parent
- [Practitioner](practitioner.md) — recorder
- [MedicationRequest](medication-request.md) — cross-check alergi sebelum resep
