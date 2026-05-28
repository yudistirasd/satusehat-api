# Dokumentasi SATUSEHAT Integration

Dokumentasi lengkap pemakaian library Laravel untuk integrasi SATUSEHAT Kemenkes RI.

## Mulai Dari Sini

1. **[Getting Started](01-getting-started.md)** — Install, konfigurasi, OAuth2, multi-tenant, sandbox vs production

## FHIR Resources (19)

### Resource Pasien & Praktisi

- **[Patient](resources/patient.md)** — Data pasien (demografi, alamat, kontak)
- **[Practitioner](resources/practitioner.md)** — Cari ID nakes by NIK (read-only)
- **[Organization](resources/organization.md)** — Data fasyankes (rumah sakit, klinik)
- **[Location](resources/location.md)** — Poli/ruangan/bangsal di fasyankes

### Resource Kunjungan & Diagnosis

- **[Encounter](resources/encounter.md)** — Kunjungan pasien (rajal, IGD, ranap, dll)
- **[Condition](resources/condition.md)** — Diagnosis (ICD-10)
- **[Procedure](resources/procedure.md)** — Tindakan medis (ICD-9-CM)
- **[ClinicalImpression](resources/clinical-impression.md)** — Anamnesis/SOAP
- **[Composition](resources/composition.md)** — Resume medis pasien
- **[CarePlan](resources/care-plan.md)** — Rencana rawat
- **[AllergyIntolerance](resources/allergy-intolerance.md)** — Riwayat alergi pasien

### Resource Pemeriksaan & Lab

- **[Observation](resources/observation.md)** — Vital signs + hasil lab
- **[ServiceRequest](resources/service-request.md)** — Permintaan pemeriksaan lab
- **[Specimen](resources/specimen.md)** — Sampel pemeriksaan
- **[DiagnosticReport](resources/diagnostic-report.md)** — Laporan hasil lab/radiologi

### Resource Obat

- **[Medication](resources/medication.md)** — Master data obat (KFA)
- **[MedicationRequest](resources/medication-request.md)** — Resep obat
- **[MedicationDispense](resources/medication-dispense.md)** — Penyerahan obat

### Resource Bundle

- **[Bundle](resources/bundle.md)** — Transaction bundle (Encounter + Condition sekaligus)

## Workflow End-to-End

- **[Alur Pasien Baru](workflows/alur-pasien-baru.md)** — Daftar pasien → kunjungan → diagnosis → resep
- **[Alur Laboratorium](workflows/alur-laboratorium.md)** — Permintaan lab → spesimen → hasil → laporan

## Konvensi Code Example

Semua contoh menggunakan namespace `Satusehat\Integration\FHIR\*`. Pastikan `OAuth2Client` sudah terkonfigurasi via `.env` (lihat [Getting Started](01-getting-started.md)).

```php
use Satusehat\Integration\FHIR\Patient;
use Satusehat\Integration\FHIR\Encounter;
// dst.
```

## Konvensi Method

Hampir semua resource punya 4 method standar:

- `json()` — Generate FHIR JSON (dengan validasi field wajib)
- `post()` — Create resource baru di SATUSEHAT, return `[statusCode, response]`
- `put($id)` — Update full resource
- `patch($id, $payload)` — Update partial

Kalau resource cuma support sebagian, akan dijelasin di docs masing-masing.

## Dokumentasi Resmi SATUSEHAT

- [Portal Developer SATUSEHAT](https://satusehat.kemkes.go.id/)
- [Profil FHIR Indonesia](https://fhir.kemkes.go.id/r4/)
- [FHIR R4 Spec](https://www.hl7.org/fhir/R4/)
