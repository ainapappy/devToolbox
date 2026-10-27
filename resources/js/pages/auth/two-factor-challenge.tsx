import { Form, Head, Link } from '@inertiajs/react';
import { ArrowRight, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { CheckboxField } from '@/components/auth/checkbox-field';
import { OtpInput } from '@/components/auth/otp-input';
import { Spinner } from '@/components/ui/spinner';
import { Input } from '@/components/ui/input';
import { login } from '@/routes';
import { store } from '@/routes/two-factor/login';

/**
 * Défi 2FA (maquette : two-factor.html) — code TOTP à 6 chiffres ou code de
 * récupération (consommé une seule fois), appareil de confiance 30 jours.
 */
export default function TwoFactorChallenge() {
    const [code, setCode] = useState('');
    const [recoveryCode, setRecoveryCode] = useState('');
    const [usingRecovery, setUsingRecovery] = useState(false);

    return (
        <>
            <Head title="Authentification à deux facteurs" />

            <AuthCard>
                <AuthHeader
                    icon={ShieldCheck}
                    title="Authentification à deux facteurs"
                    subtitle={
                        usingRecovery
                            ? "Saisissez un de vos codes de récupération d'urgence."
                            : "Saisissez le code à 6 chiffres de votre application d'authentification."
                    }
                />

                <Form
                    {...store.form()}
                    className="grid gap-4"
                    resetOnError
                    resetOnSuccess={!usingRecovery}
                >
                    {({ processing, errors, clearErrors }) => (
                        <>
                            {errors.code && (
                                <Alert variant="error">{errors.code}</Alert>
                            )}
                            {errors.recovery_code && (
                                <Alert variant="error">
                                    {errors.recovery_code}
                                </Alert>
                            )}

                            {usingRecovery ? (
                                <div className="grid gap-1.5">
                                    <label
                                        htmlFor="recovery_code"
                                        className="text-[0.8rem] font-semibold"
                                    >
                                        Code de récupération
                                    </label>
                                    <Input
                                        id="recovery_code"
                                        name="recovery_code"
                                        type="text"
                                        required
                                        autoFocus
                                        autoComplete="off"
                                        placeholder="xxxxx-xxxxx"
                                        className="h-11 rounded-xl bg-background text-center font-mono text-sm uppercase"
                                        value={recoveryCode}
                                        onChange={(e) =>
                                            setRecoveryCode(e.target.value)
                                        }
                                    />
                                </div>
                            ) : (
                                <div className="grid gap-1.5">
                                    <span className="text-[0.8rem] font-semibold">
                                        Code de vérification
                                    </span>
                                    <OtpInput
                                        value={code}
                                        onChange={setCode}
                                        disabled={processing}
                                    />
                                </div>
                            )}

                            <CheckboxField
                                id="trust"
                                name="trust"
                                label="Se souvenir de cet appareil pendant 30 jours"
                            />

                            <button
                                type="submit"
                                disabled={processing}
                                className="btn-gradient h-11 w-full rounded-xl"
                            >
                                {processing ? (
                                    <>
                                        <Spinner className="size-4" />
                                        Vérification…
                                    </>
                                ) : (
                                    <>
                                        Vérifier le code
                                        <ArrowRight
                                            className="size-4"
                                            aria-hidden
                                        />
                                    </>
                                )}
                            </button>

                            <button
                                type="button"
                                onClick={() => {
                                    setUsingRecovery((prev) => !prev);
                                    clearErrors();
                                    setCode('');
                                    setRecoveryCode('');
                                }}
                                className="btn-ghost w-full justify-center"
                            >
                                {usingRecovery
                                    ? "Utiliser un code d'authentification"
                                    : 'Utiliser un code de récupération'}
                            </button>

                            <p className="text-center text-sm text-muted-foreground">
                                Un problème&nbsp;?{' '}
                                <Link
                                    href={login()}
                                    className="font-semibold text-accent-1 hover:underline"
                                >
                                    Retour à la connexion
                                </Link>
                            </p>
                        </>
                    )}
                </Form>
            </AuthCard>
        </>
    );
}
