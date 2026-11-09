<?php

namespace App\Services;

use App\Models\Child;
use App\Models\QRCode;
use Illuminate\Support\Facades\Storage;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QRCodeService
{
    public function generateForChild(Child $child): QRCode
    {
        // The permanent child code is the one QR payload used online and offline.
        $tokenHash = hash('sha256', $child->child_code);

        // Generate QR code as SVG.
        $renderer = new ImageRenderer(
            new RendererStyle(400, 2),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $qrImage = $writer->writeString($child->child_code);
        $imagePath = 'qrcodes/' . $child->child_code . '.svg';
        Storage::disk('public')->put($imagePath, $qrImage);

        $existing = QRCode::query()
            ->where('child_id', $child->id)
            ->where('token_hash', $tokenHash)
            ->first();

        if ($existing) {
            $existing->update([
                'status' => 'active',
                'qr_image_path' => $imagePath,
            ]);
            return $existing;
        }

        // Save QR information.
        return QRCode::create([
            'child_id' => $child->id,
            'token_hash' => $tokenHash,
            'qr_image_path' => $imagePath,
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => null,
        ]);
    }

    /**
     * Generate a self-contained offline payload for a child.
     * Contains essential identification data so it can be verified
     * anywhere without internet, network access, or an external API.
     *
     * @param Child $child
     * @return array<string, mixed>
     */
    public function generateOfflinePayload(Child $child): array
    {
        // Keep the legacy method name for existing callers, but return no PII.
        return ['code' => $child->child_code];
    }

    /**
     * Generate offline JSON string for a child.
     */
    public function generateOfflineJson(Child $child): string
    {
        return $child->child_code;
    }

}
