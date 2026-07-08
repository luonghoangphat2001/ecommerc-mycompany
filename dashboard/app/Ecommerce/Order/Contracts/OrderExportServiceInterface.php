<?php

namespace App\Ecommerce\Order\Contracts;

use App\Models\Order;

interface OrderExportServiceInterface
{
    /**
     * Generate an invoice PDF for the given order.
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function exportInvoice(Order $order);

    /**
     * Generate a delivery note PDF for the given order.
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function exportDeliveryNote(Order $order);
}
