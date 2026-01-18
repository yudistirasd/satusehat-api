<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class ClinicalImpression extends OAuth2Client
{
    public array $clinicalImpression = [
        'resourceType' => 'ClinicalImpression',
        'status' => 'completed',
        'code' => [
            'coding' => [
                [
                    'system' => 'http://snomed.info/sct',
                    'code' => '312850006',
                    'display' => 'History of disorder',
                ],
            ],
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->clinicalImpression['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/clinicalimpression/' . $this->organization_id,
            'value' => $identifier,
        ];
    }

    public function setSubject($code, $display)
    {
        $this->clinicalImpression['subject'] = [
            'reference' => 'Patient/' . $code,
            'display' => $display,
        ];
    }

    public function setEncounter($encounterId)
    {
        $this->clinicalImpression['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setSummary($summary)
    {
        $this->clinicalImpression['summary'] = $summary;
    }

    public function setEffectiveDateTime($dateTime = null)
    {
        $dateTime = $dateTime ?? gmdate("Y-m-d\TH:i:sP");

        $this->clinicalImpression['effectiveDateTime'] = $dateTime;
        $this->clinicalImpression['date'] = $dateTime;
    }

    public function setAssesor($practitionerId)
    {
        $this->clinicalImpression['assessor']['reference'] = 'Practitioner/' . $practitionerId;
    }

    public function json()
    {

        return json_encode($this->clinicalImpression, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('ClinicalImpression', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->clinicalImpression['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('ClinicalImpression', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->clinicalImpression['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('ClinicalImpression', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
