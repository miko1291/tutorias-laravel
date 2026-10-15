<?php

namespace App\Http\Controllers;

use App\Mail\PasswordRecoveryMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordRecoveryController extends Controller
{
    public function requestForm(): View
    {
        return view('site.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'string', 'email', 'max:255']], [
            'email.required' => 'Escribe el correo registrado en tu cuenta.',
            'email.email' => 'Escribe una dirección de correo válida.',
        ]);

        if (config('mail.default') === 'log') {
            return back()->withErrors(['email' => 'La recuperación por correo todavía no está disponible. Solicita apoyo a la coordinación.'])->withInput($request->only('email'));
        }

        try {
            Password::sendResetLink(['email' => $data['email'], 'status' => User::STATUS_ACTIVE], function (User $user, string $token): void {
                $url = rtrim(config('app.url'), '/').route('password.reset', ['token' => $token, 'email' => $user->email], false);
                Mail::to($user->email)->send(new PasswordRecoveryMail($user->username, $url, (int) config('auth.passwords.users.expire')));
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors(['email' => 'No pudimos enviar el correo en este momento. Intenta nuevamente más tarde.'])->withInput($request->only('email'));
        }

        return back()->with('success', 'Si el correo corresponde a una cuenta activa, recibirás un enlace para crear una nueva contraseña. Revisa también la carpeta de spam.');
    }

    public function resetForm(Request $request, string $token): View
    {
        $email = $request->query('email', '');

        return view('site.reset-password', ['token' => $token, 'email' => is_string($email) ? $email : '']);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', function (string $attribute, mixed $value, \Closure $fail): void {
                if (strlen($value) > 72) {
                    $fail('La contraseña es demasiado larga.');
                }
            }],
        ], [
            'password.required' => 'Escribe tu nueva contraseña.',
            'password.min' => 'Usa al menos 8 caracteres.',
            'password.max' => 'La contraseña es demasiado larga.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'email.email' => 'Escribe una dirección de correo válida.',
            'token.required' => 'Solicita un nuevo enlace de recuperación.',
        ]);

        $status = Password::reset($data + ['status' => User::STATUS_ACTIVE], function (User $user, string $password): void {
            $user->forceFill(['password_hash' => Hash::make($password), 'auth_key' => Str::random(32), 'password_reset_token' => null])->save();
            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Tu contraseña se actualizó. Ingresa con tu usuario y tu nueva contraseña.');
        }

        return back()->withErrors(['email' => 'Este enlace no es válido o ya caducó. Solicita uno nuevo para recuperar tu acceso.'])->withInput($request->only('email'));
    }
}
