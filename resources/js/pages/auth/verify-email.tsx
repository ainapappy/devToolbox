import { Form, Head, Link } from '@inertiajs/react';
import { LogOut, Mail, MailCheck } from 'lucide-react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { Spinner } from '@/components/ui/spinner';
import { usePage } from '@inertiajs/react';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

/**
 * Vérification de l'adresse e-mail (maquette : verify-email.html).
 * La pastille e-mail affiche l'adresse réelle de l'utilisateur (props).
 */
export default function VerifyEmail({ status }: { status?: string }) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Vérifiez votre adresse e-mail" />

            <AuthCard>
                <AuthHeader
                    icon={
                        status === 'verification-link-sent' ? MailCheck : Mail
                    }
                    title="Vérifiez votre adresse e-mail"
                    subtitle="Un lien de confirmation a été envoyé à"
                />

                <p className="-mt-3 mb-5 text-center">
                    <span className="inline-flex items-center gap-2 rounded-full bg-accent-1/10 px-3 py-1.5 text-sm font-semibold text-accent-1">
                        <Mail className="size-4" aria-hidden />
                        {auth.user.email}
                    </span>
                </p>

                <div className="grid gap-4">
                    {status === 'verification-link-sent' && (
                        <Alert variant="success">
                            Un nouveau lien de vérification vient d'être envoyé.
                        </Alert>
                    )}

                    <Alert variant="info">
                        Le lien est valable <strong>60 minutes</strong>. Pensez
                        à vérifier vos spams.
                    </Alert>

                    <Form {...send.form()} className="grid gap-3">
                        {({ processing, errors }) => (
                            <>
                                {errors.email && (
                                    <Alert variant="error">
                                        {errors.email}
                                    </Alert>
                                )}

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="btn-gradient h-11 w-full rounded-xl"
                                >
                                    {processing ? (
                                        <>
                                            <Spinner className="size-4" />
                                            Renvoi…
                                        </>
                                    ) : (
                                        "Renvoyer l'e-mail de vérification"
                                    )}
                                </button>
                            </>
                        )}
                    </Form>

                    <Link
                        href={logout()}
                        as="button"
                        method="post"
                        className="inline-flex items-center justify-center gap-1.5 text-[0.8rem] text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <LogOut className="size-3.5" aria-hidden />
                        Se déconnecter
                    </Link>
                </div>
            </AuthCard>
        </>
    );
}
