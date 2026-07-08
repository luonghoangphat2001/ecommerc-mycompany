@extends('admin.layouts.app', ['title' => __('admin.auth.login_title')])

@section('content')
    <div class="card login-card">
        <div class="brand" style="border-bottom:0; margin-bottom:8px; padding:0;">
            <div class="brand-mark">A</div>
            <div>
                <div class="brand-title">{{ __('admin.brand.title') }}</div>
                <div class="brand-subtitle">{{ __('admin.brand.subtitle') }}</div>
            </div>
        </div>

        <div class="page-header" style="display:block; margin-bottom:18px;">
            <h1 class="page-title">{{ __('admin.auth.login_title') }}</h1>
            <p class="page-description">{{ __('admin.auth.login_description') }}</p>
        </div>

        <form method="post" action="{{ route('admin.login.submit') }}" class="stack">
            @csrf

            <div class="form-row">
                <label for="email">{{ __('admin.auth.email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email', config('admin.login.email')) }}" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label for="password">{{ __('admin.auth.password') }}</label>
                <input id="password" type="password" name="password" value="{{ old('password', config('admin.login.password')) }}" required>
            </div>

            <label style="display:flex; align-items:center; gap:8px; margin:2px 0 8px;">
                <input type="checkbox" name="remember" style="width:auto;">
                {{ __('admin.auth.remember') }}
            </label>

            <button type="submit" class="btn">{{ __('admin.auth.submit') }}</button>
        </form>
    </div>
@endsection
