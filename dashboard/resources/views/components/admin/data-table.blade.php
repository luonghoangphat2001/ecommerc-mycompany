@props([
    'columns' => [],
    'paginator' => null,
    'minWidth' => null,
])

<div {{ $attributes->merge(['class' => 'table-panel card']) }}>
    @if (isset($toolbar))
        {{ $toolbar }}
    @endif

    @if (isset($import))
        {{ $import }}
    @endif

    <div class="table-wrap">
        <table @if($minWidth) style="min-width: {{ $minWidth }};" @endif>
            @if (isset($head))
                <thead>
                    {{ $head }}
                </thead>
            @elseif (!empty($columns))
                <thead>
                    <tr>
                        @foreach ($columns as $col)
                            @if (is_array($col))
                                <th
                                    @if(!empty($col['class'])) class="{{ $col['class'] }}" @endif
                                    @if(!empty($col['width'])) style="width: {{ $col['width'] }};" @endif
                                >
                                    {{ $col['label'] ?? '' }}
                                </th>
                            @else
                                <th>{{ $col }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if (isset($footer))
        {{ $footer }}
    @elseif ($paginator)
        <div class="pagination">
            {{ $paginator->links('vendor.pagination.admin') }}
        </div>
    @endif
</div>

