# MedicationRequest

Resource untuk resep obat dari nakes ke pasien.

## Kapan Dipakai

- Dokter resepin obat ke pasien
- Setiap encounter yang ada resep, satu MedicationRequest per item obat (bukan per resep)
- Untuk dispensing/farmasi → gunakan [MedicationDispense](medication-dispense.md)

## Quick Example: Resep dengan Medication Reference

```php
use Satusehat\Integration\FHIR\MedicationRequest;

$req = new MedicationRequest();

$req->setIdentifier('RESEP-2026-001');
$req->setIdentifierItem('RESEP-2026-001-IT01');
$req->setStatus('active');
$req->setIntent('order');
$req->setSubject('P02478375538', 'Budi Setiawan');
$req->setEncounter('E12345');
$req->setRequester('10009880728', 'dr. Ahmad');
$req->setReference('M001', 'Paracetamol 500 mg tablet');
$req->setAuthoredOn();

$req->setDosageInstruction(
    '3x sehari setelah makan',  // aturanPakai
    'Habiskan',                  // keterangan
    3,                           // frekuensi
    1                            // periode (per X hari)
);

$req->setDispenseRequest(
    [],                                          // dispenseInterval
    [],                                          // validityPeriod
    0,                                           // numberOfRepeatsAllowed
    ['value' => 10, 'unit' => 'tablet'],         // quantity
    []                                           // expectedSupplyDuration
);

[$statusCode, $response] = $req->post();
```

## Quick Example: Resep dengan Contained Medication (Racikan)

```php
use Satusehat\Integration\FHIR\Medication;
use Satusehat\Integration\FHIR\MedicationRequest;

$med = new Medication();
$med->setStatus('active');
$med->setCode('RACIKAN-001', 'Puyer Demam');
$med->setForm('POW', 'Powder');

$req = new MedicationRequest();
$req->setContained($med);  // include Medication inline
$req->setIdentifier('RESEP-2026-002');
// ... setSubject, setEncounter, setRequester, dll
// Note: setReference() nggak perlu, auto-set ke contained ID

[$statusCode, $response] = $req->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId)` | Kunjungan |
| `setRequester($practitionerId, $name)` | Dokter peresep |
| `setReference($medId, $display)` | Reference ke Medication. **Skip kalau pake `setContained()`.** |

### Status & Intent

| Method | Default | Pilihan |
|---|---|---|
| `setStatus($status)` | `active` | active / on-hold / ended / stopped / completed / cancelled / entered-in-error / draft / unknown |
| `setIntent($intent)` | `order` | proposal / plan / order / original-order / reflex-order / filler-order / instance-order / option |

### Identifier (Recommended)

| Method | Deskripsi |
|---|---|
| `setIdentifier($id)` | ID resep (header). System: `prescription/<orgId>` |
| `setIdentifierItem($id)` | ID per item resep. System: `prescription-item/<orgId>` |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setContained(Medication $med)` | — | Inline Medication (racikan/non-KFA), auto-link via `#id` |
| `setAuthoredOn($dateTime)` | now() UTC | Kapan resep dibuat |
| `setCategory($code, $display)` | `community` | inpatient / outpatient / community / discharge |
| `setDosageInstruction($aturan, $keterangan, $frekuensi, $periode)` | — | Aturan pakai (default route Oral) |
| `setDispenseRequest($interval, $validity, $repeats, $quantity, $duration)` | — | Detail dispensing (jumlah, interval, dll) |

### Output Method

| Method | Return | Deskripsi |
|---|---|---|
| `toArray()` | `array` | Get raw payload |
| `json()` | `string` | Generate JSON. Auto-replace medicationReference kalau pake contained. |
| `post()` / `put($id)` / `patch($id, $payload)` | `[int, object]` | CRUD |

## Catatan Penting

- **Contained medication auto-handle reference** — kalau `setContained()` dipanggil, `medicationReference` di-replace ke `#<contained_id>` saat `json()`. Jangan panggil `setReference()` juga.
- **Default route `Oral`** di `setDosageInstruction`. Untuk route lain (injeksi, topical, dll), butuh modify payload manual setelah method dipanggil.
- **`courseOfTherapyType` default `acute`** sudah di-set di constructor. Untuk continuous/chronic, edit payload manual.
- **`setStatus` & `setIntent` strict** — value di luar daftar throw `FHIRException`.
- **`setDispenseRequest` accept array kosong** — kalau parameter di-pass `[]`, field itu nggak di-set (skip). Berguna kalau cuma mau set quantity tanpa interval/validity.

## Lihat Juga

- [Medication](medication.md) — master obat
- [MedicationDispense](medication-dispense.md) — penyerahan obat di farmasi
