@extends('admin.workspaces.layout', [
    'title' => $title ?? __('workspace.cskh.title'),
    'description' => $description ?? __('workspace.cskh.description')
])

@section('kpis')
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.avg_rating') }}</div>
        <div class="value" style="color: #10b981;">{{ $metrics['avg_rating'] ?? 0 }} <span style="font-size: 14px; color: #f59e0b;">★</span></div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.sentiment') }}</div>
        <div class="value" style="color: #3b82f6;">{{ $metrics['sentiment'] ?? '0%' }}</div>
    </div>
    <div class="stat" style="{{ ($metrics['open_tickets'] ?? 0) > 0 ? 'border-color: #f59e0b; background: #fffbeb;' : '' }}">
        <div class="label">{{ __('workspace.cskh.kpis.open_tickets') }}</div>
        <div class="value" style="color: {{ ($metrics['open_tickets'] ?? 0) > 0 ? '#f59e0b' : '#64748b' }};">
            {{ number_format($metrics['open_tickets'] ?? 0) }}
        </div>
    </div>
    <div class="stat">
        <div class="label">{{ __('workspace.cskh.kpis.coupons') }}</div>
        <div class="value" style="color: #8b5cf6;">{{ number_format($metrics['coupons'] ?? 0) }}</div>
    </div>
@endsection

@section('tab_contents')
    <div x-show="activeTab === 0">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <div>
                <h3 style="margin: 0;">{{ __('workspace.cskh.content.reviews_title') }}</h3>
                <p style="color: #64748b; font-size: 13px; margin-top: 0;">{{ __('workspace.cskh.content.reviews_desc') }}</p>
            </div>
            <a href="{{ route('admin.customer-reviews.create') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">+ Thêm Đánh giá</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('workspace.cskh.tables.reviews.customer') }}</th>
                        <th>{{ __('workspace.cskh.tables.reviews.rating') }}</th>
                        <th>{{ __('workspace.cskh.tables.reviews.content') }}</th>
                        <th>{{ __('workspace.cskh.tables.reviews.sentiment') }}</th>
                        <th>{{ __('workspace.cskh.tables.reviews.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr style="{{ $review->sentiment === 'negative' ? 'background: #fef2f2;' : '' }}">
                        <td>
                            <strong>{{ $review->customer_name ?? 'N/A' }}</strong><br>
                            <span style="font-size: 11px; color: #94a3b8;">{{ $review->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td>
                            <span style="color: #f59e0b; font-size: 16px;">
                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                            </span>
                        </td>
                        <td style="max-width: 300px;">
                            <div style="font-size: 13px; color: #334155; margin-bottom: 5px;">{{ $review->content }}</div>
                            @if($review->reply_content)
                                <div style="background: #f1f5f9; padding: 6px; border-radius: 4px; border-left: 2px solid #3b82f6; font-size: 12px; color: #475569;">
                                    <strong>{{ __('workspace.cskh.tables.reviews.reply') }}:</strong> {{ $review->reply_content }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($review->sentiment === 'positive')
                                <span class="badge badge-success">{{ __('workspace.cskh.sentiment_types.positive') }}</span>
                            @elseif($review->sentiment === 'negative')
                                <span class="badge badge-danger">{{ __('workspace.cskh.sentiment_types.negative') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('workspace.cskh.sentiment_types.neutral') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                @if(!$review->reply_content)
                                    <a href="{{ route('admin.customer-reviews.edit', $review->id) }}" class="link-action" style="color: #3b82f6;">{{ __('workspace.cskh.actions.reply') }}</a>
                                @endif
                                
                                @if(!$review->coupon_id)
                                    <a href="{{ route('admin.customer-reviews.edit', $review->id) }}" class="link-action" style="color: #8b5cf6;">{{ __('workspace.cskh.actions.gift_coupon') }}</a>
                                @else
                                    <span style="font-size: 11px; color: #8b5cf6;"><svg style="width: 12px; height: 12px; display: inline;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg> {{ __('workspace.cskh.actions.gifted') }} {{ $review->coupon->code ?? 'N/A' }}</span>
                                @endif
                                <a href="{{ route('admin.customer-reviews.show', $review->id) }}" class="link-action">{{ __('admin.actions.view') }}</a>
                                <a href="{{ route('admin.customer-reviews.edit', $review->id) }}" class="link-action">Sửa</a>
                                <form action="{{ route('admin.customer-reviews.destroy', $review->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-action link-danger">Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">{{ __('workspace.cskh.tables.empty_reviews') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div x-show="activeTab === 1" style="display: none;">
        <h3>{{ __('workspace.cskh.content.sentiment_title') }}</h3>
        <p>{{ __('workspace.cskh.content.sentiment_desc') }}</p>
        <div style="display: flex; gap: 24px; align-items: stretch;">
            <div style="flex: 1; height: 350px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <canvas id="sentimentChart"></canvas>
            </div>
            <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header"><h3 class="card-title">Chi tiết Cảm xúc</h3></div>
                    <div style="padding: 20px;">
                        @php
                            $allReviews = \App\Models\DepartmentCustomerReview::all();
                            $positiveCount = $allReviews->where('sentiment', 'positive')->count();
                            $neutralCount = $allReviews->where('sentiment', 'neutral')->count();
                            $negativeCount = $allReviews->where('sentiment', 'negative')->count();
                            $total = $allReviews->count() ?: 1;
                        @endphp
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px;"><span style="width: 12px; height: 12px; background: #10b981; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.positive') }}</span>
                            <strong>{{ round(($positiveCount / $total) * 100) }}%</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px;"><span style="width: 12px; height: 12px; background: #f59e0b; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.neutral') }}</span>
                            <strong>{{ round(($neutralCount / $total) * 100) }}%</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px;"><span style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%; display: inline-block;"></span> {{ __('workspace.cskh.sentiment_types.negative') }}</span>
                            <strong>{{ round(($negativeCount / $total) * 100) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div x-show="activeTab === 2" style="display: none;">
        <h3>{{ __('workspace.cskh.content.coupons_title') }}</h3>
        <p>{{ __('workspace.cskh.content.coupons_desc') }}</p>
        <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
            <p style="color: #94a3b8;">Cấu hình các loại Coupon đền bù (Giảm 10%, Freeship...)</p>
        </div>
    </div>

    <!-- Inject Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('sentimentChart').getContext('2d');
            var sentimentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ __("workspace.cskh.sentiment_types.positive") }}',
                        '{{ __("workspace.cskh.sentiment_types.neutral") }}',
                        '{{ __("workspace.cskh.sentiment_types.negative") }}'
                    ],
                    datasets: [{
                        data: [{{ $positiveCount ?? 0 }}, {{ $neutralCount ?? 0 }}, {{ $negativeCount ?? 0 }}],
                        backgroundColor: [
                            '#10b981', // Tích cực (Green)
                            '#f59e0b', // Bình thường (Yellow)
                            '#ef4444'  // Tiêu cực (Red)
                        ],
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
                                padding: 20,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 13
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
