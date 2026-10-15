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
    $donutColors = ['#d97706', '#f59e0b', '#fbbf24', '#f97316', '#84cc16', '#14b8a6'];
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
        <div style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <span>✓</span> {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- DAN ESR EXECUTIVE COMMAND CENTER (CHIEF OF STAFF AI AGENT PLATFORM)        -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 14px; padding: 24px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 18px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); display: grid; place-items: center; font-size: 22px; font-weight: 900; box-shadow: 0 0 15px rgba(245, 158, 11, 0.5);">
                    ⭐
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h2 style="font-size: 20px; font-weight: 800; margin: 0; letter-spacing: -0.02em; color: #ffffff;">DAN PT — EXECUTIVE SECRETARY & RESOURCE MANAGER</h2>
                        <span style="background: #10b981; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 6px; height: 6px; border-radius: 999px; background: #ffffff;"></span>
                            Swarm Active (6 Sub-Agents)
                        </span>
                    </div>
                    <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 13px;">
                        Hệ thống Trợ lý Điều hành Doanh nghiệp (Chief of Staff AI) — Giám sát 6 Phân hệ Nghiệp vụ ESR & Điều phối Tự động
                    </p>
                </div>
            </div>
            
            <!-- Quick Alert Badges -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="#exception-inbox" style="background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <span>📥 Exception Inbox:</span> <strong>{{ $pendingProposals->count() }} Chờ duyệt</strong>
                </a>
                <a href="{{ route('admin.workspace.show', 'ops') }}" style="background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); color: #fcd34d; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🚨 Sự cố mở:</span> <strong>{{ $urgentIncidents->count() }}</strong>
                </a>
                <a href="{{ route('admin.workspace.show', 'logistics') }}" style="background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <span>📦 Tồn kho cảnh báo:</span> <strong>{{ $lowStockProductsCount }} SKU</strong>
                </a>
            </div>
        </div>

        <!-- 6 Sub-Agents Swarm Grid -->
        <div style="margin-bottom: 22px;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px;">
                MẠNG LƯỚI 6 SUB-AGENTS CHUYÊN TRÁCH THEO PHÒNG BAN (MULTI-AGENT SWARM)
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                @php
                    $agentCards = [
                        ['code' => 'rnd', 'name' => 'Dan R&D', 'role' => 'Sản phẩm & Khuyến mãi', 'icon' => '🔬', 'color' => '#8b5cf6'],
                        ['code' => 'logistics', 'name' => 'Dan Logistics', 'role' => 'Chuỗi cung ứng & PO', 'icon' => '📦', 'color' => '#3b82f6'],
                        ['code' => 'cfo', 'name' => 'Dan CFO', 'role' => 'Dòng tiền & Chi phí', 'icon' => '💰', 'color' => '#10b981'],
                        ['code' => 'ops', 'name' => 'Dan Ops', 'role' => 'Vận hành & Sự cố', 'icon' => '⚙️', 'color' => '#f59e0b'],
                        ['code' => 'cskh', 'name' => 'Dan CSKH', 'role' => 'Trải nghiệm & Review', 'icon' => '💬', 'color' => '#ec4899'],
                        ['code' => 'hr', 'name' => 'Dan HR', 'role' => 'Nhân sự & Hợp đồng', 'icon' => '👥', 'color' => '#14b8a6'],
                    ];
                @endphp
                @foreach ($agentCards as $agent)
                    <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <span style="font-size: 18px;">{{ $agent['icon'] }}</span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 10px; color: #10b981; font-weight: 700; background: rgba(16, 185, 129, 0.15); padding: 2px 6px; border-radius: 999px;">
                                    <span style="width: 5px; height: 5px; border-radius: 999px; background: #10b981;"></span> Online
                                </span>
                            </div>
                            <strong style="color: #ffffff; font-size: 14px; display: block;">{{ $agent['name'] }}</strong>
                            <span style="color: #94a3b8; font-size: 11px; display: block; margin-top: 2px;">{{ $agent['role'] }}</span>
                        </div>
                        <a href="{{ route('admin.workspace.show', $agent['code']) }}" style="margin-top: 10px; font-size: 11px; color: #38bdf8; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            Mở Workspace ➔
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Quick ESR Command Shortcuts -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; background: rgba(0, 0, 0, 0.25); padding: 10px 14px; border-radius: 8px; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: #f59e0b; margin-right: 6px;">⚡ TÁC VỤ ĐIỀU HÀNH NHANH:</span>
            <a href="{{ route('admin.workspace.show', 'cfo') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #ffffff; padding: 4px 10px; font-size: 12px; border-radius: 6px; text-decoration: none;">
                📊 Bảng cân đối dòng tiền CFO
            </a>
            <a href="{{ route('admin.workspace.show', 'logistics') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #ffffff; padding: 4px 10px; font-size: 12px; border-radius: 6px; text-decoration: none;">
                📦 Quét tồn kho & Điểm đặt hàng EOQ
            </a>
            <a href="{{ route('admin.workspace.show', 'ops') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #ffffff; padding: 4px 10px; font-size: 12px; border-radius: 6px; text-decoration: none;">
                ⚙️ Kiểm tra sự cố giao vận Ops
            </a>
            <a href="{{ route('admin.workspace.show', 'hr') }}" class="btn" style="background: rgba(255,255,255,0.1); color: #ffffff; padding: 4px 10px; font-size: 12px; border-radius: 6px; text-decoration: none;">
                👥 Rà soát hợp đồng & Quỹ lương HR
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- EXCEPTION INBOX: PHÊ DUYỆT 1-CLICK L3 CHO CEO & GIÁM SÁT SỰ CỐ KHẨN CẤP     -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div id="exception-inbox" class="dashboard-grid" style="margin-bottom: 24px;">
        
        <!-- Cột 1: Hộp thư đề xuất tài chính chờ CEO 1-Click Phê duyệt -->
        <div class="card" style="border-top: 4px solid #ef4444;">
            <div class="panel-heading" style="margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <span>📥</span> Hộp Thư Bất Thường — Phê Duyệt Chi Phí (CEO 1-Click)
                    </h2>
                    <p style="color: #64748b; font-size: 12px; margin: 2px 0 0 0;">
                        Các đề xuất ngân sách phân tầng L3 (> 20M hoặc Khẩn cấp). CEO chỉ cần 1 thao tác để quyết định.
                    </p>
                </div>
                <span style="background: #fee2e2; color: #b91c1c; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 999px;">
                    {{ $pendingProposals->count() }} Chờ duyệt
                </span>
            </div>

            <div class="table-wrap">
                <table style="width: 100%; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 8px 10px; text-align: left;">Tiêu đề đề xuất</th>
                            <th style="padding: 8px 10px; text-align: left;">Phòng ban</th>
                            <th style="padding: 8px 10px; text-align: right;">Số tiền (VND)</th>
                            <th style="padding: 8px 10px; text-align: center;">Mức độ</th>
                            <th style="padding: 8px 10px; text-align: center;">Thao tác 1-Click</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingProposals as $proposal)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px;">
                                    <strong style="color: #0f172a;">{{ $proposal->title }}</strong>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                        Người gửi: {{ $proposal->user->name ?? 'N/A' }} | #{{ $proposal->id }}
                                    </div>
                                </td>
                                <td style="padding: 10px;">
                                    <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        {{ $proposal->department->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: right; font-weight: 800; color: #b91c1c;">
                                    {{ number_format($proposal->amount) }}
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    @if ($proposal->is_urgent)
                                        <span style="background: #ef4444; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">
                                            Khẩn cấp
                                        </span>
                                    @else
                                        <span style="background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px; border-radius: 4px;">
                                            Thường
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" style="background: #10b981; color: #ffffff; border: none; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;" title="Phê duyệt ngay 1 chạm">
                                                ✓ Duyệt
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.financial-proposals.quick-status', $proposal->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;" title="Từ chối đề xuất">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: #10b981;">
                                    ✓ Không có đề xuất nào tồn đọng trong Exception Inbox.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cột 2: Cảnh báo sự cố vận hành & Đơn mua hàng PO theo dõi -->
        <div class="card" style="border-top: 4px solid #f59e0b;">
            <div class="panel-heading" style="margin-bottom: 14px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <span>🚨</span> Radar Sự Cố Vận Hành & Chuỗi Cung Ứng
                    </h2>
                    <p style="color: #64748b; font-size: 12px; margin: 2px 0 0 0;">
                        Giám sát sự cố đơn hàng và tiến độ giao hàng từ các nhà cung cấp chiến lược.
                    </p>
                </div>
                <a href="{{ route('admin.workspace.show', 'ops') }}" style="font-size: 12px; color: #d97706; text-decoration: none; font-weight: 700;">
                    Xem chi tiết ➔
                </a>
            </div>

            <div style="display: grid; gap: 12px;">
                <!-- Danh sách sự cố khẩn cấp -->
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">
                        Sự cố cần giải quyết ({{ $urgentIncidents->count() }})
                    </div>
                    @forelse ($urgentIncidents as $incident)
                        <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 10px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #991b1b; font-size: 13px;">{{ $incident->type }}</strong>
                                <span style="font-size: 11px; color: #64748b; margin-left: 6px;">Đơn #{{ $incident->order_id }}</span>
                                <p style="margin: 3px 0 0 0; color: #7f1d1d; font-size: 12px;">{{ Str::limit($incident->description, 60) }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.incidents.quick-status', $incident->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="resolved">
                                <button type="submit" style="background: #ffffff; border: 1px solid #ef4444; color: #dc2626; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                                    Đã xử lý
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color: #10b981; font-size: 12px; padding: 8px 0;">✓ Không có sự cố vận hành mở.</div>
                    @endforelse
                </div>

                <!-- Đơn mua hàng PO đang giao -->
                <div style="margin-top: 6px;">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 6px;">
                        Theo dõi Đơn Mua Hàng PO Logistics
                    </div>
                    @forelse ($pendingPurchaseOrders as $po)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; margin-bottom: 6px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #0f172a; font-size: 13px;">{{ $po->po_number }}</strong>
                                <span style="font-size: 12px; color: #64748b; margin-left: 6px;">{{ $po->supplier_name }}</span>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                    Dự kiến: {{ optional($po->expected_delivery_date)->format('d/m/Y') ?? 'Chưa xác định' }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <strong style="font-size: 13px; color: #0284c7;">{{ number_format($po->total_amount) }} VND</strong>
                                <div>
                                    <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #e0f2fe; color: #0369a1; text-transform: uppercase;">
                                        {{ $po->status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="color: #64748b; font-size: 12px; padding: 8px 0;">Chưa có đơn PO nào đang xử lý.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- RADAR GIÁ VẬT TƯ CHIẾN LƯỢC (THÉP, NHỰA, CÁT, XI MĂNG, KÍNH)                 -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    @if ($materialPrices->isNotEmpty())
        <div class="card" style="margin-bottom: 24px; background: #ffffff; border-radius: 12px; padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;">📈</span>
                    <strong style="font-size: 14px; color: #0f172a;">Chỉ Số Giá Vật Tư Chiến Lược (Dan Logistics & R&D Price Radar)</strong>
                </div>
                <a href="{{ route('admin.workspace.show', 'cfo') }}" style="font-size: 12px; color: #d97706; text-decoration: none; font-weight: 600;">
                    Xem biểu đồ giá chi tiết ➔
                </a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px;">
                @foreach ($materialPrices as $price)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                        <div style="font-size: 12px; color: #64748b;">{{ $price->material_name }}</div>
                        <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin-top: 4px;">
                            {{ number_format($price->price) }} <span style="font-size: 11px; font-weight: 500; color: #94a3b8;">VND</span>
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                            Cập nhật: {{ optional($price->recorded_at)->format('d/m/Y') ?? 'Hôm nay' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="page-header" style="margin-top: 20px;">
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
            <svg viewBox="0 0 1000 260" width="100%" height="260" role="img" aria-label="{{ __('admin.dashboard.line_revenue') }}">
                <defs>
                    <linearGradient id="revenueFill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.32" />
                        <stop offset="100%" stop-color="#f59e0b" stop-opacity="0" />
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
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="#d97706" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($revenue as $index => $amount)
                        @php
                            $x = 40 + ($index * 80);
                            $y = 220 - (($amount / $revenueMax) * 180);
                        @endphp
                        <circle cx="{{ $x }}" cy="{{ $y }}" r="5" fill="#d97706" />
                        <text x="{{ $x }}" y="245" text-anchor="middle" fill="#64748b" font-size="11">{{ $months[$index] ?? ($index + 1) }}</text>
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
            <div style="display:grid; gap:10px;">
                @forelse ($topProductsList as $item)
                    @php
                        $revenuePercent = $topProductsMax > 0 ? max(6, (($item->revenue ?? 0) / $topProductsMax) * 100) : 6;
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; gap:12px; margin-bottom:6px;">
                            <strong style="font-size:13px;">{{ $item->product_name ?? __('admin.dashboard.labels.name') }}</strong>
                            <span class="cell-muted">{{ number_format($item->revenue ?? 0) }}</span>
                        </div>
                        <div style="height:12px; border-radius:999px; background:#e2e8f0; overflow:hidden;">
                            <div style="height:100%; width:{{ min(100, $revenuePercent) }}%; background:linear-gradient(90deg, #d97706, #f59e0b); border-radius:999px;"></div>
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
            <div style="display:grid; grid-template-columns: 240px minmax(0, 1fr); gap:18px; align-items:center;">
                <div style="display:grid; place-items:center;">
                    <div style="width:220px; height:220px; border-radius:999px; background: {{ $donutBackground }}; display:grid; place-items:center; box-shadow: inset 0 0 0 18px #fff;">
                        <div style="width:118px; height:118px; border-radius:999px; background:#fff; display:grid; place-items:center; text-align:center; box-shadow: 0 0 0 1px #e2e8f0;">
                            <div>
                                <div style="font-size:22px; font-weight:900;">{{ count($topProductsDonutList) }}</div>
                                <div class="cell-muted" style="font-size:12px;">{{ __('admin.dashboard.labels.name') }}</div>
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
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:12px; height:12px; border-radius:999px; background: {{ $donutColors[$index % count($donutColors)] }};"></span>
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
            <div class="metric-list">
                @forelse ($orderDistribution as $status => $total)
                    <div class="metric-row">
                        <span>{{ $status ?: 'unknown' }}</span>
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
            <h2 class="section-title">{{ __('admin.dashboard.latest_orders') }}</h2>
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
                                <td>{{ $order->number ?: '-' }}</td>
                                <td>{{ $status ?: '-' }}</td>
                                <td>{{ number_format($order->total) }} {{ $order->currency }}</td>
                                <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
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
            <h2 class="section-title">{{ __('admin.dashboard.order_status') }}</h2>
            <div class="metric-list">
                @forelse ($orderStatusCounts as $status => $total)
                    <div class="metric-row">
                        <span>{{ $status ?: 'unknown' }}</span>
                        <strong>{{ number_format($total) }}</strong>
                    </div>
                @empty
                    <div class="empty-state">{{ __('admin.messages.empty_state') }}</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
