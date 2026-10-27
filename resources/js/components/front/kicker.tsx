import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';

/**
 * Pastille d'accroche au-dessus des titres de section (maquette : .kicker).
 */
export function Kicker({
    children,
    icon: Icon,
}: {
    children: ReactNode;
    icon?: LucideIcon;
}) {
    return (
        <span className="inline-flex items-center gap-2 rounded-full border border-accent-1/35 px-3 py-1.5 text-xs font-bold tracking-widest text-accent-1 uppercase">
            {Icon ? <Icon className="size-3.5" /> : null}
            {children}
        </span>
    );
}
