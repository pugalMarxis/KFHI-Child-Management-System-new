<?php

namespace App\Http\Controllers\ChildOfficer;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\QRCode;
use Illuminate\Http\Request;

class QRCodeController extends Controller
{
    public function scan()
    {
        return view('child_officer.qr.scan');
    }

    public function lookup(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $scannedValue = trim($request->token);

        // Check if scanned value is self-contained JSON (offline QR format)
        if (str_starts_with($scannedValue, '{') && str_ends_with($scannedValue, '}')) {
            $jsonData = json_decode($scannedValue, true);
            if (is_array($jsonData)) {
                $code = $jsonData['code'] ?? null;
                $child = null;
                if ($code) {
                    $child = Child::where('child_code', $code)->first();
                }

                if ($child) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Child identified successfully (Offline QR Code).',
                        'child' => [
                            'id' => $child->id,
                            'child_code' => $child->child_code,
                            'full_name' => trim(preg_replace('/\s+/', ' ', $child->first_name . ' ' . ($child->middle_name ?? '') . ' ' . $child->last_name)),
                            'gender' => $child->gender,
                            'date_of_birth' => $child->date_of_birth
                                ? $child->date_of_birth->format('Y-m-d')
                                : null,
                            'school_name' => $child->school_name,
                            'grade' => $child->grade,
                            'district' => $child->district,
                            'gn_division' => $child->gn_division,
                            'status' => $child->status,
                        ],
                    ]);
                } elseif (!empty($jsonData['name'])) {
                    // Even if child not found in local database, return decoded offline data
                    return response()->json([
                        'success' => true,
                        'message' => 'Child identified from offline QR badge data.',
                        'child' => [
                            'id' => $jsonData['id'] ?? 0,
                            'child_code' => $jsonData['code'] ?? 'N/A',
                            'full_name' => $jsonData['name'],
                            'gender' => $jsonData['gender'] ?? 'N/A',
                            'date_of_birth' => $jsonData['dob'] ?? 'N/A',
                            'school_name' => $jsonData['school'] ?? 'N/A',
                            'grade' => $jsonData['grade'] ?? 'N/A',
                            'district' => $jsonData['district'] ?? 'N/A',
                            'gn_division' => $jsonData['gn'] ?? 'N/A',
                            'status' => $jsonData['status'] ?? 'active',
                        ],
                    ]);
                }
            }
        }

        // Synced child ID cards contain only the permanent child code.
        // Accept that code as well as the older URL-token and JSON QR formats.
        if (!str_contains($scannedValue, '/qr/scan/')) {
            $child = Child::where('child_code', $scannedValue)->first();

            if (!$child) {
                return response()->json([
                    'success' => false,
                    'message' => 'This child code was not found. Check that this child is registered in the CMS.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Child identified successfully from the synced child code.',
                'child' => [
                    'id' => $child->id,
                    'child_code' => $child->child_code,
                    'full_name' => trim(preg_replace('/\\s+/', ' ', $child->first_name . ' ' . ($child->middle_name ?? '') . ' ' . $child->last_name)),
                    'gender' => $child->gender,
                    'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                    'school_name' => $child->school_name,
                    'grade' => $child->grade,
                    'district' => $child->district,
                    'gn_division' => $child->gn_division,
                    'status' => $child->status,
                ],
            ]);
        }

        /*
         * The existing KFHI QR code contains:
         *
         * /qr/scan/{token}
         *
         * Extract only the token from the scanned URL.
         */
        $path = parse_url($scannedValue, PHP_URL_PATH);

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code format.',
            ], 400);
        }

        if (!preg_match('~(?:^|/)qr/scan/([^/]+)$~', $path, $matches)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid KFHI QR code.',
            ], 400);
        }

        $token = $matches[1];

        // Hash the original token exactly like QRCodeService.
        $tokenHash = hash('sha256', $token);

        // Find the active QR code.
        $qrCode = QRCode::with('child')
            ->where('token_hash', $tokenHash)
            ->where('status', 'active')
            ->first();

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive QR code.',
            ], 404);
        }

        // Update last scanned time.
        $qrCode->update([
            'last_scanned_at' => now(),
        ]);

        $child = $qrCode->child;

        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Child identified successfully.',

            'child' => [
                'id' => $child->id,

                'child_code' => $child->child_code,

                'full_name' => trim(preg_replace('/\s+/', ' ', $child->first_name . ' ' . ($child->middle_name ?? '') . ' ' . $child->last_name)),

                'gender' => $child->gender,

                'date_of_birth' => $child->date_of_birth
                    ? $child->date_of_birth->format('Y-m-d')
                    : null,

                'school_name' => $child->school_name,

                'grade' => $child->grade,

                'district' => $child->district,

                'gn_division' => $child->gn_division,

                'status' => $child->status,
            ],
        ]);
    }
}
