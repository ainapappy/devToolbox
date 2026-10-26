import { Head } from '@inertiajs/react';
import { Wrench } from 'lucide-react';

/**
 * Écran transitoire des outils en cours de développement (Phases 03→07).
 */
export default function ToolPlaceholder({
    title,
    step,
}: {
    title: string;
    step: string;
}) {
    return (
        <section className="mx-auto max-w-2xl animate-reveal text-center">
            <Head title={title} />
            <div className="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl border border-accent-1/35 bg-linear-to-br from-accent-1/20 to-transparent text-accent-1">
                <Wrench className="size-6" />
            </div>
            <p className="inline-flex items-center gap-2 rounded-full border border-accent-1/35 px-3 py-1.5 text-xs font-bold tracking-widest text-accent-1 uppercase">
                Outil {step}
            </p>
            <h1 className="mt-4 text-3xl font-bold tracking-tight sm:text-4xl">
                {title}
            </h1>
            <p className="mt-3 text-muted-foreground">
                Bientôt disponible — cet outil est en cours de construction.
            </p>
        </section>
    );
}
