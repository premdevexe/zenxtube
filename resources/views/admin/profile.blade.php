@extends('admin.layout')

@section('title', 'Admin Profile Settings')
@section('page_title', 'Admin Profile & Security')

@section('content')

    <div class="row justify-content-center">
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="font-weight-bold text-dark mb-0"><i class="fa fa-user-circle text-primary mr-1"></i> Account Details & Password</h6>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.profile.update') }}" method="post">
                        {{ csrf_field() }}

                        <h6 class="text-uppercase small font-weight-bold text-muted border-bottom pb-2 mb-3">General Information</h6>

                        <div class="form-group mb-3">
                            <label for="name" class="font-weight-bold small text-secondary">Administrator Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="font-weight-bold small text-secondary">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <h6 class="text-uppercase small font-weight-bold text-muted border-bottom pb-2 mb-3 mt-4">Change Password (Leave blank to keep current)</h6>

                        <div class="form-group mb-3">
                            <label for="current_password" class="font-weight-bold small text-secondary">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Required only if changing password">
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12 mb-3">
                                <label for="new_password" class="font-weight-bold small text-secondary">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters">
                            </div>

                            <div class="col-md-6 col-12 mb-3">
                                <label for="new_password_confirmation" class="font-weight-bold small text-secondary">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Re-type new password">
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3 text-right">
                            <button type="submit" class="btn btn-primary font-weight-bold px-4">
                                <i class="fa fa-save mr-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

