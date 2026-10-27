import { Form, Head, Link } from '@inertiajs/react';
import { Lock, Mail, UserRound } from 'lucide-react';
import { useState } from 'react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { CheckboxField } from '@/components/auth/checkbox-field';
import { InputGroup } from '@/components/auth/input-group';
import { PasswordInput } from '@/components/auth/password-input';
import { SocialRow } from '@/components/auth/social-buttons';
import { StrengthMeter } from '@/components/auth/strength-meter';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    passwordRules?: string;
};

export default function Register({ passwordRules }: Props) {
    const [password, setPassword] = useState('');
    const [termsAccepted, setTermsAccepted] = useState(false);

    const firstError = (errors: Record<string, string>): string | undefined =>
        Object.values(errors)[0];

    return (
        <>
            <Head title="Créer un compte" />

            <AuthCard>
                <AuthHeader
                    icon={Lock}
                    title="Créer un compte"
                    subtitle="Quelques secondes suffisent pour accéder à la suite."
                />

                <Form {...store.form()} className="grid gap-4">
                    {({ processing, errors }) => (
                        <>
                            {firstError(errors) && (
                                <Alert variant="error">
                                    {firstError(errors)}
                                </Alert>
                            )}

                            <div className="grid gap-1.5">
                                <label
                                    htmlFor="name"
                                    className="text-[0.8rem] font-semibold"
                                >
                                    Nom
                                </label>
                                <InputGroup
                                    icon={UserRound}
                                    id="name"
                                    type="text"
                                    name="name"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    placeholder="Votre nom"
                                />
                            </div>

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
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    placeholder="vous@exemple.com"
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
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    placeholder="••••••••"
                                    value={password}
                                    onChange={(e) =>
                                        setPassword(e.target.value)
                                    }
                                />
                                <StrengthMeter password={password} />
                                {passwordRules && (
                                    <p className="text-xs text-muted-foreground">
                                        8 caractères minimum, avec majuscule,
                                        minuscule, chiffre et symbole.
                                    </p>
                                )}
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
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    placeholder="••••••••"
                                />
                            </div>

                            <CheckboxField
                                id="terms"
                                name="terms"
                                tabIndex={5}
                                checked={termsAccepted}
                                onCheckedChange={(checked) =>
                                    setTermsAccepted(checked === true)
                                }
                                label="J'accepte les conditions d'utilisation de DevToolbox."
                            />

                            <button
                                type="submit"
                                tabIndex={6}
                                disabled={processing || !termsAccepted}
                                data-test="register-button"
                                className="btn-gradient mt-1 h-11 w-full rounded-xl"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="size-4" />
                                        Création…
                                    </>
                                ) : (
                                    'Créer mon compte'
                                )}
                            </button>

                            <SocialRow />
                        </>
                    )}
                </Form>

                <p className="mt-5 text-center text-sm text-muted-foreground">
                    Déjà un compte&nbsp;?{' '}
                    <Link
                        href={login()}
                        tabIndex={6}
                        className="font-semibold text-accent-1 hover:underline"
                    >
                        Se connecter
                    </Link>
                </p>
            </AuthCard>
        </>
    );
}
