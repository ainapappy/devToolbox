import { Form, Head, Link } from '@inertiajs/react';
import { Lock, Mail } from 'lucide-react';
import { useState } from 'react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { InputGroup } from '@/components/auth/input-group';
import { PasswordInput } from '@/components/auth/password-input';
import { StrengthMeter } from '@/components/auth/strength-meter';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

const PASSWORD_EXPIRE_MINUTES = 60;

export default function ResetPassword({
    token,
    email: userEmail,
    passwordRules,
}: Props) {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    return (
        <>
            <Head title="Nouveau mot de passe" />

            <AuthCard>
                <AuthHeader
                    icon={Lock}
                    title="Nouveau mot de passe"
                    subtitle="Choisissez un nouveau mot de passe pour votre compte."
                />

                <Form
                    {...update.form()}
                    transform={(data) => ({ ...data, token, email: userEmail })}
                    className="grid gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            {errors.password && (
                                <Alert variant="error">{errors.password}</Alert>
                            )}

                            <Alert variant="info">
                                Ce lien expire dans {PASSWORD_EXPIRE_MINUTES}{' '}
                                minutes. Une fois le mot de passe réinitialisé,
                                vos autres sessions seront déconnectées.
                            </Alert>

                            <div className="grid gap-1.5">
                                <label
                                    htmlFor="email"
                                    className="text-[0.8rem] font-semibold"
                                >
                                    Adresse e-mail
                                </label>
                                <InputGroup
                                    icon={Mail}
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    value={userEmail}
                                    readOnly
                                    className="opacity-70"
                                />
                            </div>

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
                                    autoComplete="new-password"
                                    placeholder="••••••••"
                                    value={password}
                                    onChange={(e) =>
                                        setPassword(e.target.value)
                                    }
                                    passwordrules={passwordRules}
                                />
                                <StrengthMeter password={password} />
                            </div>

                            <div className="grid gap-1.5">
                                <label
                                    htmlFor="password_confirmation"
                                    className="text-[0.8rem] font-semibold"
                                >
                                    Confirmer le mot de passe
                                </label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    autoComplete="new-password"
                                    placeholder="••••••••"
                                    value={passwordConfirmation}
                                    onChange={(e) =>
                                        setPasswordConfirmation(e.target.value)
                                    }
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                data-test="reset-password-button"
                                className="btn-gradient h-11 w-full rounded-xl"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="size-4" />
                                        Réinitialisation…
                                    </>
                                ) : (
                                    'Réinitialiser le mot de passe'
                                )}
                            </button>

                            <Link
                                href={login()}
                                className="inline-flex items-center justify-center gap-1.5 text-[0.8rem] text-muted-foreground transition-colors hover:text-foreground"
                            >
                                ← Retour à la connexion
                            </Link>
                        </>
                    )}
                </Form>
            </AuthCard>
        </>
    );
}
