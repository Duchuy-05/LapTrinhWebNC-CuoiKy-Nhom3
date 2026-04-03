@extends('layouts.app')

@section('title', 'Danh sách khóa học | StudyHub')

@section('content')
    <section class="section-block compact-top">
        <div class="container section-heading">
            <div>
                <span class="eyebrow">Thư viện khóa học</span>
                <h1>Tìm khóa học phù hợp với mục tiêu của bạn</h1>
            </div>
        </div>

        <div class="container filter-card">
            <form class="filter-grid" method="GET" action="{{ route('courses.index') }}">
                <div class="form-field">
                    <label for="search">Từ khóa</label>
                    <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Ví dụ: Laravel, HTML, PHP...">
                </div>
                <div class="form-field">
                    <label for="category">Danh mục</label>
                    <select id="category" name="category">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($selectedCategory === $category->slug)>
                                {{ $category->name }} ({{ $category->published_courses_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <button class="button" type="submit">Lọc khóa học</button>
                    <a class="button button-ghost" href="{{ route('courses.index') }}">Làm mới</a>
                </div>
            </form>
        </div>
    </section>

    <section class="section-block compact-top">
        <div class="container card-grid card-grid-3">
            @forelse ($courses as $course)
                @include('partials.course-card', ['course' => $course])
            @empty
                <article class="empty-state wide-card">
                    <h3>Chưa tìm thấy khóa học phù hợp</h3>
                    <p>Hãy thử đổi từ khóa tìm kiếm hoặc chọn một danh mục khác để tiếp tục.</p>
                </article>
            @endforelse
        </div>

        <div class="container pagination-wrap">
            @include('partials.pager', ['paginator' => $courses])
        </div>
    </section>
@endsection