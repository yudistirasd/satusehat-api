<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class CarePlan extends OAuth2Client
{
    public array $carePlan = [
        "resourceType" => "CarePlan",
        "status" => "active",
        "intent" => "plan",
        "category" => [
            [
                "coding" => [
                    [
                        "system" => "http://snomed.info/sct",
                        "code" => "736271009",
                        "display" => "Outpatient care plan"
                    ]
                ]
            ]
        ],
        "title" => "Rencana Rawat Pasien"
    ];


    public function setDescription($description)
    {
        $this->carePlan['description'] = $description;
    }

    public function setSubject($code, $display)
    {
        $this->carePlan['subject'] = [
            'reference' => 'Patient/' . $code,
            'display' => $display,
        ];
    }

    public function setEncounter($encounterId)
    {
        $this->carePlan['encounter']['reference'] = 'Encounter/' . $encounterId;
    }

    public function setAuthor($practitionerId, $display)
    {
        $this->carePlan['author'] = [
            'reference' => "Practitioner/{$practitionerId}",
            'display' => $display,
        ];
    }

    public function json()
    {

        return json_encode($this->carePlan, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('CarePlan', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->carePlan['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('CarePlan', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->carePlan['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('CarePlan', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
