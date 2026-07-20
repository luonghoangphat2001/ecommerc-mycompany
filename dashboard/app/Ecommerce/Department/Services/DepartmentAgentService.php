<?php

namespace App\Ecommerce\Department\Services;

use App\Models\DepartmentAgent;
use Illuminate\Support\Str;

class DepartmentAgentService
{
    /**
     * Revoke and regenerate API Token and Webhook Secret for an agent.
     *
     * @param DepartmentAgent $agent
     * @return array
     */
    public function revokeAndRegenerateTokens(DepartmentAgent $agent): array
    {
        $apiToken = Str::random(40);
        $webhookSecret = Str::random(60);

        $agent->update([
            'api_token_hash' => $apiToken, // Hashed by cast
            'webhook_secret' => $webhookSecret, // Kept raw for webhook HMAC if needed, or we might need to hash it. 
            // Wait, the plan says we use it for HMAC signing, so it must be raw or we only give it once. 
            // Since it's symmetric we'll store it raw or encrypt it. We'll store it raw for simplicity.
        ]);

        return [
            'api_token' => $apiToken,
            'webhook_secret' => $webhookSecret,
        ];
    }
}
