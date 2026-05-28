# Bundle

Transaction bundle untuk kirim **Encounter + Condition** sekaligus dalam 1 request atomic.

## Kapan Dipakai

- Setiap kali kunjungan punya diagnosis (kasus paling umum)
- Mau create Encounter + Condition tanpa harus 2x request terpisah
- Atomic transaction — kalau salah satu fail, rollback semua

## Quick Example

```php
use Satusehat\Integration\FHIR\Bundle;
use Satusehat\Integration\FHIR\Encounter;
use Satusehat\Integration\FHIR\Condition;

// 1. Bikin encounter (jangan post dulu)
$encounter = new Encounter();
$encounter->setArrived('2026-05-28 08:00:00');
$encounter->setInProgress('2026-05-28 08:15:00', '2026-05-28 08:45:00');
$encounter->setFinished('2026-05-28 08:50:00');
$encounter->setConsultationMethod('RAJAL');
$encounter->setSubject('P02478375538', 'Budi Setiawan');
$encounter->addParticipant('10009880728', 'dr. Ahmad');
$encounter->addLocation('LOC001', 'Poli Umum');

// 2. Bikin condition (jangan post dulu, jangan set encounter manual)
$condition = new Condition();
$condition->setSubject('P02478375538', 'Budi Setiawan');
$condition->addCode('J00', 'Acute nasopharyngitis');
// JANGAN: $condition->setEncounter(...) — Bundle handle otomatis

// 3. Bundle them
$bundle = new Bundle();
$bundle->addEncounter($encounter);    // wajib pertama
$bundle->addCondition($condition);    // bisa multiple

// 4. Post sekaligus
[$statusCode, $response] = $bundle->post();

// Response berisi ID Encounter + ID Condition yang baru dibuat
```

## Method Reference

| Method | Deskripsi |
|---|---|
| `addEncounter(Encounter $encounter)` | **Wajib pertama**. Encounter yang akan jadi anchor. |
| `addCondition(Condition $condition)` | Tambah diagnosis. Bisa dipanggil multiple kali untuk multiple diagnosis. |
| `json()` | Generate Bundle JSON |
| `post()` | POST ke endpoint `/Bundle` |

## Cara Kerja Internal

Bundle pake **UUID v4** untuk reference internal antar resource sebelum punya ID asli dari SATUSEHAT:

1. `addEncounter()` → generate UUID untuk encounter
2. `addCondition()` → generate UUID untuk condition, set reference encounter pake UUID itu (`urn:uuid:xxx`)
3. SATUSEHAT process bundle, generate ID asli, replace UUID dengan ID baru

## Catatan Penting

- **`addCondition` tanpa `addEncounter` dulu** → throw `FHIRException`. Selalu mulai dengan `addEncounter`.
- **Jangan panggil `setEncounter()` di Condition** — Bundle handle reference encounter otomatis. Kalau di-set manual, malah bentrok.
- **Encounter di Bundle nggak perlu di-post terpisah** — `Bundle::post()` yang ngirim semuanya.
- **Tidak ada `addObservation`, `addProcedure`, dll.** — Bundle versi package ini cuma support Encounter + Condition. Resource lain harus di-create terpisah pake reference ID hasil bundle.
- **Multiple Condition supported** — panggil `addCondition()` berkali-kali untuk multi-diagnosis.

## Limitations

- Hanya support `transaction` type (bukan `batch` atau `searchset`)
- Hanya support 2 resource: Encounter + Condition
- Kalau butuh transaction yang lebih kompleks, pake [SATUSEHAT API langsung](https://satusehat.kemkes.go.id/) dengan custom payload

## Lihat Juga

- [Encounter](encounter.md)
- [Condition](condition.md)
- [Workflow: Alur Pasien Baru](../workflows/alur-pasien-baru.md)
