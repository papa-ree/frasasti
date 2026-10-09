<?php

namespace Bale\Frasasti\Services;

use Bale\Core\Support\Sanitize;
use Bale\Frasasti\Models\Aduan;
use Illuminate\Support\Str;

class AduanService
{
    /**
     * Simpan aduan baru. Setiap pengiriman diberi kode referensi UUID unik.
     * Nama lengkap, NIP, dan nomor WhatsApp dienkripsi otomatis via cast `encrypted` pada model.
     */
    public function store(array $data): Aduan
    {
        $sanitized = $this->sanitize($data);

        return Aduan::create([
            'ref_code' => $this->generateUniqueRefCode(),
            'nama_lengkap' => $sanitized['nama_lengkap'],
            'nip' => $sanitized['nip'],
            'wa_number' => $sanitized['wa_number'],
            'aduan_category_id' => $sanitized['aduan_category_id'],
            'deskripsi' => $sanitized['deskripsi'],
            'status' => 'pending',
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Buat UUID yang dijamin belum pernah dipakai pada tabel aduan.
     */
    protected function generateUniqueRefCode(): string
    {
        do {
            $refCode = (string) Str::uuid();
        } while (Aduan::where('ref_code', $refCode)->exists());

        return $refCode;
    }

    /**
     * Bangun link wa.me dengan pesan terformat berisi input user dan kode referensi.
     * NIP tetap disertakan agar admin dapat mencocokkan dengan Keycloak.
     * Nomor WA pribadi sengaja TIDAK dikirim ke wa.me (privasi).
     *
     * @param  array{ref_code: string, nama_lengkap: string, nip: string, aduan_category: string, deskripsi: string}  $data
     */
    public function buildWhatsAppLink(array $data, ?string $targetNumber = null): string
    {
        // Nomor tujuan ikut dinormalisasi (0 → 62) agar link wa.me selalu valid,
        // baik saat memakai default config maupun nomor yang dioverride.
        $targetNumber = $this->normalizePhone($targetNumber ?: (string) config('frasasti.whatsapp.support_number'));

        $message = implode("\n", [
            '*Aduan Bale*',
            '',
            '*Kode Ref*: '.$data['ref_code'],
            '*Nama Lengkap*: '.$data['nama_lengkap'],
            '*NIP*: `'.$data['nip'].'`',
            '*Jenis Aduan*: '.($data['aduan_category'] ?? '-'),
            '*Deskripsi*:',
            $data['deskripsi'],
        ]);

        return 'https://wa.me/'.$targetNumber.'?text='.rawurlencode($message);
    }

    /**
     * Sanitize semua input menggunakan helper Bale\Core\Support\Sanitize.
     * Deskripsi disanitize per-baris agar line break tetap dipertahankan.
     */
    public function sanitize(array $data): array
    {
        return [
            'nama_lengkap' => Sanitize::text($data['nama_lengkap'] ?? ''),
            'nip' => Sanitize::integer($data['nip'] ?? 0),
            'wa_number' => $this->normalizePhone(Sanitize::phone($data['wa_number'] ?? '')),
            'aduan_category_id' => $data['aduan_category_id'] ?? null,
            'deskripsi' => $this->sanitizeParagraph($data['deskripsi'] ?? ''),
        ];
    }

    /**
     * Normalisasi nomor WA menjadi format internasional (0 → 62).
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Sanitize teks multi-baris tanpa menghilangkan baris baru.
     */
    protected function sanitizeParagraph(string $value): string
    {
        $lines = preg_split('/\R/', $value);

        $sanitized = array_map(
            fn (string $line) => Sanitize::text($line),
            $lines
        );

        return implode("\n", array_filter($sanitized, fn (string $line) => $line !== ''));
    }
}
