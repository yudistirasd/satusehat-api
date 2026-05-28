# SATUSEHAT Integration for Laravel

[![Latest Version](https://img.shields.io/packagist/v/yudistira/satusehat-api.svg?style=flat-square)](https://packagist.org/packages/yudistira/satusehat-api)
[![Total Downloads](https://img.shields.io/packagist/dt/yudistira/satusehat-api.svg?style=flat-square)](https://packagist.org/packages/yudistira/satusehat-api)
[![License](https://img.shields.io/packagist/l/yudistira/satusehat-api.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/php-v/yudistira/satusehat-api.svg?style=flat-square)](composer.json)

Library Laravel untuk integrasi SATUSEHAT Kemenkes RI. Generate FHIR-ready JSON sesuai profil [SATUSEHAT Documentation](https://satusehat.kemkes.go.id/platform/docs).

## Mengapa Package Ini?

- Open-source (MIT license), gratis dipakai termasuk untuk komersial
- Mendukung 19 FHIR resource (Patient, Encounter, Bundle, dst)
- OAuth2 token caching otomatis via Laravel Cache
- Multi-tenant via `Tenant` trait
- KYC Centang Biru
- Sandbox mode dengan ID pasien dan nakes development
- Tested di Laravel 8 sampai 12

## Quick Start

### 1. Install via Composer

```bash
composer require yudistira/satusehat-api
```

### 2. Publish Config dan Migration

```bash
php artisan vendor:publish --provider="Satusehat\Integration\SatusehatIntegrationServiceProvider"
php artisan migrate
```

### 3. Set Environment Variables

Daftarkan aplikasi Anda di [portal SATUSEHAT](https://satusehat.kemkes.go.id/) menu **Pengembang > Aplikasi**, lalu tambahkan kredensial berikut ke file `.env`:

| Variable | Deskripsi |
|---|---|
| `SATUSEHAT_ENV` | `DEV` untuk sandbox, `PROD` untuk production |
| `SATUSEHAT_CLIENT_ID` | Client ID dari portal SATUSEHAT |
| `SATUSEHAT_CLIENT_SECRET` | Client Secret dari portal SATUSEHAT |
| `SATUSEHAT_ORGANIZATION_ID` | Organization ID fasyankes Anda |

### 4. Contoh: Buat Resource Patient

```php
use Satusehat\Integration\FHIR\Patient;

$patient = new Patient();
$patient->addIdentifier('nik', '3174012345678901');
$patient->setName('Budi Setiawan');
$patient->setGender('male');
$patient->setBirthDate('1990-01-15');

$json = $patient->json();
```

### 5. Contoh: Kirim Bundle Kunjungan (Encounter + Diagnosis)

```php
use Satusehat\Integration\FHIR\Bundle;
use Satusehat\Integration\FHIR\Encounter;
use Satusehat\Integration\FHIR\Condition;

$encounter = new Encounter();
// ... isi data encounter

$diagnosis = new Condition();
// ... isi data diagnosis

$bundle = new Bundle();
$bundle->addEncounter($encounter);
$bundle->addCondition($diagnosis);

$response = $bundle->post();
```

Dokumentasi lengkap per resource ada di folder [`docs/`](docs/).

## FHIR Resources Tersedia (19)

`Patient`, `Practitioner`, `Encounter`, `Condition`, `Observation`, `Procedure`, `Medication`, `MedicationRequest`, `MedicationDispense`, `AllergyIntolerance`, `ClinicalImpression`, `Composition`, `DiagnosticReport`, `ServiceRequest`, `Specimen`, `CarePlan`, `Location`, `Organization`, `Bundle`

## Persyaratan

- PHP 7.4 atau 8.0+
- Laravel 8 / 9 / 10 / 11 / 12
- Akun developer SATUSEHAT ([daftar di sini](https://satusehat.kemkes.go.id/))

## Roadmap

- [ ] Dokumentasi lengkap per resource di `/docs`
- [ ] Contoh project Laravel end-to-end
- [ ] Test coverage untuk semua FHIR resource
- [ ] Migration helper dari format legacy

## Need Help?

Package ini gratis dan open-source. Kalau Anda butuh:

- Custom integration SATUSEHAT untuk SaaS klinik atau RS
- Konsultasi compliance SATUSEHAT
- Bridging BPJS (VClaim, PCare, Antrean) - lihat [bpjs-api](https://github.com/yudistirasd/bpjs-api)

Email: **yudistira.sd2@gmail.com**

## Contributing

Kontribusi welcome. Lihat [CONTRIBUTING.md](CONTRIBUTING.md).

Bug report dan feature request via [Issues](https://github.com/yudistirasd/satusehat-api/issues).

## Credits

- Original work: [ivanwilliammd/satusehat-integration](https://github.com/ivanwilliammd/satusehat-integration) by Dr. dr. Ivan William Harsono, MTI
- Maintained dan updated for latest SATUSEHAT API by [@yudistirasd](https://github.com/yudistirasd)

## License

[MIT License](LICENSE.md). Bebas dipakai termasuk untuk komersial.

---

**Maintained by [Yudistira SD](https://github.com/yudistirasd)** - Laravel developer Indonesia. Spesialis integrasi healthcare API (SATUSEHAT, BPJS) untuk SaaS klinik.
