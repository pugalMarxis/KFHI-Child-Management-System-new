<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermanentChildCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_child_edit_does_not_change_the_permanent_code(): void
    {
        $admin = $this->makeUser('admin');
        $child = $this->makeChild($admin);

        $this->actingAs($admin)
            ->put(route('children.update', $child), $this->childDetails([
                'child_code' => 'CHANGED-CODE',
                'first_name' => 'Updated',
            ]))
            ->assertRedirect(route('children.show', $child));

        $this->assertSame('KFHI-CH-TEST-001', $child->fresh()->child_code);
        $this->assertSame('Updated', $child->fresh()->first_name);
    }

    public function test_child_officer_edit_does_not_change_the_permanent_code(): void
    {
        $officer = $this->makeUser('child_officer');
        $child = $this->makeChild($officer);

        $this->actingAs($officer)
            ->put(route('child_officer.children.update', $child), $this->childDetails([
                'child_code' => 'CHANGED-CODE',
                'first_name' => 'Updated',
            ]))
            ->assertRedirect(route('child_officer.children.show', $child));

        $this->assertSame('KFHI-CH-TEST-001', $child->fresh()->child_code);
        $this->assertSame('Updated', $child->fresh()->first_name);
    }

    public function test_admin_can_bulk_delete_selected_children(): void
    {
        $admin = $this->makeUser('admin');
        $first = $this->makeChild($admin);
        $second = $this->makeChild($admin, 'KFHI-CH-TEST-002');

        $this->actingAs($admin)
            ->post(route('children.bulk-delete'), ['child_ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('children', ['id' => $first->id]);
        $this->assertDatabaseMissing('children', ['id' => $second->id]);
    }

    public function test_child_officer_bulk_delete_soft_deletes_selected_children(): void
    {
        $officer = $this->makeUser('child_officer');
        $first = $this->makeChild($officer);
        $second = $this->makeChild($officer, 'KFHI-CH-TEST-002');

        $this->actingAs($officer)
            ->post(route('child_officer.children.bulk-delete'), ['child_ids' => [$first->id, $second->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted('children', ['id' => $first->id]);
        $this->assertSoftDeleted('children', ['id' => $second->id]);
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Test',
            'username' => $role . '_permanent_code_test',
            'email' => $role . '-permanent-code@example.test',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function makeChild(User $user, string $code = 'KFHI-CH-TEST-001'): Child
    {
        return Child::create([
            'child_code' => $code,
            'first_name' => 'Original',
            'last_name' => 'Child',
            'date_of_birth' => '2015-01-01',
            'gender' => 'female',
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }

    /** @return array<string, mixed> */
    private function childDetails(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Child',
            'middle_name' => '',
            'last_name' => 'Updated',
            'date_of_birth' => '2015-01-01',
            'gender' => 'female',
            'school_name' => '',
            'grade' => '',
            'address' => '',
            'district' => '',
            'gn_division' => '',
            'notes' => '',
            'status' => 'active',
        ], $overrides);
    }
}
