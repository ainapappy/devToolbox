import { Form, Head, Link } from '@inertiajs/react';
import { KeyRound, Mail } from 'lucide-react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { InputGroup } from '@/components/auth/input-group';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

type Props = {
    status?: string;
};

export default function ForgotPassword({ status }: Props) {
    return (
        <>
            <Head title="Mot de passe oublié" />

            <AuthCard>
                <AuthHeader
                    icon={KeyRound}
                    title="Mot de passe oublié"
                    subtitle="Entrez votre e-mail pour recevoir un lien de réinitialisation."
                />

                {status ? (
                    <>
                        {/* Réponse volontairement neutre (anti-énumération) :
                            identique que le compte existe ou non. */}
                        <Alert variant="success">{status}</Alert>
                        <Link
                            href={login()}
                            data-test="back-to-login"
                            className="mt-4 inline-flex items-center gap-1.5 text-[0.8rem] text-muted-foreground transition-colors hover:text-foreground"
                        >
                            ← Retour à la connexion
                        </Link>
                    </>
                ) : (
                    <Form {...email.form()} className="grid gap-4">
                        {({ processing, errors }) => (
                            <>
                                {errors.email && (
                                    <Alert variant="error">
                                        {errors.email}
                                    </Alert>
                                )}

                                <Alert variant="info">
                                    Nous vous enverrons un lien de
                                    réinitialisation à cette adresse, qu'un
                                    compte existe ou non.
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
                                        required
                                        autoFocus
                                        autoComplete="email"
                                        placeholder="vous@exemple.com"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                    className="btn-gradient h-11 w-full rounded-xl"
                                >
                                    {processing ? (
                                        <>
                                            <Spinner className="size-4" />
                                            Envoi…
                                        </>
                                    ) : (
                                        'Envoyer le lien de réinitialisation'
                                    )}
                                </button>

                                <Link
                                    href={login()}
                                    className="mt-1 inline-flex items-center justify-center gap-1.5 text-[0.8rem] text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    ← Retour à la connexion
                                </Link>
                            </>
                        )}
                    </Form>
                )}
            </AuthCard>
        </>
    );
}
