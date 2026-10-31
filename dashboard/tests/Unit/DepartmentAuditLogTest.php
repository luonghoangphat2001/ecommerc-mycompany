<?php

namespace Tests\Unit;

use App\Enums\WebhookLogStatus;
use App\Http\Controllers\Admin\DepartmentAuditLogController;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Models\DepartmentAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentAuditLogTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected Department $department;
    protected DepartmentAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->department = Department::firstOrCreate(
            ['code' => 'audit_test_dept'],
            [
                'name' => 'Audit Test Dept',
                'description' => 'Test department for audit',
                'is_active' => true,
                'risk_level_threshold' => 'low',
            ]
        );

        $this->agent = DepartmentAgent::create([
            'department_id' => $this->department->id,
            'agent_code' => 'audit_agent_' . uniqid(),
            'name' => 'Audit Agent',
            'status' => 'active',
        ]);
    }

    public function test_department_audit_log_model_and_relations(): void
    {
        $log = DepartmentAuditLog::create([
            'department_id' => $this->department->id,
            'department_agent_id' => $this->agent->id,
            'event_id' => 'evt_' . uniqid(),
            'action' => 'TEST_ACTION',
            'event' => 'test_event',
            'payload' => ['foo' => 'bar'],
            'status' => WebhookLogStatus::Success->value,
        ]);

        $this->assertNotNull($log->id);
        $this->assertEquals($this->department->id, $log->department->id);
        $this->assertEquals($this->agent->id, $log->agent->id);
        $this->assertTrue($this->department->auditLogs->contains($log));
        $this->assertTrue($this->agent->auditLogs->contains($log));
    }

    public function test_admin_can_access_department_audit_logs_index(): void
    {
        DepartmentAuditLog::create([
            'department_id' => $this->department->id,
            'department_agent_id' => $this->agent->id,
            'event_id' => 'evt_' . uniqid(),
            'action' => 'TEST_ACTION',
            'event' => 'test_event',
            'payload' => ['foo' => 'bar'],
            'status' => WebhookLogStatus::Success->value,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-audit-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.crud.index');
        $response->assertSee('TEST_ACTION');
    }

    public function test_admin_can_access_department_audit_logs_show(): void
    {
        $log = DepartmentAuditLog::create([
            'department_id' => $this->department->id,
            'department_agent_id' => $this->agent->id,
            'event_id' => 'evt_' . uniqid(),
            'action' => 'VIEW_ACTION',
            'event' => 'view_event',
            'payload' => ['key' => 'secret_value'],
            'status' => WebhookLogStatus::Success->value,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-audit-logs.show', $log->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.crud.show');
        $response->assertSee('VIEW_ACTION');
    }
}
