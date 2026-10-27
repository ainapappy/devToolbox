import type { LucideIcon } from 'lucide-react';
import type { ComponentProps, Ref } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

type Props = ComponentProps<'input'> & {
    icon: LucideIcon;
    /** Le groupe contient un bouton à droite (toggle œil) → padding droit. */
    hasToggle?: boolean;
    ref?: Ref<HTMLInputElement>;
};

/**
 * Champ avec icône à gauche (maquette : .input-group).
 * Icônes Lucide, jamais les SVG inline des maquettes.
 */
export function InputGroup({
    icon: Icon,
    hasToggle = false,
    className,
    ...props
}: Props) {
    return (
        <div className="relative">
            <Icon
                className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                aria-hidden
            />
            <Input
                className={cn(
                    'h-11 rounded-xl bg-background pl-10 text-sm',
                    hasToggle && 'pr-11',
                    className,
                )}
                {...props}
            />
        </div>
    );
}
