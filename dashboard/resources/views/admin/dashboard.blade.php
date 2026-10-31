@extends('admin.layouts.app', ['title' => $title ?? __('admin.dashboard.title')])

@php
    $months = [
        __('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'),
        __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'),
    ];
    $revenue = array_values((array) ($revenueSeries ?? []));
    $revenueMax = max(array_merge([1], $revenue));
    $topProductsList = collect($topProducts ?? []);
    $topProductsMax = max(array_merge([1], $topProductsList->pluck('revenue')->map(fn ($value) => (float) $value)->all()));
    $topProductsDonutList = collect($topProductsDonut ?? []);
    $donutTotal = max(1, $topProductsDonutList->sum('total_sold'));
    $donutColors = ['#004687', '#0284c7', '#059669', '#fbae17', '#475569', '#94a3b8'];
    $donutSegments = [];
    $offset = 0;
    foreach ($topProductsDonutList as $index => $item) {
        $percentage = ($item->total_sold / $donutTotal) * 100;
        $donutSegments[] = $donutColors[$index % count($donutColors)] . ' ' . $offset . '% ' . ($offset + $percentage) . '%';
        $offset += $percentage;
    }
    $donutBackground = $donutSegments !== [] ? 'conic-gradient(' . implode(', ', $donutSegments) . ')' : '#e2e8f0';
@endphp

@section('content')
    @if (session('status'))
        <div class="status-message" style="display: flex; align-items: center; gap: 8px; margin-bottom: 18px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Executive Operations Command Center -->
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #09253e; display: grid; place-items: center; color: #fbae17; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h2 style="font-size: 16px; font-weight: 700; margin: 0; letter-spacing: -0.01em; color: #09253e;">TRUNG TÂM ĐIỀU HÀNH & GIÁM SÁT DOANH NGHIỆP</h2>
                        <span class="badge badge-success">
                            <span style="width: 6px; height: 6px; border-radius: 999px; background: #059669;"></span>
                            Đang hoạt động (6 phân hệ)
                        </span>
                    </div>
                    <p style="margin: 3px 0 0 0; color: #64748b; font-size: 12.5px;">
                        Bảng điều khiển tích hợp giám sát chuỗi cung ứng, vận hành, kiểm toán và phê duyệt chi phí theo thời gian thực
                    </p>
                </div>
            </div>

            <!-- Quick Alert Badges -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;" class="tabular-nums">
                <a href="#exception-inbox" class="badge badge-danger" style="padding: 5px 10px; text-decoration: none;">
                    <span>Phê duyệt chi:</span> <strong>{{ $pendingProposals->count() }} Chờ duyệt</strong>
                </a>
                <a href="{{ route('admin.workspace.show', 'ops') }}" class="badge badge-warning" style="padding: 5px 10px; text-decoration: none;">
                    <span>Sự cố mở:</span> <strong>{{ $urgentIncidents->count() }}</strong>
                </a>
                <a href="{{ route('admin.workspace.show', 'logistics') }}" class="badge badge-info" style="padding: 5px 10px; text-decoration: none;">
                    <span>Tồn kho cảnh báo:</span> <strong>{{ $lowStockProductsCount }} SKU</strong>
                </a>
            </div>
        </div>

        <!-- 6 Department Workspaces Grid -->
        <div style="margin-bottom: 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;">
                CÁC PHÂN HỆ NGHIỆP VỤ THEO PHÒNG BAN
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(185px, 1fr)); gap: 10px;">
                @php
                    $agentCards = [
                        ['code' => 'rnd', 'name' => 'Phòng R&D', 'role' => 'Sản phẩm & Khuyến mãi', 'tag' => 'R&D', 'color' => '#004687'],
                        ['code' => 'logistics', 'name' => 'Phòng Logistics', 'role' => 'Chuỗi cung ứng & PO', 'tag' => 'LOG', 'color' => '#0284c7'],
                        ['code' => 'cfo', 'name' => 'Phòng Tài Chính', 'role' => 'Dòng tiền & Chi phí', 'tag' => 'CFO', 'color' => '#059669'],
                        ['code' => 'ops', 'name' => 'Phòng Vận Hành', 'role' => 'Vận hành & Sự cố', 'tag' => 'OPS', 'color' => '#d97706'],
                        ['code' => 'cskh', 'name' => 'Phòng CSKH', 'role' => 'Trải nghiệm & Đánh giá', 'tag' => 'CS', 'color' => '#004687'],
                        ['code' => 'hr', 'name' => 'Phòng Nhân Sự', 'role' => 'Nhân sự & Hợp đồng', 'tag' => 'HR', 'color' => '#0d9488'],
                    ];
                @endphp
                @foreach ($agentCards as $agent)
                    <a href="{{ route('admin.workspace.show', $agent['code']) }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; transition: border-color 0.15s ease, background 0.15s ease;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 10.5px; font-weight: 700; color: {{ $agent['color'] }}; background: #ffffff; border: 1px solid #e2e8f0; padding: 1px 6px; border-radius: 4px;">{{ $agent['tag'] }}</span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #047857; font-weight: 600;">
                                    <span style="width: 5px; height: 5px; border-radius: 999px; background: #059669;"></span> Hoạt động
                                </span>
                            </div>
                            <strong style="color: #09253e; font-size: 13px; display: block;">{{ $agent['name'] }}</strong>
                            <span style="color: #64748b; font-size: 11.5px; display: block; margin-top: 2px;">{{ $agent['role'] }}</span>
                        </div>
                        <span style="margin-top: 10px; font-size: 11.5px; color: #004687; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            Mở phân hệ &rarr;
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Quick Shortcuts -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 8px; align-items: center;">
            <span style="font-size: 11px; font-weight: 700; color: #475569; margin-right: 4px;">TRUY CẬP NHANH:</span>
            <a href="{{ route('admin.workspace.show', 'cfo') }}" class="btn btn-secondary" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px;">
                Bảng cân đối dòng tiền
            </a>
            <a href="{{ route('admin.workspace.show', 'logistics') }}" class="btn btn-secondary" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px;">
                Kiểm tra tồn kho & Đơn PO
            </a>
            <a href="{{ route('admin.workspace.show', 'ops') }}" class="btn btn-secondary" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px;">
                Sự cố giao vận
            </a>
            <a href="{{ route('admin.workspace.show', 'hr') }}" class="btn btn-secondary" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px;">
                Hợp đồng & Quỹ lương
            </a>
        </div>
    </div>

    <!-- Exception Inbox & Incident Radar -->
    <div id="exception-inbox" class="dashboard-grid" style="margin-bottom: 20px;">
        <!-- Column 1: CEO 1-Click Financial Proposals -->
        <div class="card">
            <div class="panel-heading" style="margin-bottom: 14px;">
                <div>
                    <h2 style="display: flex; align-items: center; gap: 8px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#004687" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                        <span>Hộp Thư Bất Thường — Phê Duyệt Chi Phí (CEO 1-Click)</span>
                    </h2>
                    <p>Các đề xuất ngân sách phân tầng L3 (> 20M hoặc Khẩn cấp). CEO chỉ cần 1 thao tác để quyết định.</p>
                </div>
                <span class="badge badge-danger">
                    {{ $pendingProposals->count() }} Chờ duyệt
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tiêu đề đề xuất</th>
                            <th>Phòng ban</th>
                            <th style="text-align: right;">Số tiền (VND)</th>
                            <th style="text-align: center;">Mức độ</th>
                            <th style="text-align: center;">Thao tác 1-Click</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingProposals as $proposal)
                            <tr>
                                <td>
                                    <strong style="color: #0f172a;">{{ $proposal->title }}</strong>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                        Người gửi: {{ $proposal->user->name ?? 'N/A' }} &bull; #{{ $proposal->id }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ $proposal->department->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #b91c1c;">
                                    {{ number_format($proposal->amount) }}
                                </td>
                                <td style="text-align: center;">
                                    @if ($proposal->is_urgent)
                                        <span class="badge badge-danger">Khẩn cấp</span>
                                    @else
                                        <span class="badge badge-secondary">Thường</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px; background: #059669;" title="Phê duyệt ngay 1 chạm">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 4px 8px; font-size: 11.5px;" title="Từ chối đề xuất">
                                                Từ chối
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: #059669; font-weight: 600;">
                                    Không có đề xuất nào tồn đọng trong Exception Inbox.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 2: Operational Incidents & Purchase Orders -->
        <div class="card">
            <div class="panel-heading" style="margin-bottom: 14px;">
                <div>
                    <h2 style="display: flex; align-items: center; gap: 8px;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Radar Sự Cố Vận Hành & Chuỗi Cung Ứng</span>
                    </h2>
                    <p>Giám sát sự cố đơn hàng và tiến độ giao hàng từ các nhà cung cấp chiến lược.</p>
                </div>
                <a href="{{ route('admin.workspace.show', 'ops') }}" style="font-size: 12px; color: #004687; text-decoration: none; font-weight: 600; white-space: nowrap;">
                    Xem chi tiết &rarr;
                </a>
            </div>

            <div style="display: grid; gap: 14px;" class="tabular-nums">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                        Sự cố cần giải quyết ({{ $urgentIncidents->count() }})
                    </div>
                    @forelse ($urgentIncidents as $incident)
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                            <div>
                                <strong style="color: #991b1b; font-size: 12.5px;">{{ $incident->type }}</strong>
                                <span style="font-size: 11.5px; color: #64748b; margin-left: 6px;">Đơn #{{ $incident->order_id }}</span>
                                <p style="margin: 2px 0 0 0; color: #7f1d1d; font-size: 12px;">{{ Str::limit($incident->description, 60) }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.incidents.quick-status', $incident->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 4px 10px; font-size: 11.5px; border-color: #fca5a5; color: #b91c1c;">
                                    Đã xử lý
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color: #059669; font-size: 12.5px; font-weight: 500; padding: 6px 0;">Không có sự cố vận hành mở.</div>
                    @endforelse
                </div>

                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px;">
                        Theo dõi Đơn Mua Hàng PO Logistics
                    </div>
                    @forelse ($pendingPurchaseOrders as $po)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #09253e; font-size: 12.5px;">{{ $po->po_number }}</strong>
                                <span style="font-size: 12px; color: #64748b; margin-left: 6px;">{{ $po->supplier_name }}</span>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    Dự kiến: {{ optional($po->expected_delivery_date)->format('d/m/Y') ?? 'Chưa xác định' }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <strong style="font-size: 12.5px; color: #004687; display: block; margin-bottom: 2px;">{{ number_format($po->total_amount) }} VND</strong>
                                <span class="badge badge-info">{{ $po->status }}</span>
                            </div>
                        </div>
                    @empty
                        <div style="color: #64748b; font-size: 12.5px; padding: 6px 0;">Chưa có đơn PO nào đang xử lý.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Strategic Material Price Radar -->
    @if ($materialPrices->isNotEmpty())
        <div class="card" style="margin-bottom: 20px; padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#004687" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                    <strong style="font-size: 13.5px; color: #09253e;">Chỉ Số Giá Vật Tư Chiến Lược (Logistics & R&D Price Radar)</strong>
                </div>
                <a href="{{ route('admin.workspace.show', 'cfo') }}" style="font-size: 12px; color: #004687; text-decoration: none; font-weight: 600;">
                    Xem biểu đồ giá chi tiết &rarr;
                </a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px;" class="tabular-nums">
                @foreach ($materialPrices as $price)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                        <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">{{ $price->material_name }}</div>
                        <div style="font-size: 15px; font-weight: 800; color: #09253e; margin-top: 3px;">
                            {{ number_format($price->price) }} <span style="font-size: 11px; font-weight: 500; color: #64748b;">VND</span>
                        </div>
                        <div style="font-size: 10.5px; color: #94a3b8; margin-top: 2px;">
                            Cập nhật: {{ optional($price->recorded_at)->format('d/m/Y') ?? 'Hôm nay' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="page-header" style="margin-top: 18px;">
        <div>
            <h1 class="page-title">{{ __('admin.dashboard.title') }}</h1>
            <p class="page-description">{{ __('admin.dashboard.description') }}</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.users') }}</div>
            <div class="value">{{ number_format($totalUsers) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.products') }}</div>
            <div class="value">{{ number_format($totalProducts) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.orders') }}</div>
            <div class="value">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.revenue') }}</div>
            <div class="value">{{ number_format($totalRevenue) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.pending_orders') }}</div>
            <div class="value">{{ number_format($pendingOrders) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.paid_payments') }}</div>
            <div class="value">{{ number_format($paidPayments) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.content') }}</div>
            <div class="value">{{ number_format($totalPosts) }} / {{ number_format($totalPages) }}</div>
        </div>
        <div class="stat">
            <div class="label">{{ __('admin.dashboard.stats.media') }}</div>
            <div class="value">{{ number_format($totalMedia) }} / {{ number_format($totalTaxClasses) }} / {{ number_format($totalWebhooks) }}</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.line_revenue') }}</h2>
                    <p>{{ __('admin.dashboard.labels.value') }}</p>
                </div>
            </div>
            <svg viewBox="0 0 1000 260" width="100%" height="250" role="img" aria-label="{{ __('admin.dashboard.line_revenue') }}">
                <defs>
                    <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#004687" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="#004687" stop-opacity="0" />
                    </linearGradient>
                </defs>
                @for ($i = 0; $i < 11; $i++)
                    <line x1="{{ 40 + $i * 88 }}" y1="20" x2="{{ 40 + $i * 88 }}" y2="220" stroke="#f1f5f9" />
                @endfor
                @for ($i = 0; $i < 5; $i++)
                    <line x1="40" y1="{{ 20 + $i * 50 }}" x2="960" y2="{{ 20 + $i * 50 }}" stroke="#f1f5f9" />
                @endfor
                @if ($revenue !== [])
                    @php
                        $points = [];
                        $areaPoints = ['40,220'];
                        foreach ($revenue as $index => $amount) {
                            $x = 40 + ($index * 80);
                            $y = 220 - (($amount / $revenueMax) * 180);
                            $points[] = $x . ',' . $y;
                            $areaPoints[] = $x . ',' . $y;
                        }
                        $areaPoints[] = (40 + (count($revenue) - 1) * 80) . ',220';
                    @endphp
                    <polygon points="{{ implode(' ', $areaPoints) }}" fill="url(#revenueFill)" />
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#004687" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($revenue as $index => $amount)
                        @php
                            $x = 40 + ($index * 80);
                            $y = 220 - (($amount / $revenueMax) * 180);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4.5" fill="#ffffff" stroke="#004687" stroke-width="2.5" />
                        <text x="{{ $x }}" y="245" text-anchor="middle" fill="#64748b" font-size="11" font-weight="600">{{ $months[$index] ?? ($index + 1) }}</text>
                    @endforeach
                @endif
            </svg>
        </div>

        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.column_products') }}</h2>
                    <p>{{ __('admin.dashboard.labels.name') }}</p>
                </div>
            </div>
            <div style="display:grid; gap:12px;" class="tabular-nums">
                @forelse ($topProductsList as $item)
                    @php
                        $revenuePercent = $topProductsMax > 0 ? max(6, (($item->revenue ?? 0) / $topProductsMax) * 100) : 6;
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:5px;">
                            <strong style="font-size:12.5px; color:#09253e;">{{ $item->product_name ?? __('admin.dashboard.labels.name') }}</strong>
                            <span class="cell-muted" style="font-weight:600;">{{ number_format($item->revenue ?? 0) }}</span>
                        </div>
                        <div style="height:8px; border-radius:999px; background:#f1f5f9; overflow:hidden;">
                            <div style="height:100%; width:{{ min(100, $revenuePercent) }}%; background:#004687; border-radius:999px;"></div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top:16px;">
        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.donut_distribution') }}</h2>
                    <p>{{ __('admin.dashboard.labels.percentage') }}</p>
                </div>
            </div>
            <div style="display:grid; grid-template-columns: 220px minmax(0, 1fr); gap:18px; align-items:center;" class="tabular-nums">
                <div style="display:grid; place-items:center;">
                    <div style="width:196px; height:196px; border-radius:999px; background: {{ $donutBackground }}; display:grid; place-items:center; box-shadow: inset 0 0 0 14px #fff;">
                        <div style="width:108px; height:108px; border-radius:999px; background:#fff; display:grid; place-items:center; text-align:center; box-shadow: 0 0 0 1px #e2e8f0;">
                            <div>
                                <div style="font-size:20px; font-weight:800; color:#09253e;">{{ count($topProductsDonutList) }}</div>
                                <div class="cell-muted" style="font-size:11px;">{{ __('admin.dashboard.labels.name') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="metric-list">
                    @forelse ($topProductsDonutList as $index => $item)
                        @php
                            $percent = round((($item->total_sold ?? 0) / $donutTotal) * 100, 1);
                        @endphp
                        <div class="metric-row">
                            <span style="display:flex; align-items:center; gap:9px; font-weight:500;">
                                <span style="width:10px; height:10px; border-radius:3px; background: {{ $donutColors[$index % count($donutColors)] }};"></span>
                                {{ $item->product_name ?? '-' }}
                            </span>
                            <strong>{{ $percent }}%</strong>
                        </div>
                    @empty
                        <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card">
            <div class="panel-heading">
                <div>
                    <h2>{{ __('admin.dashboard.order_status') }}</h2>
                    <p>{{ __('admin.dashboard.labels.status') }}</p>
                </div>
            </div>
            <div class="metric-list tabular-nums">
                @forelse ($orderDistribution as $status => $total)
                    <div class="metric-row">
                        <span class="badge badge-{{ $status ?: 'secondary' }}">{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top:16px;">
        <div class="card">
            <h2 class="section-title" style="margin-top:0;">{{ __('admin.dashboard.latest_orders') }}</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('admin.dashboard.labels.id') }}</th>
                            <th>{{ __('admin.dashboard.labels.code') }}</th>
                            <th>{{ __('admin.dashboard.labels.status') }}</th>
                            <th>{{ __('admin.dashboard.labels.total') }}</th>
                            <th>{{ __('admin.dashboard.labels.created_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestOrders as $order)
                            @php
                                $status = $order->status instanceof \BackedEnum ? $order->status->value : $order->status;
                            @endphp
                            <tr>
                                <td class="cell-muted">#{{ $order->id }}</td>
                                <td style="font-weight:600;">{{ $order->number ?: '-' }}</td>
                                <td><span class="badge badge-{{ $status ?: 'secondary' }}">{{ $status ?: '-' }}</span></td>
                                <td style="font-weight:600;">{{ number_format($order->total) }} {{ $order->currency }}</td>
                                <td class="cell-muted">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">{{ __('admin.messages.empty_state') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2 class="section-title" style="margin-top:0;">{{ __('admin.dashboard.order_status') }}</h2>
            <div class="metric-list tabular-nums">
                @forelse ($orderStatusCounts as $status => $total)
                    <div class="metric-row">
                        <span class="badge badge-{{ $status ?: 'secondary' }}">{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
