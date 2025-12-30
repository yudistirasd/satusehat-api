<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\Exception\FHIR\FHIRException;
use Satusehat\Integration\OAuth2Client;

class Medication extends OAuth2Client
{
    public array $medication = [
        'resourceType' => 'Medication',
        'meta' => [
            'profile' => [
                'https://fhir.kemkes.go.id/r4/StructureDefinition/Medication',
            ],
        ],
        'extension' => [
            [
                'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/MedicationType',
                'valueCodeableConcept' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-type',
                            'code' => 'NC',
                            'display' => 'Non-compound',

                        ],
                    ],
                ],
            ],
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->medication['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/medication/' . $this->organization_id,
            'value' => $identifier,
        ];
    }

    public function setStatus($status = 'active')
    {
        $statusAvailable = ['active', 'inactive', 'entered-in-error'];

        if (! in_array($status, $statusAvailable)) {
            $statusAvailableString = implode(',', $statusAvailable);

            throw new FHIRException("Medication status berdasarkan http://hl7.org/fhir/codesystem-medication-status.html adalah : {$statusAvailableString}");
        }

        $this->medication['status'] = $status;
    }

    public function setForm($code, $display = '')
    {
        $this->medication['form'] = [
            'coding' => [
                [
                    'system' => 'http://terminology.kemkes.go.id/CodeSystem/medication-form',
                    'code' => $code,
                    'display' => $display,
                ],
            ],
        ];
    }

    public function setCode($code, $display = '')
    {
        $this->medication['code']['coding'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/kfa',
            'code' => $code,
            'display' => $display,
        ];
    }

    public function setManufacturer()
    {
        $this->medication['manufacturer'] = [
            'reference' => 'Organization/' . $this->organization_id
        ];
    }

    public function getPayload($key = null)
    {
        if (! empty($key)) {
            return $this->medication[$key];
        }

        return $this->medication;
    }

    public function json()
    {
        return json_encode($this->medication, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function toArray()
    {
        return $this->medication;
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('Medication', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->medication['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('Medication', $id, $payload);

        return [$statusCode, $res];
    }
}
