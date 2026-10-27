import { Form, Head } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { PasswordInput } from '@/components/auth/password-input';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';
import {
    index as confirmOptions,
    store as confirmStore,
} from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
import PasskeyVerify from '@/components/passkey-verify';

/**
 * Confirmation de mot de passe pour les zones sensibles
 * (maquette : confirm-password.html). Le timeout est piloté par
 * auth.password_confirmation_timeout.
 */
export default function ConfirmPassword() {
    return (
        <>
            <Head title="Confirmer votre mot de passe" />

            <AuthCard>
                <AuthHeader
                    icon={ShieldCheck}
                    title="Zone sensible"
                    subtitle="Confirmez votre mot de passe pour continuer."
                />

                <PasskeyVerify
                    routes={{
                        options: confirmOptions(),
                        submit: confirmStore(),
                    }}
                    label="Confirmer avec une passkey"
                    loadingLabel="Confirmation…"
                    separator="ou"
                />

                <Form
                    {...store.form()}
                    resetOnSuccess={['password']}
                    className="grid gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            {errors.password && (
                                <Alert variant="error">{errors.password}</Alert>
                            )}

                            <div className="grid gap-1.5">
                                <label
                                    htmlFor="password"
                                    className="text-[0.8rem] font-semibold"
                                >
                                    Mot de passe
                                </label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    autoFocus
                                    autoComplete="current-password"
                                    placeholder="••••••••"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                data-test="confirm-password-button"
                                className="btn-gradient h-11 w-full rounded-xl"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="size-4" />
                                        Vérification…
                                    </>
                                ) : (
                                    'Confirmer'
                                )}
                            </button>
                        </>
                    )}
                </Form>
            </AuthCard>
        </>
    );
}
