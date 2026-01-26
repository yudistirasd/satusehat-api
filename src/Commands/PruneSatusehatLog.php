<?php

namespace Satusehat\Integration\Commands;

use Illuminate\Console\Command;
use Satusehat\Integration\Models\SatusehatLog;

class PruneSatusehatLog extends Command
{
    // Signature dengan default 60 hari (2 bulan)
    protected $signature = 'satusehat:prune-logs
                            {--days=60 : Jumlah hari log yang dipertahankan}
                            {--all : Menghapus seluruh data log tanpa kecuali}';
    protected $description = 'Membersihkan data Satusehat Log yang sudah lama secara bertahap';

    public function handle()
    {
        $all = $this->option('all');
        $days = $this->option('days');
        $date = now()->subDays($days);

        // 1. Tentukan Query & Pesan
        $query = SatusehatLog::query();

        if ($all) {
            $this->warn("PERINGATAN: Anda memilih untuk menghapus SELURUH data log.");
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan?', false)) {
                $this->comment('Operasi dibatalkan.');
                return 0;
            }
        } else {
            $query->where('created_at', '<=', $date);
            $this->info("Memulai pembersihan log sebelum tanggal: {$date->format('Y-m-d')}");
        }

        // 2. Hitung Total (Gunakan clone agar query asli tidak terpengaruh)
        $totalToDelete = (clone $query)->count();

        if ($totalToDelete === 0) {
            $this->comment('Tidak ada log yang perlu dihapus.');
            return 0;
        }

        $this->warn("Menghapus {$totalToDelete} log dalam batch (1000 baris/batch)...");

        // 3. Progress Bar
        $bar = $this->output->createProgressBar($totalToDelete);
        $bar->start();

        // 4. Proses Batch Deletion
        while ((clone $query)->exists()) {
            (clone $query)->limit(1000)->delete();

            $bar->advance(1000);
            usleep(100000); // Jeda 0.1 detik agar DB tidak overload
        }

        $bar->finish();
        $this->newLine();
        $this->info("Pembersihan selesai!");

        return 0;
    }
}
