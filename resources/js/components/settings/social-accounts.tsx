import { Link } from '@inertiajs/react';
import { Link2, Unlink } from 'lucide-react';
import Heading from '@/components/heading';
import { Alert } from '@/components/auth/alert';
import { GoogleIcon, GithubIcon } from '@/components/auth/social-buttons';
import { unlink, redirect } from '@/routes/social';
import type { SocialAccountSummary } from '@/types';

type Props = {
    socialAccounts: SocialAccountSummary[];
    status?: string;
};

const PROVIDER_LABELS: Record<string, string> = {
    github: 'GitHub',
    google: 'Google',
};

const PROVIDER_ICONS: Record<string, () => React.ReactNode> = {
    github: GithubIcon,
    google: GoogleIcon,
};

/**
 * Section « Connexions sociales » de la page sécurité (Phase 13) :
 * liaison / déliaction des providers, même design system que le reste.
 */
export function SocialAccounts({ socialAccounts, status }: Props) {
    const linked = (provider: string): boolean =>
        socialAccounts.some((account) => account.provider === provider);

    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="Connexions sociales"
                description="Connectez GitHub ou Google pour vous identifier en un clic."
            />

            {status && <Alert variant="info">{status}</Alert>}

            <div className="grid gap-3">
                {['github', 'google'].map((provider) => {
                    const Icon = PROVIDER_ICONS[provider];
                    const isLinked = linked(provider);

                    return (
                        <div
                            key={provider}
                            className="flex items-center justify-between rounded-xl border border-border p-4"
                        >
                            <span className="flex items-center gap-3 text-sm font-semibold">
                                <Icon />
                                {PROVIDER_LABELS[provider]}
                                <span className="text-muted-foreground">
                                    {isLinked &&
                                        socialAccounts.find(
                                            (account) =>
                                                account.provider === provider,
                                        )?.provider_email}
                                </span>
                            </span>

                            {isLinked ? (
                                <Link
                                    href={unlink(provider)}
                                    method="delete"
                                    as="button"
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-sm font-semibold hover:bg-muted"
                                >
                                    <Unlink className="size-4" aria-hidden />
                                    Délier
                                </Link>
                            ) : (
                                <a
                                    href={redirect(provider).url}
                                    className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-sm font-semibold text-primary-foreground"
                                >
                                    <Link2 className="size-4" aria-hidden />
                                    Connecter
                                </a>
                            )}
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
