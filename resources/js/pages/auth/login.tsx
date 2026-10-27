import { Form, Head, Link } from '@inertiajs/react';
import { Lock, Mail } from 'lucide-react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { CheckboxField } from '@/components/auth/checkbox-field';
import { Divider } from '@/components/auth/divider';
import { InputGroup } from '@/components/auth/input-group';
import { PasswordInput } from '@/components/auth/password-input';
import { SocialRow } from '@/components/auth/social-buttons';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import PasskeyVerify from '@/components/passkey-verify';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Connexion" />

            <AuthCard>
                <AuthHeader
                    icon={Lock}
                    title="Bon retour 👋"
                    subtitle="Connectez-vous pour accéder à vos outils."
                />

                {status && (
                    <Alert variant="success" className="mb-4">
                        {status}
                    </Alert>
                )}

                <PasskeyVerify
                    label="Se connecter avec une passkey"
                    separator="ou"
                />

                <Form
                    {...store.form()}
                    resetOnSuccess={['password']}
                    className="grid gap-4"
                >
                    {({ processing, errors }) => (
                        <>
                            {errors.email && (
                                <Alert variant="error">{errors.email}</Alert>
                            )}
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
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="vous@exemple.com"
                                />
                            </div>

                            <div className="grid gap-1.5">
                                <div className="flex items-center justify-between gap-3">
                                    <label
                                        htmlFor="password"
                                        className="text-[0.8rem] font-semibold"
                                    >
                                        Mot de passe
                                    </label>
                                    {canResetPassword && (
                                        <Link
                                            href={request()}
                                            tabIndex={5}
                                            className="text-[0.82rem] font-semibold text-accent-1 hover:underline"
                                        >
                                            Mot de passe oublié&nbsp;?
                                        </Link>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder="••••••••"
                                />
                            </div>

                            <CheckboxField
                                id="remember"
                                name="remember"
                                tabIndex={3}
                                label="Se souvenir de moi pendant 30 jours"
                            />

                            <button
                                type="submit"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                                className="btn-gradient mt-1 h-11 w-full rounded-xl"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="size-4" />
                                        Connexion…
                                    </>
                                ) : (
                                    'Se connecter'
                                )}
                            </button>

                            <Divider />
                            <SocialRow />
                        </>
                    )}
                </Form>

                <p className="mt-5 text-center text-sm text-muted-foreground">
                    Pas encore de compte&nbsp;?{' '}
                    <Link
                        href={register()}
                        tabIndex={5}
                        className="font-semibold text-accent-1 hover:underline"
                    >
                        Créer un compte
                    </Link>
                </p>
            </AuthCard>
        </>
    );
}
