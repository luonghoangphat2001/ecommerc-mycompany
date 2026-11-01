@extends('admin.layouts.app', ['title' => __('admin.sidebar.shipping_carriers') ?: 'Hãng vận chuyển'])

@section('content')
    <x-admin.page-header
        :title="'Cấu hình Hãng vận chuyển (Carrier API)'"
        :description="'Quản lý tích hợp API thời gian thực với các hãng vận chuyển trong nước và quốc tế.'"
    />

    @if (!empty($tabs))
        <div style="margin-bottom: var(--fi-space-4, 16px);">
            <x-admin.tabs
                mode="link"
                :active="$activeTab ?? 'shipping-carriers'"
                :tabs="$tabs"
            />
        </div>
    @endif

    @if (session('status'))
        <div class="status-message" style="margin-bottom: 16px; padding: 12px 16px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px;">
        @foreach ($carriers as $id => $carrier)
            @php
                $config = $carrier->getConfig();
                $isConfigured = $carrier->isConfigured();
                $isEnabled = $carrier->isEnabled();
                $isSandbox = $carrier->isSandbox();
            @endphp
            <div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">{{ $carrier->getName() }}</h3>
                            </div>
                            <div style="display: flex; gap: 6px; margin-top: 6px;">
                                @if ($carrier->isDomestic())
                                    <span style="font-size: 11px; padding: 2px 8px; background: #e0f2fe; color: #0369a1; border-radius: 12px; font-weight: 600;">Trong nước (VN)</span>
                                @endif
                                @if ($carrier->isInternational())
                                    <span style="font-size: 11px; padding: 2px 8px; background: #fef3c7; color: #b45309; border-radius: 12px; font-weight: 600;">Quốc tế</span>
                                @endif
                                <span style="font-size: 11px; padding: 2px 8px; background: {{ $isSandbox ? '#f1f5f9; color: #475569' : '#dcfce7; color: #15803d' }}; border-radius: 12px; font-weight: 600;">
                                    {{ $isSandbox ? 'Sandbox (Test)' : 'Production (Live)' }}
                                </span>
                            </div>
                        </div>

                        <div>
                            @if ($isEnabled && $isConfigured)
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #16a34a; font-weight: 600;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span> Hoạt động
                                </span>
                            @elseif (!$isConfigured)
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #f59e0b; font-weight: 600;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span> Chờ cấu hình
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #64748b; font-weight: 600;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8;"></span> Đã tắt
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="font-size: 13px; color: #475569; background: #f8fafc; border-radius: 8px; padding: 12px; margin-bottom: 16px; border: 1px solid #f1f5f9;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span>API Token / Key:</span>
                            <span style="font-family: monospace; font-weight: 600;">
                                {{ $carrier->getMaskedToken('api_token') ?: ($carrier->getMaskedToken('client_id') ?: '<Chưa nhập>') }}
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span>Phí cộng thêm (Markup):</span>
                            <span style="font-weight: 600;">
                                {{ ($config['markup_amount'] ?? 0) > 0 ? number_format($config['markup_amount']) . '₫' : '' }}
                                {{ ($config['markup_percent'] ?? 0) > 0 ? '+' . $config['markup_percent'] . '%' : '' }}
                                {{ ($config['markup_amount'] ?? 0) == 0 && ($config['markup_percent'] ?? 0) == 0 ? '0₫' : '' }}
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Cước dự phòng khi lỗi (Fallback):</span>
                            <span style="font-weight: 600;">{{ number_format($config['fallback_rate'] ?? 35000) }}₫</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 14px;">
                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        onclick="testCarrier('{{ $id }}', '{{ $carrier->getName() }}')"
                        style="flex: 1; justify-content: center; font-size: 12px;"
                    >
                        ⚡ Test kết nối
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        onclick="openCarrierModal('{{ $id }}')"
                        style="flex: 1; justify-content: center; font-size: 12px;"
                    >
                        ⚙️ Cấu hình
                    </button>
                </div>
            </div>

            <!-- Modal Cấu hình cho Carrier {{ $id }} -->
            <div id="modal-{{ $id }}" class="admin-modal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
                <div style="background: #ffffff; border-radius: 12px; max-width: 540px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; pb: 12px;">
                        <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Cấu hình {{ $carrier->getName() }}</h3>
                        <button type="button" onclick="closeCarrierModal('{{ $id }}')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #64748b;">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.shipping-carriers.update', $id) }}">
                        @csrf
                        @method('PUT')

                        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <input type="checkbox" name="enabled" value="1" {{ $isEnabled ? 'checked' : '' }}>
                                Kích hoạt hãng này
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                <input type="checkbox" name="sandbox" value="1" {{ $isSandbox ? 'checked' : '' }}>
                                Chế độ Sandbox (Test)
                            </label>
                        </div>

                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                API Token / Secret Key
                            </label>
                            <input
                                type="text"
                                name="api_token"
                                value="{{ $carrier->getMaskedToken('api_token') }}"
                                placeholder="Nhập API Token từ nhà cung cấp"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                            >
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                    Shop ID / Client ID
                                </label>
                                <input
                                    type="text"
                                    name="shop_id"
                                    value="{{ $config['shop_id'] ?? ($config['client_id'] ?? '') }}"
                                    placeholder="Shop ID hoặc Client ID"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                                >
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                    Client Secret (nếu có)
                                </label>
                                <input
                                    type="password"
                                    name="client_secret"
                                    value="{{ $carrier->getMaskedToken('client_secret') }}"
                                    placeholder="Client Secret"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                                >
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                    Phí Markup cố định (VND)
                                </label>
                                <input
                                    type="number"
                                    name="markup_amount"
                                    value="{{ $config['markup_amount'] ?? 0 }}"
                                    min="0"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                                >
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                    Phí Markup phần trăm (%)
                                </label>
                                <input
                                    type="number"
                                    step="0.1"
                                    name="markup_percent"
                                    value="{{ $config['markup_percent'] ?? 0 }}"
                                    min="0"
                                    max="100"
                                    style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                                >
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #475569; margin-bottom: 4px;">
                                Giá cước dự phòng khi API lỗi (VND)
                            </label>
                            <input
                                type="number"
                                name="fallback_rate"
                                value="{{ $config['fallback_rate'] ?? 35000 }}"
                                min="0"
                                style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;"
                            >
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" class="btn btn-secondary" onclick="closeCarrierModal('{{ $id }}')">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Alert / Result Modal for Connection Testing -->
    <div id="testResultModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 10000; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
            <div id="testResultIcon" style="font-size: 36px; margin-bottom: 12px; text-align: center;"></div>
            <h4 id="testResultTitle" style="font-size: 18px; font-weight: 700; text-align: center; margin: 0 0 8px 0;"></h4>
            <p id="testResultMessage" style="font-size: 14px; color: #475569; text-align: center; margin: 0 0 16px 0;"></p>
            <pre id="testResultDetails" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; font-size: 11px; max-height: 150px; overflow-y: auto; color: #334155; margin-bottom: 16px;"></pre>
            <div style="text-align: center;">
                <button type="button" class="btn btn-primary" onclick="closeTestResultModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        function openCarrierModal(id) {
            const modal = document.getElementById('modal-' + id);
            if (modal) modal.style.display = 'flex';
        }

        function closeCarrierModal(id) {
            const modal = document.getElementById('modal-' + id);
            if (modal) modal.style.display = 'none';
        }

        function closeTestResultModal() {
            document.getElementById('testResultModal').style.display = 'none';
        }

        async function testCarrier(carrierId, carrierName) {
            const modal = document.getElementById('testResultModal');
            const icon = document.getElementById('testResultIcon');
            const title = document.getElementById('testResultTitle');
            const msg = document.getElementById('testResultMessage');
            const details = document.getElementById('testResultDetails');

            icon.innerText = '⏳';
            title.innerText = 'Đang kiểm tra kết nối...';
            msg.innerText = 'Đang gửi yêu cầu xác thực tới máy chủ ' + carrierName;
            details.innerText = '';
            modal.style.display = 'flex';

            try {
                const response = await fetch('{{ url("admin/shipping-carriers") }}/' + carrierId + '/test', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (data.success) {
                    icon.innerText = '✅';
                    title.innerText = 'Kết nối thành công!';
                    title.style.color = '#16a34a';
                } else {
                    icon.innerText = '⚠️';
                    title.innerText = 'Kết nối thất bại';
                    title.style.color = '#dc2626';
                }
                msg.innerText = data.message || 'Không có phản hồi chi tiết.';
                details.innerText = JSON.stringify(data.details || {}, null, 2);
            } catch (err) {
                icon.innerText = '❌';
                title.innerText = 'Lỗi hệ thống';
                title.style.color = '#dc2626';
                msg.innerText = err.message || 'Không thể gửi yêu cầu kiểm tra.';
                details.innerText = '';
            }
        }
    </script>
@endsection
