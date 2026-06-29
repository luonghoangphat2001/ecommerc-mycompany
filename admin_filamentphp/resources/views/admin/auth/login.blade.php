@extends('admin.layouts.app', ['title' => 'Admin Login'])

@section('content')
    <div class="card login-card">
        <div class="brand" style="border-bottom:0; margin-bottom:8px; padding:0;">
            <div class="brand-mark">A</div>
            <div>
                <div class="brand-title">Admin Panel</div>
                <div class="brand-subtitle">Laravel thuần</div>
            </div>
        </div>

        <div class="page-header" style="display:block; margin-bottom:18px;">
            <h1 class="page-title">Đăng nhập quản trị</h1>
            <p class="page-description">Truy cập khu vực vận hành ecommerce.</p>
        </div>

        <form method="post" action="{{ route('admin.login.submit') }}" class="stack">
            @csrf

            <div class="form-row">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password" required>
            </div>

            <label style="display:flex; align-items:center; gap:8px; margin:2px 0 8px;">
                <input type="checkbox" name="remember" style="width:auto;">
                Ghi nhớ đăng nhập
            </label>

            <button type="submit" class="btn">Đăng nhập</button>
        </form>
    </div>
@endsection
