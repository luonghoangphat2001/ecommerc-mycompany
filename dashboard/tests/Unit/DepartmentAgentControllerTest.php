<?php

namespace Tests\Unit;

use App\Ecommerce\Department\Services\DepartmentAgentService;
use App\Http\Controllers\Admin\Crud\BaseCrudController;
use App\Http\Controllers\Admin\DepartmentAgentController;
use App\Models\Department;
use App\Models\DepartmentAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentAgentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->department = Department::firstOrCreate(
            ['code' => 'rnd_test'],
            [
                'name' => 'R&D Test',
                'description' => 'Test department',
                'is_active' => true,
                'risk_level_threshold' => 'low',
            ]
        );
    }

    public function test_controller_inheritance_and_methods(): void
    {
        $controller = new DepartmentAgentController();
        $this->assertInstanceOf(BaseCrudController::class, $controller);
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'create'));
        $this->assertTrue(method_exists($controller, 'store'));
        $this->assertTrue(method_exists($controller, 'show'));
        $this->assertTrue(method_exists($controller, 'edit'));
        $this->assertTrue(method_exists($controller, 'update'));
        $this->assertTrue(method_exists($controller, 'destroy'));
        $this->assertTrue(method_exists($controller, 'storeForDepartment'));
        $this->assertTrue(method_exists($controller, 'destroyForDepartment'));
        $this->assertTrue(method_exists($controller, 'regenerateTokens'));
    }

    public function test_admin_can_access_department_agents_index(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-agents.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.crud.index');
    }

    public function test_admin_can_access_department_agents_create(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-agents.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.crud.form');
    }

    public function test_admin_can_store_department_agent_via_crud(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.department-agents.store'), [
                'department_id' => $this->department->id,
                'agent_code' => 'agent_unit_test_' . uniqid(),
                'name' => 'Test Agent Unit',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.department-agents.index'));
        $this->assertDatabaseHas('department_agents', [
            'name' => 'Test Agent Unit',
            'department_id' => $this->department->id,
        ]);
    }

    public function test_admin_can_view_department_agent_show_and_edit(): void
    {
        $agent = DepartmentAgent::create([
            'department_id' => $this->department->id,
            'agent_code' => 'agent_show_test_' . uniqid(),
            'name' => 'Test Show Agent',
            'status' => 'active',
        ]);

        $showResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-agents.show', $agent->id));
        $showResponse->assertStatus(200);

        $editResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.department-agents.edit', $agent->id));
        $editResponse->assertStatus(200);
    }

    public function test_admin_can_regenerate_tokens(): void
    {
        $agent = DepartmentAgent::create([
            'department_id' => $this->department->id,
            'agent_code' => 'agent_regen_test_' . uniqid(),
            'name' => 'Test Regen Agent',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.departments.agents.regenerate', [$this->department, $agent]));

        $response->assertRedirect(route('admin.departments.show', $this->department));
        $response->assertSessionHas('new_api_token');
        $response->assertSessionHas('new_webhook_secret');
    }
}
