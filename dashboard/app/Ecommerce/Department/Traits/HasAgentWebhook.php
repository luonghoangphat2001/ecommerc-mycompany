<?php

namespace App\Ecommerce\Department\Traits;

use App\Models\DepartmentAgent;
use Illuminate\Support\Facades\Http;

trait HasAgentWebhook
{
    /**
     * Dispatch an event payload to an agent via webhook.
     *
     * @param DepartmentAgent $agent
     * @param string $url
     * @param array $payload
     * @return \Illuminate\Http\Client\Response
     */
    public function dispatchToAgent(DepartmentAgent $agent, string $url, array $payload)
    {
        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, $agent->webhook_secret);

        return Http::withHeaders([
            'X-SSOT-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
    }
}
