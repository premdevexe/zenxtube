@extends('app')

@php
    $siteName = \App\SiteSetting::get('site_name', config('app.name', 'Zen X Download'));
@endphp

@section('title', 'Terms of Service - ' . $siteName)

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <h1 class="h3 font-weight-bold text-dark mb-1"><i class="fa fa-file-text-o text-primary mr-2"></i> Terms of Service</h1>
        <p class="text-muted small mb-0">Please read these terms carefully before using our service.</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white">
                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">1. Agreement to Terms</h4>
                    <p class="text-secondary">
                        By accessing or using <strong>{{ $siteName }}</strong> ("the Service"), you agree to be bound by these Terms of Service ("Terms"). If you disagree with any part of these terms, you must not use or access the Service.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">2. Permitted Use & Personal Fair Use</h4>
                    <p class="text-secondary">
                        {{ $siteName }} provides an online software tool designed solely for personal, non-commercial, fair-use downloading and offline viewing of publicly accessible online media. You agree that:
                    </p>
                    <ul class="text-secondary">
                        <li>You will only download content for which you hold appropriate rights, copyright licenses, or explicit fair-use permissions under applicable local laws.</li>
                        <li>You will not redistribute, broadcast, monetize, or commercially exploit any copyrighted downloaded content without express authorization from the rights holder.</li>
                        <li>You are solely responsible for ensuring your use of the Service complies with the terms of service of third-party platforms you access (including YouTube, Facebook, Twitter, and others).</li>
                    </ul>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">3. Prohibited Conduct</h4>
                    <p class="text-secondary">
                        When using {{ $siteName }}, you agree NOT to:
                    </p>
                    <ul class="text-secondary">
                        <li>Use automated scripts, bots, scrapers, or high-volume tools that compromise server stability or degrade service for other users.</li>
                        <li>Circumvent or attempt to bypass DRM (Digital Rights Management) or access-control technologies implemented by rights holders.</li>
                        <li>Attempt to reverse engineer, disrupt, hack, or inject malicious code into the Service infrastructure.</li>
                        <li>Use the Service for any unlawful, copyright-infringing, or abusive purposes.</li>
                    </ul>
                </section>

                <section class="mb-4" id="dmca">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">4. Intellectual Property & DMCA Policy</h4>
                    <p class="text-secondary">
                        {{ $siteName }} respects intellectual property rights. <strong>{{ $siteName }} does not host, store, stream, or index any copyrighted media files.</strong> All download pipelines connect directly to public third-party CDN endpoints requested by the user.
                    </p>
                    <p class="text-secondary">
                        If you are a copyright owner or an agent thereof and believe that any functionality on {{ $siteName }} facilitates infringement of your copyrighted work, please contact us with:
                    </p>
                    <ul class="text-secondary">
                        <li>Identification of the copyrighted work claimed to have been infringed.</li>
                        <li>Clear identification of the specific material or URL.</li>
                        <li>Your contact information (name, address, telephone number, and email).</li>
                        <li>A statement made under penalty of perjury that you are authorized to act on behalf of the copyright holder.</li>
                    </ul>
                    <p class="text-secondary">
                        Send copyright inquiries to our support desk via our <a href="{{ url('contact') }}" class="text-primary font-weight-bold">Contact Page</a>.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">5. Disclaimer of Warranties</h4>
                    <p class="text-secondary">
                        The Service is provided on an <strong>"AS IS"</strong> and <strong>"AS AVAILABLE"</strong> basis without warranties of any kind, whether express, implied, or statutory. {{ $siteName }} makes no guarantee that the service will be uninterrupted, error-free, completely secure, or that all video formats or platforms will remain permanently accessible.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">6. Limitation of Liability</h4>
                    <p class="text-secondary">
                        In no event shall {{ $siteName }}, its maintainers, or affiliates be liable for any indirect, incidental, special, consequential, or punitive damages arising out of your use of or inability to use the Service.
                    </p>
                </section>

                <section>
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">7. Governing Law & Updates</h4>
                    <p class="text-secondary">
                        These Terms shall be interpreted in accordance with standard international fair-use principles. We reserve the right to revise these Terms at any time without prior notice. By continuing to use the Service, you agree to be bound by the revised Terms.
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

