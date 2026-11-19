<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Throwable;

trait HandleTransactions
{
    /**
     * Wrap logic in a database transaction.
     *
     * @return mixed
     *
     * @throws Throwable
     */
    protected function useTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}
