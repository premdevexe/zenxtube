@extends('admin.layout')

@section('title', 'Registered Devices')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-mobile text-success mr-2" style="font-size: 1.5rem;"></i> Device Directory & Hardware Fingerprints
        </h2>
        <p class="text-muted small mb-0">Inspect mobile and client devices connected to the Zen X API ecosystem.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="{{ route('admin.api.tokens') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
            <i class="fa fa-key mr-1"></i> View Active Tokens
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

<!-- Filters & Search -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.api.devices') }}" class="form-row align-items-center">
            <div class="col-md-5 my-1">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by UUID, model, or IP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 my-1">
                <select name="platform" class="form-control form-control-sm">
                    <option value="">All Platforms</option>
                    <option value="android" {{ request('platform') === 'android' ? 'selected' : '' }}>Android</option>
                    <option value="ios" {{ request('platform') === 'ios' ? 'selected' : '' }}>iOS</option>
                    <option value="web" {{ request('platform') === 'web' ? 'selected' : '' }}>Web</option>
                </select>
            </div>
            <div class="col-md-4 my-1 text-md-right">
                <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                    <i class="fa fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.api.devices') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Device Hardware & Model</th>
                    <th>Platform</th>
                    <th>Device UUID</th>
                    <th>App / OS</th>
                    <th>Downloads</th>
                    <th>Last Active</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                <tr class="{{ $device->is_blocked ? 'table-danger' : '' }}">
                    <td>
                        <div class="font-weight-bold text-dark">{{ $device->device_model ?: 'Unknown Device' }}</div>
                        <small class="text-muted"><i class="fa fa-globe mr-1"></i>{{ $device->ip_address ?: 'Unknown IP' }}</small>
                    </td>
                    <td>
                        @if($device->platform === 'android')
                            <span class="badge badge-success px-2 py-1"><i class="fa fa-android mr-1"></i> Android</span>
                        @elseif($device->platform === 'ios')
                            <span class="badge badge-dark px-2 py-1"><i class="fa fa-apple mr-1"></i> iOS</span>
                        @else
                            <span class="badge badge-primary px-2 py-1"><i class="fa fa-globe mr-1"></i> Web</span>
                        @endif
                    </td>
                    <td>
                        <code class="small text-truncate d-inline-block" style="max-width: 160px;" title="{{ $device->device_uuid }}">{{ $device->device_uuid }}</code>
                    </td>
                    <td>
                        <div><span class="badge badge-light border">v{{ $device->app_version ?: '1.0' }}</span></div>
                        <small class="text-muted">{{ $device->os_version ?: 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="badge badge-info badge-pill">{{ $device->download_jobs_count }}</span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $device->last_active_at ? $device->last_active_at->diffForHumans() : 'Never' }}</small>
                    </td>
                    <td>
                        @if($device->is_blocked)
                            <span class="badge badge-danger" title="{{ $device->block_reason ?: 'Blocked' }}">Blocked</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <!-- Toggle Block -->
                        <form action="{{ route('admin.api.devices.toggle_block', $device->id) }}" method="POST" class="d-inline">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-sm {{ $device->is_blocked ? 'btn-outline-success' : 'btn-outline-warning' }}" title="{{ $device->is_blocked ? 'Unblock Device' : 'Block Device' }}">
                                <i class="fa {{ $device->is_blocked ? 'fa-unlock' : 'fa-lock' }}"></i>
                            </button>
                        </form>

                        <!-- Revoke Tokens -->
                        <form action="{{ route('admin.api.tokens.revoke_all', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke all active tokens for this device?');">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Revoke All Tokens">
                                <i class="fa fa-key"></i>
                            </button>
                        </form>

                        <!-- Delete Device -->
                        <form action="{{ route('admin.api.devices.delete', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this device permanently?');">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Device">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No devices matching query.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($devices->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Showing {{ $devices->firstItem() }} to {{ $devices->lastItem() }} of {{ $devices->total() }} devices</small>
        {{ $devices->links() }}
    </div>
    @endif
</div>
@endsection
