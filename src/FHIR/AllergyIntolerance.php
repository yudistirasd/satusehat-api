<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class AllergyIntolerance extends OAuth2Client
{
    public array $allergy = [
        'resourceType' => 'AllergyIntolerance',
        'verificationStatus' => [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-verification',
                    'code' => 'confirmed',
                    'display' => 'Confirmed'
                ]
            ]
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->allergy['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/allergy/' . $this->organization_id,
            'value' => $identifier,
        ];
    }

    public function setPatient($code, $display)
    {
        $this->allergy['patient'] = [
            'reference' => 'Patient/' . $code,
            'display' => $display,
        ];
    }

    public function setEncounter($encounterId)
    {
        $this->allergy['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setCategory($category = [])
    {
        $this->allergy['category'] = $category;
    }

    public function setCoding($system, $code, $display, $text)
    {
        $this->allergy['code'] =  [
            'coding' => [
                [
                    'system' => $system,
                    'code' => $code,
                    'display' => $display
                ]
            ],
            'text' => $text
        ];
    }

    public function setRecorder($practitionerId, $practitionerDisplay)
    {
        $this->allergy['recorder'] = [
            'reference' => 'Practitioner/' . $practitionerId,
            'display' => $practitionerDisplay
        ];

        $this->allergy['recordedDate'] = gmdate("Y-m-d\TH:i:sP");
    }

    public function setStatus($code = 'active')
    {

        $display = match ($code) {
            "active" => "Active",
            "inactive" => "Inactive",
            "resolved" => "Resolved"
        };

        $this->allergy['clinicalStatus'] = [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical',
                    'code' => $code,
                    'display' => $display
                ]
            ]
        ];
    }

    public function json()
    {

        return json_encode($this->allergy, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('AllergyIntolerance', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->allergy['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('AllergyIntolerance', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->allergy['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('AllergyIntolerance', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
