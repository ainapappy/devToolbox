import { store as markdownStore } from '@/actions/App/Http/Controllers/Tools/MarkdownController';
import { Head, useForm } from '@inertiajs/react';
import { Download, FileText, Link2, Share2 } from 'lucide-react';
import { useMemo } from 'react';
import { ToolHero } from '@/components/front/tool-hero';
import { useDebouncedMarkdown } from '@/hooks/use-debounced-markdown';
import { useClipboard } from '@/hooks/use-clipboard';
import { show as showRoute } from '@/routes/tools/markdown';
import type { MarkdownIndexProps } from '@/types';

const defaultMarkdown = `# README du projet

Un outil **rapide** pour rédiger
de la *documentation propre*.

## Fonctionnalités
- Rendu **temps réel**
- Partage par lien
- Export \`HTML\`

> "Simple, efficace, élégant."`;

export default function Index({ created }: MarkdownIndexProps) {
    const [copied, copy] = useClipboard();
    const { data, setData, post, processing } = useForm<{
        content: string;
    }>({
        content: defaultMarkdown,
    });
    const { html } = useDebouncedMarkdown(data.content);
    const content = data.content;

    const shareUrl = created
        ? `${window.location.origin}${showRoute(created.slug).url}`
        : null;

    const share = () => {
        post(markdownStore.url(), { preserveScroll: true });
    };

    const exportHtml = () => {
        const blob = new Blob(
            [
                '<!DOCTYPE html>\n<html lang="fr">\n<head>\n<meta charset="utf-8">\n<title>Document</title>\n</head>\n<body>\n',
                html,
                '\n</body>\n</html>',
            ],
            { type: 'text/html' },
        );
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = 'document.html';
        anchor.click();
        URL.revokeObjectURL(url);
    };

    const wordCount = useMemo(
        () => content.split(/\s+/).filter(Boolean).length,
        [content],
    );

    return (
        <>
            <Head title="Markdown Previewer" />

            <ToolHero
                step="04"
                title="Markdown Previewer"
                description="Écrivez à gauche, visualisez à droite — le rendu se met à jour en temps réel."
                icon={FileText}
                color="#e87ba4"
            />

            <section className="grid animate-reveal grid-cols-1 gap-5 [animation-delay:.15s] md:grid-cols-2">
                <div className="flex flex-col rounded-2xl border border-border bg-card p-5">
                    <div className="mb-3 flex items-center justify-between text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        <span>
                            Éditeur{' '}
                            <span className="ml-1 inline-block h-3.5 w-0.5 animate-blink bg-accent-2 align-middle" />
                        </span>
                        <span className="font-mono normal-case">
                            {wordCount} mots
                        </span>
                    </div>
                    <textarea
                        value={content}
                        onChange={(event) =>
                            setData('content', event.target.value)
                        }
                        spellCheck={false}
                        aria-label="Éditeur Markdown"
                        className="min-h-80 w-full flex-1 resize-y rounded-xl border border-input bg-background p-4 font-mono text-sm leading-relaxed text-foreground focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                    />
                    <div className="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            onClick={share}
                            disabled={processing}
                            className="btn-gradient"
                        >
                            <Share2 className="size-4" />
                            {processing ? 'Sauvegarde…' : 'Partager'}
                        </button>
                        <button
                            type="button"
                            onClick={exportHtml}
                            className="btn-ghost"
                        >
                            <Download className="size-4" />
                            Export .html
                        </button>
                    </div>
                </div>

                <div className="flex flex-col rounded-2xl border border-border bg-card p-5">
                    <div className="mb-3 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        Aperçu HTML
                    </div>
                    <div
                        className="md-preview min-h-80 flex-1"
                        dangerouslySetInnerHTML={{ __html: html }}
                    />
                </div>
            </section>

            {shareUrl && (
                <section className="tool-card mt-6 flex animate-reveal flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-card p-6 [animation-delay:.3s]">
                    <span className="inline-flex items-center gap-2 font-mono text-sm">
                        <Link2 className="size-4 text-accent-1" />
                        {shareUrl}
                    </span>
                    <button
                        type="button"
                        onClick={() => copy(shareUrl)}
                        className="btn-ghost"
                    >
                        {copied === shareUrl ? 'Copié !' : 'Copier le lien'}
                    </button>
                </section>
            )}
        </>
    );
}
