# Workflow: Alur Pasien Baru

End-to-end flow registrasi pasien baru sampai resep obat. Cocok untuk skenario rajal (rawat jalan) standar.

## Flow Diagram

```
1. Cari/Daftar Patient    →  Patient ID
2. Cari Practitioner      →  Practitioner ID (dari NIK nakes)
3. Bikin Encounter         →  Encounter ID
   atau Bundle (Encounter + Condition sekaligus)
4. Tambah Procedure        →  Procedure ID (kalau ada tindakan)
5. Tambah Observation      →  Observation ID (vital signs)
6. Resep MedicationRequest →  MedicationRequest ID
7. Penyerahan MedicationDispense → MedicationDispense ID
8. Resume Composition      →  Composition ID
```

## Skenario

Pasien Budi datang ke poli umum jam 08:00 dengan keluhan demam batuk pilek 3 hari. Diperiksa dr. Ahmad, didiagnosis common cold, diresepkan paracetamol. Selesai jam 08:50.

## Step 1: Patient (Sekali Daftar)

Kalau pasien udah pernah ke fasyankes kamu, skip step ini. Reuse Patient ID dari database lokal.

```php
use Satusehat\Integration\FHIR\Patient;

$patient = new Patient();
$patient->addIdentifier('nik', '3174012345678901');
$patient->setName('Budi Setiawan');
$patient->addTelecom('081234567890');
$patient->setMultipleBirth(1);
$patient->setAddress([
    'address' => 'Jl. Mawar No. 10', 'city' => 'Sragen',
    'postalCode' => '57211', 'country' => 'ID',
    'provinceCode' => '33', 'cityCode' => '3314',
    'districtCode' => '331401', 'villageCode' => '3314011001',
    'rt' => '001', 'rw' => '002',
]);
$patient->setGender('male');
$patient->setBirthDate('1990-01-15');

[$status, $res] = $patient->post();
$patientId = $res->id; // misal: P02478375538
// SIMPEN $patientId di database lokal kamu
```

## Step 2: Practitioner (Sekali Lookup)

Dapetin Practitioner ID dari NIK dr. Ahmad. Cache hasilnya.

```php
use Satusehat\Integration\FHIR\Practitioner;

$pract = new Practitioner();
if ($pract->getSSNik('3375012345678901')) {
    $practitionerId = $pract->getId(); // misal: 10009880728
    $practitionerName = $pract->getName();
    // SIMPEN di database lokal, jangan lookup berulang
}
```

## Step 3: Encounter + Condition (Bundle)

Untuk efisiensi (1 request, atomic), pake Bundle:

```php
use Satusehat\Integration\FHIR\Encounter;
use Satusehat\Integration\FHIR\Condition;
use Satusehat\Integration\FHIR\Bundle;

// Encounter
$encounter = new Encounter();
$encounter->setArrived('2026-05-28 08:00:00');
$encounter->setInProgress('2026-05-28 08:15:00', '2026-05-28 08:45:00');
$encounter->setFinished('2026-05-28 08:50:00');
$encounter->setConsultationMethod('RAJAL');
$encounter->setSubject($patientId, 'Budi Setiawan');
$encounter->addParticipant($practitionerId, 'dr. Ahmad');
$encounter->addLocation('LOC-POLI-UMUM', 'Poli Umum');

// Condition (diagnosis)
$condition = new Condition();
$condition->setSubject($patientId, 'Budi Setiawan');
$condition->addCode('J00', 'Acute nasopharyngitis [common cold]');
// JANGAN setEncounter manual — Bundle handle otomatis

// Bundle
$bundle = new Bundle();
$bundle->addEncounter($encounter);
$bundle->addCondition($condition);
[$status, $res] = $bundle->post();

// Extract IDs dari response bundle
foreach ($res->entry as $entry) {
    if ($entry->response->status === '201 Created') {
        $location = $entry->response->location; // "Encounter/E12345"
        // parse ID-nya
    }
}
$encounterId = '...';   // dari response
$conditionId = '...';   // dari response
```

## Step 4: Observation (Vital Signs)

Catat TTV pasien:

```php
use Satusehat\Integration\FHIR\Observation;

$obs = new Observation();
$obs->setStatus('final')
    ->addCategory('vital-signs')
    ->addCode('8310-5', 38.5)             // Suhu 38.5C
    ->setSubject($patientId, 'Budi Setiawan')
    ->setPerformer($practitionerId, 'dr. Ahmad')
    ->setEncounter($encounterId);

$obs->setEffectiveDateTime();
[$status, $res] = $obs->post();
```

Untuk vital signs lengkap (TD, nadi, RR), buat 1 Observation per parameter (atau loop).

## Step 5: MedicationRequest (Resep)

```php
use Satusehat\Integration\FHIR\MedicationRequest;

$req = new MedicationRequest();
$req->setIdentifier('RESEP-001');
$req->setIdentifierItem('RESEP-001-IT01');
$req->setStatus('active');
$req->setIntent('order');
$req->setSubject($patientId, 'Budi Setiawan');
$req->setEncounter($encounterId);
$req->setRequester($practitionerId, 'dr. Ahmad');
$req->setReference('M001', 'Paracetamol 500 mg tablet');
$req->setAuthoredOn();

$req->setDosageInstruction('3x sehari setelah makan', 'Habiskan', 3, 1);
$req->setDispenseRequest([], [], 0, ['value' => 10, 'unit' => 'tablet'], []);

[$status, $res] = $req->post();
$medRequestId = $res->id;
```

## Step 6: MedicationDispense (Penyerahan Obat)

Setelah pasien ke farmasi:

```php
use Satusehat\Integration\FHIR\MedicationDispense;

$disp = new MedicationDispense();
$disp->setIdentifier('DISP-001');
$disp->setIdentifierItem('DISP-001-IT01');
$disp->setStatus('completed');
$disp->setCategory('outpatient', 'Outpatient');
$disp->setSubject($patientId, 'Budi Setiawan');
$disp->setEncounter($encounterId);
$disp->setPerformer('PRACT-APOTEKER-ID', 'Apt. Siti');
$disp->setReference('M001', 'Paracetamol 500 mg tablet');
$disp->setLocation('LOC-FARMASI', 'Farmasi RSUD Sragen');
$disp->setAuthorizingPrescription($medRequestId);
$disp->setPrepared();
$disp->setHandedOver();
$disp->setSubtitution(false);
$disp->setIntruction(1, '3x sehari setelah makan, habiskan');

[$status, $res] = $disp->post();
```

## Step 7: Composition (Resume Medis - Opsional)

```php
use Satusehat\Integration\FHIR\Composition;

$comp = new Composition();
$comp->setIdentifier('COMP-001');
$comp->setSubject($patientId, 'Budi Setiawan');
$comp->setEncounter($encounterId);
$comp->setAuthor($practitionerId);

[$status, $res] = $comp->post();
```

## Best Practices

- **Cache aggressively** — Patient ID + Practitioner ID + Location ID disimpen di database lokal, jangan lookup tiap request.
- **Idempotency** — kasih `Identifier` di tiap resource (RESEP-001, DISP-001, dll) supaya kalau request gagal di tengah, retry nggak duplicate.
- **Order matters** — Encounter dulu, baru Condition/Observation/Procedure (semua butuh encounter ID).
- **Bundle untuk Encounter+Condition** — atomic, hemat 1 request.
- **MedicationRequest sebelum MedicationDispense** — dispense butuh ID resep.
- **Error handling** — wrap dalam try-catch, log payload + response untuk debugging. Gunakan tabel `satu_sehat_log` (auto).

## Lihat Juga

- [Patient](../resources/patient.md), [Encounter](../resources/encounter.md), [Condition](../resources/condition.md)
- [Workflow: Alur Laboratorium](alur-laboratorium.md)
