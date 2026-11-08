@extends('admin.layout')

@section('title', 'Contact Inquiries')
@section('page_title', 'Contact Messages & Support')

@section('content')

    <!-- Status Filters -->
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center">
            <div class="btn-group mb-2 mb-md-0" role="group">
                <a href="{{ route('admin.messages') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} font-weight-bold">
                    All Messages
                </a>
                <a href="{{ route('admin.messages', ['status' => 'unread']) }}" class="btn btn-sm {{ request('status') === 'unread' ? 'btn-danger' : 'btn-outline-secondary' }} font-weight-bold">
                    Unread
                </a>
                <a href="{{ route('admin.messages', ['status' => 'read']) }}" class="btn btn-sm {{ request('status') === 'read' ? 'btn-info' : 'btn-outline-secondary' }} font-weight-bold">
                    Read
                </a>
                <a href="{{ route('admin.messages', ['status' => 'replied']) }}" class="btn btn-sm {{ request('status') === 'replied' ? 'btn-success' : 'btn-outline-secondary' }} font-weight-bold">
                    Replied
                </a>
            </div>

            <div class="text-muted small">
                Showing <strong>{{ $messages->total() }}</strong> total inquiries
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="card border-0 shadow-sm bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-envelope text-primary mr-1"></i> Received Inquiries</h6>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr>
                                <td class="align-middle">
                                    @if($msg->status === 'unread')
                                        <span class="badge badge-danger">Unread</span>
                                    @elseif($msg->status === 'replied')
                                        <span class="badge badge-success">Replied</span>
                                    @else
                                        <span class="badge badge-secondary">Read</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <strong class="text-dark d-block">{{ $msg->name }}</strong>
                                    <small class="text-muted"><a href="mailto:{{ $msg->email }}">{{ $msg->email }}</a></small>
                                </td>
                                <td class="align-middle font-weight-bold text-dark">
                                    {{ $msg->subject }}
                                </td>
                                <td class="align-middle text-secondary small" style="max-width: 320px;">
                                    <span class="d-inline-block text-truncate" style="max-width: 300px;">
                                        {{ $msg->message }}
                                    </span>
                                </td>
                                <td class="align-middle small text-muted">
                                    {{ $msg->created_at ? $msg->created_at->format('M d, Y H:i') : '' }}
                                </td>
                                <td class="align-middle text-right">
                                    <button type="button" class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#msgModal{{ $msg->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>

                                    <form action="{{ route('admin.messages.delete', $msg->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this message?')">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Message Detail Modal -->
                            <div class="modal fade" id="msgModal{{ $msg->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title font-weight-bold">{{ $msg->subject }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <small class="text-muted text-uppercase font-weight-bold">From</small>
                                                <p class="mb-0"><strong>{{ $msg->name }}</strong> &lt;{{ $msg->email }}&gt;</p>
                                                <small class="text-muted">Sent on {{ $msg->created_at ? $msg->created_at->format('M d, Y at H:i:s') : '' }} &bull; IP: <code>{{ $msg->ip_address ?? 'N/A' }}</code></small>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted text-uppercase font-weight-bold">Message Content</small>
                                                <div class="p-3 bg-light rounded border mt-1" style="white-space: pre-wrap; font-size: 0.95rem;">{{ $msg->message }}</div>
                                            </div>

                                            <form action="{{ route('admin.messages.status', $msg->id) }}" method="post" class="mt-4">
                                                {{ csrf_field() }}
                                                <label class="small text-muted font-weight-bold">Update Status:</label>
                                                <div class="input-group">
                                                    <select name="status" class="form-control form-control-sm">
                                                        <option value="read" {{ $msg->status === 'read' ? 'selected' : '' }}>Mark as Read</option>
                                                        <option value="unread" {{ $msg->status === 'unread' ? 'selected' : '' }}>Mark as Unread</option>
                                                        <option value="replied" {{ $msg->status === 'replied' ? 'selected' : '' }}>Mark as Replied</option>
                                                    </select>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold">Save Status</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <a href="mailto:{{ $msg->email }}?subject=Re: {{ urlencode($msg->subject) }}" class="btn btn-success btn-sm font-weight-bold">
                                                <i class="fa fa-reply"></i> Reply via Email
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa fa-envelope-open-o fa-3x mb-3 d-block text-secondary"></i>
                                    <h5>No messages found</h5>
                                    <p class="small mb-0">Messages submitted through the Contact Us page will be listed here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($messages->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-center">
                {{ $messages->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

@endsection

