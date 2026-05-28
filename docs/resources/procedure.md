# Procedure

Resource untuk tindakan medis yang dilakukan ke pasien (ICD-9-CM).

## Kapan Dipakai

- Catat tindakan medis (operasi, injeksi, jahit luka, EKG, dll)
- Tracking treatment yang dilakukan saat encounter

## Quick Example

```php
use Satusehat\Integration\FHIR\Procedure;

$procedure = new Procedure();

$procedure->setSubject('P02478375538', 'Budi Setiawan');
$procedure->setEncounter('E12345');
$procedure->addCode('86.59', 'Closure of skin and subcutaneous tissue of other sites');
$procedure->addPerformer('10009880728', 'dr. Ahmad');
$procedure->setStatus('completed');

[$statusCode, $response] = $procedure->post();
```

## Method Reference

### Field Wajib (untuk valid FHIR)

| Method | Deskripsi |
|---|---|
| `setSubject($patientId, $name)` | Pasien |
| `setEncounter($encounterId, $display=null, $bundle=false)` | Kunjungan |
| `addCode($icd9Code, $display=null)` | **Validasi ICD-9-CM otomatis** |
| `addPerformer($practitionerId, $name)` | Nakes yang melakukan tindakan |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setStatus($status)` | `completed` | preparation / in-progress / not-done / on-hold / stopped / completed / entered-in-error / unknown |

### Output Method

- `json()` / `post()` / `put($id)`

## Catatan Penting

- **`addCode` strict ICD-9-CM** — code di-cek ke tabel `icd9`. Kode invalid → `FHIRException("Kode ICD 9 tidak ditemukan")`.
- **`json()` tidak validate field wajib** secara explicit — kalau lupa setSubject/setEncounter, JSON tetap generate tapi SATUSEHAT akan reject. Pastikan semua field di-set.
- **`addPerformer` pake actor wrapper** — beda dari Encounter yang langsung pake reference flat.

## Lihat Juga

- [Encounter](encounter.md) — kunjungan parent
- [Condition](condition.md) — diagnosis sebelum tindakan
