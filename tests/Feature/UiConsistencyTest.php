<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_messages_are_rendered_once_by_the_layout(): void
    {
        $user = User::create([
            'username' => 'ui-test',
            'auth_key' => str_repeat('a', 32),
            'password_hash' => Hash::make('password123'),
            'email' => 'ui-test@example.com',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now()->timestamp,
            'updated_at' => now()->timestamp,
        ]);
        DB::table('auth_assignment')->insert([
            'item_name' => 'admin',
            'user_id' => (string) $user->id,
            'created_at' => now()->timestamp,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Grupo guardado correctamente.'])
            ->get('/admin/index');

        $this->assertSame(1, substr_count($response->getContent(), 'Grupo guardado correctamente.'));
    }

    public function test_identical_notifications_are_not_created_twice_in_one_day(): void
    {
        $first = Notification::createForUser(1, 'Aviso', 'Mensaje', '/site/index', 'info');
        $second = Notification::createForUser(1, 'Aviso', 'Mensaje', '/site/index', 'info');

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second?->id);
        $this->assertDatabaseCount('notification', 1);
    }
}
