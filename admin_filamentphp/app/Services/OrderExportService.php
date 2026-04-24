<?php

namespace App\Services;

use App\Contracts\Services\OrderExportServiceInterface;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderExportService implements OrderExportServiceInterface
{
    /**
     * @inheritDoc
     */
    public function exportInvoice(Order $order)
    {
        $pdf = Pdf::loadView('pdf.invoice', compact('order'));
        
        return $pdf->download('invoice-' . $order->number . '.pdf');
    }

    /**
     * @inheritDoc
     */
    public function exportDeliveryNote(Order $order)
    {
        $pdf = Pdf::loadView('pdf.delivery-note', compact('order'));
        
        return $pdf->download('delivery-note-' . $order->number . '.pdf');
    }
}
