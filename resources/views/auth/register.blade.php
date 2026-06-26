@extends('layouts.app')

@section('title', 'Register | KNEWS')

@section('content')
    <div class="form-panel">
        <h1 class="form-panel__title">CREATE ACCOUNT</h1>

        <form action="{{ route('register.store') }}" method="POST">
            @csrf

            <div class="form-panel__group">
                <label class="form-panel__label" for="name">NAME</label>
                <input class="form-panel__input" type="text" name="name" id="name" value="{{ old('name') }}" autofocus>
                @error('name')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="email">EMAIL</label>
                <input class="form-panel__input" type="email" name="email" id="email" value="{{ old('email') }}">
                @error('email')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="password">PASSWORD</label>
                <input class="form-panel__input" type="password" name="password" id="password">
                @error('password')<div class="form-panel__error">{{ $message }}</div>@enderror
            </div>

            <div class="form-panel__group">
                <label class="form-panel__label" for="password_confirmation">CONFIRM PASSWORD</label>
                <input class="form-panel__input" type="password" name="password_confirmation" id="password_confirmation">
            </div>

            <div class="form-panel__actions">
                <button type="submit" class="subscribe-button">REGISTER</button>
                <a href="{{ route('login') }}" class="btn-brutal btn-brutal--stone">HAVE AN ACCOUNT?</a>
            </div>
        </form>
    </div>
@endsection
