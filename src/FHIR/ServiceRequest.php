<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class ServiceRequest extends OAuth2Client
{
    public array $serviceRequest = [
        'resourceType' => 'ServiceRequest',
        'status' => 'active',
        'intent' => 'original-order',
        'priority' => 'routine',
        'category' => [
            [
                'coding' => [
                    [
                        'system' => 'http://snomed.info/sct',
                        'code' => '108252007',
                        'display' => 'Laboratory procedure'
                    ]
                ]
            ]
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->serviceRequest['identifier'] = [
            [
                'system' => 'http://sys-ids.kemkes.go.id/servicerequest/' . $this->organization_id,
                'value' => $identifier
            ]
        ];
    }

    public function setCode($code, $display, $codeSystem, $text = null)
    {
        $this->serviceRequest['code'] = [
            'coding' => [
                [
                    'system' => $codeSystem,
                    'code' => $code,
                    'display' => $display
                ],
            ],
            'text' => $text ?? 'Permintaan Pemeriksaan ' . $display
        ];
    }

    public function setSubject($subjectId)
    {
        $this->serviceRequest['subject'] = [
            'reference' => 'Patient/' . $subjectId
        ];
    }

    public function setEncounter($encounterId)
    {
        $this->serviceRequest['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setAuthored($authoredOn = null)
    {
        $authoredOn = $authoredOn ?? gmdate("Y-m-d\TH:i:sP");

        $this->serviceRequest['authoredOn'] = $authoredOn;
        $this->serviceRequest['occurrenceDateTime'] = $authoredOn;
    }

    public function setRequester($practitionerId, $practitionerDisplay)
    {
        $this->serviceRequest['requester']['reference'] = 'Practitioner/' . $practitionerId;
        $this->serviceRequest['requester']['display'] = $practitionerDisplay;
    }

    public function setPerformer($practitionerId, $practitionerDisplay)
    {
        $this->serviceRequest['performer'] = [
            [
                'reference' => 'Practitioner/' . $practitionerId,
                'display' => $practitionerDisplay
            ]
        ];
    }

    public function json()
    {

        return json_encode($this->serviceRequest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('ServiceRequest', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->serviceRequest['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('ServiceRequest', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->serviceRequest['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('ServiceRequest', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
