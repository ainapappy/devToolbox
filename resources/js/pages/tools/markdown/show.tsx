import { Head } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import type { MarkdownShowProps } from '@/types';

export default function Show({ markdown, html }: MarkdownShowProps) {
    return (
        <>
            <Head title="Document partagé" />

            <ToolHero
                step="04"
                title="Document partagé"
                description="Un document Markdown partagé via DevToolbox, rendu côté serveur."
                icon={FileText}
                color="#e87ba4"
            />

            <section className="tool-card grid animate-reveal grid-cols-1 gap-5 rounded-2xl border border-border bg-card p-6 [animation-delay:.15s] lg:grid-cols-2">
                <div>
                    <div className="mb-3 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        Source Markdown
                    </div>
                    <pre className="overflow-x-auto rounded-xl border border-border bg-background p-4 font-mono text-sm leading-relaxed whitespace-pre-wrap text-foreground">
                        {markdown}
                    </pre>
                </div>
                <div>
                    <div className="mb-3 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        Aperçu HTML (rendu serveur, échappé)
                    </div>
                    <div
                        className="md-preview"
                        dangerouslySetInnerHTML={{ __html: html }}
                    />
                </div>
            </section>
        </>
    );
}
