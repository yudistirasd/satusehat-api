<?php

namespace Satusehat\Integration\FHIR;

use Satusehat\Integration\Exception\FHIR\FHIRException;
use Satusehat\Integration\OAuth2Client;
use Satusehat\Integration\Terminology\Icd9;

class Procedure extends OAuth2Client
{
    public array $procedure = ['resourceType' => 'Procedure'];

    public function addCode($code = null, $display = null)
    {
        $code_check = Icd9::where('icd9_code', $code)->first();

        if (! $code_check) {
            throw new FHIRException('Kode ICD 9 (' . $code . ') tidak ditemukan');
        }

        $display = $display ? $display : $code_check->icd9_display;

        $this->procedure['code']['coding'][] = [
            'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            'code' => $code,
            'display' => $display,
        ];
    }

    public function addPerformer($practitionerId, $name)
    {
        $this->procedure['performer'][] = [
            'actor' => [
                'reference' => 'Practitioner/' . $practitionerId,
                'display' => $name,
            ],
        ];
    }

    public function setStatus($status = 'completed')
    {
        $status = strtolower($status);
        switch ($status) {
            case 'preparation':
                $this->procedure['status'] = 'preparation';
                break;
            case 'in-progress':
                $this->procedure['status'] = 'in-progress';
                break;
            case 'not-done':
                $this->procedure['status'] = 'not-done';
                break;
            case 'on-hold':
                $this->procedure['status'] = 'on-hold';
                break;
            case 'stopped':
                $this->procedure['status'] = 'stopped';
                break;
            case 'completed':
                $this->procedure['status'] = 'completed';
                break;
            case 'entered-in-error':
                $this->procedure['status'] = 'entered-in-error';
                break;
            case 'unknown':
                $this->procedure['status'] = 'unknown';
                break;
            default:
                $this->procedure['status'] = 'completed';
        }
    }

    public function setSubject($subjectId, $name)
    {
        $this->procedure['subject']['reference'] = 'Patient/' . $subjectId;
        $this->procedure['subject']['display'] = $name;
    }

    public function setEncounter($encounterId, $display = null, $bundle = false)
    {
        $this->procedure['encounter']['reference'] = ($bundle ? 'urn:uuid:' : 'Encounter/') . $encounterId;
        $this->procedure['encounter']['display'] = $display ? $display : 'Kunjungan ' . $encounterId;
    }

    public function json()
    {
        return json_encode($this->procedure, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = $this->json();
        [$statusCode, $res] = $this->ss_post('Procedure', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $this->procedure['id'] = $id;

        $payload = $this->json();
        [$statusCode, $res] = $this->ss_put('Procedure', $id, $payload);

        return [$statusCode, $res];
    }
}
