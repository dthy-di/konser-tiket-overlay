<?php

namespace App\Support;

/**
 * Membuat dan memverifikasi isi QR tiket.
 *
 * Format isi QR : TKT-{KODE 20 hex}-{TANDA TANGAN 12 hex}
 * Tanda tangan  : HMAC-SHA256(kode, rahasia) dipotong 12 karakter.
 * Jadi QR tidak bisa dibuat/ditebak orang lain tanpa tahu rahasia server.
 */
class TicketCode
{
    public function __construct(private string $secret)
    {
    }

    /** Kode acak unik per tiket (20 karakter hex, huruf besar). */
    public function generate(): string
    {
        return strtoupper(bin2hex(random_bytes(10)));
    }

    public function sign(string $code): string
    {
        return substr(hash_hmac('sha256', $code, $this->secret), 0, 12);
    }

    /** Isi yang dimasukkan ke dalam QR. */
    public function payload(string $code): string
    {
        return 'TKT-'.$code.'-'.$this->sign($code);
    }

    /**
     * @return array{code: string, verified: bool}|null
     *   verified=true  : hasil scan QR dengan tanda tangan benar
     *   verified=false : petugas mengetik kode manual (tanpa tanda tangan)
     *   null           : format tidak dikenali atau tanda tangan salah
     */
    public function parse(string $input): ?array
    {
        $input = trim($input);
        $parts = explode('-', $input);

        if (count($parts) === 3 && strtoupper($parts[0]) === 'TKT') {
            $code = strtoupper($parts[1]);
            $signature = strtolower($parts[2]);

            if (preg_match('/^[0-9A-F]{20}$/', $code) !== 1) {
                return null;
            }

            return hash_equals($this->sign($code), $signature)
                ? ['code' => $code, 'verified' => true]
                : null;
        }

        $code = strtoupper($input);
        if (preg_match('/^[0-9A-F]{20}$/', $code) === 1) {
            return ['code' => $code, 'verified' => false];
        }

        return null;
    }
}
