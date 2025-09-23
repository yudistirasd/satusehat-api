<?php

namespace Satusehat\Integration;

use Satusehat\Integration\Exception\Helper\TenantException;
use Satusehat\Integration\Models\SatuSehatProfileFasyankes;

trait Tenant
{
    public function getProfile()
    {
        $kode = request()->get('code') ?? request()->header('X-Profile-Code');

        if (! $kode) {
            throw new TenantException('Tenant code is missing', 403);
        }

        $profile = SatuSehatProfileFasyankes::where('kode', $kode)
            ->where('env', $this->satusehat_env)
            ->first();

        if (! $profile) {
            throw new TenantException('Tenant not found', 404);
        }

        return $profile;
    }
}
