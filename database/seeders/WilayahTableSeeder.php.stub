<?php

namespace Database\Seeders;

use JeroenZwart\CsvSeeder\CsvSeeder;
use DB;

class WilayahTableSeeder extends CsvSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $files = [
            [
                'file' => base_path('/database/seeders/csv/provinsi.csv'),
                'table' => config('satusehatintegration.wilayah.provinsi_table_name'),
            ],
            [
                'file' => base_path('/database/seeders/csv/kabupaten.csv'),
                'table' => config('satusehatintegration.wilayah.kabupaten_table_name'),
            ],
            [
                'file' => base_path('/database/seeders/csv/kecamatan.csv'),
                'table' => config('satusehatintegration.wilayah.kecamatan_table_name'),
            ],
            [
                'file' => base_path('/database/seeders/csv/kelurahan.csv'),
                'table' => config('satusehatintegration.wilayah.kelurahan_table_name'),
            ],
        ];

        foreach ($files as $config) {
            $this->file = $config['file'];
            $this->tablename = $config['table'];
            $this->delimiter = ';';
            $this->connection = config('satusehatintegration.database_connection_satusehat');

            parent::run();
        }
    }
}
