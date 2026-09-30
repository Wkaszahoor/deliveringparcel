@extends('home2.layouts.app')
@section('title', 'Review Widget Preview')

@section('content')
<section class="h2-section">
    <div class="h2-container">
        <h2>Review widget — instance preview</h2>
        <p class="muted">The same <code>&lt;x-review-widget /&gt;</code> component rendered from different configurations (RV-006/RV-011 integration point for the Theme/Widget manager).</p>
        @foreach ($instances as $i)
            <div class="h2-card" style="margin:1.5rem 0">
                <div class="h2-card-body">
                    <h5 class="muted">{{ $i['title'] }}</h5>
                    <x-review-widget :widget="$i['widget']" />
                    @if (empty($i['widget']['title']))
                        <p class="muted small mb-0">Config: <code>{{ json_encode($i['widget']) }}</code></p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection
