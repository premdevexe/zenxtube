@extends('app')

@section('title', 'Contact Us - Zen X Download')

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <h1 class="h3 font-weight-bold text-dark mb-1"><i class="fa fa-envelope-o text-primary mr-2"></i> Contact & Support</h1>
        <p class="text-muted small mb-0">Have a question, feedback, or DMCA inquiry? We are here to help.</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ url('contact') }}" method="post">
                    {{ csrf_field() }}

                    <div class="form-group mb-3">
                        <label for="name" class="font-weight-bold text-dark">Your Name</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" required placeholder="e.g. John Doe" value="{{ old('name') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="font-weight-bold text-dark">Your Email Address</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="e.g. john@example.com" value="{{ old('email') }}">
                    </div>

                    <div class="form-group mb-3">
                        <label for="subject" class="font-weight-bold text-dark">Subject / Topic</label>
                        <select class="form-control form-control-lg" id="subject" name="subject">
                            <option value="General Inquiry">General Inquiry / Feedback</option>
                            <option value="Technical Bug">Technical Issue or Bug Report</option>
                            <option value="Feature Suggestion">Platform or Feature Suggestion</option>
                            <option value="DMCA Copyright">DMCA / Copyright Inquiry</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label for="message" class="font-weight-bold text-dark">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Describe your question or inquiry in detail...">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block font-weight-bold shadow-sm">
                        <i class="fa fa-paper-plane mr-1"></i> Send Message
                    </button>
                </form>

                <hr class="my-4">

                <div class="row text-center">
                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <i class="fa fa-clock-o text-primary fa-2x mb-2"></i>
                        <h6 class="font-weight-bold mb-1">Response Time</h6>
                        <small class="text-muted">Usually within 24 hours</small>
                    </div>
                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <i class="fa fa-shield text-success fa-2x mb-2"></i>
                        <h6 class="font-weight-bold mb-1">Privacy Safe</h6>
                        <small class="text-muted">Your email is never shared</small>
                    </div>
                    <div class="col-md-4 col-12">
                        <i class="fa fa-life-ring text-info fa-2x mb-2"></i>
                        <h6 class="font-weight-bold mb-1">Free Support</h6>
                        <small class="text-muted">100% free assistance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
