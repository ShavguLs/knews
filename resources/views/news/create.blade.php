@extends('layouts.app')

@section('title', 'Create News | KNEWS')

@section('content')
    <div class="form-panel">
        <h1 class="form-panel__title">CREATE DISPATCH</h1>

        <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-panel__group">
                <label class="form-panel__label" for="title">TITLE</label>
                <input class="form-panel__input" type="text" name="title" id="title" value="{{ old('title') }}">
                @error('title')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="category">CATEGORY</label>
                <input class="form-panel__input" type="text" name="category" id="category" value="{{ old('category') }}">
                @error('category')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="author">AUTHOR</label>
                <input class="form-panel__input" type="text" name="author" id="author" value="{{ old('author') }}">
                @error('author')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="body">BODY</label>
                <textarea class="form-panel__textarea" name="body" id="body" rows="8">{{ old('body') }}</textarea>
                @error('body')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="image_file">IMAGE UPLOAD</label>
                <input class="form-panel__input" type="file" name="image_file" id="image_file" accept="image/*">
                @error('image_file')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="image_url">IMAGE URL FALLBACK</label>
                <input class="form-panel__input" type="url" name="image_url" id="image_url" value="{{ old('image_url') }}">
                @error('image_url')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="published_at">PUBLISHED DATE</label>
                <input class="form-panel__input" type="date" name="published_at" id="published_at" value="{{ old('published_at') }}">
                @error('published_at')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="status">STATUS</label>
                <select class="form-panel__input" name="status" id="status">
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>PENDING</option>
                    <option value="done" {{ old('status', 'pending') === 'done' ? 'selected' : '' }}>DONE</option>
                </select>
                @error('status')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group form-panel__group--checkbox">
                <label class="form-panel__checkbox-label">
                    <input type="checkbox" name="is_hero" value="1" {{ old('is_hero') ? 'checked' : '' }}>
                    <span>SHOW THIS NEWS IN HERO</span>
                </label>
            </div>

            <div class="form-panel__actions">
                <button type="submit" class="subscribe-button">SAVE NEWS</button>
                <a href="{{ route('admin.news.index') }}" class="btn-brutal btn-brutal--stone">BACK</a>
            </div>
        </form>
    </div>
@endsection
