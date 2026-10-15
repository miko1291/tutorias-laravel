<?php

namespace Tests\Feature;

use App\Mail\PasswordRecoveryMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function account(int $status = User::STATUS_ACTIVE): User
    {
        return User::create(['username' => 'cuenta-prueba', 'email' => 'cuenta@example.com', 'status' => $status, 'password_hash' => Hash::make('ClaveAnterior123'), 'auth_key' => str_repeat('a', 32)]);
    }

    private function resetData(User $user, string $token): array
    {
        return ['email' => $user->email, 'token' => $token, 'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123'];
    }

    public function test_recovery_sends_a_branded_email_with_a_hashed_single_use_token(): void
    {
        Mail::fake();
        $user = $this->account();
        $this->get('/site/login')->assertOk()->assertSee('/forgot-password', false);
        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        $mail = Mail::sent(PasswordRecoveryMail::class)->sole();
        $this->assertTrue($mail->hasTo($user->email));
        $this->assertStringStartsWith(config('app.url').'/reset-password/', $mail->resetUrl);
        $token = basename(parse_url($mail->resetUrl, PHP_URL_PATH));
        $this->assertNotSame($token, DB::table('password_reset_tokens')->value('token'));
        $this->assertTrue(Password::tokenExists($user, $token));
        $html = $mail->render();
        $this->assertStringContainsString('Crear nueva contraseña', $html);
        $this->assertStringContainsString('60 minutos', $html);
        $this->assertStringContainsString('#00325f', $html);
        $this->get('/reset-password/'.$token.'?email='.$user->email)->assertOk()->assertSee('autocomplete="new-password"', false);
        $this->post('/reset-password', $this->resetData($user, $token))->assertRedirect(route('login'))->assertSessionHas('success');
        $user->refresh();
        $this->assertTrue(Hash::check('NuevaClave123', $user->password_hash));
        $this->assertFalse(Hash::check('ClaveAnterior123', $user->password_hash));
        $this->assertNotSame(str_repeat('a', 32), $user->auth_key);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->post('/reset-password', $this->resetData($user, $token))->assertSessionHasErrors('email');
        $this->post('/site/login', ['username' => $user->username, 'password' => 'NuevaClave123'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_unknown_and_inactive_accounts_get_the_same_response_without_email(): void
    {
        Mail::fake();
        $user = $this->account(User::STATUS_INACTIVE);
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        $this->post('/forgot-password', ['email' => 'missing@example.com'])->assertSessionHas('success');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_repeated_requests_are_throttled_without_invalidating_the_emailed_token(): void
    {
        Mail::fake();
        $user = $this->account();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        $stored = DB::table('password_reset_tokens')->value('token');
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');
        Mail::assertSent(PasswordRecoveryMail::class, 1);
        $this->assertSame($stored, DB::table('password_reset_tokens')->value('token'));
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/forgot-password', ['email' => $user->email]);
        }
        $response->assertStatus(429);
    }

    public function test_expired_invalid_and_disabled_account_tokens_cannot_change_passwords(): void
    {
        $user = $this->account();
        $token = Password::createToken($user);
        $this->post('/reset-password', $this->resetData($user, 'incorrecto'))->assertSessionHasErrors('email');
        $this->travel(61)->minutes();
        $this->post('/reset-password', $this->resetData($user, $token))->assertSessionHasErrors('email');
        $this->travelBack();
        $token = Password::createToken($user);
        $user->update(['status' => User::STATUS_INACTIVE]);
        $this->post('/reset-password', $this->resetData($user, $token))->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('ClaveAnterior123', $user->fresh()->password_hash));
    }

    public function test_validation_rejects_unconfirmed_short_and_overlong_passwords(): void
    {
        $user = $this->account();
        $data = $this->resetData($user, Password::createToken($user));
        $this->post('/reset-password', array_replace($data, ['password_confirmation' => 'OtraClave123']))->assertSessionHasErrors('password');
        $this->post('/reset-password', array_replace($data, ['password' => 'corta', 'password_confirmation' => 'corta']))->assertSessionHasErrors('password');
        $long = str_repeat('ñ', 40);
        $this->post('/reset-password', array_replace($data, ['password' => $long, 'password_confirmation' => $long]))->assertSessionHasErrors('password');
        $this->post('/forgot-password', ['email' => 'incorrecto'])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('ClaveAnterior123', $user->fresh()->password_hash));
    }

    public function test_unconfigured_delivery_does_not_claim_to_have_sent_a_message(): void
    {
        config(['mail.default' => 'log']);
        $user = $this->account();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasErrors('email')->assertSessionMissing('success');
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_delivery_failure_shows_a_helpful_error_without_changing_the_password(): void
    {
        $user = $this->account();
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('SMTP unavailable'));
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasErrors('email')->assertSessionMissing('success');
        $this->assertTrue(Hash::check('ClaveAnterior123', $user->fresh()->password_hash));
    }
}
