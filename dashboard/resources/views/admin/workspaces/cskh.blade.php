@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.cskh.title'),
    'description' => $description ?? __('workspace.cskh.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.avg_rating') }}</div>
        <div class="value" style="color: #047857;">{{ $metrics['avg_rating'] ?? 0 }} <span style="font-size: 14px; color: #f59e0b;">★</span></div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.sentiment') }}</div>
        <div class="value" style="color: var(--fi-primary);">{{ $metrics['sentiment'] ?? '0%' }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['open_tickets'] ?? 0) > 0 ? 'border-color: #fde68a; background: #fffbeb;' : '' }}">
        <div class="label">{{ __('workspace.cskh.kpis.open_tickets') }}</div>
        <div class="value" style="color: {{ ($metrics['open_tickets'] ?? 0) > 0 ? '#b45309' : 'var(--fi-muted)' }};">
            {{ number_format($metrics['open_tickets'] ?? 0) }}
        </div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.coupons') }}</div>
        <div class="value" style="color: var(--fi-text);">{{ number_format($metrics['coupons'] ?? 0) }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 12px; flex-wrap: wrap;">
            <div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cskh.content.reviews_title') }}</h3>
                <p style="color: var(--fi-muted); font-size: 12.5px; margin: 3px 0 0;">{{ __('workspace.cskh.content.reviews_desc') }}</p>
            </div>
            <a href="{{ route('admin.customer-reviews.create') }}" class="btn btn-primary btn-sm">+ Thêm Đánh giá</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 180px;">{{ __('workspace.cskh.tables.reviews.customer') }}</th>
                        <th style="width: 130px;">{{ __('workspace.cskh.tables.reviews.rating') }}</th>
                        <th>{{ __('workspace.cskh.tables.reviews.content') }}</th>
                        <th style="width: 140px;">{{ __('workspace.cskh.tables.reviews.sentiment') }}</th>
                        <th class="text-right" style="width: 260px;">{{ __('workspace.cskh.tables.reviews.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        @php
                            $sentimentVal = $review->sentiment instanceof \BackedEnum ? $review->sentiment->value : $review->sentiment;
                        @endphp
                        <tr style="{{ $sentimentVal === 'negative' ? 'background: #fffafa;' : '' }}">
                            <td>
                                <strong style="font-weight: 600;">{{ $review->customer_name ?? 'N/A' }}</strong><br>
                                <span class="mono cell-muted" style="font-size: 11px;">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <span style="color: #f59e0b; font-size: 14px; letter-spacing: 1px;">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </span>
                            </td>
                            <td style="max-width: 360px;">
                                <div style="font-size: 13px; color: var(--fi-text); margin-bottom: 4px;">{{ $review->content }}</div>
                                @if($review->reply_content)
                                    <div style="background: #f8fafc; padding: 7px 10px; border-radius: var(--fi-radius-sm); border: 1px solid var(--fi-border); font-size: 12px; color: var(--fi-text-secondary);">
                                        <strong>{{ __('workspace.cskh.tables.reviews.reply') }}:</strong> {{ $review->reply_content }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <x-admin.status-badge :status="$sentimentVal" :label="__('workspace.cskh.sentiment_types.' . $sentimentVal)" />
                            </td>
                            <td class="text-right">
                                <x-admin.row-actions
                                    :viewUrl="route('admin.customer-reviews.show', $review->id)"
                                    :editUrl="route('admin.customer-reviews.edit', $review->id)"
                                    :deleteUrl="route('admin.customer-reviews.destroy', $review->id)"
                                >
                                    @if(!$review->reply_content)
                                        <a href="{{ route('admin.customer-reviews.edit', $review->id) }}" class="link-action">{{ __('workspace.cskh.actions.reply') }}</a>
                                    @endif

                                    @if(!$review->coupon_id)
                                        <a href="{{ route('admin.customer-reviews.edit', $review->id) }}" class="link-action">{{ __('workspace.cskh.actions.gift_coupon') }}</a>
                                    @else
                                        <x-admin.status-badge status="info" :label="__('workspace.cskh.actions.gifted') . ' ' . ($review->coupon->code ?? 'N/A')" />
                                    @endif
                                </x-admin.row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">{{ __('workspace.cskh.tables.empty_reviews') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 1" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cskh.content.sentiment_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.cskh.content.sentiment_desc') }}</p>
        </div>
        <div class="split-grid-2col">
            <div style="height: 320px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 20px;">
                <canvas id="sentimentChart"></canvas>
            </div>
            <div>
                <div class="card" style="margin-bottom: 0; padding: 0;">
                    <div class="card-header" style="margin: 0;"><h3 class="card-title">Chi tiết Cảm xúc</h3></div>
                    <div class="card-body" style="padding: 18px 20px;">
                        @php
                            $allReviews = \App\Models\DepartmentCustomerReview::all();
                            $positiveCount = $allReviews->filter(fn ($r) => ($r->sentiment instanceof \BackedEnum ? $r->sentiment->value : $r->sentiment) === 'positive')->count();
                            $neutralCount = $allReviews->filter(fn ($r) => ($r->sentiment instanceof \BackedEnum ? $r->sentiment->value : $r->sentiment) === 'neutral')->count();
                            $negativeCount = $allReviews->filter(fn ($r) => ($r->sentiment instanceof \BackedEnum ? $r->sentiment->value : $r->sentiment) === 'negative')->count();
                            $total = $allReviews->count() ?: 1;
                        @endphp
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px; font-size: 13px;"><span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.positive') }}</span>
                            <strong class="mono">{{ round(($positiveCount / $total) * 100) }}%</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px; font-size: 13px;"><span style="width: 10px; height: 10px; background: #f59e0b; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.neutral') }}</span>
                            <strong class="mono">{{ round(($neutralCount / $total) * 100) }}%</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px; font-size: 13px;"><span style="width: 10px; height: 10px; background: #ef4444; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.negative') }}</span>
                            <strong class="mono">{{ round(($negativeCount / $total) * 100) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 2" style="display: none;">
        <div style="margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 700;">{{ __('workspace.cskh.content.coupons_title') }}</h3>
            <p style="margin: 3px 0 0; font-size: 12.5px; color: var(--fi-muted);">{{ __('workspace.cskh.content.coupons_desc') }}</p>
        </div>
        <div class="empty-state" style="border: 1px dashed var(--fi-border-strong); border-radius: var(--fi-radius-md); padding: 48px 20px;">
            Cấu hình các loại Coupon đền bù (Giảm 10%, Freeship...)
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var canvas = document.getElementById('sentimentChart');
            if (!canvas) return;
            var ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("workspace.cskh.sentiment_types.positive") }}',
                        '{{ __("workspace.cskh.sentiment_types.neutral") }}',
                        '{{ __("workspace.cskh.sentiment_types.negative") }}'
                    ],
                    datasets: [{
                        data: [{{ $positiveCount ?? 0 }}, {{ $neutralCount ?? 0 }}, {{ $negativeCount ?? 0 }}],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 18,
                                font: {
                                    family: "'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
                                    size: 12.5
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
@endsection
