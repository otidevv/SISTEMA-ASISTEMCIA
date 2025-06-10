@extends('layouts.cepre')

@section('title', 'CEPRE UNAMAD')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/nice-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets_cepre/style.css') }}">
@endpush

@section('content')
    <div id="preloader" class="preloader">
        <div class="animation-preloader">
            <div class="edu-preloader-icon"> 
                <img src="{{ asset('assets_cepre/IMG/preloader.gif') }}" alt="">               
            </div>
            <div class="txt-loading">
                @foreach (['C', 'E', 'P', 'R', 'E', 'U', 'N', 'A', 'M', 'A', 'D'] as $letter)
                    <span class="letters-loading" data-text-preloader="{{ $letter }}">{{ $letter }}</span>
                @endforeach
            </div>
            <p class="text-center">Cargando...</p>
        </div>
        <div class="loader">
            <div class="row">
                <div class="col-3 loader-section section-left"><div class="bg"></div></div>
                <div class="col-3 loader-section section-left"><div class="bg"></div></div>
                <div class="col-3 loader-section section-right"><div class="bg"></div></div>
                <div class="col-3 loader-section section-right"><div class="bg"></div></div>
            </div>
        </div>
    </div>

    @include('partials.cepreunamad')
@endsection

