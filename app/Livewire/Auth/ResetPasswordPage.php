<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

use function Pest\Laravel\session;

#[Title('Reset Password - LAZZAAPPEE')]
class ResetPasswordPage extends Component
{
    public $token;
    #[Url]
    public $email;
    public $password;
    public $password_confirmation;

    public function mount($token)
    {
        $this->token = $token;
    }

    public function save()
    {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function (User $user, string $password) {
                $password = $this->password;
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login');
        } else {
            $this->addError('error', 'Something went wrong. Please try again.');
        }
    }
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
