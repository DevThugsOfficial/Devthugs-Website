@extends('layouts.app')

@section('title', 'Devthugz — Home')

@section('content')
    <main>
        @include('components.hero-section')
        @include('components.featured-projects', ['projects' => $featuredProjects])
        @include('components.why-us')
        @include('components.participated')
        @include('components.partnerships', ['partners' => $partners])
        @include('components.persuasive-cta')
    </main>
@endsection
