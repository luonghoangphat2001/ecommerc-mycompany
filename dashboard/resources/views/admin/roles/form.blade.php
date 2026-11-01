@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            <p class="page-description">{{ __('admin.roles.description') }}</p>
        </div>
        <div class="actions">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.back') }}</a>
        </div>
    </div>

    <form method="post" action="{{ $record ? route($routePrefix . '.update', $record->id) : route($routePrefix . '.store') }}" class="shield-form">
        @csrf
        @if ($record)
            @method('put')
        @endif

        <div class="shield-layout">
            <div class="card shield-card">
                <div class="panel-heading">
                        <div>
                            <h2>{{ __('admin.roles.details') }}</h2>
                            <p>{{ __('admin.roles.details_desc') }}</p>
                        </div>
                </div>

                <div class="form-grid compact-grid">
                    <div class="form-row">
                        <label for="name">{{ __('admin.roles.role_name') }}</label>
                        @php
                            $roleOptions = \App\Enums\RoleEnum::options();
                            $currentName = old('name', $record->name ?? '');
                            if ($currentName && !array_key_exists($currentName, $roleOptions)) {
                                $roleOptions[$currentName] = $currentName . ' (Hiện tại)';
                            }
                        @endphp
                        <select id="name" name="name" class="form-select" required>
                            <option value="">-- {{ __('admin.actions.select') }} --</option>
                            @foreach ($roleOptions as $roleVal => $roleLabel)
                                <option value="{{ $roleVal }}" @selected($currentName === $roleVal)>
                                    {{ $roleLabel }} ({{ $roleVal }})
                                </option>
                            @endforeach
                        </select>
                        @error('name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-row">
                        <label for="guard_name">{{ __('admin.roles.guard') }}</label>
                        <input id="guard_name" type="text" name="guard_name" value="{{ old('guard_name', $record->guard_name ?? 'web') }}" required>
                        @error('guard_name')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card shield-card shield-summary">
                <div class="label">{{ __('admin.roles.selected_permissions') }}</div>
                <div class="value"><span data-permission-count>{{ count(old('permissions', $selectedPermissions)) }}</span></div>
                <p class="page-description">{{ __('admin.roles.summary_desc') }}</p>
            </div>
        </div>

        <div class="card shield-permissions">
            <div class="panel-heading shield-heading">
                <div>
                    <h2>{{ __('admin.sidebar.permissions') }}</h2>
                    <p>{{ __('admin.roles.permissions_desc') }}</p>
                </div>
                <div class="actions">
                    <button class="btn btn-secondary" type="button" data-check-all>{{ __('admin.actions.choose_all') }}</button>
                    <button class="btn btn-secondary" type="button" data-uncheck-all>{{ __('admin.actions.clear_all') }}</button>
                </div>
            </div>

            @error('permissions')<div class="error">{{ $message }}</div>@enderror

            <div class="shield-matrix">
                @forelse ($permissionMatrix as $resource => $actions)
                    @php($resourceId = 'resource-' . \Illuminate\Support\Str::slug($resource))
                    @php($resourcePermissions = collect($actions)->flatten()->values()->all())
                    <section class="shield-resource" data-resource-card>
                        <header class="shield-resource-header">
                            <label class="shield-resource-title" for="{{ $resourceId }}">
                                <input id="{{ $resourceId }}" type="checkbox" data-resource-toggle>
                                <span>{{ $resource }}</span>
                            </label>
                            <span>{{ count($resourcePermissions) }} quyền</span>
                        </header>

                        <div class="shield-action-grid">
                            @foreach ($actions as $action => $permissions)
                                @foreach ($permissions as $permission)
                                    @php($isChecked = in_array($permission, old('permissions', $selectedPermissions), true))
                                    <label class="shield-permission-pill" title="{{ $permission }}">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked($isChecked)>
                                        <span class="shield-action-tag">{{ \App\Support\AdminLabel::action($action) }}</span>
                                        <code class="shield-perm-code">{{ $permission }}</code>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="empty-state">{{ __('admin.roles.empty_state') }}</div>
                @endforelse
            </div>
        </div>

        <div class="form-footer sticky-footer">
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">{{ __('admin.actions.cancel') }}</a>
            <button class="btn" type="submit">{{ __('admin.actions.save') }}</button>
        </div>
    </form>

    <script>
        (() => {
            const form = document.querySelector('.shield-form');
            const count = document.querySelector('[data-permission-count]');
            if (!form || !count) return;

            const update = () => {
                const checked = form.querySelectorAll('input[name="permissions[]"]:checked');
                count.textContent = checked.length;

                form.querySelectorAll('[data-resource-card]').forEach((card) => {
                    const boxes = [...card.querySelectorAll('input[name="permissions[]"]')];
                    const selected = boxes.filter((box) => box.checked).length;
                    const toggle = card.querySelector('[data-resource-toggle]');
                    if (!toggle) return;
                    toggle.checked = boxes.length > 0 && selected === boxes.length;
                    toggle.indeterminate = selected > 0 && selected < boxes.length;
                });
            };

            form.addEventListener('change', (event) => {
                if (event.target.matches('[data-resource-toggle]')) {
                    event.target.closest('[data-resource-card]')
                        .querySelectorAll('input[name="permissions[]"]')
                        .forEach((box) => box.checked = event.target.checked);
                }
                update();
            });

            form.querySelector('[data-check-all]')?.addEventListener('click', () => {
                form.querySelectorAll('input[name="permissions[]"]').forEach((box) => box.checked = true);
                update();
            });

            form.querySelector('[data-uncheck-all]')?.addEventListener('click', () => {
                form.querySelectorAll('input[name="permissions[]"]').forEach((box) => box.checked = false);
                update();
            });

            update();
        })();
    </script>
@endsection
