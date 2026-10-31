@extends('admin.layouts.app', ['title' => __('admin.auth.login_title')])

@section('content')
    <div class="card login-card">
        <div class="brand" style="border-bottom: 1px solid #f1f5f9; margin-bottom: 18px; padding: 0 0 14px 0;">
            <div class="brand-mark">N</div>
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

            <label style="display:flex; align-items:center; gap:8px; margin:4px 0 8px; font-weight:500;">
                <input type="checkbox" name="remember" style="width:15px; height:15px; accent-color:var(--fi-primary);">
                <span>{{ __('admin.auth.remember') }}</span>
            </label>

            <button type="submit" class="btn" style="width:100%; min-height:38px;">{{ __('admin.auth.submit') }}</button>
        </form>
    </div>
@endsection
