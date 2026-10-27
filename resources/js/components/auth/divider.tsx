import { cn } from '@/lib/utils';

type Props = {
    children?: string;
    className?: string;
};

/** Séparateur horizontal avec texte centré (maquette : .divider). */
export function Divider({ children = 'ou continuer avec', className }: Props) {
    return (
        <div
            className={cn(
                'flex items-center gap-3 text-[0.72rem] font-semibold tracking-[0.1em] text-muted-foreground uppercase before:h-px before:flex-1 before:bg-border after:h-px after:flex-1 after:bg-border',
                className,
            )}
        >
            {children}
        </div>
    );
}
