<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentData extends Model
{
    use HasFactory;

    protected $table = 'shop_payment_data';

    protected $fillable = ['gateway', 'key', 'value'];

    protected $casts = ['value' => 'array'];

    /** @return MorphTo<Model, $this> */
    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }
}
