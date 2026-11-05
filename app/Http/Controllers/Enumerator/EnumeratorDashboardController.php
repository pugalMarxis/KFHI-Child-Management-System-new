<?php

namespace App\Http\Controllers\Enumerator;


use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Enumerator;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\QRCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnumeratorDashboardController extends Controller
{
    /**
     * Show the individual identity verification form.
     */
    // public function showVerify(Request $request): View
    // {
    //     $commonPassword = Enumerator::getCommonPassword();
    //     return view('enumerator.verify', compact('commonPassword'));
    // }
            public function showVerify(): View
        {
            return view('enumerator.verify');
        }
    /**
     * Process identity verification for the enumerator session using Name + Common Password or Code + PIN.
     */
    public function verify(Request $request): RedirectResponse
    {
        $rawIdentifier = trim($request->input('enumerator_name') ?: $request->input('name') ?: $request->input('enumerator_code') ?: '');
        $pin = trim($request->input('pin_code') ?: $request->input('access_password') ?: $request->input('password') ?: '');

        if (empty($rawIdentifier)) {
            return back()
                ->withInput()
                ->withErrors(['enumerator_name' => 'Please enter your Full Name.']);
        }

        if (empty($pin)) {
            return back()
                ->withInput()
                ->withErrors(['pin_code' => 'Please enter the Common Access Password.']);
        }

        $enumerator = null;

        // 1. Check if common access password is valid
        if (Enumerator::verifyCommonPassword($pin)) {
            $enumerator = Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->where(function ($q) use ($rawIdentifier) {
                    $q->where('name', $rawIdentifier)
                      ->orWhere('enumerator_code', strtoupper($rawIdentifier));
                })->first();

           
        if (!$enumerator) {
                $lastId = Enumerator::where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                    ->max('id') ?? 0;

                $enumerator = Enumerator::create([
                    'enumerator_code' => 'ENUM-' . str_pad((string) ($lastId + 1), 3, '0', STR_PAD_LEFT),
                    'name' => $rawIdentifier,
                    'phone' => $request->input('phone'),
                    'pin_code' => $pin,
                    'status' => 'active',
                    'created_by' => auth()->id() ?? 1,
                ]);
            }

            if (!$enumerator->isActive()) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'enumerator_name' => 'Your enumerator account is inactive. Ask your supervisor for help.',
                    ]);
          }
        } else {
            // 2. Check individual code + individual PIN
            $enumerator = Enumerator::where('enumerator_code', strtoupper($rawIdentifier))
                ->where('enumerator_code', '!=', 'GLOBAL_PASSWORD')
                ->first();

            if (!$enumerator || !$enumerator->isActive() || !$enumerator->verifyPin($pin)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'pin_code' => 'Invalid Access Password or PIN. Please enter the correct common password.',
                    ]);
            }
        }

        // Establish the verified identity in the session
        session([
            'verified_enumerator_id' => $enumerator->id,
            'verified_enumerator_code' => $enumerator->enumerator_code,
            'verified_enumerator_name' => $enumerator->name,
        ]);

        return redirect()->intended(route('enumerator.dashboard'))
            ->with('success', "Welcome, {$enumerator->name}! You are active as {$enumerator->enumerator_code}.");
    }

    public function lookupChild(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:10000'],
        ]);

        $enumerator = Enumerator::find(session('verified_enumerator_id'));
        if (!$enumerator || !$enumerator->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Enumerator access has expired. Verify again before looking up a child.',
            ], 401);
        }

        $scannedValue = trim($validated['token']);
        $child = null;
        $qrCode = null;

        if (str_starts_with($scannedValue, '{')) {
            $payload = json_decode($scannedValue, true);
            $childCode = is_array($payload) ? ($payload['code'] ?? $payload['c'] ?? null) : null;
            if ($childCode) {
                $child = Child::where('child_code', $childCode)->first();
            }
        } elseif (str_contains($scannedValue, '/qr/scan/')) {
            $path = parse_url($scannedValue, PHP_URL_PATH);
            if ($path && preg_match('~(?:^|/)qr/scan/([^/]+)$~', $path, $matches)) {
                $qrCode = QRCode::with('child')
                    ->where('token_hash', hash('sha256', $matches[1]))
                    ->where('status', 'active')
                    ->first();
                $child = $qrCode?->child;
            }
        } else {
            $child = Child::where('child_code', $scannedValue)->first();
        }

        if (!$child || $child->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Child code was not found, is inactive, or is not available to your account.',
            ], 404);
        }

        if (!$enumerator->allowedChildren()->where('children.id', $child->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This child has not been allowed for your account. Ask your supervisor to sync the child to your account.',
            ], 403);
        }

        if ($qrCode) {
            $qrCode->update(['last_scanned_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Child identified successfully.',
            'child' => [
                'id' => $child->id,
                'child_code' => $child->child_code,
                'first_name' => $child->first_name,
                'middle_name' => $child->middle_name,
                'last_name' => $child->last_name,
                'full_name' => trim(implode(' ', array_filter([
                    $child->first_name,
                    $child->middle_name,
                    $child->last_name,
                ], fn ($part) => filled($part)))),
                'gender' => $child->gender,
                'date_of_birth' => $child->date_of_birth?->format('Y-m-d'),
                'school_name' => $child->school_name,
                'grade' => $child->grade,
                'district' => $child->district,
                'gn_division' => $child->gn_division,
                'address' => $child->address,
                'status' => $child->status,
            ],
        ]);
    }

    /**
     * Enumerator Dashboard.
     */
    public function dashboard(Request $request): View|RedirectResponse
    {
        $enumeratorId = session('verified_enumerator_id');

        if (!$enumeratorId) {
            return redirect()->route('enumerator.verify')
                ->with('info', 'Please verify your individual Enumerator ID before proceeding.');
        }

        $enumerator = Enumerator::find($enumeratorId);
        if (!$enumerator || !$enumerator->isActive()) {
            session()->forget(['verified_enumerator_id', 'verified_enumerator_code', 'verified_enumerator_name']);
            return redirect()->route('enumerator.verify')
                ->withErrors(['enumerator_code' => 'Your enumerator account is inactive or not found.']);
        }

        // Retrieve questionnaires assigned to this enumerator, plus any open published questionnaires
        $assignedIds = $enumerator->questionnaires()->pluck('questionnaires.id')->all();

        $questionnaires = Questionnaire::where('status', 'published')
            ->where(function ($q) use ($assignedIds) {
                if (!empty($assignedIds)) {
                    $q->whereIn('id', $assignedIds)
                      ->orWhere('requires_enumerator', false);
                }
            })
            ->with(['event', 'questions'])
            ->latest()
            ->get();

        // Individual enumerator stats
        $stats = [
            'total_submitted' => QuestionnaireResponse::where('enumerator_id', $enumerator->id)->count(),
            'today_submitted' => QuestionnaireResponse::where('enumerator_id', $enumerator->id)
                ->whereDate('submitted_at', today())
                ->count(),
            'awaiting_review' => QuestionnaireResponse::where('enumerator_id', $enumerator->id)
                ->whereIn('review_status', ['submitted', 'under_review'])
                ->count(),
            'returned_or_rejected' => QuestionnaireResponse::where('enumerator_id', $enumerator->id)
                ->whereIn('review_status', ['returned', 'rejected'])
                ->count(),
        ];

        return view('enumerator.dashboard', compact('enumerator', 'questionnaires', 'stats'));
    }

    /**
     * Clear current individual identity to allow another enumerator to verify.
     */
    public function switchIdentity(): RedirectResponse
    {
        session()->forget([
            'verified_enumerator_id',
            'verified_enumerator_code',
            'verified_enumerator_name',
        ]);

        return redirect()->route('enumerator.verify')
            ->with('info', 'Identity cleared. Please enter your Enumerator ID and PIN.');
    }
}
