@extends('app')

@php
    $siteName = \App\SiteSetting::get('site_name', config('app.name', 'Zen X Download'));
    $supportEmail = \App\SiteSetting::get('support_email', 'support@' . (request() ? request()->getHost() : 'zenxdownload.com'));
@endphp

@section('title', 'DMCA & Copyright Policy - ' . $siteName)

@section('content')
<div class="bg-light py-4 py-md-5 border-bottom">
    <div class="container text-center px-3">
        <span class="badge badge-secondary px-3 py-1 font-weight-bold mb-2 text-white" style="border-radius: 12px; font-size: 0.78rem;">
            <i class="fa fa-balance-scale"></i> LEGAL COMPLIANCE
        </span>
        <h1 class="h3 font-weight-bold text-dark mb-1">DMCA & Copyright Policy</h1>
        <p class="text-muted small mb-0">Digital Millennium Copyright Act Notice and Takedown Procedure</p>
    </div>
</div>

<div class="container py-4 py-md-5 px-3 px-sm-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-12">
            <div class="card shadow-sm border-0 p-3 p-sm-4 p-md-5 bg-white mb-4">
                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">1. Notice of Copyright Compliance</h4>
                    <p class="text-secondary">
                        <strong>{{ $siteName }}</strong> respects the intellectual property rights of others and strictly complies with the provisions of the Digital Millennium Copyright Act (Title 17, United States Code, Section 512, "DMCA") and corresponding international copyright treaties. It is our policy to respond expeditiously to valid notices of alleged infringement and take appropriate action under applicable intellectual property laws.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">2. Nature of the Service & Technical Role</h4>
                    <p class="text-secondary">
                        {{ $siteName }} operates solely as an automated technical utility and conversion gateway. Please note the following key operational facts:
                    </p>
                    <ul class="text-secondary">
                        <li><strong>No Media Hosting:</strong> {{ $siteName }} does not host, upload, archive, index, or store video or audio files on its servers. All files processed are transiently accessed directly from public hosting platforms (such as YouTube, Facebook, Twitter, etc.) based upon user-supplied URLs.</li>
                        <li><strong>Transitory Digital Network Communications:</strong> We operate strictly as an intermediary conduit under Section 512(a) of the DMCA.</li>
                        <li><strong>User Responsibility:</strong> Users of our service are required by our <a href="{{ route('terms') }}">Terms of Service</a> to possess the legal right or permission to download any content. {{ $siteName }} does not endorse or authorize unauthorized reproduction of copyrighted works.</li>
                    </ul>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">3. Filing a DMCA Takedown Notice</h4>
                    <p class="text-secondary">
                        If you are a copyright owner, or authorized to act on behalf of one, and you believe in good faith that any content accessible via our service infringes upon your copyright, you may submit a formal written DMCA notice to our designated agent containing the following mandatory elements:
                    </p>
                    <div class="p-3 bg-light rounded border mb-3">
                        <ol class="text-secondary mb-0 small" style="line-height: 1.7;">
                            <li>A physical or electronic signature of a person authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
                            <li>Identification of the copyrighted work claimed to have been infringed, or, if multiple works are covered by a single notice, a representative list of such works.</li>
                            <li>Identification of the specific material or URL(s) that is claimed to be infringing or the subject of infringing activity and that is to be blocked.</li>
                            <li>Information reasonably sufficient to permit us to contact you, such as your full legal name, physical address, telephone number, and valid email address.</li>
                            <li>A statement that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law.</li>
                            <li>A statement that the information in the notification is accurate, and under penalty of perjury, that you are authorized to act on behalf of the owner of an exclusive right that is allegedly infringed.</li>
                        </ol>
                    </div>
                </section>

                <section class="mb-4">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">4. Designated Copyright Agent</h4>
                    <p class="text-secondary">
                        Please direct all formal DMCA notices to our Designated Copyright Representative via our online contact system or email:
                    </p>
                    <div class="card bg-light border p-3">
                        <p class="mb-1 text-dark"><strong>DMCA Compliance Team & Copyright Agent</strong></p>
                        <p class="mb-1 text-secondary small">{{ $siteName }} Operations</p>
                        <p class="mb-2 text-secondary small">Email: <code>{{ $supportEmail }}</code></p>
                        <div>
                            <a href="{{ route('contact') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                                <i class="fa fa-envelope-o mr-1"></i> Submit DMCA Notice Online
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mb-2">
                    <h4 class="font-weight-bold text-dark border-bottom pb-2">5. Counter-Notification</h4>
                    <p class="text-secondary">
                        If you believe that a URL was wrongly disabled or removed due to a mistake or misidentification, you may submit a written counter-notification pursuant to Section 512(g)(3) of the DMCA containing your legal contact information, consent to jurisdiction, and a statement under penalty of perjury confirming your good faith belief that the material was removed by mistake.
                    </p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

