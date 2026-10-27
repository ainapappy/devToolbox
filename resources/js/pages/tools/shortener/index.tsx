import { store as shortenerStore } from '@/actions/App/Http/Controllers/Tools/UrlShortenerController';
import { Head, useForm } from '@inertiajs/react';
import { ArrowRight, Check, Copy, Link2, Zap } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { useClipboard } from '@/hooks/use-clipboard';
import { stats as statsRoute } from '@/routes/tools/shortener';
import type { ShortenerIndexProps } from '@/types';

export default function Index({ created, links }: ShortenerIndexProps) {
    const [copied, copy] = useClipboard();
    const { data, setData, post, processing, errors } = useForm<{
        target_url: string;
    }>({
        target_url: '',
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post(shortenerStore.url(), { preserveScroll: true });
    };

    const shortUrl = created
        ? `${window.location.origin}/s/${created.code}`
        : null;

    return (
        <>
            <Head title="URL Shortener" />

            <ToolHero
                step="03"
                title="URL Shortener & Analytics"
                description="Raccourcissez vos liens et mesurez chaque clic : navigateurs, pays, tendance sur 7 jours."
                icon={Link2}
                color="#eb6834"
            />

            <section className="tool-card mb-8 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.1s]">
                <form onSubmit={submit} className="flex flex-wrap gap-3">
                    <input
                        type="url"
                        value={data.target_url}
                        onChange={(event) =>
                            setData('target_url', event.target.value)
                        }
                        required
                        placeholder="https://exemple.com/mon-tres-long-article-de-blog-2026"
                        aria-label="URL à raccourcir"
                        className="min-w-60 flex-1 rounded-xl border border-input bg-background px-4 py-3 text-sm text-foreground transition-colors focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                    />
                    <button
                        type="submit"
                        disabled={processing}
                        className="btn-gradient"
                    >
                        <Zap className="size-4" />
                        {processing ? 'Raccourcissement…' : 'Raccourcir'}
                    </button>
                </form>
                {errors.target_url && (
                    <p className="mt-3 text-sm text-destructive">
                        {errors.target_url}
                    </p>
                )}

                {created && shortUrl && (
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                        <span className="font-mono text-sm">{shortUrl}</span>
                        <span className="flex items-center gap-1 font-mono text-xs text-muted-foreground">
                            <ArrowRight className="size-3.5" />
                            {created.target_url.length > 40
                                ? `${created.target_url.slice(0, 40)}…`
                                : created.target_url}
                        </span>
                        <button
                            type="button"
                            onClick={() => copy(shortUrl)}
                            className="btn-ghost"
                        >
                            {copied === shortUrl ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}
                            {copied === shortUrl ? 'Copié !' : 'Copier'}
                        </button>
                    </div>
                )}
            </section>

            {links.length > 0 && (
                <section className="tool-card mb-8 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.2s]">
                    <h2 className="mb-2 flex items-center gap-2 text-sm font-semibold uppercase tracking-wide text-muted-foreground">
                        <Link2 className="size-4" />
                        Vos liens ({links.length})
                    </h2>
                    <ul className="divide-y divide-border">
                        {links.map((link) => {
                            const linkUrl = `${window.location.origin}/s/${link.code}`;

                            return (
                                <li
                                    key={link.id}
                                    className="flex flex-wrap items-center gap-3 py-3"
                                >
                                    <div className="min-w-0 flex-1">
                                        <span className="font-mono text-sm text-foreground">
                                            /s/{link.code}
                                        </span>
                                        <span className="block truncate text-xs text-muted-foreground">
                                            {link.target_url}
                                        </span>
                                    </div>
                                    <span className="shrink-0 font-mono text-xs text-muted-foreground">
                                        {link.clicks_count}{' '}
                                        {link.clicks_count > 1
                                            ? 'clics'
                                            : 'clic'}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => copy(linkUrl)}
                                        className="btn-ghost shrink-0"
                                        aria-label={`Copier /s/${link.code}`}
                                    >
                                        {copied === linkUrl ? (
                                            <Check className="size-4" />
                                        ) : (
                                            <Copy className="size-4" />
                                        )}
                                    </button>
                                </li>
                            );
                        })}
                    </ul>
                </section>
            )}

            <section className="animate-reveal text-center [animation-delay:.3s]">
                <a href={statsRoute().url} className="btn-ghost mx-auto">
                    Voir les statistiques
                    <ArrowRight className="size-4" />
                </a>
            </section>
        </>
    );
}
