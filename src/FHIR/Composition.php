<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class Composition extends OAuth2Client
{
    public array $composition = [
        'resourceType' => 'Composition',
        'status' => 'final',
        'category' => [
            [
                'coding' => [
                    [
                        'system' => 'http://loinc.org',
                        'code' => 'LP173421-1',
                        'display' => 'Report',
                    ],
                ],
            ],
        ],
        'type' => [
            'coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => '88645-7',
                    'display' => 'Outpatient hospital Discharge summary',
                ],
            ],
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->composition['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/composition/' . $this->organization_id,
            'value' => $identifier,
        ];

        $this->composition['custodian'] = [
            'reference' => 'Organization/' . $this->organization_id,
        ];
    }

    public function setSubject($code, $display)
    {

        $date = gmdate("Y-m-d\TH:i:sP");

        $this->composition['subject'] = [
            'reference' => 'Patient/' . $code,
        ];

        $this->composition['date'] = $date;

        $this->composition['title'] = "Resume Medis Pasien Rawat Jalan {$display} pada {$date}";
    }

    public function setEncounter($encounterId)
    {
        $this->composition['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setAuthor($practitionerId)
    {
        $this->composition['author'] = [
            [
                'reference' => 'Practitioner/' . $practitionerId,
            ],
        ];
    }

    public function json()
    {

        return json_encode($this->composition, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('Composition', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->composition['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('Composition', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->composition['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('Composition', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
