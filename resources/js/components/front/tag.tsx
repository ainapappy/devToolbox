import type { ReactNode } from 'react';

/**
 * Étiquette de technologie sur les cartes d'outils (maquette : .tag).
 */
export function Tag({ children }: { children: ReactNode }) {
    return (
        <span className="rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground">
            {children}
        </span>
    );
}
