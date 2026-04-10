@extends('layouts.app')

@section('title', 'Detail Permintaan Hapus')
@section('page_title', 'Detail Permintaan Hapus')
@section('breadcrumb', 'Home / Tiket / Permintaan Hapus / Detail')

@php
    $reasonLabels = [
        'duplicate_ticket' => 'Tiket duplikat',
        'issue_resolved_without_action' => 'Masalah sudah selesai tanpa tindakan vendor',
        'wrong_category_or_input' => 'Salah kategori atau salah input data',
        'ticket_created_by_mistake' => 'Tiket dibuat tidak sengaja',
        'no_longer_relevant' => 'Tiket sudah tidak relevan',
    ];
@endphp

@section('content')
<div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Permintaan #{{ $requestItem->id }}</h5>
                    <small class="text-muted">Tiket: {{ $requestItem->ticket->ticket_number ?? '-' }} - {{ $requestItem->ticket->title ?? 'Tiket sudah terhapus' }}</small>
                </div>
                <a href="{{ route('admin.ticket-deletion-requests.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Alasan Dari Client</h6>
            <ul class="mb-3">
                @foreach(($requestItem->reasons ?? []) as $reason)
                    <li>{{ $reasonLabels[$reason] ?? $reason }}</li>
                @endforeach
            </ul>
            <div class="p-3 border rounded bg-light">
                <div class="fw-semibold mb-1">Alasan Tambahan (custom)</div>
                <div class="text-muted" style="white-space: pre-wrap;">{{ $requestItem->custom_reason }}</div>
            </div>
            <div class="mt-3 text-muted small">
                Diajukan oleh <strong>{{ $requestItem->user->name ?? '-' }}</strong> pada {{ $requestItem->created_at?->format('d M Y H:i') }}.
            </div>
        </div>
    </div>

    @if($requestItem->status === 'pending')
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Proses Permintaan</h6>
                <form method="POST" action="{{ route('admin.ticket-deletion-requests.process', $requestItem->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Catatan Admin (opsional)</label>
                        <textarea class="form-control" name="admin_note" rows="4" placeholder="Tambahkan catatan untuk keputusan Anda..."></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="action" value="approve" class="btn btn-success">Approve & Hapus Tiket</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-2">Hasil Review</h6>
                <p class="mb-1">Status: <strong>{{ strtoupper($requestItem->status) }}</strong></p>
                <p class="mb-1">Reviewer: <strong>{{ $requestItem->reviewer->name ?? '-' }}</strong></p>
                <p class="mb-1">Waktu review: <strong>{{ $requestItem->reviewed_at?->format('d M Y H:i') ?? '-' }}</strong></p>
                @if($requestItem->admin_note)
                    <div class="mt-2 p-3 border rounded bg-light text-muted" style="white-space: pre-wrap;">{{ $requestItem->admin_note }}</div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

