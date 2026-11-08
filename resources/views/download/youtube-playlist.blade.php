@extends('app')

@section('title', ($playlist['title'] ?? 'Playlist') . ' - ' . \App\SiteSetting::get('site_name', 'Zen X Download'))

@section('content')

    <div class="jumbotron jumbotron-fluid bg-white border-bottom py-3 py-md-4 mb-4" style="box-shadow: inset 0 -10px 20px -10px rgba(0,0,0,0.03);">
        <div class="container text-center px-3 px-sm-4">
            <h1 class="h4 font-weight-bold text-dark mb-3">Online Video & Playlist Downloader</h1>
            <form id="fd" action="{{ url('download') }}" method="post" class="col-md-10 col-lg-8 mx-auto p-0">
                {{ csrf_field() }}
                <div class="input-group responsive-search-box shadow-sm">
                    <input name="videourl" value="{{ $url }}" required class="form-control responsive-search-input px-3 px-sm-4" placeholder="Enter Video or Playlist Link..." type="text" autofocus>
                    <div class="input-group-append">
                        <button class="btn btn-primary responsive-search-btn">
                            <i class="fa fa-cloud-download mr-1" aria-hidden="true"></i> Download
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container mb-5 px-3 px-sm-4">
        <div class="card border-0 shadow-sm p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h3 class="h5 font-weight-bold mb-2 mb-md-0 text-dark">
                    <i class="fa fa-list-ul text-primary mr-2"></i> {{ $playlist['title'] }}
                </h3>
                <span class="badge badge-info px-3 py-2 font-weight-bold">{{ count($playlist['video']) }} Videos</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 110px;">Thumbnail</th>
                            <th>Video Title</th>
                            <th class="text-right" style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($playlist['video'] as $video)
                            <tr>
                                <td class="align-middle">
                                    <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}" class="img-fluid rounded" style="width: 100px; height: 58px; object-fit: cover;">
                                </td>
                                <td class="align-middle font-weight-bold text-dark" style="white-space: normal; min-width: 200px;">
                                    {{ $video['title'] }}
                                </td>
                                <td class="align-middle text-right">
                                    <form action="{{ url('download') }}" method="post" target="_blank">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="videourl" value="https://www.youtube.com/watch?v={{ $video['encrypted_id'] }}">
                                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3 shadow-sm">
                                            <i class="fa fa-download mr-1"></i> Get Links
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
