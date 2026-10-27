import { Eye, EyeOff, KeyRound } from 'lucide-react';
import { useState } from 'react';
import { InputGroup } from '@/components/auth/input-group';
import type { ComponentProps } from 'react';

type Props = Omit<ComponentProps<'input'>, 'type'>;

/**
 * Champ mot de passe avec icône clé et toggle œil (maquette : .input-group.has-toggle).
 * `aria-label` dynamique selon l'état d'affichage.
 */
export function PasswordInput({ className, ...props }: Props) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative">
            <InputGroup
                icon={KeyRound}
                hasToggle
                type={visible ? 'text' : 'password'}
                className={className}
                {...props}
            />
            <button
                type="button"
                tabIndex={-1}
                onClick={() => setVisible((prev) => !prev)}
                aria-label={
                    visible
                        ? 'Masquer le mot de passe'
                        : 'Afficher le mot de passe'
                }
                className="absolute top-1/2 right-1.5 grid -translate-y-1/2 place-items-center rounded-md p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
            >
                {visible ? (
                    <EyeOff className="size-4" aria-hidden />
                ) : (
                    <Eye className="size-4" aria-hidden />
                )}
            </button>
        </div>
    );
}
