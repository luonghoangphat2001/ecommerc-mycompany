<?php

namespace Database\Seeders\Demo;

use App\Ecommerce\Order\Contracts\OrderServiceInterface;
use App\Ecommerce\Order\Enums\OrderStatus;
use App\Enums\AddressType;
use App\Enums\OrderItemType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderCoupon;
use App\Models\OrderItem;
use App\Models\OrderRefund;
use App\Models\OrderShipping;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the order demo seeder.
     */
    public function run(): void
    {
        $products = Product::where('is_visible', true)->get();
        if ($products->isEmpty()) {
            $this->command?->error("No products found to seed orders.");
            return;
        }

        $customers = User::role('Thành viên đăng ký')->get();
        $shippingMethod = ShippingMethod::where('is_enabled', true)->first() ?? ShippingMethod::first();
        $coupons = Coupon::where('is_active', true)->get();
        $orderService = app(OrderServiceInterface::class);

        // We will seed 60 realistic orders across the past 6 months (approx 10 per month)
        $totalOrdersTarget = 60;
        $orderStatuses = [
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Completed,
            OrderStatus::Delivering,
            OrderStatus::Processing,
            OrderStatus::New,
            OrderStatus::Pending,
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
        ];

        $paymentMethods = [
            PaymentMethod::Cod,
            PaymentMethod::BankTransfer,
            PaymentMethod::CreditCard,
            PaymentMethod::BankTransfer,
            PaymentMethod::Cod,
        ];

        $guestNames = [
            ['first' => 'Văn Hùng', 'last' => 'Nguyễn', 'phone' => '0912111333', 'email' => 'guest.hung@gmail.com', 'street' => '88 Nguyễn Huệ, P. Bến Nghé, Quận 1'],
            ['first' => 'Thị Hà', 'last' => 'Trần', 'phone' => '0938222444', 'email' => 'guest.ha@yahoo.com', 'street' => '15 Thảo Điền, TP. Thủ Đức'],
            ['first' => 'Quốc Khánh', 'last' => 'Lê', 'phone' => '0909333555', 'email' => 'guest.khanh@outlook.com', 'street' => '220 Nguyễn Thị Minh Khai, Quận 3'],
            ['first' => 'Thanh Thảo', 'last' => 'Võ', 'phone' => '0977444666', 'email' => 'guest.thao@gmail.com', 'street' => '54 Nguyễn Thị Thập, Tân Phú, Quận 7'],
            ['first' => 'Minh Trí', 'last' => 'Đặng', 'phone' => '0944555777', 'email' => 'guest.tri@gmail.com', 'street' => '12 Phan Đình Phùng, Phú Nhuận'],
        ];

        for ($i = 1; $i <= $totalOrdersTarget; $i++) {
            $orderNumber = 'DEMO-ORD-' . str_pad($i, 5, '0', STR_PAD_LEFT);

            // Distribution across the last 180 days (6 months)
            $daysAgo = (int) (($totalOrdersTarget - $i) * 3); // Spans from 180 days ago to today
            $orderDate = Carbon::now()->subDays($daysAgo)->subHours(rand(1, 12))->subMinutes(rand(1, 55));

            // 75% registered customers, 25% guest customers
            $isGuest = ($i % 4 === 0) || $customers->isEmpty();
            $customer = $isGuest ? null : $customers->random();

            $order = Order::updateOrCreate(
                ['number' => $orderNumber],
                [
                    'user_id' => $customer?->id,
                    'status' => OrderStatus::New, // Will set final status after calculation
                    'currency' => 'VND',
                    'exchange_rate' => 1.0,
                    'type' => 'shop',
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'total' => 0,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]
            );

            // 1. Order Items (1 to 3 items)
            $order->items()->delete();
            $selectedProducts = $products->random(rand(1, 3));
            foreach ($selectedProducts as $prod) {
                $qty = rand(1, 3);
                $variantId = null;
                $itemName = $prod->getTranslation('name', 'vi');
                $originalPrice = $prod->price;
                $salePrice = $prod->sale_price;

                if ($prod->isVariable() && $prod->variants()->exists()) {
                    $variant = $prod->variants->random();
                    $variantId = $variant->id;
                    $originalPrice = $variant->price;
                    $salePrice = $variant->sale_price;
                    $attrSummary = collect($variant->attributes)->map(fn($v, $k) => "$k: $v")->join(', ');
                    $itemName .= " ({$attrSummary})";
                }

                $unitPrice = $salePrice ?: $originalPrice;
                $lineTotal = $unitPrice * $qty;
                $discountAmount = $salePrice ? (($originalPrice - $salePrice) * $qty) : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'shop_product_id' => $prod->id,
                    'variant_id' => $variantId,
                    'type' => OrderItemType::Product->value,
                    'name' => $itemName,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                    'original_price' => $originalPrice,
                    'sale_price' => $salePrice,
                    'discount_amount' => $discountAmount,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // 2. Order Addresses
            $order->addresses()->delete();
            if ($customer && $customer->defaultShippingAddress) {
                $addr = $customer->defaultShippingAddress;
                $fName = $addr->first_name ?: $customer->name;
                $lName = $addr->last_name ?: '';
                $phone = $addr->phone ?: $customer->phone ?: '0901234567';
                $email = $addr->email ?: $customer->email;
                $detail = $addr->address_detail ?: '123 Pasteur';
                $city = $addr->city_id ?: 'Quận 1';
            } else {
                $guestInfo = $guestNames[($i % count($guestNames))];
                $fName = $guestInfo['first'];
                $lName = $guestInfo['last'];
                $phone = $guestInfo['phone'];
                $email = $guestInfo['email'];
                $detail = $guestInfo['street'];
                $city = 'Quận 1';
            }

            foreach ([AddressType::Shipping, AddressType::Billing] as $type) {
                OrderAddress::create([
                    'order_id' => $order->id,
                    'type' => $type->value,
                    'first_name' => $fName,
                    'last_name' => $lName,
                    'phone' => $phone,
                    'email' => $email,
                    'country_code' => 'VN',
                    'state_id' => 'Thành phố Hồ Chí Minh',
                    'city_id' => $city,
                    'ward_id' => 'Phường Bến Nghé',
                    'address_detail' => $detail,
                    'postal_code' => '70000',
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // 3. Shipping method
            $order->shipping()->delete();
            $shippingCost = 35000;
            if ($shippingMethod) {
                OrderShipping::create([
                    'order_id' => $order->id,
                    'shop_shipping_method_id' => $shippingMethod->id,
                    'method' => $shippingMethod->name,
                    'amount' => $shippingCost,
                    'tax_amount' => 0,
                    'tracking_number' => 'VNPOST-' . strtoupper(Str::random(8)),
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // 4. Coupons on some orders
            $order->coupons()->delete();
            if (($i % 4 === 1) && $coupons->isNotEmpty()) {
                $coupon = $coupons->random();
                $discountAmount = $coupon->type === 'percentage'
                    ? (int) ($order->items()->sum('total') * ($coupon->amount / 100))
                    : (int) $coupon->amount;

                OrderCoupon::create([
                    'order_id' => $order->id,
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $discountAmount,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // 5. Recalculate totals via OrderService
            try {
                $orderService->recalculateTotals($order);
            } catch (\Throwable $e) {
                // Fallback manual calculation if tax rule throws
                $subtotal = (int) $order->items()->where('type', OrderItemType::Product->value)->sum('total');
                $discount = (int) $order->coupons()->sum('discount_amount');
                $order->update([
                    'subtotal' => $subtotal,
                    'total' => max(0, $subtotal + $shippingCost - $discount),
                ]);
            }

            // 6. Set Final Order Status
            $status = $orderStatuses[($i % count($orderStatuses))];
            $order->updateQuietly([
                'status' => $status,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // 7. Seed Payment
            $method = $paymentMethods[($i % count($paymentMethods))];
            $paymentStatus = match ($status) {
                OrderStatus::Completed, OrderStatus::Delivering => PaymentStatus::Paid,
                OrderStatus::Refunded => PaymentStatus::Refunded,
                OrderStatus::Cancelled => PaymentStatus::Failed,
                default => ($method === PaymentMethod::Cod ? PaymentStatus::Pending : PaymentStatus::Paid),
            };

            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'reference' => 'PAY-' . strtoupper(Str::random(10)),
                    'amount' => $order->total,
                    'currency' => 'VND',
                    'provider' => $method->value,
                    'method' => $method,
                    'status' => $paymentStatus,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]
            );

            // 8. Order Refund if refunded
            if ($status === OrderStatus::Refunded) {
                OrderRefund::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'amount' => $order->total,
                        'reason' => 'Khách hàng đổi trả chai vang bị lỗi nút bần theo chính sách hoàn tiền 100%.',
                        'metadata' => ['refund_channel' => 'bank_transfer', 'approved_by' => 'CFO Department Agent'],
                        'created_at' => $orderDate->copy()->addDays(2),
                        'updated_at' => $orderDate->copy()->addDays(2),
                    ]
                );
            }
        }

        $this->command?->info("OrderSeeder completed: {$totalOrdersTarget} realistic orders seeded with items, addresses, shippings, coupons, payments, and refunds.");
    }
}
