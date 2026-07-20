<?php

namespace App\Ecommerce\Workspace\Repositories;

interface CfoRepositoryInterface
{
    public function getMetrics(string $period = 'all'): array;
    public function getProposals(string $period = 'all');
    public function getPayrolls(string $period = 'all');
    public function getMaterialPrices(string $period = 'all');
    public function createProposal(array $data);
    public function updateProposal($id, array $data);
    public function deleteProposal($id);
    public function createPayroll(array $data);
    public function updatePayroll($id, array $data);
    public function deletePayroll($id);
}
