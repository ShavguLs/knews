@extends('layouts.app')

@section('title', 'Admin Login | KNEWS')

@section('content')
    <div class="form-panel">
        <h1 class="form-panel__title">ADMIN LOGIN</h1>

        <form action="{{ route('admin.login.store') }}" method="POST">
            @csrf

            <div class="form-panel__group">
                <label class="form-panel__label" for="email">EMAIL</label>
                <input class="form-panel__input" type="email" name="email" id="email" value="{{ old('email') }}" autofocus>
                @error('email')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="password">PASSWORD</label>
                <input class="form-panel__input" type="password" name="password" id="password">
                @error('password')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__actions">
                <button type="submit" class="subscribe-button">LOGIN</button>
            </div>
        </form>
    </div>
@endsection