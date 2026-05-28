# MedicationDispense

Resource untuk penyerahan obat dari farmasi ke pasien (eksekusi dari `MedicationRequest`).

## Kapan Dipakai

- Apoteker serahin obat ke pasien
- Setiap item obat yang diserahkan = 1 MedicationDispense (bukan per pasien)
- Catat substitusi obat (kalau resep diganti dengan brand lain)

## Quick Example

```php
use Satusehat\Integration\FHIR\MedicationDispense;

$disp = new MedicationDispense();

$disp->setIdentifier('DISP-2026-001');
$disp->setIdentifierItem('DISP-2026-001-IT01');
$disp->setStatus('completed');
$disp->setCategory('outpatient', 'Outpatient');
$disp->setSubject('P02478375538', 'Budi Setiawan');
$disp->setEncounter('E12345');
$disp->setPerformer('10009880728', 'Apt. Siti'); // apoteker
$disp->setReference('M001', 'Paracetamol 500 mg tablet');
$disp->setLocation('LOC-FARMASI', 'Farmasi RSUD Sragen');
$disp->setAuthorizingPrescription('MR12345'); // ID MedicationRequest
$disp->setPrepared();
$disp->setHandedOver();
$disp->setSubtitution(false);
$disp->setIntruction(1, '3x sehari setelah makan, habiskan');

[$statusCode, $response] = $disp->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId)` | Kunjungan |
| `setPerformer($practitionerId, $name)` | Apoteker yang menyerahkan |
| `setReference($medId, $display)` | Reference ke Medication |
| `setLocation($locId, $display)` | Lokasi penyerahan (farmasi mana) |
| `setAuthorizingPrescription($mrId)` | ID MedicationRequest (resep yang dieksekusi) |

### Status & Category

| Method | Default | Pilihan |
|---|---|---|
| `setStatus($status)` | `completed` | `active` / `completed` |
| `setCategory($code, $display)` | `community` / Community | inpatient / outpatient / community / discharge |

### Identifier

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID dispense (header) |
| `setIdentifierItem($id)` | ID per item dispense |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setContained(array $medication)` | — | Inline obat racikan |
| `setPrepared($dateTime)` | now() local time | Kapan obat disiapin |
| `setHandedOver($dateTime)` | now() UTC | Kapan diserahkan ke pasien |
| `setSubtitution(bool $sub)` | — | Apakah ada substitusi obat |
| `setIntruction($sequence, $text)` | — | Aturan pakai (sederhana, sequence number + text) |

### Output Method

- `json()` / `post()` / `put($id)` / `patch($id, $payload)`

## Catatan Penting

- **`setStatus` strict** — cuma `active` atau `completed`. Throw `FHIRException` kalau lainnya.
- **Bug spelling di method name:** `setSubtitution` (harusnya `setSubstitution`) dan `setIntruction` (harusnya `setInstruction`). Tetap dipake apa adanya untuk backward compat.
- **`setHandedOver` salah set field** — di source code, value-nya nge-replace `whenPrepared` (bukan `whenHandedOver`). Workaround: panggil `setPrepared()` setelah `setHandedOver()` kalau butuh dua-duanya. *Bug ini ada di package, akan diperbaiki di update mendatang.*
- **`setEncounter` pake `context.reference`** (bukan `encounter.reference` seperti resource lain). Konsisten dengan FHIR R4 spec untuk MedicationDispense.
- **Contained medication auto-link** — sama kayak MedicationRequest, kalau pake `setContained()` reference auto-replace ke `#<id>` di `json()`.
- **`json()` nggak validate field wajib** — pastikan semua di-set sebelum post.

## Lihat Juga

- [MedicationRequest](medication-request.md) — resep yang di-dispense
- [Medication](medication.md) — master obat
