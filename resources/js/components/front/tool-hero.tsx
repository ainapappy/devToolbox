import type { LucideIcon } from 'lucide-react';
import type { CSSProperties, ReactNode } from 'react';
import { Kicker } from '@/components/front/kicker';

/**
 * En-tête des pages outils (maquettes : .hero de secure-paste.html etc.).
 * La couleur d'accent de l'outil est injectée via la variable CSS --tool.
 */
export function ToolHero({
    step,
    title,
    description,
    icon,
    color,
    children,
}: {
    step: string;
    title: string;
    description: string;
    icon: LucideIcon;
    color: string;
    children?: ReactNode;
}) {
    return (
        <section
            className="mb-10 animate-reveal"
            style={{ '--tool': color } as CSSProperties}
        >
            <Kicker icon={icon}>Outil {step}</Kicker>
            <h1 className="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">
                {title}
            </h1>
            <p className="mt-3 max-w-[60ch] text-muted-foreground">
                {description}
            </p>
            {children}
        </section>
    );
}
