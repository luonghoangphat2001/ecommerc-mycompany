@extends('admin.layouts.app', ['title' => __('admin.auth.login_title')])

@section('content')
    <div style="width: min(100%, 420px);">
        <div class="card" style="padding: 28px 30px; box-shadow: 0 12px 32px -8px rgba(9, 30, 52, 0.12), 0 2px 6px -1px rgba(9, 30, 52, 0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 18px; margin-bottom: 20px; border-bottom: 1px solid var(--fi-border);">
                <div style="display: flex; align-items: center; gap: 11px;">
                    <div class="brand-mark" style="width: 36px; height: 36px; font-size: 15px;">N</div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: var(--fi-text); letter-spacing: -0.01em;">{{ __('admin.brand.title') }}</div>
                        <div style="font-size: 11.5px; color: var(--fi-muted);">{{ __('admin.brand.subtitle') }}</div>
                    </div>
                </div>
                <span class="badge badge-info" style="font-family: var(--fi-font-mono); font-size: 10.5px;">ADMIN ERP</span>
            </div>

            <div style="margin-bottom: 20px;">
                <h1 class="page-title" style="font-size: 18px;">{{ __('admin.auth.login_title') }}</h1>
                <p class="page-description" style="margin-top: 4px;">{{ __('admin.auth.login_description') }}</p>
            </div>

            <form method="post" action="{{ route('admin.login.submit') }}" class="stack" style="gap: 14px;">
                @csrf

                <div class="form-row">
                    <label for="email">{{ __('admin.auth.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', config('admin.login.email')) }}" required autofocus placeholder="admin@example.com">
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <label for="password">{{ __('admin.auth.password') }}</label>
                    <input id="password" type="password" name="password" value="{{ old('password', config('admin.login.password')) }}" required placeholder="••••••••">
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin: 2px 0 4px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 500; font-size: 12.5px; cursor: pointer;">
                        <input type="checkbox" name="remember">
                        <span>{{ __('admin.auth.remember') }}</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; min-height: 38px; font-size: 13px;">
                    <span>{{ __('admin.auth.submit') }}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 16px; color: var(--fi-muted); font-size: 11.5px;">
            Hệ thống quản trị doanh nghiệp bảo mật &bull; {{ date('Y') }} {{ __('admin.brand.title') }}
        </div>
    </div>
@endsection
