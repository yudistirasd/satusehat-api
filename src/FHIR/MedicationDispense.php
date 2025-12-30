<?php

namespace Satusehat\Integration\FHIR;

use Illuminate\Support\Str;
use Satusehat\Integration\Exception\FHIR\FHIRException;
use Satusehat\Integration\OAuth2Client;

class MedicationDispense extends OAuth2Client
{
    public array $medicationDispense = [
        'resourceType' => 'MedicationDispense',
    ];

    public function setContained($medication)
    {
        $medication['id'] = Str::uuid();
        $this->medicationDispense['contained'][] = $medication;
    }

    public function setIdentifier($identifier)
    {
        $this->medicationDispense['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/prescription/' . $this->organization_id,
            'use' => 'official',
            'value' => $identifier,
        ];
    }

    public function setIdentifierItem($identifier)
    {
        $this->medicationDispense['identifier'][] = [
            'system' => 'http://sys-ids.kemkes.go.id/prescription-item/' . $this->organization_id,
            'use' => 'official',
            'value' => $identifier,
        ];
    }

    public function setStatus($status = 'completed')
    {
        $statusAvailable = ['active',  'completed'];

        if (! in_array($status, $statusAvailable)) {
            $statusAvailableString = implode(',', $statusAvailable);

            throw new FHIRException("Medication Dispense Status adalah : {$statusAvailableString}");
        }

        $this->medicationDispense['status'] = $status;
    }

    public function setCategory($code = 'community', $display = 'Community')
    {
        $this->medicationDispense['category'] = [
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

    public function setSubject($code, $display)
    {
        $this->medicationDispense['subject'] = [
            'reference' => 'Patient/' . $code,
            'display' => $display,
        ];
    }

    public function setEncounter($encounterId)
    {
        $this->medicationDispense['context']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setPerformer($code, $display)
    {
        $this->medicationDispense['performer'][] = [
            'actor' => [
                'reference' => 'Practitioner/' . $code,
                'display' => $display,
            ],
        ];
    }

    public function setReference($code = null, $display = null)
    {
        $this->medicationDispense['medicationReference'] = [
            'reference' => 'Medication/' . $code,
            'display' => $display,
        ];
    }

    public function setLocation($code, $display)
    {
        $this->medicationDispense['location'] = [
            'reference' => 'Location/' . $code,
            'display' => $display,
        ];
    }

    public function setAuthorizingPrescription($code)
    {
        $this->medicationDispense['authorizingPrescription'][] = [
            'reference' => 'MedicationRequest/' . $code,
        ];
    }

    public function setPrepared($whenPrepared = null)
    {
        $whenPrepared = $whenPrepared ?? date("Y-m-d\TH:i:sP");

        $this->medicationDispense['whenPrepared'] = $whenPrepared;
    }

    public function setHandedOver($whenHandedOver = null)
    {
        $whenHandedOver = $whenHandedOver ?? gmdate("Y-m-d\TH:i:sP");

        $this->medicationDispense['whenPrepared'] = $whenHandedOver;
    }

    public function setSubtitution($subtitute = false)
    {
        $this->medicationDispense['substitution']['wasSubstituted'] = $subtitute;
    }

    public function setIntruction($sequence, $text)
    {
        $this->medicationDispense['dosageInstruction'][] = [
            'sequence' => (int) $sequence,
            'text' => $text,
        ];
    }

    public function json()
    {
        // auto replace reference based on contained medication id
        if (! empty($this->medicationDispense['contained'])) {
            $this->medicationDispense['medicationReference']['reference'] = '#' . $this->medicationDispense['contained'][0]['id'];
        }

        return json_encode($this->medicationDispense, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('MedicationDispense', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->medicationDispense['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('MedicationDispense', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->medicationDispense['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('MedicationDispense', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
