# Workflow: Alur Laboratorium

End-to-end flow pemeriksaan laboratorium dari permintaan dokter sampai laporan hasil.

## Flow Diagram

```
1. Encounter (sudah ada)         ←  pasien sudah berkunjung
2. ServiceRequest                 →  Permintaan lab dari dokter
3. Specimen                       →  Pengambilan sampel
4. Observation (per parameter)    →  Hasil detail tiap parameter lab
5. DiagnosticReport               →  Laporan summary
```

## Skenario

dr. Ahmad request CBC (Complete Blood Count) untuk Budi. Petugas lab ambil sampel darah, kerjain pemeriksaan, hasil keluar 3 jam kemudian.

## Step 1: ServiceRequest (Dokter Request Lab)

```php
use Satusehat\Integration\FHIR\ServiceRequest;

$sr = new ServiceRequest();
$sr->setIdentifier('SR-2026-001');
$sr->setCode('58410-2', 'CBC panel - Blood by Automated count', 'http://loinc.org');
$sr->setSubject($patientId);
$sr->setEncounter($encounterId);
$sr->setRequester($drAhmadId, 'dr. Ahmad');
$sr->setPerformer($analisLabId, 'Sdr. Andi (Analis Lab)');
$sr->setAuthored();

[$status, $res] = $sr->post();
$serviceRequestId = $res->id;
```

## Step 2: Specimen (Pengambilan Sampel)

```php
use Satusehat\Integration\FHIR\Specimen;

$spec = new Specimen();
$spec->setIdentifier('SPEC-2026-001');
$spec->setStatus('available');
$spec->setType('Darah');
$spec->setSubject($patientId, 'Budi Setiawan');
$spec->setServiceRequest($serviceRequestId);
$spec->setProcessing();

[$status, $res] = $spec->post();
$specimenId = $res->id;
```

## Step 3: Observation (Hasil Per Parameter)

CBC menghasilkan multiple parameter (Hb, WBC, RBC, Hct, dll). Bikin 1 Observation per parameter:

```php
use Satusehat\Integration\FHIR\Observation;

$results = [
    ['code' => '718-7',  'display' => 'Hemoglobin',           'value' => 13.5],
    ['code' => '789-8',  'display' => 'Erythrocytes [#/volume]', 'value' => 4.5],
    ['code' => '6690-2', 'display' => 'Leukocytes [#/volume]',   'value' => 7.2],
    ['code' => '777-3',  'display' => 'Platelets [#/volume]',    'value' => 250],
];

$observationIds = [];

foreach ($results as $r) {
    $obs = new Observation();
    $obs->setStatus('final')
        ->addCategory('laboratory')
        ->addCode($r['code'], $r['value'], 'laboratory', $r['display'])
        ->setSubject($patientId, 'Budi Setiawan')
        ->setPerformer($analisLabId, 'Sdr. Andi')
        ->setEncounter($encounterId);

    $obs->setSpecimen($specimenId);          // wajib untuk lab
    $obs->setServiceRequest($serviceRequestId); // wajib untuk lab
    $obs->setEffectiveDateTime();

    [$status, $res] = $obs->post();
    $observationIds[] = $res->id;
}
```

## Step 4: DiagnosticReport (Laporan Summary)

```php
use Satusehat\Integration\FHIR\DiagnosticReport;

$dr = new DiagnosticReport();
$dr->setStatus('final')
   ->setSubject($patientId, 'Budi Setiawan')
   ->setEncounter($encounterId)
   ->setPerformer($analisLabId, 'Sdr. Andi');

$dr->setCode('58410-2', 'CBC panel - Blood by Automated count', 'http://loinc.org');
$dr->setSpecimen($specimenId);
$dr->setServiceRequest($serviceRequestId);
$dr->setEffectiveDateTime();

// Note: setResult cuma support 1 ID. Untuk multiple results, modify payload manual:
$drPayload = json_decode($dr->json(), true);
$drPayload['result'] = array_map(function ($id) {
    return ['reference' => "Observation/{$id}"];
}, $observationIds);

// Post payload custom
$satusehat = new \Satusehat\Integration\OAuth2Client();
[$status, $res] = $satusehat->ss_post('DiagnosticReport', json_encode($drPayload));
```

## Best Practices

- **Order matters** — ServiceRequest → Specimen → Observation → DiagnosticReport. Step kebelakang butuh ID dari step depan.
- **`setSpecimen` + `setServiceRequest` wajib** untuk Observation kategori `laboratory`. Skip salah satu → throw `FHIRMissingProperty`.
- **Multiple parameter = multiple Observation** — Tapi mereka di-link via `result` di DiagnosticReport.
- **`DiagnosticReport.setResult` limitation** — package cuma support 1 ID, untuk multiple butuh override payload manual seperti contoh di atas.
- **Status `preliminary` untuk hasil sementara** — kalau lab masih ngerjain, set status `preliminary`, baru `final` setelah selesai. Update via `put()`.
- **Specimen status:** `available` (siap diperiksa), `unavailable` (rusak/hilang), `unsatisfactory` (sampel jelek), `entered-in-error`.

## Skenario Spesial

### Lab di Luar (External)

Kalau pemeriksaan dilakukan di lab eksternal (rujukan), tetap bikin ServiceRequest + Specimen + Observation + DiagnosticReport di sistem kamu, tapi:

- `setPerformer` di ServiceRequest → praktisi lab eksternal
- `setLocation` di payload manual → reference ke Location lab eksternal

### Pemeriksaan Tertunda

Kalau hasil belum keluar, post DiagnosticReport dengan status `preliminary`. Update via `put($id)` setelah hasil masuk dengan status `final`.

## Lihat Juga

- [ServiceRequest](../resources/service-request.md)
- [Specimen](../resources/specimen.md)
- [Observation](../resources/observation.md)
- [DiagnosticReport](../resources/diagnostic-report.md)
- [Workflow: Alur Pasien Baru](alur-pasien-baru.md)
