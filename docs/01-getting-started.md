# Getting Started

Panduan awal pakai package `yudistira/satusehat-api` di project Laravel kamu.

## Install

```bash
composer require yudistira/satusehat-api
php artisan vendor:publish --provider="Satusehat\Integration\SatusehatIntegrationServiceProvider"
php artisan migrate
```

## Konfigurasi Environment

Daftarkan aplikasi di [portal SATUSEHAT](https://satusehat.kemkes.go.id/) menu **Pengembang > Aplikasi**, lalu isi `.env`:

| Variable | Deskripsi |
|---|---|
| `SATUSEHAT_ENV` | `DEV` (sandbox), `STG` (staging), atau `PROD` |
| `SATUSEHAT_CLIENT_ID` | Client ID dari portal |
| `SATUSEHAT_CLIENT_SECRET` | Client Secret dari portal |
| `SATUSEHAT_ORGANIZATION_ID` | Organization ID fasyankes kamu |

### Endpoint Default

| Environment | Base URL |
|---|---|
| `DEV` | `https://api-satusehat-dev.dto.kemkes.go.id` |
| `STG` | `https://api-satusehat-stg.dto.kemkes.go.id` |
| `PROD` | `https://api-satusehat.kemkes.go.id` |

Auth path default `/oauth2/v1`, FHIR path default `/fhir-r4/v1`. Bisa di-override via `config/satusehatintegration.php`.

## Cara Pakai (Pattern Standar)

Setiap resource FHIR mengikuti pola yang sama:

```php
use Satusehat\Integration\FHIR\Patient;

// 1. Instantiate (auto-load OAuth2 + token cache)
$patient = new Patient();

// 2. Set field-field
$patient->addIdentifier('nik', '3174012345678901');
$patient->setName('Budi Setiawan');
// dst.

// 3. Pilih salah satu output:
$json = $patient->json();           // ambil JSON string
[$status, $res] = $patient->post(); // POST ke SATUSEHAT
[$status, $res] = $patient->put($id); // PUT update
```

OAuth2 token dihandle otomatis (di-cache di Laravel Cache, refresh kalau expired).

## Multi-Tenant

Pake kalau satu instance Laravel mau handle banyak fasyankes (misal SaaS klinik).

### 1. Aktifkan Override

Set di `config/satusehatintegration.php`:

```php
'ss_parameter_override' => true,
```

### 2. Simpen Profile Per-Tenant

Setelah migrate, ada tabel `satu_sehat_profile_fasyankes`. Isi dengan kredensial tiap tenant:

| Kolom | Deskripsi |
|---|---|
| `kode` | Kode unik tenant kamu (misal kode klinik) |
| `env` | `DEV` / `STG` / `PROD` |
| `client_id` | Client ID SATUSEHAT tenant |
| `client_secret` | Client Secret SATUSEHAT tenant |
| `organization_id` | Organization ID tenant |

### 3. Kirim Kode Tenant Saat Request

Per-request, sertain kode tenant via query string atau header:

```php
// Via query string
GET /api/patient?code=KLINIK001

// Via header
X-Profile-Code: KLINIK001
```

Package akan auto-resolve kredensial sesuai kode tenant di setiap call.

**Pitfall:** kalau kode tenant nggak ada atau env mismatch, throw `TenantException`.

## Sandbox: ID Pasien & Nakes Development

Buat testing di environment `DEV`, package udah include daftar ID valid yang aman dipake:

### ID Pasien Dev

```
P02478375538, P02428473601, P03647103112, P01058967035, P01836748436,
P01654557057, P00805884304, P00883356749, P00912894463
```

### ID Nakes Dev

```
10009880728, 10006926841, 10001354453, 10010910332, 10018180913,
10002074224, 10012572188, 10018452434, 10014058550, 10001915884
```

Property publik di `OAuth2Client`:

```php
$client = new \Satusehat\Integration\OAuth2Client();
print_r($client->patient_dev);
print_r($client->practitioner_dev);
```

## KYC Centang Biru

Untuk kebutuhan validasi identitas pasien (verifikasi NIK + foto wajah). Pakai class `Satusehat\Integration\KYC`:

```php
use Satusehat\Integration\KYC;

$kyc = new KYC();
[$publicKey, $privateKey] = array_values($kyc->generateKey());
// ...lanjut ke flow generateRSAKeyPair, encrypt payload, dll.
```

KYC API butuh setup terpisah di portal SATUSEHAT (request akses KYC). Detail flow ada di [dokumentasi resmi](https://satusehat.kemkes.go.id/).

## Logging Request/Response

Setiap call ke SATUSEHAT otomatis disimpen di tabel `satu_sehat_log`. Berguna untuk audit + debugging.

Cleanup log lama:

```bash
php artisan satusehat:prune-log
```

Default retention bisa di-set di config.

## Troubleshooting

### "SATUSEHAT environment is missing"
Set `SATUSEHAT_ENV` di `.env`.

### "SATUSEHAT environment invalid"
Pastikan value `DEV`, `STG`, atau `PROD` (case-sensitive uppercase).

### "Tenant code is missing" (multi-tenant)
Sertain query `?code=` atau header `X-Profile-Code` di request.

### Token expired terus
Cek timezone server kamu. SATUSEHAT pakai UTC, library auto-handle, tapi system clock skew bisa bikin token cache mismatch.

### Resource validation error: "Please use ... to pass the data"
Field wajib belum di-set. Lihat docs masing-masing resource untuk cek apa aja yang wajib.

## Selanjutnya

Pilih resource yang mau kamu pakai dari [index dokumentasi](README.md), atau pelajari workflow end-to-end:

- [Alur Pasien Baru](workflows/alur-pasien-baru.md)
- [Alur Laboratorium](workflows/alur-laboratorium.md)
