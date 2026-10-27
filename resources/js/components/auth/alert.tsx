import { CheckCircle2, Info, TriangleAlert } from 'lucide-react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { AlertVariant } from '@/types/auth-ui';

const ICONS: Record<AlertVariant, typeof Info> = {
    info: Info,
    success: CheckCircle2,
    error: TriangleAlert,
};

const VARIANTS: Record<AlertVariant, string> = {
    info: 'border-accent-2/30 bg-accent-2/10 text-foreground [&_svg]:text-accent-2',
    success:
        'border-emerald-500/30 bg-emerald-500/10 text-foreground [&_svg]:text-emerald-500',
    error: 'border-red-500/30 bg-red-500/10 text-foreground [&_svg]:text-red-500',
};

type Props = {
    variant?: AlertVariant;
    children: ReactNode;
    className?: string;
};

/**
 * Alerte d'état (maquette : .alert).
 * `role="alert"` pour l'annonce aux lecteurs d'écran.
 */
export function Alert({ variant = 'info', children, className }: Props) {
    const Icon = ICONS[variant];

    return (
        <div
            role="alert"
            className={cn(
                'flex items-start gap-2.5 rounded-xl border px-3.5 py-3 text-[0.82rem] leading-relaxed text-muted-foreground',
                VARIANTS[variant],
                className,
            )}
        >
            <Icon className="mt-0.5 size-4 shrink-0" aria-hidden />
            <div>{children}</div>
        </div>
    );
}
