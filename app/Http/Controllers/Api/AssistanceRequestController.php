<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceDocument;
use App\Models\AssistanceRequest;
use App\Models\Demarche;
use App\Models\User;
use App\Services\CloudinaryService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssistanceRequestController extends Controller
{
    public function __construct(
        private readonly CloudinaryService $cloudinary,
        private readonly StripeService $stripe
    ) {
    }

    /**
     * Public: Return Stripe publishable key for frontend card element initialization.
     */
    public function stripeConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'publishable_key' => $this->stripe->getPublishableKey(),
        ]);
    }

    /**
     * Public: Create a Stripe PaymentIntent for an assistance or prestation procedure card payment.
     */
    public function createStripeIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'demarche_id' => 'nullable|integer',
            'demarche_title' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'amount' => 'nullable|numeric|min:0.5',
            'tracking_code' => 'nullable|string|max:60',
            'request_id' => 'nullable|integer',
        ]);

        $assistanceRequest = null;
        if (!empty($validated['request_id'])) {
            $assistanceRequest = AssistanceRequest::find($validated['request_id']);
        } elseif (!empty($validated['tracking_code'])) {
            $assistanceRequest = AssistanceRequest::where('tracking_code', $validated['tracking_code'])->first();
        }

        $email = $validated['email'] ?? ($assistanceRequest?->email ?? 'client@sejour-bresil.org');
        $demarche = !empty($validated['demarche_id']) ? Demarche::find($validated['demarche_id']) : null;

        if ($request->filled('amount') && (float) $request->amount >= 0.5) {
            $amount = (float) $request->amount;
        } elseif ($assistanceRequest && (float) $assistanceRequest->amount > 0) {
            $amount = (float) $assistanceRequest->amount;
        } elseif ($demarche !== null && (float) ($demarche->assistance_price ?? 0) > 0) {
            $amount = (float) $demarche->assistance_price;
        } else {
            $amount = 50.00;
        }

        if ($amount < 0.50) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant minimum pour un paiement par carte Stripe est de R$ 0,50.',
            ], 422);
        }

        $amountCents = (int) round($amount * 100);
        $title = $assistanceRequest?->demarche_title
            ?? ($demarche ? $demarche->title : ($validated['demarche_title'] ?? 'Prestation Séjour au Brésil'));

        $customerName = $validated['full_name']
            ?? ($assistanceRequest?->full_name ?? 'Client Séjour Brésil');

        $intent = $this->stripe->createPaymentIntent(
            amountCents: $amountCents,
            currency: 'brl',
            metadata: [
                'demarche_id' => (string) ($demarche?->id ?? ''),
                'demarche_title' => Str::limit($title, 100),
                'tracking_code' => (string) ($assistanceRequest?->tracking_code ?? ($validated['tracking_code'] ?? '')),
                'customer_name' => Str::limit((string) $customerName, 100),
                'customer_email' => strtolower(trim($email)),
            ],
            receiptEmail: strtolower(trim($email)),
            description: "Séjour au Brésil : {$title}"
        );

        return response()->json([
            'success' => true,
            'client_secret' => $intent['client_secret'],
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
            'currency' => 'brl',
            'publishable_key' => $this->stripe->getPublishableKey(),
        ]);
    }

    /**
     * Public: Submit a new assistance request with files (images/PDF) uploaded to Cloudinary.
     * Automatically attaches or creates a 'user' account by email.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'demarche_id' => 'nullable',
            'demarche_title' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:60',
            'nationality' => 'nullable|string|max:120',
            'location_status' => 'nullable|string|max:100',
            'city_country' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:500',
            'details' => 'nullable|string|max:5000',
            'payment_method' => 'nullable|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
            'stripe_payment_intent_id' => 'nullable|string|max:255',
            'card_last4' => 'nullable|string|max:4',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'doc_keys' => 'nullable|array',
            'doc_labels' => 'nullable|array',
        ]);

        $email = strtolower(trim($validated['email']));
        $rawDemarcheId = $request->input('demarche_id');
        $demarcheTitle = trim($validated['demarche_title']);
        $isPrestation = ($rawDemarcheId === 999 || $rawDemarcheId === '999')
            || (!empty($rawDemarcheId) && !is_numeric($rawDemarcheId))
            || Str::startsWith(strtolower($demarcheTitle), 'prestation')
            || $request->input('category') === 'prestation';

        $numericDemarcheId = (is_numeric($rawDemarcheId) && (int) $rawDemarcheId !== 999) ? (int) $rawDemarcheId : null;
        $demarche = (!$isPrestation && $numericDemarcheId) ? Demarche::find($numericDemarcheId) : null;
        $demarcheId = $demarche ? $demarche->id : null;

        if ($isPrestation) {
            $amount = 0.00;
            $paymentMethod = $validated['payment_method'] ?? 'sur_devis';
        } else {
            $amount = $demarche !== null ? (float) ($demarche->assistance_price ?? 50.00) : 50.00;
            $paymentMethod = $validated['payment_method'] ?? ($amount <= 0 ? 'free' : 'apres_reponse');
        }

        $verifiedStripeReference = null;
        if (!$isPrestation && $amount > 0 && $paymentMethod === 'card') {
            $piId = trim((string) ($validated['stripe_payment_intent_id'] ?? ''));
            if ($piId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le paiement par carte Stripe est requis avant l’envoi du dossier.',
                ], 422);
            }

            if (AssistanceRequest::where('payment_reference', $piId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce paiement Stripe a déjà été utilisé pour une autre demande.',
                ], 422);
            }

            $intent = $this->stripe->retrievePaymentIntent($piId);
            $expectedCents = (int) round($amount * 100);
            $receivedCents = (int) ($intent['amount_received'] ?? $intent['amount'] ?? 0);

            if (($intent['status'] ?? '') !== 'succeeded' || $receivedCents < $expectedCents || strtolower((string) ($intent['currency'] ?? '')) !== 'brl') {
                return response()->json([
                    'success' => false,
                    'message' => 'Le paiement par carte Stripe n’a pas encore été validé ou le montant ne correspond pas.',
                ], 422);
            }

            $verifiedStripeReference = $intent['id'];
        }

        $result = DB::transaction(function () use ($request, $validated, $email, $demarcheId, $demarcheTitle, $amount, $paymentMethod, $isPrestation, $verifiedStripeReference) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => trim($validated['full_name']),
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                ]);
            }

            // Generate unique tracking code (PS- for prestations, AS- for assistance)
            $codePrefix = $isPrestation ? 'PS-' : 'AS-';
            do {
                $code = $codePrefix . date('Y') . '-' . strtoupper(Str::random(5));
            } while (AssistanceRequest::where('tracking_code', $code)->exists());

            // Resolve payment method & status
            $paymentStatus = 'pending_payment';
            $paymentReference = $validated['payment_reference'] ?? null;
            $paymentProofUrl = null;

            if ($isPrestation) {
                $paymentStatus = 'pending_payment';
                $paymentReference = $paymentReference ?? ('DEVIS-' . $code);
            } elseif ($amount <= 0) {
                $paymentMethod = 'free';
                $paymentStatus = 'paid';
            } elseif ($paymentMethod === 'card') {
                $paymentStatus = 'paid';
                $paymentReference = $verifiedStripeReference;
            } elseif ($paymentMethod === 'pix') {
                $paymentStatus = 'awaiting_verification';
                if (empty($paymentReference)) {
                    $paymentReference = 'PIX-' . $code;
                }
                if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
                    try {
                        $proofUpload = $this->cloudinary->upload(
                            $request->file('payment_proof'),
                            "sejour_bresil/assistance/{$code}/payment"
                        );
                        $paymentProofUrl = $proofUpload['secure_url'];
                    } catch (\Throwable $proofErr) {
                        \Log::warning("Upload payment proof local fallback: " . $proofErr->getMessage());
                        $path = $request->file('payment_proof')->store("assistance/{$code}/payment", 'public');
                        $paymentProofUrl = url('storage/' . $path);
                    }
                }
            } else {
                $paymentStatus = 'pending_payment';
            }

            $assistanceRequest = AssistanceRequest::create([
                'tracking_code' => $code,
                'user_id' => $user->id,
                'demarche_id' => $demarcheId,
                'demarche_title' => $demarcheTitle,
                'full_name' => trim($validated['full_name']),
                'email' => $email,
                'phone' => $validated['phone'] ?? null,
                'nationality' => $validated['nationality'] ?? null,
                'location_status' => $validated['location_status'] ?? null,
                'city_country' => $validated['city_country'] ?? null,
                'purpose' => trim($validated['purpose']),
                'details' => $validated['details'] ?? null,
                'amount' => $amount,
                'currency' => 'BRL',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $paymentReference,
                'payment_proof_url' => $paymentProofUrl,
                'status' => 'pending',
            ]);

            $uploadedFiles = $request->file('files', []);
            $docKeys = $request->input('doc_keys', []);
            $docLabels = $request->input('doc_labels', []);

            foreach ($uploadedFiles as $idx => $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                try {
                    $uploadResult = $this->cloudinary->upload(
                        $file,
                        "sejour_bresil/assistance/{$code}"
                    );
                    $fileUrl = $uploadResult['secure_url'];
                    $publicId = $uploadResult['public_id'];
                    $resourceType = $uploadResult['resource_type'];
                    $originalName = $uploadResult['original_filename'];
                } catch (\Throwable $fileErr) {
                    \Log::warning("Upload assistance document local fallback: " . $fileErr->getMessage());
                    $path = $file->store("assistance/{$code}", 'public');
                    $fileUrl = url('storage/' . $path);
                    $publicId = 'local_' . basename($path);
                    $resourceType = 'local';
                    $originalName = $file->getClientOriginalName();
                }

                AssistanceDocument::create([
                    'assistance_request_id' => $assistanceRequest->id,
                    'doc_key' => $docKeys[$idx] ?? ('doc_' . ($idx + 1)),
                    'doc_label' => $docLabels[$idx] ?? $originalName,
                    'file_url' => $fileUrl,
                    'public_id' => $publicId,
                    'resource_type' => $resourceType,
                    'original_name' => $originalName,
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }

            return [$user, $assistanceRequest];
        });

        [$user, $assistanceRequest] = $result;

        $token = null;
        if (!$user->isAdmin()) {
            $token = $user->createToken('user-assistance-token')->plainTextToken;
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre demande d\'assistance a été transmise avec succès.',
            'data' => $assistanceRequest->load('documents'),
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'user',
            ],
        ], 201);
    }

    /**
     * Authenticated User: Fetch all assistance requests belonging to the logged-in user.
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = AssistanceRequest::with(['documents', 'demarche'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', strtolower($user->email));
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $requests->count(),
            'data' => $requests,
        ]);
    }

    /**
     * Admin: List all assistance requests with filtering.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès réservé aux administrateurs.'], 403);
        }

        $query = AssistanceRequest::with(['documents', 'demarche']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('demarche_id') && $request->demarche_id !== 'all') {
            $query->where('demarche_id', (int) $request->demarche_id);
        }

        if ($request->filled('category')) {
            if ($request->category === 'prestations') {
                $query->where(function ($q) {
                    $q->where('demarche_id', 999)
                      ->orWhere('demarche_title', 'like', 'Prestation%')
                      ->orWhere('tracking_code', 'like', 'PS-%');
                });
            } elseif ($request->category === 'demarches') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->whereNull('demarche_id')
                            ->orWhere('demarche_id', '!=', 999);
                    })->where('demarche_title', 'not like', 'Prestation%')
                      ->where('tracking_code', 'not like', 'PS-%');
                });
            }
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('tracking_code', 'like', $s)
                  ->orWhere('full_name', 'like', $s)
                  ->orWhere('email', 'like', $s)
                  ->orWhere('purpose', 'like', $s)
                  ->orWhere('demarche_title', 'like', $s);
            });
        }

        $items = $query->orderByDesc('created_at')->get();

        $demarchesCount = AssistanceRequest::where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('demarche_id')
                    ->orWhere('demarche_id', '!=', 999);
            })->where('demarche_title', 'not like', 'Prestation%')
              ->where('tracking_code', 'not like', 'PS-%');
        })->count();

        $prestationsCount = AssistanceRequest::where(function ($q) {
            $q->where('demarche_id', 999)
              ->orWhere('demarche_title', 'like', 'Prestation%')
              ->orWhere('tracking_code', 'like', 'PS-%');
        })->count();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'stats' => [
                'total' => AssistanceRequest::count(),
                'demarches_total' => $demarchesCount,
                'prestations_total' => $prestationsCount,
                'pending' => AssistanceRequest::where('status', 'pending')->count(),
                'in_review' => AssistanceRequest::where('status', 'in_review')->count(),
                'eligible_payment' => AssistanceRequest::where('status', 'eligible_payment')->count(),
                'action_required' => AssistanceRequest::where('status', 'action_required')->count(),
                'completed' => AssistanceRequest::where('status', 'completed')->count(),
                'paid_count' => AssistanceRequest::where('payment_status', 'paid')->count(),
                'paid_amount' => (float) AssistanceRequest::where('payment_status', 'paid')->sum('amount'),
                'awaiting_payment_count' => AssistanceRequest::whereIn('payment_status', ['pending_payment', 'awaiting_verification'])->count(),
                'users_count' => User::where('role', 'user')->count(),
            ],
            'data' => $items,
        ]);
    }

    /**
     * Admin: Respond to an assistance request (update status, write feedback, optionally upload return PDF/image).
     */
    public function adminRespond(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Accès réservé aux administrateurs.'], 403);
        }

        $assistanceRequest = AssistanceRequest::find($id);
        if (!$assistanceRequest) {
            return response()->json(['success' => false, 'message' => 'Demande introuvable.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_review,eligible_payment,action_required,completed,rejected',
            'payment_status' => 'nullable|in:pending_payment,awaiting_verification,paid,refunded',
            'amount' => 'nullable|numeric|min:0',
            'admin_feedback' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            try {
                $uploadResult = $this->cloudinary->upload(
                    $file,
                    "sejour_bresil/assistance/{$assistanceRequest->tracking_code}/responses"
                );
                $assistanceRequest->admin_attachment_url = $uploadResult['secure_url'];
                $assistanceRequest->admin_attachment_name = $uploadResult['original_filename'];
                $assistanceRequest->admin_attachment_public_id = $uploadResult['public_id'];
            } catch (\Throwable $respErr) {
                \Log::warning("Admin response attachment fallback to local: " . $respErr->getMessage());
                $path = $file->store("assistance/{$assistanceRequest->tracking_code}/responses", 'public');
                $assistanceRequest->admin_attachment_url = url('storage/' . $path);
                $assistanceRequest->admin_attachment_name = $file->getClientOriginalName();
                $assistanceRequest->admin_attachment_public_id = 'local_' . basename($path);
            }
        }

        $assistanceRequest->status = $validated['status'];
        if (!empty($validated['payment_status'])) {
            $assistanceRequest->payment_status = $validated['payment_status'];
        }
        if (isset($validated['amount'])) {
            $assistanceRequest->amount = (float) $validated['amount'];
        }
        $assistanceRequest->admin_feedback = $validated['admin_feedback'] ?? $assistanceRequest->admin_feedback;
        $assistanceRequest->responded_by = $user->id;
        $assistanceRequest->responded_at = now();
        $assistanceRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'Retour enregistré et transmis à l\'usager.',
            'data' => $assistanceRequest->fresh(['documents', 'demarche']),
        ]);
    }

    /**
     * Public / User: Confirm payment for an assistance request (Pix proof upload or Stripe confirmation).
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => 'required|integer',
            'payment_method' => 'nullable|string|in:pix,card,flooz,tmoney,mixx',
            'stripe_payment_intent_id' => 'nullable|string|max:255',
            'card_last4' => 'nullable|string|max:4',
            'transaction_ref' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $item = AssistanceRequest::find($validated['request_id']);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Dossier introuvable.'], 404);
        }

        $method = $validated['payment_method'] ?? 'pix';
        if ($method === 'mixx') {
            $method = 'tmoney';
        }

        if ($request->hasFile('proof_file') && $request->file('proof_file')->isValid()) {
            $file = $request->file('proof_file');
            try {
                $upload = $this->cloudinary->upload($file, "sejour_bresil/assistance/{$item->tracking_code}/payments");
                $item->payment_proof_url = $upload['secure_url'];
                $item->payment_proof_public_id = $upload['public_id'];
            } catch (\Throwable $e) {
                \Log::warning("Payment proof upload fallback to local: " . $e->getMessage());
                $path = $file->store("assistance/{$item->tracking_code}/payments", 'public');
                $item->payment_proof_url = url('storage/' . $path);
                $item->payment_proof_public_id = 'local_' . basename($path);
            }
        }

        $item->payment_method = $method;

        if ($method === 'pix') {
            if ($item->payment_status !== 'paid') {
                $item->payment_status = 'awaiting_verification';
            }
            if (!empty($validated['transaction_ref'])) {
                $item->payment_reference = $validated['transaction_ref'];
            }
        } elseif ($method === 'flooz' || $method === 'tmoney') {
            if ($item->payment_status !== 'paid') {
                $item->payment_status = 'awaiting_verification';
            }
            $refParts = [];
            if (!empty($validated['transaction_ref'])) {
                $refParts[] = 'Réf: ' . $validated['transaction_ref'];
            }
            if (!empty($validated['phone_number'])) {
                $refParts[] = 'Tél: ' . $validated['phone_number'];
            }
            if (!empty($refParts)) {
                $item->payment_reference = implode(' | ', $refParts);
            }
        } elseif ($method === 'card') {
            $item->payment_status = 'paid';
            if (!empty($validated['stripe_payment_intent_id'])) {
                $item->stripe_payment_intent_id = $validated['stripe_payment_intent_id'];
                $item->payment_reference = $validated['stripe_payment_intent_id'];
            }
            if (!empty($validated['card_last4'])) {
                $item->card_last4 = $validated['card_last4'];
            }
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès.',
            'data' => $item->fresh(['documents', 'demarche']),
        ]);
    }
}
