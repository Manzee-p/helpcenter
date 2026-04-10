@extends('layouts.app')

@section('title', 'Permintaan Penghapusan Tiket')
@section('page_title', 'Permintaan Hapus Tiket')
@section('breadcrumb', 'Home / Tiket / Permintaan Hapus')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="mb-1">Daftar Permintaan Penghapusan</h5>
                <small class="text-muted">Client tidak menghapus langsung. Semua request harus direview admin.</small>
            </div>
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>

        @if($requests->isEmpty())
            <div class="alert alert-light border text-muted mb-0">Belum ada permintaan penghapusan tiket.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tiket</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Diajukan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->ticket->ticket_number ?? '-' }}</div>
                                    <small class="text-muted">{{ $item->ticket->title ?? 'Tiket sudah terhapus' }}</small>
                                </td>
                                <td>{{ $item->user->name ?? '-' }}</td>
                                <td>
                                    @php
                                        $cls = match($item->status) {
                                            'pending' => 'bg-warning-subtle text-warning-emphasis',
                                            'approved' => 'bg-success-subtle text-success-emphasis',
                                            'rejected' => 'bg-danger-subtle text-danger-emphasis',
                                            default => 'bg-secondary-subtle text-secondary-emphasis',
                                        };
                                    @endphp
                                    <span class="badge {{ $cls }}">{{ strtoupper($item->status) }}</span>
                                </td>
                                <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.ticket-deletion-requests.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

