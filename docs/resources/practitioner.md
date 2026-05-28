# Practitioner

Resource untuk cari data nakes by NIK. **Read-only** — package ini hanya support GET (search), bukan POST/PUT.

## Kapan Dipakai

- Cari Practitioner ID SATUSEHAT pakai NIK nakes
- Validasi nakes existing sebelum dipakai sebagai performer/participant
- Caching data nakes ke database lokal

## Quick Example

```php
use Satusehat\Integration\FHIR\Practitioner;

$practitioner = new Practitioner();
$result = $practitioner->getSSNik('3174012345678901');

if ($result === null) {
    // Nakes nggak ditemukan di SATUSEHAT
    return;
}

$id   = $practitioner->getId();              // "10009880728"
$nama = $practitioner->getName();            // "dr. Ahmad"
$gen  = $practitioner->getGender();          // "male"
$birt = $practitioner->getBirthDate();       // "1985-05-20"
$qual = $practitioner->getQualificationValue(); // STR/SIP value
$line = $practitioner->getAddressLine();
$city = $practitioner->getCity();
$vill = $practitioner->getVillage();
```

## Method Reference

| Method | Return | Deskripsi |
|---|---|---|
| `getSSNik($nik)` | `object\|null` | Cari by NIK. Return raw resource atau null. |
| `getId()` | `string\|null` | Practitioner ID SATUSEHAT |
| `getName()` | `string\|null` | Nama nakes |
| `getGender()` | `string\|null` | `male` / `female` |
| `getBirthDate()` | `string\|null` | Format `YYYY-MM-DD` |
| `getQualificationValue()` | `string\|null` | Kode STR/SIP |
| `getAddressLine()` | `string\|null` | Alamat |
| `getCity()` | `string\|null` | Kode kota |
| `getVillage()` | `string\|null` | Kode kelurahan |

## Catatan Penting

- **Wajib panggil `getSSNik()` dulu** sebelum getter lain. Kalau belum, semua getter return `null`.
- **Cache result-nya** di database lokal kamu. SATUSEHAT punya rate limit, dan data nakes jarang berubah.
- **Untuk testing di env DEV**, pake daftar `practitioner_dev` di [Getting Started](../01-getting-started.md). NIK dari production nggak akan ketemu di sandbox.
- Kalau `getSSNik()` return null bisa karena NIK belum terdaftar di SATUSEHAT, atau request gagal (cek log untuk detail).

## Lihat Juga

- [Encounter](encounter.md) — pakai Practitioner ID sebagai participant
- [Condition](condition.md), [Procedure](procedure.md) — pakai sebagai recorder/performer
