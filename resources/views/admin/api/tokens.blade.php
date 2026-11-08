@extends('admin.layout')

@section('title', 'API Token Management')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-key text-secondary mr-2"></i> Token Directory & SHA-256 Hashes
        </h2>
        <p class="text-muted small mb-0">Audit cryptographic bearer access tokens and rotating refresh tokens issued to client devices.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="{{ route('admin.api.devices') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-mobile mr-1"></i> Device Directory
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3 d-flex flex-wrap justify-content-between align-items-center">
        <div class="btn-group btn-group-sm mb-2 mb-md-0" role="group">
            <a href="{{ route('admin.api.tokens') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">All Tokens</a>
            <a href="{{ route('admin.api.tokens', ['status' => 'active']) }}" class="btn {{ request('status') === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">Active Only</a>
            <a href="{{ route('admin.api.tokens', ['status' => 'expired']) }}" class="btn {{ request('status') === 'expired' ? 'btn-primary' : 'btn-outline-secondary' }}">Expired</a>
            <a href="{{ route('admin.api.tokens', ['status' => 'revoked']) }}" class="btn {{ request('status') === 'revoked' ? 'btn-primary' : 'btn-outline-secondary' }}">Revoked</a>
        </div>
        <small class="text-muted"><i class="fa fa-info-circle mr-1"></i> Tokens are stored strictly as irreversible SHA-256 hashes.</small>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Device</th>
                    <th>Access Token Hash (SHA-256)</th>
                    <th>Access Expires</th>
                    <th>Refresh Expires</th>
                    <th>Last IP</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tokens as $tok)
                @php
                    $isExpired = $tok->isAccessExpired();
                    $isRevoked = $tok->isRevoked();
                @endphp
                <tr class="{{ $isRevoked ? 'table-secondary text-muted' : ($isExpired ? 'table-light text-muted' : '') }}">
                    <td>
                        <div class="font-weight-bold text-dark">{{ $tok->device ? $tok->device->device_model : 'Deleted Device' }}</div>
                        <small class="text-muted">{{ $tok->device ? $tok->device->platform : 'N/A' }} &bull; {{ $tok->device ? substr($tok->device->device_uuid, 0, 14) . '...' : '' }}</small>
                    </td>
                    <td>
                        <code>{{ substr($tok->access_token_hash, 0, 16) }}...{{ substr($tok->access_token_hash, -8) }}</code>
                    </td>
                    <td>
                        <small class="{{ $isExpired ? 'text-muted' : 'text-dark font-weight-bold' }}">
                            {{ $tok->access_expires_at ? $tok->access_expires_at->diffForHumans() : 'N/A' }}
                        </small>
                    </td>
                    <td>
                        <small class="text-muted">
                            {{ $tok->refresh_expires_at ? $tok->refresh_expires_at->diffForHumans() : 'N/A' }}
                        </small>
                    </td>
                    <td>
                        <small class="text-muted">{{ $tok->last_used_ip ?: '---' }}</small>
                    </td>
                    <td>
                        @if($isRevoked)
                            <span class="badge badge-secondary">Revoked</span>
                        @elseif($isExpired)
                            <span class="badge badge-light border text-muted">Expired</span>
                        @else
                            <span class="badge badge-success">Valid & Active</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if(!$isRevoked && !$isExpired)
                        <form action="{{ route('admin.api.tokens.revoke', $tok->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke this bearer token immediately?');">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke Token">
                                <i class="fa fa-ban mr-1"></i> Revoke
                            </button>
                        </form>
                        @else
                            <span class="text-muted small">&mdash;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No tokens found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tokens->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Showing {{ $tokens->firstItem() }} to {{ $tokens->lastItem() }} of {{ $tokens->total() }} tokens</small>
        {{ $tokens->links() }}
    </div>
    @endif
</div>
@endsection
