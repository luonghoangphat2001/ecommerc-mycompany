<?php

namespace App\Ecommerce\Cart\Services;

use App\Ecommerce\Cart\Actions\CartValidationAction;
use App\Ecommerce\Cart\Contracts\CartServiceInterface;

class CartService implements CartServiceInterface
{
    protected $cartValidationAction;

    public function __construct(CartValidationAction $cartValidationAction)
    {
        $this->cartValidationAction = $cartValidationAction;
    }

    public function syncAndValidate(array $items): array
    {
        return $this->cartValidationAction->execute($items);
    }
}
