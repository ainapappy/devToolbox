<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Mails\PasswordChangedMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        // Sécurité : invalide toutes les sessions existantes de l'utilisateur,
        // y compris celles ouvertes sur d'autres appareils (Phase 11).
        DB::table('sessions')->where('user_id', $user->getKey())->delete();

        // Confirmation par email (Phase 16) — hors quota "sécurité" car
        // l'utilisateur vient de prouver la possession de sa boîte.
        Mail::to($user->email)->send(new PasswordChangedMail(
            $user,
            now()->translatedFormat('d F Y \à H:i'),
        ));
    }
}
