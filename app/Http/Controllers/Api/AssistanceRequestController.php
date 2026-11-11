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
     * Public: Create a Stripe PaymentIntent for an assistance procedure card payment.
     */
    public function createStripeIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'demarche_id' => 'nullable|integer',
            'demarche_title' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $demarche = !empty($validated['demarche_id']) ? Demarche::find($validated['demarche_id']) : null;
        $amount = $demarche !== null ? (float) ($demarche->assistance_price ?? 50.00) : 50.00;

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cette démarche est gratuite et ne nécessite pas de paiement.',
            ], 422);
        }

        $amountCents = (int) round($amount * 100);
        if ($amountCents < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant minimum pour un paiement par carte Stripe est de R$ 0,50.',
            ], 422);
        }

        $title = $demarche ? $demarche->title : ($validated['demarche_title'] ?? 'Assistance Séjour au Brésil');

        $intent = $this->stripe->createPaymentIntent(
            amountCents: $amountCents,
            currency: 'brl',
            metadata: [
                'demarche_id' => (string) ($demarche?->id ?? ''),
                'demarche_title' => Str::limit($title, 100),
                'customer_name' => Str::limit((string) ($validated['full_name'] ?? ''), 100),
                'customer_email' => strtolower(trim($validated['email'])),
            ],
            receiptEmail: strtolower(trim($validated['email'])),
            description: "Assistance : {$title}"
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
            'demarche_id' => 'nullable|integer',
            'demarche_title' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:60',
            'nationality' => 'nullable|string|max:120',
            'location_status' => 'nullable|string|max:100',
            'city_country' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:500',
            'details' => 'nullable|string|max:5000',
            'payment_method' => 'nullable|in:pix,card,free',
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
        $demarche = !empty($validated['demarche_id']) ? Demarche::find($validated['demarche_id']) : null;
        $demarcheId = $demarche ? $demarche->id : null;
        $amount = $demarche !== null ? (float) ($demarche->assistance_price ?? 50.00) : 50.00;
        $paymentMethod = $validated['payment_method'] ?? ($amount <= 0 ? 'free' : 'pix');

        $verifiedStripeReference = null;
        if ($amount > 0 && $paymentMethod === 'card') {
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

        $result = DB::transaction(function () use ($request, $validated, $email, $demarcheId, $amount, $paymentMethod, $verifiedStripeReference) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => trim($validated['full_name']),
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                ]);
            }

            // Generate unique tracking code
            do {
                $code = 'AS-' . date('Y') . '-' . strtoupper(Str::random(5));
            } while (AssistanceRequest::where('tracking_code', $code)->exists());

            // Resolve payment method & status
            $paymentStatus = 'pending_payment';
            $paymentReference = $validated['payment_reference'] ?? null;
            $paymentProofUrl = null;

            if ($amount <= 0) {
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
                    $proofUpload = $this->cloudinary->upload(
                        $request->file('payment_proof'),
                        "sejour_bresil/assistance/{$code}/payment"
                    );
                    $paymentProofUrl = $proofUpload['secure_url'];
                }
            }

            $assistanceRequest = AssistanceRequest::create([
                'tracking_code' => $code,
                'user_id' => $user->id,
                'demarche_id' => $demarcheId,
                'demarche_title' => trim($validated['demarche_title']),
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

                $uploadResult = $this->cloudinary->upload(
                    $file,
                    "sejour_bresil/assistance/{$code}"
                );

                AssistanceDocument::create([
                    'assistance_request_id' => $assistanceRequest->id,
                    'doc_key' => $docKeys[$idx] ?? ('doc_' . ($idx + 1)),
                    'doc_label' => $docLabels[$idx] ?? $uploadResult['original_filename'],
                    'file_url' => $uploadResult['secure_url'],
                    'public_id' => $uploadResult['public_id'],
                    'resource_type' => $uploadResult['resource_type'],
                    'original_name' => $uploadResult['original_filename'],
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

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'stats' => [
                'total' => AssistanceRequest::count(),
                'pending' => AssistanceRequest::where('status', 'pending')->count(),
                'in_review' => AssistanceRequest::where('status', 'in_review')->count(),
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
            'status' => 'required|in:pending,in_review,action_required,completed,rejected',
            'payment_status' => 'nullable|in:pending_payment,awaiting_verification,paid,refunded',
            'admin_feedback' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $uploadResult = $this->cloudinary->upload(
                $file,
                "sejour_bresil/assistance/{$assistanceRequest->tracking_code}/responses"
            );
            $assistanceRequest->admin_attachment_url = $uploadResult['secure_url'];
            $assistanceRequest->admin_attachment_name = $uploadResult['original_filename'];
            $assistanceRequest->admin_attachment_public_id = $uploadResult['public_id'];
        }

        $assistanceRequest->status = $validated['status'];
        if (!empty($validated['payment_status'])) {
            $assistanceRequest->payment_status = $validated['payment_status'];
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
}
