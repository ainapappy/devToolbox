import { redirect } from '@/routes/social';
import { Spinner } from '@/components/ui/spinner';
import { useState } from 'react';

type Provider = 'github' | 'google';

/**
 * Bouton social avec logo officiel (maquette : .btn-social).
 * Connexion sociale active (Phase 13) — état loading pendant la redirection.
 */
export function SocialButton({
    provider,
    label,
    loading,
    onLoading,
}: {
    provider: Provider;
    label: string;
    loading: Provider | null;
    onLoading: (provider: Provider) => void;
}) {
    const Logo = provider === 'github' ? GithubIcon : GoogleIcon;

    return (
        <a
            href={redirect(provider).url}
            onClick={() => onLoading(provider)}
            aria-busy={loading === provider}
            className="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-border bg-background text-sm font-semibold text-foreground transition-transform hover:-translate-y-0.5 hover:border-accent-1/40"
        >
            {loading === provider ? <Spinner className="size-4" /> : <Logo />}
            {label}
        </a>
    );
}

/** Logos officiels (SVG inline réservés aux boutons sociaux, cf. Phase 09 §4). */
export function GithubIcon() {
    return (
        <svg
            viewBox="0 0 16 16"
            className="size-4"
            fill="currentColor"
            aria-hidden
        >
            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z" />
        </svg>
    );
}

export function GoogleIcon() {
    return (
        <svg viewBox="0 0 18 18" className="size-4" aria-hidden>
            <path
                fill="#4285F4"
                d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92a8.78 8.78 0 0 0 2.68-6.62Z"
            />
            <path
                fill="#34A853"
                d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.32-1.58-5.03-3.71H.96v2.34A9 9 0 0 0 9 18Z"
            />
            <path
                fill="#FBBC05"
                d="M3.97 10.71a5.41 5.41 0 0 1 0-3.42V4.96H.96a9 9 0 0 0 0 8.09l3.01-2.34Z"
            />
            <path
                fill="#EA4335"
                d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58A9 9 0 0 0 .96 4.96l3.01 2.33C4.68 5.16 6.66 3.58 9 3.58Z"
            />
        </svg>
    );
}

/** Rangée des deux boutons sociaux (maquette : .social-row). */
export function SocialRow({ className }: { className?: string }) {
    const [loading, setLoading] = useState<Provider | null>(null);

    return (
        <div className={className ?? 'grid grid-cols-2 gap-3'}>
            <SocialButton
                provider="github"
                label="GitHub"
                loading={loading}
                onLoading={setLoading}
            />
            <SocialButton
                provider="google"
                label="Google"
                loading={loading}
                onLoading={setLoading}
            />
        </div>
    );
}
