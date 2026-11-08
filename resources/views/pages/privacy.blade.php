@extends('app')

@php
    $siteName = \App\SiteSetting::get('site_name', config('app.name', 'Zen X Download'));
@endphp

@section('title', 'Privacy Policy - ' . $siteName)

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <h1 class="h3 font-weight-bold text-dark mb-1"><i class="fa fa-shield text-success mr-2"></i> Privacy Policy</h1>
        <p class="text-muted small mb-0">Last updated: {{ date('F Y') }}</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white">
                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">1. Overview & Commitment</h4>
                    <p class="text-secondary">
                        Welcome to <strong>{{ $siteName }}</strong> ("we", "our", or "us"). We respect your privacy and are committed to protecting any information that may be gathered while using our website and services. This Privacy Policy explains our practices regarding data collection, usage, and disclosure when you interact with our online media downloader.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">2. Information We Do NOT Collect</h4>
                    <p class="text-secondary">
                        We firmly believe in privacy-by-design for media conversion utilities:
                    </p>
                    <ul class="text-secondary">
                        <li><strong>No Account Required:</strong> You do not need to register, log in, or provide personal identifiable information (name, address, telephone number, payment details) to use {{ $siteName }}.</li>
                        <li><strong>No Media Archiving:</strong> We do not log, retain, or store personal files or converted videos on persistent storage beyond the temporary streaming cache necessary to deliver the file to your device.</li>
                        <li><strong>No Tracking of Video Content:</strong> We do not build profiles based on the URLs or videos you download.</li>
                    </ul>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">3. Automatically Collected Information</h4>
                    <p class="text-secondary">
                        When you access {{ $siteName }}, standard web server logs may temporarily record general non-identifying technical information, including:
                    </p>
                    <ul class="text-secondary">
                        <li>Browser type and version</li>
                        <li>Operating system and device category</li>
                        <li>Referring URL and requested page resources</li>
                        <li>Timestamps of requests for server health and rate-limiting</li>
                        <li>Truncated or anonymized IP addresses used exclusively to combat abuse and DDoS attacks</li>
                    </ul>
                </section>

                <section class="mb-4" id="cookies">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">4. Cookies and Local Storage</h4>
                    <p class="text-secondary">
                        {{ $siteName }} uses minimal cookies and browser local storage strictly necessary to:
                    </p>
                    <ul class="text-secondary">
                        <li>Remember session state and security tokens (CSRF protection)</li>
                        <li>Prevent spam, malicious scraping, and automated abuse</li>
                        <li>Maintain user interface preferences</li>
                    </ul>
                    <p class="text-secondary">
                        You can configure your browser to reject cookies; however, some interactive features may function with degraded performance.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">5. Third-Party Links & Platforms</h4>
                    <p class="text-secondary">
                        {{ $siteName }} processes publicly available links from third-party hosting platforms (e.g., YouTube, Facebook, Twitter/X, Vimeo, Instagram). We do not control and are not responsible for the privacy practices, content, or terms of third-party platforms. We encourage users to review the privacy policies of the respective platforms they access.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">6. Security Measures</h4>
                    <p class="text-secondary">
                        We implement modern standard technical safeguards, including 256-bit SSL encryption (HTTPS) across all pages and download streams, firewall protections, and automated rate limiters to ensure safe, secure file transmission between our servers and your device.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">7. Changes to This Policy</h4>
                    <p class="text-secondary">
                        We may update this Privacy Policy periodically to reflect technological improvements or legal requirements. Updated policies will be posted on this page with the effective date revised accordingly.
                    </p>
                </section>

                <section>
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">8. Contact Us</h4>
                    <p class="text-secondary">
                        If you have questions, feedback, or privacy-related inquiries regarding this Privacy Policy, please visit our <a href="{{ url('contact') }}" class="text-primary font-weight-bold">Contact Page</a>.
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

