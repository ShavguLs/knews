@extends('layouts.app')

@section('title', $news->title . ' | KNEWS')

@php
    $fallbackImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuB3LYG0wfKd8oj34vEa4xqPV_t8xl4TRrCzrRBCSLqvxPjeUto3ow0QU_JgcgsCh0VEcD9F-_v0jUwKkBgfXfgGibrQueI3wVUIgsKImEfIEll_ME2GrjDmzGBdgAAxIZkQzamnvlhuU3HOd1AI166mKRxsccaepolfA1jhU_MWubjFkuklFl8NEewOr2gicOyHkB4RN6ztOO5ITdFNLOOXH0a3yNmyVBRUX7kf2RDzm3mb9V__Yo7niRL_p1SA_8_2qUlFCHEw-zR';
    $articleImage = $news->image_url ?: $fallbackImage;
    $articleDate = $news->published_at ? $news->published_at->format('F j, Y') : 'UNPUBLISHED';
@endphp

@section('content')
    <div class="article-detail">
        <span class="article-detail__kicker">{{ strtoupper($news->category ?? 'NEWS') }}</span>
        <h1 class="article-detail__title">{{ $news->title }}</h1>
        <div class="article-detail__byline">
            <span class="article-detail__author">BY {{ strtoupper($news->author ?? 'UNKNOWN') }}</span>
            <span class="article-detail__date">{{ $articleDate }}</span>
        </div>

        <div class="article-detail__image-frame">
            <img alt="{{ $news->title }}" class="article-detail__image" src="{{ $articleImage }}">
        </div>

        <div class="article-detail__body">{{ $news->body }}</div>

        <a class="article-detail__back" href="{{ route('news.index') }}">BACK TO DISPATCHES</a>
    </div>
@endsection