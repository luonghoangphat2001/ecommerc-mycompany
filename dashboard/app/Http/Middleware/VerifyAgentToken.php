<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DepartmentAgent;
use App\Ecommerce\Department\Enums\AgentStatus;
use Illuminate\Support\Facades\Hash;
use App\Ecommerce\Department\Enums\RiskLevelThreshold;
use Symfony\Component\HttpFoundation\Response;

class VerifyAgentToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'API_REQUEST'): Response
    {
        $token = $request->bearerToken();
        $agentCode = $request->header('X-Agent-Code');

        if (!$token || !$agentCode) {
            return response()->json(['error' => 'Unauthorized. Missing token or agent code.'], 401);
        }

        $agent = DepartmentAgent::where('agent_code', $agentCode)
            ->where('status', AgentStatus::ACTIVE)
            ->first();

        if (!$agent || !Hash::check($token, $agent->api_token_hash)) {
            return response()->json(['error' => 'Unauthorized. Invalid credentials.'], 401);
        }

        if (!$agent->department || !$agent->department->is_active) {
            return response()->json(['error' => 'Forbidden. Department is inactive.'], 403);
        }

        $agent->update(['last_active_at' => now()]);
        
        $request->attributes->set('agent', $agent);
        
        // Log action and check risk level
        $department = $agent->department;
        $risk = $department->risk_level_threshold;
        
        $isHighRisk = in_array($risk, [RiskLevelThreshold::HIGH, RiskLevelThreshold::CRITICAL]);
        $status = $isHighRisk ? 'pending_approval' : 'success';
        
        $eventId = $request->header('X-Event-Id', uniqid('evt_'));

        \App\Models\WebhookLog::create([
            'department_id' => $department->id,
            'department_agent_id' => $agent->id,
            'event_id' => $eventId,
            'action' => $action,
            'event' => 'inbound_agent_request',
            'payload' => $request->all(),
            'status' => $status,
        ]);

        if ($isHighRisk) {
            return response()->json([
                'message' => 'Action marked as pending approval due to high risk threshold.',
                'event_id' => $eventId,
                'status' => 'pending_approval'
            ], 202);
        }

        return $next($request);
    }
}
