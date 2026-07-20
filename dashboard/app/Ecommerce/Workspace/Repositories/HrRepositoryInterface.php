<?php

namespace App\Ecommerce\Workspace\Repositories;

interface HrRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array;
    public function getContracts(string $period = 'all');
    public function createContract(array $data);
    public function updateContract($id, array $data);
    public function deleteContract($id);
}
