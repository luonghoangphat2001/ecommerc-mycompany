@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteUrl' => null,
    'confirmMessage' => null,
])

<div class="actions" style="justify-content: flex-end;">
    {{ $slot }}

    @if ($viewUrl)
        <a class="link-action" href="{{ $viewUrl }}">{{ __('admin.actions.view') }}</a>
    @endif

    @if ($editUrl)
        <a class="link-action" href="{{ $editUrl }}">{{ __('admin.actions.edit') }}</a>
    @endif

    @if ($deleteUrl)
        <form method="post" action="{{ $deleteUrl }}" style="margin: 0; display: inline;">
            @csrf
            @method('delete')
            <button
                class="link-action link-danger"
                type="submit"
                onclick="return confirm('{{ $confirmMessage ?? __('admin.messages.confirm_delete') }}')"
            >
                {{ __('admin.actions.delete') }}
            </button>
        </form>
    @endif
</div>

