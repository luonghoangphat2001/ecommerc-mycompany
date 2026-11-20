@extends('admin.layouts.app', ['title' => $title])

@section('content')
    <x-admin.page-header
        :title="$title"
        :description="$record->location ?? $record->slug"
        :backUrl="route($routePrefix . '.index')"
    >
        <x-slot:actions>
            <a class="btn btn-secondary" href="{{ route($routePrefix . '.index') }}">&larr; {{ __('admin.actions.back') }}</a>
            <a class="btn btn-primary" href="{{ route($routePrefix . '.edit', $record->id) }}">{{ __('admin.actions.edit') }}</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px)">
            <x-admin.tabs mode="link" :active="$activeTab ?? ''" :tabs="$tabs" />
        </div>
    @endif

    <x-admin.data-table :paginator="$stockRows">
        <x-slot:head>
            <tr>
                <th>Ảnh</th>
                <th>Sản phẩm</th>
                <th>SKU</th>
                <th>Biến thể</th>
                <th class="text-end">Số lượng</th>
            </tr>
        </x-slot:head>

        @forelse ($stockRows as $product)
                        @php
                            $variant = $product->variants->firstWhere('id', $product->pivot->variant_id);
                            $variantLabel = $variant
                                ? collect($variant->attributes ?? [])->map(fn ($value, $key) => $key . ': ' . $value)->join(', ')
                                : '—';
                        @endphp
                        <tr>
                            <td>
                                @if ($product->featuredImage?->preview_url)
                                    <img src="{{ $product->featuredImage->preview_url }}" alt="{{ is_array($product->name) ? ($product->name[app()->getLocale()] ?? reset($product->name)) : $product->name }}" loading="lazy" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ is_array($product->name) ? ($product->name[app()->getLocale()] ?? reset($product->name)) : $product->name }}</td>
                            <td>{{ $variant?->sku ?? $product->sku ?? '—' }}</td>
                            <td>{{ $variantLabel }}</td>
                            <td class="text-end"><strong>{{ number_format((int) $product->pivot->stock_quantity) }}</strong></td>
                        </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Kho chưa có tồn kho sản phẩm.</td></tr>
        @endforelse
    </x-admin.data-table>
@endsection
