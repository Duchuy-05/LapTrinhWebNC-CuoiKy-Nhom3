@extends('layouts.app')

@section('title', $page->title . ' | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container container-tight">
            <article class="surface-card prose-article">
                <span class="eyebrow">Nội dung hệ thống</span>
                <h1>{{ $page->title }}</h1>
                @if ($page->summary)
                    <p class="lead-text">{{ $page->summary }}</p>
                @endif
                <div class="prose-block">
                    <p>{!! nl2br(e($page->body)) !!}</p>
                </div>
            </article>
        </div>
    </section>
@endsection