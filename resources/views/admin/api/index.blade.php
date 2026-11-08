@extends('admin.layout')

@section('title', 'API Platform Overview')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-cubes text-primary mr-2"></i> Centralized API Gateway Platform
        </h2>
        <p class="text-muted small mb-0">Monitor mobile applications (Android/iOS), web clients, real-time telemetry, and extraction jobs.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <a href="{{ route('admin.api.clients') }}?action=create" class="btn btn-success btn-sm mr-2 shadow-sm font-weight-bold">
            <i class="fa fa-plus-circle mr-1"></i> + Create API Client
        </a>
        <a href="{{ route('admin.api.docs') }}" class="btn btn-outline-primary btn-sm mr-2 shadow-sm font-weight-bold">
            <i class="fa fa-book mr-1"></i> Interactive API Docs
        </a>
        <a href="{{ route('admin.api.settings') }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
            <i class="fa fa-sliders mr-1"></i> Gateway Config
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

<!-- Gateway Health & Status Banner -->
<div class="card mb-4 border-0 shadow-sm {{ $isMaintenance ? 'bg-danger text-white' : 'bg-white' }}">
    <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="mr-3">
                @if($isMaintenance)
                    <span class="badge badge-warning p-2"><i class="fa fa-warning fa-2x"></i></span>
                @else
                    <span class="badge badge-success p-2" style="background-color: #10b981;"><i class="fa fa-check-circle fa-2x text-white"></i></span>
                @endif
            </div>
            <div>
                <h5 class="mb-0 font-weight-bold {{ $isMaintenance ? 'text-white' : 'text-dark' }}">
                    Gateway Status: {{ $isMaintenance ? 'MAINTENANCE MODE ACTIVE' : 'SYSTEM FULLY OPERATIONAL' }}
                </h5>
                <small class="{{ $isMaintenance ? 'text-white-50' : 'text-muted' }}">
                    Base URL: <code>{{ $baseUrl }}</code> &bull; Protocol: <strong>REST v1</strong> &bull; SSRF Filter: <strong>STRICT ACTIVE</strong>
                </small>
            </div>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.api.settings') }}" class="btn {{ $isMaintenance ? 'btn-light text-danger' : 'btn-outline-secondary' }} btn-sm font-weight-bold">
                {{ $isMaintenance ? 'Disable Maintenance' : 'Configure Maintenance' }}
            </a>
        </div>
    </div>
</div>

<!-- Primary KPI Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left border-primary shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary text-white mr-3 shadow-sm">
                    <i class="fa fa-exchange"></i>
                </div>
                <div>
                    <div class="text-uppercase text-muted small font-weight-bold">Requests Today</div>
                    <div class="h3 font-weight-bold text-dark mb-0">{{ number_format($metrics['requests_today']) }}</div>
                    <small class="text-muted">Total: {{ number_format($metrics['total_requests']) }} calls</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left border-success shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success text-white mr-3 shadow-sm">
                    <i class="fa fa-mobile" style="font-size: 28px;"></i>
                </div>
                <div>
                    <div class="text-uppercase text-muted small font-weight-bold">Registered Devices</div>
                    <div class="h3 font-weight-bold text-dark mb-0">{{ number_format($metrics['total_devices']) }}</div>
                    <small class="text-success"><i class="fa fa-circle text-success" style="font-size: 8px;"></i> {{ $metrics['active_devices_24h'] }} active (24h)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left border-info shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-info text-white mr-3 shadow-sm">
                    <i class="fa fa-key"></i>
                </div>
                <div>
                    <div class="text-uppercase text-muted small font-weight-bold">Active Tokens</div>
                    <div class="h3 font-weight-bold text-dark mb-0">{{ number_format($metrics['active_tokens']) }}</div>
                    <small class="text-muted">Rotating SHA-256 Auth</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-left border-warning shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning text-white mr-3 shadow-sm">
                    <i class="fa fa-tachometer"></i>
                </div>
                <div>
                    <div class="text-uppercase text-muted small font-weight-bold">Avg Response Time</div>
                    <div class="h3 font-weight-bold text-dark mb-0">{{ $metrics['avg_latency_ms'] }} <span class="small text-muted font-weight-normal">ms</span></div>
                    <small class="{{ $metrics['error_rate_pct'] > 5 ? 'text-danger' : 'text-success' }}">
                        Error Rate: {{ $metrics['error_rate_pct'] }}%
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent API Activity Logs -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fa fa-history text-primary mr-1"></i> Live API Activity Telemetry
                </h6>
                <a href="{{ route('admin.api.logs') }}" class="btn btn-sm btn-outline-secondary">View All Logs</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>IP Address</th>
                            <th>Latency</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                        <tr>
                            <td>
                                @if($log->status_code >= 200 && $log->status_code < 300)
                                    <span class="badge badge-success">{{ $log->status_code }}</span>
                                @elseif($log->status_code >= 400 && $log->status_code < 500)
                                    <span class="badge badge-warning">{{ $log->status_code }}</span>
                                @else
                                    <span class="badge badge-danger">{{ $log->status_code }}</span>
                                @endif
                            </td>
                            <td><code>{{ $log->method }}</code></td>
                            <td><span class="font-weight-600 text-dark">{{ $log->endpoint }}</span></td>
                            <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                            <td><span class="badge badge-light border">{{ $log->duration_ms }} ms</span></td>
                            <td><small class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : 'just now' }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No API requests logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Platform Breakdown & Quick Links -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fa fa-pie-chart text-info mr-1"></i> Client Platforms
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fa fa-android text-success mr-2"></i> Android Devices</span>
                        <span class="badge badge-success badge-pill">{{ $platformBreakdown['android'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fa fa-apple text-dark mr-2"></i> iOS Devices</span>
                        <span class="badge badge-dark badge-pill">{{ $platformBreakdown['ios'] ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="fa fa-globe text-primary mr-2"></i> Web Clients</span>
                        <span class="badge badge-primary badge-pill">{{ $platformBreakdown['web'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-dark">
                    <i class="fa fa-shield text-danger mr-1"></i> Security Enforcements
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-2 d-flex justify-content-between">
                    <span class="small font-weight-bold">SSRF IP Guard:</span>
                    <span class="badge badge-success">ACTIVE</span>
                </div>
                <div class="mb-2 d-flex justify-content-between">
                    <span class="small font-weight-bold">Token Hashing:</span>
                    <span class="badge badge-info">SHA-256</span>
                </div>
                <div class="mb-2 d-flex justify-content-between">
                    <span class="small font-weight-bold">Refresh Token Rotation:</span>
                    <span class="badge badge-success">STRICT</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="small font-weight-bold">Retention Auto-Purge:</span>
                    <span class="badge badge-secondary">15 MIN</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
