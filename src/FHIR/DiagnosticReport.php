<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\OAuth2Client;

class DiagnosticReport extends OAuth2Client
{
    private array $diagnostic = [
        'resourceType' => 'DiagnosticReport',
        'conclusionCode' => [
            [
                'coding' => [
                    [
                        'system' => 'http://loinc.org',
                        'code' => 'LA19710-5',
                        'display' => 'Group A',
                    ],
                ],
            ],
        ],
    ];

    public function setStatus($status = 'final')
    {
        switch ($status) {
            case 'registered':
                $code = 'registered';
                break;
            case 'preliminary':
                $code = 'preliminary';
                break;
            case 'final':
                $code = 'final';
                break;
            case 'amended':
                $code = 'amended';
                break;
            case 'corrected':
                $code = 'corrected';
                break;
            case 'cancelled':
                $code = 'cancelled';
                break;
            case 'entered-in-error':
                $code = 'entered-in-error';
                break;
            case 'unknown':
                $code = 'unknown';
                break;
            default:
                $code = 'final';
        }

        $this->diagnostic['status'] = $code;

        return $this;
    }

    public function setCode($code, $display, $codeSystem)
    {
        $this->diagnostic['code'] = [
            'coding' => [
                [
                    'system' => $codeSystem,
                    'code' => $code,
                    'display' => $display,
                ],
            ],
        ];
    }

    /**
     * Sets the subject of the observation.
     *
     * @param  string  $subjectId  The SATUSEHAT ID of the subject.
     * @param  string  $name  The name of the subject.
     * @return Observation The current observation instance.
     */
    public function setSubject(string $subjectId, string $name)
    {
        $this->diagnostic['subject'] = [
            'reference' => "Patient/{$subjectId}",
            'display' => $name,
        ];

        return $this;
    }

    public function setEncounter(string $encounterId, ?string $display = null)
    {
        $this->diagnostic['encounter'] = [
            'reference' => "Encounter/{$encounterId}",
            'display' => ! empty($display) ? $display : "Kunjungan {$encounterId}",
        ];

        return $this;
    }

    /**
     * Sets the performer of the observation.
     *
     * @param  string  $performerId  The SATUSEHAT ID of the performer.
     * @param  string  $name  The name of the performer.
     * @return Observation The current observation instance.
     */
    public function setPerformer(string $performerId, string $name)
    {
        $this->diagnostic['performer'][] = [
            'reference' => "Practitioner/{$performerId}",
            'display' => $name,
        ];

        return $this;
    }

    public function setSpecimen($specimenId)
    {
        $this->diagnostic['specimen'] = [
            [
                'reference' => 'Specimen/' . $specimenId,
            ],
        ];
    }

    public function setServiceRequest($serviceRequestId)
    {
        $this->diagnostic['basedOn'] = [
            [
                'reference' => 'ServiceRequest/' . $serviceRequestId,
            ],
        ];
    }

    public function setEffectiveDateTime($dateTime = null)
    {
        $dateTime = $dateTime ?? gmdate("Y-m-d\TH:i:sP");

        $this->diagnostic['effectiveDateTime'] = $dateTime;
        $this->diagnostic['issued'] = $dateTime;
    }

    public function setResult($observationId)
    {
        $this->diagnostic['result'] = [
            [
                'reference' => 'Observation/' . $observationId,
            ],
        ];
    }

    public function json()
    {

        return json_encode($this->diagnostic, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('DiagnosticReport', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->diagnostic['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('DiagnosticReport', $id, $payload);

        return [$statusCode, $res];
    }
}
