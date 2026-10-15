<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrateLegacyDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.legacy' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]]);

        Schema::connection('legacy')->create('user', function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->string('username');
            $table->string('auth_key');
            $table->string('password_hash');
            $table->string('email');
            $table->smallInteger('status');
            $table->unsignedInteger('created_at');
            $table->unsignedInteger('updated_at');
        });
        Schema::connection('legacy')->create('auth_assignment', function (Blueprint $table): void {
            $table->string('item_name');
            $table->string('user_id');
            $table->unsignedInteger('created_at')->nullable();
        });
        DB::connection('legacy')->table('user')->insert([
            'id' => 77,
            'username' => 'legacy.user',
            'auth_key' => str_repeat('b', 32),
            'password_hash' => 'hash',
            'email' => 'legacy@example.com',
            'status' => 10,
            'created_at' => 1,
            'updated_at' => 1,
        ]);
        DB::connection('legacy')->table('auth_assignment')->insert([
            'item_name' => 'admin',
            'user_id' => '77',
            'created_at' => 1,
        ]);
    }

    public function test_migrates_legacy_rows_without_overwriting_existing_rows(): void
    {
        $this->artisan('tutorias:migrate-legacy', ['--table' => ['user', 'auth_assignment']])
            ->assertExitCode(0);

        $this->assertDatabaseHas('user', ['id' => 77, 'username' => 'legacy.user']);
        $this->assertDatabaseHas('auth_assignment', ['item_name' => 'admin', 'user_id' => '77']);

        DB::table('user')->where('id', 77)->update(['username' => 'local.user']);
        $this->artisan('tutorias:migrate-legacy', ['--table' => ['user']]);

        $this->assertDatabaseHas('user', ['id' => 77, 'username' => 'local.user']);
    }
}
