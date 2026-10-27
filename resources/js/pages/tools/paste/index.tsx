import { Head, useForm } from '@inertiajs/react';
import { Copy, Lock, Sparkles, Timer, Trash2 } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { useClipboard } from '@/hooks/use-clipboard';
import { store as pasteStore } from '@/routes/tools/paste';
import type { PasteCreateProps } from '@/types';

type Expiration = 'burn' | '1h' | '24h';

const expirations: Array<{
    value: Expiration;
    label: string;
    icon: typeof Timer;
}> = [
    { value: 'burn', label: 'Supprimer après 1 lecture', icon: Trash2 },
    { value: '1h', label: 'Expirer dans 1 h', icon: Timer },
    { value: '24h', label: 'Expirer dans 24 h', icon: Timer },
];

export default function Index({ created }: PasteCreateProps) {
    const [copied, copy] = useClipboard();
    const { data, setData, post, processing, errors } = useForm<{
        content: string;
        expiration: Expiration;
    }>({
        content: '',
        expiration: 'burn',
    });

    const shareUrl = created
        ? `${window.location.origin}/p/${created.hash}`
        : null;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post(pasteStore.url(), {
            preserveScroll: true,
            onSuccess: () => setData('content', ''),
        });
    };

    return (
        <>
            <Head title="Secure Paste" />

            <ToolHero
                step="01"
                title="Secure Paste"
                description="Partagez un secret qui s'autodétruit : un lien unique, une lecture, puis plus rien."
                icon={Lock}
                color="#a855f7"
            />

            <section className="mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <form onSubmit={submit} className="flex flex-col gap-4">
                    <textarea
                        value={data.content}
                        onChange={(event) =>
                            setData('content', event.target.value)
                        }
                        rows={7}
                        required
                        placeholder="Collez votre texte confidentiel ici…"
                        aria-label="Contenu du secret"
                        className="w-full rounded-xl border border-input bg-background p-4 font-mono text-sm text-foreground transition-colors focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                    />
                    {errors.content && (
                        <p className="text-sm text-destructive">
                            {errors.content}
                        </p>
                    )}

                    <div
                        className="flex flex-wrap gap-2"
                        role="radiogroup"
                        aria-label="Option d'expiration"
                    >
                        {expirations.map((option) => (
                            <button
                                key={option.value}
                                type="button"
                                role="radio"
                                aria-checked={data.expiration === option.value}
                                onClick={() =>
                                    setData('expiration', option.value)
                                }
                                className="option-pill"
                                style={
                                    {
                                        '--tool': '#a855f7',
                                    } as React.CSSProperties
                                }
                            >
                                <option.icon className="size-4" />
                                {option.label}
                            </button>
                        ))}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="btn-gradient disabled:opacity-60"
                    >
                        <Sparkles className="size-4" />
                        {processing
                            ? 'Génération…'
                            : 'Générer le lien sécurisé'}
                    </button>
                </form>
            </section>

            {created && shareUrl && (
                <section className="tool-card mx-auto mt-6 max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.3s]">
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <span className="inline-flex items-center gap-2 font-mono text-sm">
                            <span className="size-2 animate-pulse-dot rounded-full bg-emerald-500" />
                            {shareUrl}
                        </span>
                        <button
                            type="button"
                            onClick={() => copy(shareUrl)}
                            className="btn-ghost"
                        >
                            <Copy className="size-4" />
                            {copied === shareUrl ? '✓ Copié !' : 'Copier'}
                        </button>
                    </div>
                    <p className="mt-3 text-sm text-muted-foreground">
                        {created.burn_after_read ? (
                            <>
                                Ce lien sera{' '}
                                <strong>
                                    détruit après la première lecture
                                </strong>
                                . Il ne reste qu'une seule vue.
                            </>
                        ) : (
                            <>
                                Ce lien expirera automatiquement selon l'option
                                choisie.
                            </>
                        )}
                    </p>
                    {created.burn_after_read && (
                        <div className="mt-4 h-1.5 overflow-hidden rounded-full bg-background">
                            <span className="block h-full w-full animate-burn rounded-full bg-linear-to-r from-[#a855f7] to-accent-3" />
                        </div>
                    )}
                </section>
            )}
        </>
    );
}
