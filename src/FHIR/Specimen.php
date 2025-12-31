<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class Specimen extends OAuth2Client
{
    public array $specimen = [
        "resourceType" => "Specimen",
        "status" => "available",
        "type" => [
            "coding" => [
                [
                    "system" => "http://snomed.info/sct",
                    "code" => "119297000",
                    "display" => "Blood specimen (specimen)"
                ]
            ]
        ],
    ];

    public function setIdentifier($identifier)
    {
        $this->specimen['identifier'] = [
            [
                "system" => "http://sys-ids.kemkes.go.id/specimen/" . $this->organization_id,
                "value" => $identifier,
                "assigner" => [
                    "reference" => "Organization/" . $this->organization_id,
                ]
            ]
        ];
    }

    public function setStatus($status = 'available')
    {
        $this->specimen['status'] = $status;
    }

    public function setType($type)
    {

        switch ($type) {
            case 'Darah':
                $code =  '119297000';
                $display = 'Blood specimen';
                break;
            case 'Urine':
                $code =  '122575003';
                $display = 'Urine specimen';
                break;
            case 'Feses':
                $code =  '119339001';
                $display = 'Stool specimen';
                break;
            case 'Jaringan tubuh':
                $code =  '119376003';
                $display = 'Tissue specimen';
                break;
            case 'Serum':
                $code =  '119364003';
                $display = 'Serum specimen';
                break;
            default:
                $code =  '74964007';
                $display = 'Other';
                break;
        }

        $this->specimen['type'] = [
            "coding" => [
                [
                    "system" => "http://snomed.info/sct",
                    "code" => $code,
                    "display" => $display
                ]
            ]
        ];
    }

    public function setProcessing($dateTime = null)
    {
        $dateTime = $dateTime ?? gmdate("Y-m-d\TH:i:sP");

        $this->specimen['processing'] = [
            [
                "procedure" => [
                    "coding" => [
                        [
                            "system" => "http://snomed.info/sct",
                            "code" => "9265001",
                            "display" => "Specimen processing"
                        ]
                    ]
                ],
                "timeDateTime" => $dateTime,
            ]
        ];

        $this->specimen['receivedTime'] = $dateTime;
    }

    public function setSubject($subjectId, $display)
    {
        $this->specimen['subject'] = [
            'reference' => 'Patient/' . $subjectId,
            'display' => $display
        ];
    }

    public function setServiceRequest($serviceRequestId)
    {
        $this->specimen['request'] = [
            [
                "reference" => "ServiceRequest/" . $serviceRequestId,
            ]
        ];
    }

    public function json()
    {

        return json_encode($this->specimen, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('Specimen', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->specimen['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('Specimen', $id, $payload);

        return [$statusCode, $res];
    }

    public function patch($id, $payload)
    {
        $this->specimen['id'] = $id;

        [$statusCode, $res] = $this->ss_patch('Specimen', $id, json_encode($payload));

        return [$statusCode, $res];
    }
}
