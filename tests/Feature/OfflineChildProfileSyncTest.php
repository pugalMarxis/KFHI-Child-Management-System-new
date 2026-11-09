<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineChildProfileSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'name' => 'Profile Sync Admin',
            'username' => 'profile_sync_admin',
            'email' => 'profile-sync-admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    public function test_offline_child_create_can_be_retried_without_creating_a_second_child(): void
    {
        $operation = $this->createOperation();

        $this->actingAs($this->admin)
            ->postJson(route('admin.child-sync.profile-sync'), ['operations' => [$operation]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted')
            ->assertJsonPath('results.0.child.child_code', $operation['child_code']);

        $this->postJson(route('admin.child-sync.profile-sync'), ['operations' => [$operation]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $this->assertSame(1, Child::where('child_code', $operation['child_code'])->count());
        $this->assertDatabaseHas('qr_codes', [
            'child_id' => Child::where('child_code', $operation['child_code'])->value('id'),
            'token_hash' => hash('sha256', $operation['child_code']),
            'status' => 'active',
        ]);
    }

    public function test_offline_child_update_rejects_a_stale_profile_version(): void
    {
        $child = Child::create($this->profileFields() + [
            'child_code' => 'KFHI-CH-EXIST-001',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $baseVersion = (int) $child->profile_version;
        $firstUpdate = $this->updateOperation($child, 'First update', $baseVersion);
        $this->actingAs($this->admin)
            ->postJson(route('admin.child-sync.profile-sync'), ['operations' => [$firstUpdate]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'accepted');

        $staleUpdate = $this->updateOperation($child, 'Stale update', $baseVersion);
        $this->postJson(route('admin.child-sync.profile-sync'), ['operations' => [$staleUpdate]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'conflict');

        $this->assertSame('First update', $child->fresh()->first_name);
        $this->assertSame(2, (int) $child->fresh()->profile_version);
    }

    public function test_enumerator_cannot_upload_child_profile_changes(): void
    {
        $enumerator = User::create([
            'name' => 'Profile Sync Enumerator',
            'username' => 'profile_sync_enumerator',
            'email' => 'profile-sync-enumerator@example.test',
            'password' => 'password',
            'role' => 'enumerator',
            'status' => 'active',
        ]);

        $this->actingAs($enumerator)
            ->postJson(route('admin.child-sync.profile-sync'), [
                'operations' => [$this->createOperation()],
            ])
            ->assertForbidden();

        $this->assertSame(0, Child::count());
    }

    private function createOperation(): array
    {
        return $this->profileFields() + [
            'operation_uuid' => (string) Str::uuid(),
            'action' => 'create',
            'child_code' => 'KFHI-CH-OFFLINE-001',
        ];
    }

    private function updateOperation(Child $child, string $name, int $baseVersion): array
    {
        return $this->profileFields(['first_name' => $name]) + [
            'operation_uuid' => (string) Str::uuid(),
            'action' => 'update',
            'child_id' => $child->id,
            'base_profile_version' => $baseVersion,
            'child_code' => $child->child_code,
        ];
    }

    private function profileFields(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Offline',
            'middle_name' => null,
            'last_name' => 'Child',
            'date_of_birth' => '2015-04-20',
            'gender' => 'female',
            'school_name' => null,
            'grade' => null,
            'district' => 'Badulla',
            'gn_division' => null,
            'address' => null,
            'notes' => null,
            'status' => 'active',
        ], $overrides);
    }
}
