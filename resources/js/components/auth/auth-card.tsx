import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import { home } from '@/routes';
import { cn } from '@/lib/utils';

type Props = {
    icon: LucideIcon;
    title: string;
    subtitle?: string;
    className?: string;
};

/**
 * En-tête de carte auth : badge icône + titre + sous-titre
 * (maquette : .auth-head / .auth-badge).
 */
export function AuthHeader({ icon: Icon, title, subtitle, className }: Props) {
    return (
        <header className={cn('mb-6 text-center', className)}>
            <span className="mx-auto mb-4 grid size-13 place-items-center rounded-2xl border border-accent-1/35 bg-gradient-to-br from-accent-1/20 to-accent-3/15 text-accent-1">
                <Icon className="size-6" aria-hidden />
            </span>
            <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
            {subtitle && (
                <p className="mt-1.5 text-sm text-muted-foreground">
                    {subtitle}
                </p>
            )}
        </header>
    );
}

type CardProps = {
    children: ReactNode;
    className?: string;
};

/** Carte formulaire (maquette : .auth-card) avec animation reveal. */
export function AuthCard({ children, className }: CardProps) {
    return (
        <section
            className={cn(
                'w-full max-w-[430px] animate-reveal rounded-2xl border border-border bg-card p-6 shadow-[0_24px_60px_-30px] shadow-accent-1/30 sm:p-9',
                className,
            )}
        >
            {children}
        </section>
    );
}

/** Lien « Retour à l'accueil » sous la carte (maquette : .auth-back). */
export function AuthBackLink({ className }: { className?: string }) {
    return (
        <Link
            href={home()}
            className={cn(
                'inline-flex items-center gap-1.5 text-[0.8rem] text-muted-foreground transition-colors hover:text-foreground',
                className,
            )}
        >
            <ArrowLeft className="size-4" aria-hidden />
            Retour à l'accueil
        </Link>
    );
}
