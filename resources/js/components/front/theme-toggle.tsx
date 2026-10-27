import { Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';

/**
 * Bouton de bascule sombre/clair (maquette : .theme-btn).
 */
export function ThemeToggle() {
    const { resolvedAppearance, updateAppearance } = useAppearance();

    return (
        <button
            type="button"
            aria-label="Basculer le thème"
            title="Basculer le thème"
            onClick={() =>
                updateAppearance(
                    resolvedAppearance === 'dark' ? 'light' : 'dark',
                )
            }
            className="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card text-foreground transition-transform duration-300 hover:scale-110 hover:-rotate-12"
        >
            <Sun className="hidden size-4 dark:block" />
            <Moon className="size-4 dark:hidden" />
        </button>
    );
}
