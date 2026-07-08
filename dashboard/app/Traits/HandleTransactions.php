<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Throwable;

trait HandleTransactions
{
    /**
     * Wrap logic in a database transaction.
     *
     * @param callable $callback
     * @return mixed
     * @throws Throwable
     */
    protected function useTransaction(callable $callback)
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
