import type { LucideIcon } from 'lucide-react';

/** Variants d'alerte (maquette : .alert.info / .success / .error). */
export type AlertVariant = 'info' | 'success' | 'error';

/** Score de force du mot de passe, 0 (vide) à 4 (excellent). */
export type PasswordScore = 0 | 1 | 2 | 3 | 4;

/** Avantage mis en avant dans le panneau de marque du AuthLayout. */
export type Perk = {
    icon: LucideIcon;
    title: string;
    description: string;
    /** Couleur d'accent (maquette : --tool), ex. '#a855f7'. */
    color: string;
};

/** Carte flottante décorative du panneau de marque. */
export type FloatCard = {
    icon: LucideIcon;
    label: string;
    color: string;
    /** Position (maquette : .fc-1 / .fc-2). */
    position: 'fc-1' | 'fc-2';
};
