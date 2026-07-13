# Medication

Resource untuk master data obat. Reference ke Kamus Farmasi & Alkes (KFA) Kemenkes.

## Kapan Dipakai

- Daftar obat baru ke SATUSEHAT (sekali aja per obat)
- Reference dari `MedicationRequest` (resep) dan `MedicationDispense` (penyerahan)
- Atau pakai `setContained()` di MedicationRequest untuk obat racikan/non-KFA (tanpa perlu post Medication terpisah)

## Quick Example

```php
use Satusehat\Integration\FHIR\Medication;

$med = new Medication();

$med->setIdentifier('OBAT-001');
$med->setStatus('active');
$med->setCode('92000054', 'Paracetamol 500 mg tablet'); // KFA code
$med->setForm('TAB', 'Tablet');
$med->setManufacturer(); // auto-set ke organization_id

[$statusCode, $response] = $med->post();
```

## Method Reference

### Field Standard

| Method | Deskripsi |
|---|---|
| `setIdentifier($identifier)` | ID internal obat di sistem kamu |
| `setStatus($status)` | `active` / `inactive` / `entered-in-error`. Default `active`. |
| `setCode($kfaCode, $display)` | Kode KFA + nama obat |
| `setForm($code, $display)` | Bentuk sediaan (TAB/CAP/SYR/dll) — Kemenkes medication-form code |
| `setManufacturer()` | Auto-set Organization reference ke organization_id kamu |

### Output Method

| Method | Return | Deskripsi |
|---|---|---|
| `getPayload($key=null)` | `array` | Get raw payload (full atau by key) |
| `toArray()` | `array` | Same as `getPayload()` |
| `json()` | `string` | Generate JSON FHIR |
| `post()` | `[int, object]` | Create medication baru |
| `put($id)` | `[int, object]` | Update full |

## Catatan Penting

- **`setStatus` strict** — cuma `active`, `inactive`, atau `entered-in-error`. Lainnya throw `FHIRException`.
- **Default extension `MedicationType=NC`** (Non-compound) sudah di-set di constructor.
- **`json()` nggak validate field wajib** — pastikan kamu set semua yang diperlukan.
- **Untuk obat racikan** (compound), pake `MedicationRequest::setContained()` instead of post Medication terpisah.

## Lihat Juga

- [MedicationRequest](medication-request.md) — resep
- [MedicationDispense](medication-dispense.md) — penyerahan obat
