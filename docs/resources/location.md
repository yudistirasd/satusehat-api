# Location

Resource untuk lokasi spesifik di fasyankes (poli, ruangan, bangsal, bed).

## Kapan Dipakai

- Daftar lokasi di fasyankes (sekali aja per lokasi)
- Reference dari `Encounter::addLocation()`
- Hierarki: Building → Wing → Room → Bed (bisa nested via `partOf`)

## Quick Example

```php
use Satusehat\Integration\FHIR\Location;

$loc = new Location();

$loc->addIdentifier('POLI-UMUM-01');
$loc->setName('Poli Umum');
$loc->setStatus('active');
$loc->setOperationalStatus('U');                  // Unoccupied
$loc->addPhone('081234567890');
$loc->setAddress(
    'Jl. Mawar No. 10',
    '12345',
    'Sragen',
    '3174011001'                                  // village_code (8-10 digit)
);
$loc->addPhysicalType('ro');                      // Room
$loc->addPosition('-7.4267', '111.0226');
$loc->setManagingOrganization('YOUR-ORG-ID');

[$statusCode, $response] = $loc->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `addIdentifier($identifier)` | ID internal lokasi |
| `setName($name, $description=null)` | Nama lokasi. Description default = name. |

### Field Opsional

| Method | Default | Deskripsi |
|---|---|---|
| `setStatus($status)` | `active` | active / suspended / inactive |
| `setOperationalStatus($code)` | `U` | U/O/C/H/I/K (lihat tabel) |
| `addPhone($phone)`, `addEmail($email)`, `addUrl($url)` | env vars | Kontak lokasi |
| `setAddress(...)` | env vars | Alamat lengkap (line/postal/city/village_code) |
| `addPhysicalType($code)` | `ro` (Room) | bu/wi/co/ro/ve/ho/ca/rd/area/bd |
| `addPosition($lat, $long)` | env vars LATITUDE/LONGITUDE | GPS coordinates |
| `setManagingOrganization($orgId)` | organization_id (auto) | Organisasi parent |
| `setPartOf($parentLocId)` | — | Lokasi parent (untuk nested location) |

### Output Method

- `json()` / `post()` / `put($id)`

## Operational Status (`setOperationalStatus`)

| Code | Display |
|---|---|
| `U` | Unoccupied |
| `O` | Occupied |
| `C` | Closed |
| `H` | Housekeeping |
| `I` | Isolated |
| `K` | Contaminated |

## Physical Type (`addPhysicalType`)

| Code | Display |
|---|---|
| `bu` | Building |
| `wi` | Wing |
| `co` | Corridor |
| `ro` | Room |
| `ve` | Vehicle |
| `ho` | House |
| `ca` | Cabinet |
| `rd` | Road |
| `area` | Area |
| `bd` | Bed |

## Catatan Penting

- **Field wajib di `json()`**: `name` + `identifier`. Kurang salah satu return string error.
- **Default fallback ke env vars** — `setAddress()` dan `addPosition()` ambil dari env (`ALAMAT`, `KOTA`, `KODEPOS`, `LATITUDE`, `LONGITUDE`) kalau parameter null.
- **`village_code` di setAddress** dipakai derive 4 level kode wilayah (province/city/district/village). Kalau null, fallback ke env vars `KODE_PROVINSI`/`KODE_KABUPATEN`/`KODE_KECAMATAN`/`KODE_KELURAHAN`.
- **`setManagingOrganization()` auto-set ke organization_id** kalau dipanggil tanpa parameter.
- **Hierarchy via `setPartOf`** — Misal Bed di dalam Room: bikin Room dulu, terus Bed dengan `setPartOf(roomLocationId)`.

## Lihat Juga

- [Organization](organization.md) — fasyankes parent
- [Encounter](encounter.md) — `addLocation()` untuk reference ke Location
