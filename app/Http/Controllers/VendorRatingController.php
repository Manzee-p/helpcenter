<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Ticket;
use App\Models\User;
use App\Models\VendorWarning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VendorRatingController extends Controller
{
    private const LOW_RATING_MAX             = 2;
    private const MIN_COMPLETED_TICKETS      = 10;
    private const SYSTEM_WARNING_THRESHOLD   = 5;
    private const ADMIN_WARNING_THRESHOLD    = 8;

    // ── Blade: Admin vendor ratings index ─────────────────────
    public function adminIndex(Request $request)
    {
        try {
            $warningTableExists = Schema::hasTable('vendor_warnings');
            $attentionVendors   = $this->buildVendorAttentionMap();
            $this->syncSystemWarnings($attentionVendors);

            $feedbackQuery = Feedback::query()
                ->with([
                    'user:id,name,email',
                    'ticket' => fn($q) => $q->with([
                        'user:id,name,email',
                        'assignedTo:id,name,email,company_name',
                        'category:id,name',
                    ]),
                ])
                ->whereHas('ticket', fn($q) => $q->whereNotNull('assigned_to'));

            if ($request->filled('vendor_id')) {
                $feedbackQuery->whereHas('ticket', fn($q) => $q->where('assigned_to', $request->vendor_id));
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $feedbackQuery->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                      ->orWhereHas('ticket', fn($tq) => $tq
                          ->where('title', 'like', "%{$search}%")
                          ->orWhere('ticket_number', 'like', "%{$search}%")
                          ->orWhereHas('assignedTo', fn($vq) => $vq->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"))
                          ->orWhereHas('user', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                      );
                });
            }

            $sort = $request->input('sort', 'latest');
            match ($sort) {
                'lowest_rating'  => $feedbackQuery->orderBy('rating')->orderByDesc('created_at'),
                'highest_rating' => $feedbackQuery->orderByDesc('rating')->orderByDesc('created_at'),
                'oldest'         => $feedbackQuery->oldest(),
                default          => $feedbackQuery->latest(),
            };

            $ratings = $feedbackQuery->paginate($request->input('per_page', 15))->withQueryString();

            $completedBase = Ticket::query()->whereNotNull('assigned_to')->whereIn('status', ['resolved', 'closed']);

            $summary = [
                'total_feedbacks'    => Feedback::whereHas('ticket', fn($q) => $q->whereNotNull('assigned_to'))->count(),
                'average_rating'     => round((float) Feedback::whereHas('ticket', fn($q) => $q->whereNotNull('assigned_to'))->avg('rating'), 2),
                'completed_tickets'  => (clone $completedBase)->count(),
                'pending_ratings'    => (clone $completedBase)->doesntHave('feedback')->count(),
                'system_warning_count' => $warningTableExists ? VendorWarning::where('warning_type', 'system')->count() : 0,
                'admin_warning_count'  => $warningTableExists ? VendorWarning::where('warning_type', 'admin')->count() : 0,
            ];

            $warningMap  = $this->getLatestWarningsMap();
            $vendorStats = User::where('role', 'vendor')
                ->select('id', 'name', 'email', 'company_name')
                ->withCount([
                    'assignedTickets as completed_tickets' => fn($q) => $q->whereIn('status', ['resolved', 'closed']),
                    'assignedTickets as rated_tickets'     => fn($q) => $q->whereIn('status', ['resolved', 'closed'])->whereHas('feedback'),
                    'assignedTickets as pending_ratings'   => fn($q) => $q->whereIn('status', ['resolved', 'closed'])->whereDoesntHave('feedback'),
                ])
                ->get()
                ->map(function ($vendor) use ($attentionVendors, $warningMap) {
                    $meta = $attentionVendors[$vendor->id] ?? $this->emptyVendorMeta($vendor->id);
                    return array_merge(['id' => $vendor->id, 'name' => $vendor->name, 'email' => $vendor->email, 'company_name' => $vendor->company_name,
                        'completed_tickets' => $vendor->completed_tickets, 'rated_tickets' => $vendor->rated_tickets, 'pending_ratings' => $vendor->pending_ratings],
                        $meta, ['latest_warning' => $warningMap[$vendor->id] ?? null]
                    );
                })
                ->sortByDesc(fn($v) => ($v['warning_level'] === 'admin' ? 2 : ($v['warning_level'] === 'system' ? 1 : 0)) * 100000 + (int)$v['low_rating_count'] * 100)
                ->values();

            $summary['low_rating_vendors']                  = $vendorStats->where('needs_attention', true)->count();
            $summary['vendors_recommended_admin_warning']   = $vendorStats->where('should_receive_admin_warning', true)->count();
            $vendorOptions = $vendorStats->map(fn($v) => ['id' => $v['id'], 'name' => $v['name']])->values()->all();

            return view('admin.vendor-ratings.index', compact('summary', 'vendorStats', 'vendorOptions', 'ratings'));
        } catch (\Throwable $e) {
            Log::error('Admin vendor ratings error', ['error' => $e->getMessage()]);
            abort(500, $e->getMessage());
        }
    }

    // ── Blade: Vendor ratings page ─────────────────────────────
    public function vendorIndex(Request $request)
    {
        $vendorId           = Auth::id();
        $vendorMeta         = $this->buildVendorAttentionMap();
        $this->syncSystemWarnings($vendorMeta);
        $warningTableExists = Schema::hasTable('vendor_warnings');

        $query = Ticket::with(['user:id,name,email', 'category:id,name', 'feedback'])
            ->where('assigned_to', $vendorId)
            ->whereIn('status', ['resolved', 'closed']);

        if ($request->filled('feedback_status')) {
            if ($request->feedback_status === 'rated')   $query->whereHas('feedback');
            if ($request->feedback_status === 'pending') $query->whereDoesntHave('feedback');
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(fn($q) => $q
                ->where('title', 'like', "%{$s}%")
                ->orWhere('ticket_number', 'like', "%{$s}%")
                ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%"))
            );
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'lowest_rating'  => $query->leftJoin('feedbacks', 'feedbacks.ticket_id', '=', 'tickets.id')
                                      ->select('tickets.*')
                                      ->orderByRaw('CASE WHEN feedbacks.rating IS NULL THEN 999 ELSE feedbacks.rating END')
                                      ->orderByDesc('tickets.resolved_at'),
            'oldest'         => $query->orderBy('resolved_at'),
            'pending_first'  => $query->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM feedbacks WHERE feedbacks.ticket_id = tickets.id) THEN 2 ELSE 1 END')
                                      ->orderByDesc('resolved_at'),
            default          => $query->orderByRaw('CASE WHEN status = "closed" THEN 2 ELSE 1 END')
                                      ->orderByDesc('resolved_at'),
        };

        $tickets    = $query->paginate($request->input('per_page', 15))->withQueryString();
        $ratingMeta = $vendorMeta[$vendorId] ?? $this->emptyVendorMeta($vendorId);

        $warnings = $warningTableExists
            ? VendorWarning::where('vendor_id', $vendorId)->latest()
                ->get(['id', 'warning_type', 'message', 'created_at'])
                ->map(fn($w) => ['id' => $w->id, 'warning_type' => $w->warning_type, 'message' => $w->message, 'created_at' => $w->created_at])
                ->values()->toArray()
            : [];

        $stats = array_merge($ratingMeta, [
            'completed_tickets' => Ticket::where('assigned_to', $vendorId)->whereIn('status', ['resolved', 'closed'])->count(),
            'rated_tickets'     => Ticket::where('assigned_to', $vendorId)->whereIn('status', ['resolved', 'closed'])->has('feedback')->count(),
            'pending_ratings'   => Ticket::where('assigned_to', $vendorId)->whereIn('status', ['resolved', 'closed'])->doesntHave('feedback')->count(),
            'latest_warning'    => $warnings[0] ?? null,
            'warnings'          => $warnings,
        ]);

        return view('vendor.ratings.index', compact('tickets', 'stats'));
    }

    // ── API: Delete feedback (admin) ───────────────────────────
    public function destroy($id)
    {
        try {
            $feedback = Feedback::whereHas('ticket', fn($q) => $q->whereNotNull('assigned_to'))->findOrFail($id);
            $feedback->delete();
            return response()->json(['success' => true, 'message' => 'Vendor rating deleted successfully']);
        } catch (\Throwable $e) {
            Log::error('Vendor rating deletion failed', ['feedback_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to delete vendor rating'], 500);
        }
    }

    // ── API / Blade: Send admin warning ───────────────────────
    public function sendAdminWarning(Request $request, $vendorId)
    {
        try {
            $vendor     = User::where('role', 'vendor')->findOrFail($vendorId);
            $vendorMeta = $this->buildVendorAttentionMap();
            $ratingMeta = $vendorMeta[$vendorId] ?? $this->emptyVendorMeta($vendorId);

            if (!$ratingMeta['should_receive_admin_warning']) {
                return response()->json(['success' => false, 'message' => 'Vendor belum memenuhi ambang peringatan admin.'], 422);
            }

            $message = trim((string) $request->input('message', 'Admin memberikan peringatan langsung karena performa penanganan Anda tidak sesuai SOP/peraturan yang berlaku. Mohon lakukan evaluasi dan perbaikan segera.'));
            $warning = VendorWarning::create(['vendor_id' => $vendor->id, 'warning_type' => 'admin', 'message' => $message ?: 'Admin memberikan peringatan langsung kepada vendor.']);

            return response()->json(['success' => true, 'message' => 'Peringatan admin berhasil dikirim.', 'warning' => $warning]);
        } catch (\Throwable $e) {
            Log::error('Admin warning creation failed', ['vendor_id' => $vendorId, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengirim peringatan admin.'], 500);
        }
    }

    // ── Private helpers (unchanged logic) ─────────────────────
    private function buildVendorAttentionMap(): array
    {
        $warningTableExists = Schema::hasTable('vendor_warnings');

        $ratingRows = Feedback::join('tickets', 'tickets.id', '=', 'feedbacks.ticket_id')
            ->whereNotNull('tickets.assigned_to')
            ->groupBy('tickets.assigned_to')
            ->selectRaw('tickets.assigned_to as vendor_id, AVG(feedbacks.rating) as average_rating, COUNT(feedbacks.id) as rated_tickets, SUM(CASE WHEN feedbacks.rating <= ? THEN 1 ELSE 0 END) as low_rating_count', [self::LOW_RATING_MAX])
            ->get()->keyBy('vendor_id');

        $completedRows = Ticket::whereNotNull('assigned_to')
            ->whereIn('status', ['resolved', 'closed'])
            ->groupBy('assigned_to')
            ->selectRaw('assigned_to as vendor_id, COUNT(*) as completed_tickets')
            ->get()->keyBy('vendor_id');

        $systemWarningCounts = $warningTableExists ? VendorWarning::where('warning_type', 'system')->selectRaw('vendor_id, COUNT(*) as total')->groupBy('vendor_id')->pluck('total', 'vendor_id') : collect();
        $adminWarningCounts  = $warningTableExists ? VendorWarning::where('warning_type', 'admin')->selectRaw('vendor_id, COUNT(*) as total')->groupBy('vendor_id')->pluck('total', 'vendor_id')  : collect();

        return $ratingRows->keys()->merge($completedRows->keys())->unique()->values()
            ->mapWithKeys(function ($vendorId) use ($ratingRows, $completedRows, $systemWarningCounts, $adminWarningCounts) {
                $ratingRow       = $ratingRows->get($vendorId);
                $completedRow    = $completedRows->get($vendorId);
                $completedTickets = (int)($completedRow->completed_tickets ?? 0);
                $ratedTickets     = (int)($ratingRow->rated_tickets ?? 0);
                $average          = round((float)($ratingRow->average_rating ?? 0), 2);
                $lowCount         = (int)($ratingRow->low_rating_count ?? 0);
                $eligible         = $completedTickets >= self::MIN_COMPLETED_TICKETS;
                $systemLevel      = $eligible && $lowCount >= self::SYSTEM_WARNING_THRESHOLD;
                $adminLevel       = $eligible && $lowCount >= self::ADMIN_WARNING_THRESHOLD;

                return [(int)$vendorId => [
                    'id'                            => (int)$vendorId,
                    'average_rating'                => $average,
                    'completed_tickets'             => $completedTickets,
                    'rated_tickets'                 => $ratedTickets,
                    'low_rating_count'              => $lowCount,
                    'needs_attention'               => $systemLevel || $adminLevel || ($eligible && $average > 0 && $average < 3.5),
                    'warning_level'                 => $adminLevel ? 'admin' : ($systemLevel ? 'system' : 'normal'),
                    'should_receive_admin_warning'  => $adminLevel,
                    'system_warning_count'          => (int)($systemWarningCounts[$vendorId] ?? 0),
                    'admin_warning_count'           => (int)($adminWarningCounts[$vendorId] ?? 0),
                    'warning_message'               => $this->buildWarningMessage($completedTickets, $lowCount, $average, $systemLevel, $adminLevel),
                ]];
            })->toArray();
    }

    private function syncSystemWarnings(array $vendorMeta): void
    {
        if (!Schema::hasTable('vendor_warnings')) return;
        foreach ($vendorMeta as $vendorId => $meta) {
            if (!in_array($meta['warning_level'], ['system', 'admin'], true)) continue;
            $message = $meta['warning_level'] === 'admin'
                ? 'Sistem mendeteksi pola rating buruk berulang. Admin disarankan segera memberikan teguran langsung kepada vendor ini.'
                : 'Sistem mendeteksi penurunan performa vendor. Mohon evaluasi kualitas penanganan, komunikasi, dan kepatuhan SOP.';
            if (!VendorWarning::where('vendor_id', $vendorId)->where('warning_type', 'system')->where('message', $message)->exists()) {
                VendorWarning::create(['vendor_id' => $vendorId, 'warning_type' => 'system', 'message' => $message]);
            }
        }
    }

    private function getLatestWarningsMap(): array
    {
        if (!Schema::hasTable('vendor_warnings')) return [];
        return VendorWarning::latest()->get()->groupBy('vendor_id')
            ->map(fn($ws) => ['id' => $ws->first()->id, 'warning_type' => $ws->first()->warning_type, 'message' => $ws->first()->message, 'created_at' => $ws->first()->created_at])
            ->toArray();
    }

    private function buildWarningMessage(int $c, int $low, float $avg, bool $sys, bool $adm): ?string
    {
        if ($adm) return "Vendor memiliki {$low} rating buruk dari {$c} tiket selesai. Admin perlu memberikan teguran langsung karena performa tidak sesuai SOP.";
        if ($sys) return "Vendor memiliki {$low} rating buruk dari {$c} tiket selesai. Sistem memberi peringatan agar vendor segera memperbaiki kualitas layanan.";
        if ($c < self::MIN_COMPLETED_TICKETS && $low > 0) return 'Sudah ada rating rendah, tetapi vendor belum mencapai minimum ' . self::MIN_COMPLETED_TICKETS . ' tiket selesai untuk warning otomatis.';
        if ($avg > 0 && $avg < 3.5) return 'Rata-rata rating vendor menurun. Mohon evaluasi komunikasi, kecepatan, dan kualitas penyelesaian pekerjaan.';
        return null;
    }

    private function emptyVendorMeta(int $vendorId): array
    {
        return ['id' => $vendorId, 'average_rating' => 0, 'completed_tickets' => 0, 'rated_tickets' => 0,
            'low_rating_count' => 0, 'needs_attention' => false, 'warning_level' => 'normal',
            'should_receive_admin_warning' => false, 'system_warning_count' => 0, 'admin_warning_count' => 0, 'warning_message' => null];
    }
}