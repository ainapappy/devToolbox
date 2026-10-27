import { Checkbox } from '@/components/ui/checkbox';
import type { ComponentProps } from 'react';

type Props = ComponentProps<typeof Checkbox> & {
    /** Label multi-lignes possible : rendu dans un <span> (maquette : .check > span). */
    label: React.ReactNode;
};

/**
 * Case à cocher avec label (maquette : .check).
 * Le label vit dans un <span> pour gérer l'alignement multi-lignes.
 */
export function CheckboxField({ label, ...props }: Props) {
    return (
        <label className="flex cursor-pointer items-start gap-2.5 text-[0.82rem] text-muted-foreground select-none">
            <Checkbox className="mt-0.5" {...props} />
            <span className="flex-1">{label}</span>
        </label>
    );
}
