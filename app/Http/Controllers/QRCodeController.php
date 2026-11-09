<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\QRCode;
use App\Services\QRCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QRCodeController extends Controller
{
    public function generate(
        Child $child,
        QRCodeService $qrCodeService
    ): RedirectResponse {
        $this->authorizeAdmin();

        $qrCode = $qrCodeService->generateForChild($child);

        return redirect()
            ->route('children.show', $child)
            ->with(
                'success',
                'QR code generated successfully.'
            );
    }

    public function show(Child $child, QRCodeService $qrCodeService): View
    {
        $this->authorizeAdmin();

        // Refresh the saved image so this card always shows the permanent child-code QR.
        $qrCode = $qrCodeService->generateForChild($child);

        return view(
            'admin.qr_codes.show',
            compact('child', 'qrCode')
        );
    }

    public function scan(string $token): View
    {
        $tokenHash = hash('sha256', $token);

        $qrCode = QRCode::with('child')
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
            ->first();

        if (!$qrCode) {
            abort(404, 'Invalid or inactive QR code.');
        }

        $qrCode->update([
            'last_scanned_at' => now(),
        ]);

        $child = $qrCode->child;

        return view(
            'qr.scan',
            compact('child', 'qrCode')
        );
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

}
