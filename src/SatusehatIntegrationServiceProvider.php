<?php

namespace Satusehat\Integration;

use Illuminate\Support\ServiceProvider;

class SatusehatIntegrationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish Config
        $this->publishes([
            __DIR__ . '/../config/satusehatintegration.php' => config_path('satusehatintegration.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/satusehatintegration.php', 'satusehatintegration');

        // Publish Migrations for Token
        if (! class_exists('CreateSatusehatTokenTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_satusehat_token_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_token_table.php"),
            ], 'migrations');
        }

        // Publish Migrations for Log
        if (! class_exists('CreateSatusehatLogTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_satusehat_log_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_log_table.php"),
            ], 'migrations');
        }

        // Publish Migrations for ICD 10
        if (! class_exists('CreateSatusehatIcd10Table')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_satusehat_icd10_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_icd10_table.php"),
            ], 'icd10');
        }

        // Publish ICD 10 csv data
        $this->publishes([
            __DIR__ . '/../database/seeders/csv/icd10.csv.stub' => database_path('/seeders/csv/icd10.csv'),
        ], 'icd10');

        // Publish Seeder for ICD 10
        if (! class_exists('Icd10Seeder')) {
            $this->publishes([
                __DIR__ . '/../database/seeders/Icd10Seeder.php.stub' => database_path('/seeders/Icd10Seeder.php'),
            ], 'icd10');
        }

        // Publish Migrations for ICD 9
        if (! class_exists('CreateSatusehatIcd9Table')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_satusehat_icd9_table.php.stub' => database_path("/migrations/{$timestamp}_create_satusehat_icd9_table.php"),
            ], 'icd9');
        }

        // Publish ICD 9 csv data
        $this->publishes([
            __DIR__ . '/../database/seeders/csv/icd9.csv.stub' => database_path('/seeders/csv/icd9.csv'),
        ], 'icd9');

        // Publish Seeder for ICD 9
        if (! class_exists('Icd10Seeder')) {
            $this->publishes([
                __DIR__ . '/../database/seeders/Icd9Seeder.php.stub' => database_path('/seeders/Icd9Seeder.php'),
            ], 'icd9');
        }

        // Publish Migrations for Kode Wilayah Indonesia
        if (! class_exists('CreateKodeWilayahIndonesiaTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_kode_wilayah_indonesia_table.php.stub' => database_path("/migrations/{$timestamp}_create_kode_wilayah_indonesia_table.php"),
            ], 'kodewilayahindonesia');
        }

        // Publish Wilayah Indonesia (prov,kab,kec,kel) migration & CSV Data
        // Provinsi
        if (! class_exists('CreateProvinsiTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_provinsi_table.php.stub' => database_path("/migrations/{$timestamp}_create_provinsi_table.php"),
            ], 'wilayah');
        }

        $this->publishes([
            __DIR__ . '/../database/seeders/csv/provinsi.csv.stub' => database_path('/seeders/csv/provinsi.csv'),
        ], 'wilayah');


        // Kabupaten
        if (! class_exists('CreateKabupatenTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_kabupaten_table.php.stub' => database_path("/migrations/{$timestamp}_create_kabupaten_table.php"),
            ], 'wilayah');
        }

        $this->publishes([
            __DIR__ . '/../database/seeders/csv/kabupaten.csv.stub' => database_path('/seeders/csv/kabupaten.csv'),
        ], 'wilayah');


        // Kecamatan
        if (! class_exists('CreateKecamatanTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_kecamatan_table.php.stub' => database_path("/migrations/{$timestamp}_create_kecamatan_table.php"),
            ], 'wilayah');
        }

        $this->publishes([
            __DIR__ . '/../database/seeders/csv/kecamatan.csv.stub' => database_path('/seeders/csv/kecamatan.csv'),
        ], 'wilayah');


        // Kelurahan
        if (! class_exists('CreateKelurahanTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_kelurahan_table.php.stub' => database_path("/migrations/{$timestamp}_create_kelurahan_table.php"),
            ], 'wilayah');
        }

        $this->publishes([
            __DIR__ . '/../database/seeders/csv/kelurahan.csv.stub' => database_path('/seeders/csv/kelurahan.csv'),
        ], 'wilayah');

        // Publish Seeder Wilayah Indonesia (prov,kab,kec,kel)
        if (! class_exists('WilayahTableSeeder')) {
            $this->publishes([
                __DIR__ . '/../database/seeders/WilayahTableSeeder.php.stub' => database_path('/seeders/WilayahTableSeeder.php'),
            ], 'wilayah');
        }
    }

    public function register()
    {
        //
    }
}
