@extends('admin.layout')

@section('title', 'API Activity Telemetry Logs')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h4 font-weight-bold mb-1 text-dark">
            <i class="fa fa-list-alt text-danger mr-2"></i> API Telemetry & Audit Logs
        </h2>
        <p class="text-muted small mb-0">Live audit log of all REST API requests, execution latencies, status codes, and payloads.</p>
    </div>
    <div class="col-md-5 text-md-right mt-3 mt-md-0">
        <form action="{{ route('admin.api.logs.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Purge all logged API requests?');">
            {{ csrf_field() }}
            <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                <i class="fa fa-trash mr-1"></i> Clear Telemetry Logs
            </button>
        </form>
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

<!-- Filter Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.api.logs') }}" class="form-row align-items-center">
            <div class="col-md-3 my-1">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All HTTP Statuses</option>
                    <option value="200" {{ request('status') == '200' ? 'selected' : '' }}>2xx Success</option>
                    <option value="400" {{ request('status') == '400' ? 'selected' : '' }}>4xx Client Errors</option>
                    <option value="500" {{ request('status') == '500' ? 'selected' : '' }}>5xx Server Errors</option>
                </select>
            </div>
            <div class="col-md-4 my-1">
                <input type="text" name="endpoint" class="form-control form-control-sm" placeholder="Filter endpoint (e.g. video/info, download)..." value="{{ request('endpoint') }}">
            </div>
            <div class="col-md-3 my-1">
                <input type="text" name="ip" class="form-control form-control-sm" placeholder="Filter by IP..." value="{{ request('ip') }}">
            </div>
            <div class="col-md-2 my-1 text-md-right">
                <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">
                    <i class="fa fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.api.logs') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Status</th>
                    <th>Method</th>
                    <th>Endpoint</th>
                    <th>IP / Device</th>
                    <th>Latency</th>
                    <th>Time</th>
                    <th class="text-right">Payload</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
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
                    <td>
                        <div class="font-weight-bold small text-dark">{{ $log->ip_address }}</div>
                        <small class="text-muted">{{ $log->device ? $log->device->device_model : substr($log->user_agent, 0, 30) . '...' }}</small>
                    </td>
                    <td>
                        <span class="badge {{ $log->duration_ms > 2000 ? 'badge-danger' : ($log->duration_ms > 500 ? 'badge-warning' : 'badge-light border') }}">
                            {{ $log->duration_ms }} ms
                        </span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $log->created_at ? $log->created_at->format('M d H:i:s') : 'N/A' }}</small>
                    </td>
                    <td class="text-right">
                        @if(!empty($log->request_payload) || !empty($log->response_summary))
                            <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#logModal{{ $log->id }}">
                                <i class="fa fa-eye"></i>
                            </button>

                            <!-- Modal Payload Viewer -->
                            <div class="modal fade text-left" id="logModal{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-dark text-white">
                                            <h5 class="modal-title font-weight-bold">
                                                <span class="badge badge-info mr-2">{{ $log->method }}</span> {{ $log->endpoint }}
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body bg-light">
                                            <div class="mb-3">
                                                <h6 class="font-weight-bold text-dark mb-1">Request Payload</h6>
                                                <pre class="bg-white p-3 border rounded text-dark" style="max-height: 200px; overflow: auto; font-size: 0.85rem;">{{ $log->request_payload ? json_encode(json_decode($log->request_payload), JSON_PRETTY_PRINT) : 'Empty payload' }}</pre>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-1">Response Summary</h6>
                                                <pre class="bg-white p-3 border rounded text-dark" style="max-height: 300px; overflow: auto; font-size: 0.85rem;">{{ $log->response_summary ? (json_decode($log->response_summary) ? json_encode(json_decode($log->response_summary), JSON_PRETTY_PRINT) : $log->response_summary) : 'Empty response' }}</pre>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-white">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted small">&mdash;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No log records found matching query.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <small class="text-muted">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} records</small>
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
