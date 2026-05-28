# Patient

Resource untuk data pasien (demografi, alamat, kontak, status pernikahan, dll).

## Kapan Dipakai

- Daftarin pasien baru ke SATUSEHAT
- Sinkronisasi data pasien dari sistem lokal kamu ke SATUSEHAT
- Wajib dibuat **sebelum** bisa pakai `Encounter`, `Condition`, dan resource klinis lainnya

## Quick Example

```php
use Satusehat\Integration\FHIR\Patient;

$patient = new Patient();

// Field wajib
$patient->addIdentifier('nik', '3174012345678901');
$patient->setName('Budi Setiawan');
$patient->addTelecom('081234567890');
$patient->setMultipleBirth(1); // anak ke-1
$patient->setAddress([
    'address'      => 'Jl. Mawar No. 10',
    'city'         => 'Jakarta Selatan',
    'postalCode'   => '12345',
    'country'      => 'ID',
    'provinceCode' => '31',
    'cityCode'     => '3174',
    'districtCode' => '317401',
    'villageCode'  => '3174011001',
    'rt'           => '001',
    'rw'           => '002',
]);

// Field opsional (tapi recommended)
$patient->setGender('male');
$patient->setBirthDate('1990-01-15');
$patient->setMaritalStatus('Married');

// Generate JSON (validasi otomatis)
$json = $patient->json();

// Atau langsung POST ke SATUSEHAT
[$statusCode, $response] = $patient->post();
```

## Method Reference

### Field Wajib

| Method | Deskripsi |
|---|---|
| `addIdentifier($type, $value)` | Identifier pasien. **`$type` cuma boleh `'nik'` atau `'nik-ibu'`**. Lainnya bakal throw exception. |
| `setName($name)` | Nama lengkap pasien (1 string utuh, bukan first/last name) |
| `addTelecom($value, $system='phone', $use='mobile')` | Nomor kontak. Default phone+mobile. |
| `setAddress(array $detail)` | Alamat lengkap. Lihat struktur array di bawah. |
| `setMultipleBirth($value)` | Anak ke berapa. **Wajib integer** kalau mau lolos validasi `json()`. |

### Field Opsional

| Method | Deskripsi |
|---|---|
| `setGender($gender)` | `'male'` atau `'female'` |
| `setBirthDate($date)` | Format `YYYY-MM-DD` |
| `setDeceased(bool $bool)` | `true` kalau pasien sudah meninggal |
| `setMaritalStatus($status)` | `'Married'`, `'Unmarried'`, `'Divorced'`, `'Never'`, `'Widowed'` (case-insensitive) |
| `setEmergencyContact($name, $phone)` | Kontak darurat |
| `setCommunication($code='id-ID', $display='Indonesian', $preferred=true)` | Bahasa pasien |
| `setExtension($birthCity, $birthCountry, $citizenship)` | Tempat lahir + status kewarganegaraan (`WNI`/`WNA`) |

### Output Method

| Method | Return | Deskripsi |
|---|---|---|
| `json()` | `string` | Generate JSON FHIR (validasi field wajib) |
| `post()` | `[int, object]` | Kirim ke SATUSEHAT, return `[statusCode, responseBody]` |

## Struktur Array Address

```php
[
    'address'      => 'Jl. Mawar No. 10',     // teks alamat
    'city'         => 'Jakarta Selatan',
    'postalCode'   => '12345',
    'country'      => 'ID',
    'provinceCode' => '31',                    // 2 digit
    'cityCode'     => '3174',                  // 4 digit
    'districtCode' => '317401',                // 6 digit
    'villageCode'  => '3174011001',            // 10 digit
    'rt'           => '001',
    'rw'           => '002',
]
```

Kode wilayah ngikutin **Kode Wilayah Kemendagri**. Bisa pake helper `KodeWilayahIndonesia` (sudah include di package) untuk lookup.

## Custom Marital Status

Kalau status nikah pasien nggak masuk 5 preset di atas, pake parameter ke-2 dan ke-3:

```php
$patient->setMaritalStatus('', 'UNK', 'Unknown');
```

Reference value set: [HL7 Marital Status](https://www.hl7.org/fhir/valueset-marital-status.html).

## Output JSON

```json
{
    "resourceType": "Patient",
    "meta": {
        "profile": [
            "https://fhir.kemkes.go.id/r4/StructureDefinition/Patient"
        ]
    },
    "active": true,
    "identifier": [
        {
            "use": "official",
            "system": "https://fhir.kemkes.go.id/id/nik",
            "value": "3174012345678901"
        }
    ],
    "name": [
        {
            "use": "official",
            "text": "Budi Setiawan"
        }
    ],
    "telecom": [
        {
            "system": "phone",
            "value": "081234567890",
            "use": "mobile"
        }
    ],
    "gender": "male",
    "birthDate": "1990-01-15",
    "address": [...],
    "maritalStatus": {...},
    "multipleBirthInteger": 1
}
```

## Catatan Penting

- **`addIdentifier` strict** — cuma terima `'nik'` atau `'nik-ibu'`. Identifier lain (passport, KK, dll) belum di-support.
- **`setMultipleBirth` wajib integer** kalau mau lolos `json()`. Walau method ini sebenernya juga terima boolean, validator di `json()` cek `multipleBirthInteger` aja. Pake `1` kalau pasien anak tunggal/pertama.
- **Field wajib di `json()`:** `identifier`, `name`, `address`, `telecom`, `multipleBirthInteger`. Kurang salah satu → `FHIRException`.
- **NIK Ibu** (`'nik-ibu'`) dipakai khusus untuk bayi yang belum punya NIK sendiri (pakai NIK ibu dulu).
- Response sukses dari `post()` mengembalikan `Patient ID` SATUSEHAT yang harus kamu simpan di database lokal — dipake sebagai `subjectId` di resource lain (Encounter, Condition, dll).

## Lihat Juga

- [Encounter](encounter.md) — kunjungan pasien
- [Practitioner](practitioner.md) — cari ID praktisi/nakes
- [Workflow: Alur Pasien Baru](../workflows/alur-pasien-baru.md) — end-to-end Patient → Encounter → Diagnosis
