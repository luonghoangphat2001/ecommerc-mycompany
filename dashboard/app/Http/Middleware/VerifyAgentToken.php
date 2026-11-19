<?php

namespace App\Http\Middleware;

use App\Ecommerce\Department\Enums\AgentStatus;
use App\Ecommerce\Department\Enums\RiskLevelThreshold;
use App\Enums\WebhookLogStatus;
use App\Models\DepartmentAgent;
use App\Models\WebhookLog;
use App\Support\Infrastructure;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VerifyAgentToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'API_REQUEST'): Response
    {
        $token = $request->bearerToken();
        $agentCode = $request->header('X-Agent-Code');

        if (! $token || ! $agentCode) {
            return $this->unauthorized('Unauthorized. Missing token or agent code.');
        }

        $agent = DepartmentAgent::with('department')
            ->where('agent_code', $agentCode)
            ->where('status', AgentStatus::ACTIVE)
            ->first();

        if (! $agent || ! Hash::check($token, $agent->api_token_hash)) {
            return $this->unauthorized('Unauthorized. Invalid credentials.');
        }

        if (! $agent->department || ! $agent->department->is_active) {
            return $this->forbidden('Forbidden. Department is inactive.');
        }

        $agent->update(['last_active_at' => now()]);

        $request->attributes->set('agent', $agent);

        // Log action and check risk level
        $department = $agent->department;
        $risk = $department->risk_level_threshold;

        $isHighRisk = in_array($risk, [RiskLevelThreshold::HIGH, RiskLevelThreshold::CRITICAL]);
        $status = $isHighRisk ? WebhookLogStatus::PendingApproval->value : WebhookLogStatus::Success->value;

        $eventId = $request->header('X-Event-Id') ?: 'evt_' . Str::uuid();

        $payload = Infrastructure::mask($request->except([
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
        ]));

        WebhookLog::create([
            'department_id' => $department->id,
            'department_agent_id' => $agent->id,
            'event_id' => $eventId,
            'action' => $action,
            'event' => 'inbound_agent_request',
            'payload' => $payload,
            'status' => $status,
        ]);

        if ($isHighRisk) {
            return response()->json([
                'message' => 'Action marked as pending approval due to high risk threshold.',
                'event_id' => $eventId,
                'status' => 'pending_approval',
            ], 202);
        }

        return $next($request);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json(['error' => $message], 401);
    }

    private function forbidden(string $message): Response
    {
        return response()->json(['error' => $message], 403);
    }
}
