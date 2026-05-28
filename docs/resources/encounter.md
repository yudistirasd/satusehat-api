# Encounter

Resource untuk kunjungan pasien (rajal, IGD, rawat inap, telekonsultasi, dll).

## Kapan Dipakai

- Setiap kali pasien datang berkunjung (apapun jenisnya)
- Sebelum bisa kirim `Condition`, `Procedure`, `Observation`, `MedicationRequest` (semua perlu reference encounter)
- Tracking durasi kunjungan (arrived → in-progress → finished)

## Quick Example

```php
use Satusehat\Integration\FHIR\Encounter;

$encounter = new Encounter();

// 1. Set status (state machine: arrived → in-progress → finished)
$encounter->setArrived('2026-05-28 08:00:00');
$encounter->setInProgress('2026-05-28 08:15:00', '2026-05-28 08:45:00');
$encounter->setFinished('2026-05-28 08:50:00');

// 2. Tipe kunjungan
$encounter->setConsultationMethod('RAJAL'); // RAJAL/IGD/RANAP/HOMECARE/TELEKONSULTASI

// 3. Pasien yang berkunjung
$encounter->setSubject('P02478375538', 'Budi Setiawan');

// 4. Nakes yang menangani
$encounter->addParticipant('10009880728', 'dr. Ahmad');

// 5. Lokasi (poli/ruangan)
$encounter->addLocation('LOC001', 'Poli Umum');

// Kirim ke SATUSEHAT
[$statusCode, $response] = $encounter->post();
```

## Method Reference

### Status Lifecycle (Wajib)

Encounter pake state machine, harus dipanggil berurutan:

| Method | Deskripsi |
|---|---|
| `setArrived($timestamp)` | Pasien datang. **Wajib pertama**. |
| `setInProgress($start, $end)` | Sedang ditangani. Hanya jalan kalau `setArrived` sudah dipanggil. |
| `setFinished($timestamp)` | Selesai. |

Format timestamp: apapun yang `strtotime()` bisa parse (`'2026-05-28 08:00:00'`, ISO 8601, dll).

### Field Wajib Lainnya

| Method | Deskripsi |
|---|---|
| `setConsultationMethod($method)` | `RAJAL` / `IGD` / `RANAP` / `HOMECARE` / `TELEKONSULTASI` |
| `setSubject($patientId, $name)` | Pasien (Patient ID dari SATUSEHAT) |
| `addParticipant($practitionerId, $name)` | Nakes yang menangani |
| `addLocation($locationId, $name)` | Poli/ruangan |

### Field Opsional

| Method | Deskripsi |
|---|---|
| `addRegistrationId($id)` | ID registrasi internal (no. RM, no. antrian, dll) |
| `setServiceType($system, $code, $display)` | Jenis pelayanan |
| `setServiceProvider()` | Auto-set ke organization_id kamu (otomatis dipanggil di `json()` kalau belum) |
| `addDiagnosis($conditionId, $icd10, $display, $bundle)` | Reference ke Condition. Validasi ICD-10 otomatis. |

### Output Method

| Method | Return | Deskripsi |
|---|---|---|
| `json()` | `string` | Generate JSON FHIR |
| `post()` | `[int, object]` | Create encounter baru |
| `put($id)` | `[int, object]` | Update full encounter |
| `patch($id, $payload)` | `[int, object]` | Update partial |

## Tipe Kunjungan (`setConsultationMethod`)

| Input | Class Code | Display |
|---|---|---|
| `RAJAL` | AMB | Ambulatory (rawat jalan) |
| `IGD` | EMER | Emergency |
| `RANAP` | IMP | Inpatient encounter |
| `HOMECARE` | HH | Home health |
| `TELEKONSULTASI` | TELE | Teleconsultation |

## Catatan Penting

- **State machine strict** — kalau panggil `setInProgress` tanpa `setArrived` dulu, return string error (bukan exception). Selalu mulai dari `setArrived`.
- **Idempotent** — panggil `setArrived` 2x nggak duplicate, dia cek dulu via `statusHistoryValidate`.
- **Diagnosis bisa skip** kalau encounter belum punya diagnosis (misal IGD baru triase). Tapi kalau encounter selesai tanpa diagnosis, `Condition` harus di-create terpisah dengan reference ke encounter.
- **`addDiagnosis` validasi ICD-10** — kalau code-nya nggak valid, return string error `"Kode ICD-10 invalid"`. Pake [browser ICD-10](https://icd.who.int/browse10/) atau database `icd10` yang ke-include di package.
- **`json()` kembalikan string error**, bukan throw exception, kalau ada field wajib kurang. Cek dengan `is_string(json_decode($x))` atau pake `try-catch` kalau pakai `post()`.
- Field wajib di `json()`: `status`, `class`, `subject`, `participant`, `location`. Kurang salah satu → error string.

## Lihat Juga

- [Patient](patient.md) — bikin pasien dulu sebelum encounter
- [Condition](condition.md) — diagnosis
- [Procedure](procedure.md) — tindakan medis
- [Bundle](bundle.md) — kirim Encounter + Condition sekaligus dalam 1 transaction
- [Workflow: Alur Pasien Baru](../workflows/alur-pasien-baru.md)
