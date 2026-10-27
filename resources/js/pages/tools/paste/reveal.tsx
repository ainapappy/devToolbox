import { Head } from '@inertiajs/react';
import { Copy, Eye } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { useClipboard } from '@/hooks/use-clipboard';
import type { PasteRevealProps } from '@/types';
import { Lock } from 'lucide-react';

export default function Reveal({ content }: PasteRevealProps) {
    const [copied, copy] = useClipboard();

    return (
        <>
            <Head title="Secret révélé" />

            <ToolHero
                step="01"
                title="Voici votre secret"
                description="Ne le partagez plus une fois copié — il a peut-être déjà été détruit."
                icon={Lock}
                color="#a855f7"
            />

            <section className="tool-card mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="mb-4 flex items-center justify-between gap-4">
                    <span className="inline-flex items-center gap-2 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        <Eye className="size-4" />
                        Secret
                    </span>
                    <button
                        type="button"
                        onClick={() => copy(content)}
                        className="btn-ghost"
                        aria-label="Copier le secret"
                    >
                        <Copy className="size-4" />
                        {copied === content ? '✓ Copié !' : 'Copier'}
                    </button>
                </div>
                <pre className="overflow-x-auto rounded-xl border border-border bg-background p-4 font-mono text-sm whitespace-pre-wrap text-foreground">
                    {content}
                </pre>
            </section>
        </>
    );
}
