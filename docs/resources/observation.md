# Observation

Resource untuk observasi/pemeriksaan: vital signs (TD, suhu, nadi, RR) atau hasil lab.

## Kapan Dipakai

- Catat vital signs pasien (TTV) saat kunjungan
- Catat hasil pemeriksaan lab
- Wajib reference ke encounter (jadi bagian dari kunjungan)

## Quick Example: Vital Signs

```php
use Satusehat\Integration\FHIR\Observation;

$obs = new Observation();
$obs->setStatus('final')
    ->addCategory('vital-signs')
    ->addCode('8480-6', 120)              // sistol 120 mmHg
    ->setSubject('P02478375538', 'Budi Setiawan')
    ->setPerformer('10009880728', 'dr. Ahmad')
    ->setEncounter('E12345');

$obs->setEffectiveDateTime();             // default: now (UTC)

[$statusCode, $response] = $obs->post();
```

## Quick Example: Hasil Lab

```php
$obs = new Observation();
$obs->setStatus('final')
    ->addCategory('laboratory')
    ->addCode('LA19710-5', 'Group A', 'laboratory', 'ABO Group')
    ->setSubject('P02478375538', 'Budi Setiawan')
    ->setPerformer('10009880728', 'dr. Ahmad')
    ->setEncounter('E12345');

$obs->setSpecimen('SPEC001');             // wajib untuk laboratory
$obs->setServiceRequest('SR001');         // wajib untuk laboratory
$obs->setEffectiveDateTime();

[$statusCode, $response] = $obs->post();
```

## Method Reference

Semua setter return `Observation` instance jadi bisa di-chain (`->`).

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setStatus($status)` | Default `final`. Lainnya: registered/preliminary/amended/corrected/cancelled/entered-in-error/unknown |
| `addCategory($category)` | `vital-signs` atau `laboratory` |
| `addCode($loincCode, $value, $type, $loincDisplay)` | LOINC code + value (lihat tabel di bawah) |
| `setSubject($patientId, $name)` | Pasien |
| `setPerformer($practitionerId, $name)` | Nakes yang melakukan |
| `setEncounter($encounterId, $display=null)` | Kunjungan |

### Field Wajib KHUSUS Lab (kategori `laboratory`)

| Method | Deskripsi |
|---|---|
| `setSpecimen($specimenId)` | Reference ke Specimen |
| `setServiceRequest($serviceRequestId)` | Reference ke ServiceRequest |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setEffectiveDateTime($dateTime)` | now() UTC | Kapan diobservasi |

### Output Method

- `json()` / `post()` / `put($id)`

## LOINC Code untuk Vital Signs (built-in)

Package udah handle unit + display untuk 5 LOINC code ini:

| LOINC Code | Display | Unit |
|---|---|---|
| `8480-6` | Systolic blood pressure | mm[Hg] |
| `8462-4` | Diastolic blood pressure | mm[Hg] |
| `8867-4` | Heart rate | {beats}/min |
| `8310-5` | Body temperature | Cel |
| `9279-1` | Respiratory rate | breaths/min |

Cara pake: `$obs->addCode('8480-6', 120);` — value-nya 120, otomatis di-wrap ke `valueQuantity` dengan unit yang bener.

## Pemeriksaan Lab dengan `addCode`

Untuk lab, parameter `$type='laboratory'` dan `$loincDisplay` wajib:

```php
$obs->addCode(
    '5778-6',                  // LOINC code
    'Yellow',                  // value (string hasil)
    'laboratory',              // type
    'Color of Urine'           // display LOINC
);
```

Hasil di JSON pake `valueCodeableConcept` (bukan `valueQuantity`).

## Catatan Penting

- **Semua field wajib divalidate di `json()`** — kurang salah satu → throw `FHIRMissingProperty`.
- **Lab tanpa specimen+serviceRequest** → throw exception. Kalau cuma vital-signs, dua field itu nggak perlu.
- **`addCode` switch hardcoded** — LOINC code di luar 5 vital signs di atas, kalau type bukan `laboratory`, akan generate JSON dengan code/display null. Aman pake type `laboratory` untuk semua selain 5 vital signs.
- **`setEffectiveDateTime()` set 2 field**: `effectiveDateTime` dan `issued` dengan value sama.

## Lihat Juga

- [ServiceRequest](service-request.md) — permintaan lab (parent dari observation lab)
- [Specimen](specimen.md) — sampel lab
- [DiagnosticReport](diagnostic-report.md) — laporan summary lab
- [Workflow: Alur Laboratorium](../workflows/alur-laboratorium.md)
