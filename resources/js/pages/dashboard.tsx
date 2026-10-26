import { Head } from '@inertiajs/react';
import { Kicker } from '@/components/front/kicker';
import { ToolCard } from '@/components/front/tool-card';
import type { DashboardProps } from '@/types';

export default function Dashboard({ tools }: DashboardProps) {
    return (
        <>
            <Head title="Dashboard" />

            <section className="mb-12 max-w-3xl animate-reveal">
                <Kicker>Portfolio · Laravel + React + Inertia</Kicker>
                <h1 className="mt-5 text-4xl leading-[1.1] font-bold tracking-tight text-balance sm:text-5xl">
                    Tous vos outils dev,
                    <br />
                    au même endroit.
                </h1>
                <p className="mt-4 max-w-[55ch] text-muted-foreground">
                    DevToolbox est une suite de micro-services pour développeurs
                    : partage de secrets, mock d'API, raccourcisseur de liens,
                    éditeur Markdown et générateur d'images. Un monolithe
                    modulaire, une UX fluide.
                </p>
            </section>

            <section
                aria-label="Liste des outils"
                className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3"
            >
                {tools.map((tool, index) => (
                    <ToolCard key={tool.slug} tool={tool} index={index} />
                ))}
            </section>
        </>
    );
}
