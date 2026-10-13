<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/** Membuat gambar QR berbentuk SVG (tidak butuh ekstensi GD/Imagick). */
class QrRenderer
{
    public function svg(string $data, int $size = 240): string
    {
        $renderer = new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd());
        $svg = (new Writer($renderer))->writeString($data);

        // Buang baris deklarasi XML di awal supaya SVG aman ditempel langsung di dalam HTML.
        return (string) preg_replace('/^<\?xml.*?\?>\s*/s', '', $svg);
    }
}
