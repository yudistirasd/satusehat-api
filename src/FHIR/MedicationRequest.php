<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\Exception\FHIR\FHIRException;
use Satusehat\Integration\Helper\Uuid;
use Satusehat\Integration\OAuth2Client;

class MedicationRequest extends OAuth2Client
{
    public array $medicationRequest = [
        'resourceType' => 'MedicationRequest',
        'courseOfTherapyType' => [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-course-of-therapy',
                    'code' => 'acute',
                    'display' => 'Short course (acute) therapy'
                ]
            ]
        ]
    ];

    public function setContained(Medication $medication)
    {
        if (empty($medication)) {
            throw new FHIRException('Parameter medication is required');
        }

        // convert to array from json
        $medication = $medication->toArray();
        $medication['id'] = Uuid::generateV4();

        $this->medicationRequest['contained'][] = $medication;
    }

    public function setIdentifier($identifier)
    {
        $this->medicationRequest['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/prescription/' . $this->organization_id,
            'use' => 'official',
            'value' => $identifier,
        ];
    }

    public function setIdentifierItem($identifier)
    {
        $this->medicationRequest['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/prescription-item/' . $this->organization_id,
            'use' => 'official',
            'value' => $identifier,
        ];
    }

    public function setStatus($status = 'active')
    {
        $statusAvailable = ['active', 'on-hold', 'ended', 'stopped', 'completed', 'cancelled', 'entered-in-error', 'draft', 'unknown'];

        if (! in_array($status, $statusAvailable)) {
            $statusAvailableString = implode(',', $statusAvailable);

            throw new FHIRException("Medication Request Status berdasarkan http://hl7.org/fhir/codesystem-medicationrequest-status.html adalah : {$statusAvailableString}");
        }

        $this->medicationRequest['status'] = $status;
    }

    public function setIntent($intent = 'order')
    {
        $intentAvailable = ['proposal', 'plan', 'order', 'original-order', 'reflex-order', 'filler-order', 'instance-order', 'option'];

        if (! in_array($intent, $intentAvailable)) {
            $intentAvailableString = implode(',', $intentAvailable);

            throw new FHIRException("Medication Request Intent berdasarkan http://hl7.org/fhir/codesystem-medicationrequest-intent.html adalah : {$intentAvailableString}");
        }

        $this->medicationRequest['intent'] = $intent;
    }

    public function setSubject($subjectId, $name)
    {
        $this->medicationRequest['subject']['reference'] = 'Patient/' . $subjectId;
        $this->medicationRequest['subject']['display'] = $name;
    }

    public function setEncounter($encounterId)
    {
        $this->medicationRequest['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setRequester($practitionerId, $name)
    {
        $this->medicationRequest['requester']['reference'] = 'Practitioner/' . $practitionerId;
        $this->medicationRequest['requester']['display'] = $name;
    }

    public function setReference($referenceId = null, $display = null)
    {
        $this->medicationRequest['medicationReference']['reference'] = 'Medication/' . $referenceId;
        $this->medicationRequest['medicationReference']['display'] = $display;
    }

    public function setAuthoredOn($authoredOn = null)
    {
        $authoredOn = $authoredOn ?? gmdate("Y-m-d\TH:i:sP");

        $this->medicationRequest['authoredOn'] = $authoredOn;
    }

    public function setCategory($code = 'community', $display = 'Community')
    {
        $this->medicationRequest['category'] = [
            [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
                        'code' => $code,
                        'display' => $display,
                    ],
                ]
            ],
        ];
    }

    public function setDosageInstruction($aturanPakai, $keterangan, $frekuensi, $periode)
    {
        $this->medicationRequest['dosageInstruction'] = [
            [
                'sequence' => 1,
                'text' => $aturanPakai,
                'additionalInstruction' => [
                    [
                        'text' => $aturanPakai,
                    ],
                ],
                'patientInstruction' => $keterangan,
                'timing' => [
                    'repeat' => [
                        'frequency' => $frekuensi,
                        'period' => $periode,
                        'periodUnit' => 'd',
                    ],
                ],
                'route' => [
                    'coding' => [
                        [
                            'system' => 'http://www.whocc.no/atc',
                            'code' => 'O',
                            'display' => 'Oral',
                        ],
                    ],
                ],
                'doseAndRate' => [
                    [
                        'type' => [
                            'coding' => [
                                [
                                    'system' => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                                    'code' => 'ordered',
                                    'display' => 'Ordered',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
    public function setDispenseRequest($dispenseInterval = [], $validityPeriod = [], $numberOfRepeatsAllowed = 0, $quantity = [], $expectedSupplyDuration = [])
    {

        if (! empty($dispenseInterval)) {
            $this->medicationRequest['dispenseRequest']['dispenseInterval'] = $dispenseInterval;
        }

        if (! empty($validityPeriod)) {
            $this->medicationRequest['dispenseRequest']['validityPeriod'] = $validityPeriod;
        }

        if (! empty($numberOfRepeatsAllowed)) {
            $this->medicationRequest['dispenseRequest']['numberOfRepeatsAllowed'] = $numberOfRepeatsAllowed;
        }

        if (! empty($quantity)) {
            $this->medicationRequest['dispenseRequest']['quantity'] = $quantity;
        }

        if (! empty($expectedSupplyDuration)) {
            $this->medicationRequest['dispenseRequest']['expectedSupplyDuration'] = $expectedSupplyDuration;
        }

        $this->medicationRequest['dispenseRequest']['performer'] = [
            'reference' => 'Organization/' . $this->organization_id,
        ];
    }

    public function json()
    {
        // auto replace reference based on contained medication id
        if (! empty($this->medicationRequest['contained'])) {
            $this->medicationRequest['medicationReference']['reference'] = '#' . $this->medicationRequest['contained'][0]['id'];
        }

        return json_encode($this->medicationRequest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function toArray()
    {
        return $this->medicationRequest;
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('MedicationRequest', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->medicationRequest['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('MedicationRequest', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->medicationRequest['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('MedicationRequest', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
