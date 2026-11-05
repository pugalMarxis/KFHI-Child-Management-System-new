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

        $child->qrCode()
            ->where('status', 'active')
            ->update([
                'status' => 'inactive',
            ]);

        $qrCode = $qrCodeService->generateForChild($child);

        return redirect()
            ->route('children.show', $child)
            ->with(
                'success',
                'QR code generated successfully.'
            );
    }

    public function show(Child $child): View
    {
        $this->authorizeAdmin();

        $qrCode = $child->qrCode;

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

    public function offlineQr(Request $request, QRCodeService $qrService): View
    {
        $this->authorizeAdmin();

        $children = Child::with('guardians')
            ->orderBy('first_name')
            ->get();

        $childrenData = $children->map(function ($child) use ($qrService) {
            $primaryGuardian = $child->guardians->where('is_primary', true)->first()
                ?: $child->guardians->first();

            return [
                'id' => $child->id,
                'child_code' => $child->child_code,
                'full_name' => trim(
                    $child->first_name . ' ' .
                    ($child->middle_name ? $child->middle_name . ' ' : '') .
                    $child->last_name
                ),
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'gender' => $child->gender,
                'date_of_birth' => $child->date_of_birth ? $child->date_of_birth->format('Y-m-d') : '',
                'school_name' => $child->school_name,
                'grade' => $child->grade,
                'district' => $child->district,
                'gn_division' => $child->gn_division,
                'status' => $child->status ?? 'active',
                'profile_photo' => $child->profile_photo ? asset('storage/' . $child->profile_photo) : null,
                'guardian_name' => $primaryGuardian ? $primaryGuardian->full_name : '',
                'guardian_relation' => $primaryGuardian ? $primaryGuardian->relationship : '',
                'guardian_phone' => $primaryGuardian ? $primaryGuardian->phone : '',
                'offline_payload' => $qrService->generateOfflinePayload($child),
            ];
        });

        return view('admin.qr_codes.offline', [
            'children' => $children,
            'childrenData' => $childrenData,
            'activeTool' => in_array($request->query('tool'), ['generator', 'scanner', 'log'], true)
                ? $request->query('tool')
                : 'generator',
        ]);
    }

    public function downloadOfflineSvg(Child $child, QRCodeService $qrService)
    {
        $this->authorizeAdmin();

        $svg = $qrService->generateOfflineSvg($child, 400);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $child->child_code . '_offline_qr.svg"',
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

}