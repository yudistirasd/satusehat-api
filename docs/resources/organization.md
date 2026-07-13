# Organization

Resource untuk fasyankes (RS, klinik, puskesmas) atau departemen di dalamnya.

## Kapan Dipakai

- Daftar fasyankes/departemen ke SATUSEHAT (sekali aja)
- Reference dari `Encounter::serviceProvider`, `Location::managingOrganization`
- Hierarki organisasi (e.g., RS → Departemen → Sub-departemen)

## Quick Example

```php
use Satusehat\Integration\FHIR\Organization;

$org = new Organization();

$org->addIdentifier('RSUD-SRAGEN-001');
$org->setName('RSUD Sragen');
$org->setType('prov');                      // prov atau dept
$org->setOperationalStatus('active');
$org->addPhone('0271-123456');
$org->addEmail('admin@rsud-sragen.go.id');
$org->addUrl('https://rsud-sragen.go.id');
$org->addAddress(
    'Jl. Raya Sragen No. 1',
    '57211',
    'Sragen',
    '3314011001'                            // village_code
);

[$statusCode, $response] = $org->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `addIdentifier($identifier)` | ID internal organisasi |
| `setName($name)` | Nama fasyankes |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setType($type)` | `dept` | `prov` (Healthcare Provider) atau `dept` (Hospital Department). Lainnya throw `FHIRException`. |
| `setOperationalStatus($status)` | env `OPERATIONAL_STATUS` / `active` | Status operasional Kemenkes |
| `setPartOf($parentOrgId)` | organization_id | Organisasi parent |
| `addPhone($phone)`, `addEmail($email)`, `addUrl($url)` | env vars PHONE/EMAIL/WEBSITE | Kontak |
| `addAddress(...)` | env vars | Alamat lengkap |

### Output Method

- `json()` / `post()` / `put($id)`

## Tipe Organization (`setType`)

| Code | Display |
|---|---|
| `prov` | Healthcare Provider (untuk fasyankes) |
| `dept` | Hospital Department (untuk departemen) |

Strict validation — selain `prov`/`dept` throw exception.

## Catatan Penting

- **Field wajib di `json()`**: `identifier` + `name`. Kurang salah satu return string error.
- **`active=true` default** sudah di-set di constructor.
- **Default type `dept`** kalau `setType()` nggak dipanggil. Untuk fasyankes utama, pake `setType('prov')`.
- **`setPartOf()` default ke organization_id** kalau dipanggil tanpa parameter — sub-departemen otomatis nested di fasyankes parent.
- **Default fallback ke env vars** — `addAddress()`, `addPhone()`, `addEmail()`, `addUrl()` ambil dari env kalau parameter null. Useful untuk fasyankes yang konfigurasi via .env.
- **Bug minor di `setType`** — display selalu ambil dari index `prov` (Healthcare Provider) walaupun type-nya `dept`. Cek payload sebelum post.

## Lihat Juga

- [Location](location.md) — lokasi spesifik di organisasi
- [Encounter](encounter.md) — auto-reference Organization sebagai serviceProvider
