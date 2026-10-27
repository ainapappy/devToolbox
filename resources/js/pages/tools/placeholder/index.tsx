import { Head } from '@inertiajs/react';
import { Check, Code2, Copy, Image as ImageIcon } from 'lucide-react';
import { useMemo, useState, type CSSProperties } from 'react';
import { ToolHero } from '@/components/front/tool-hero';
import { useClipboard } from '@/hooks/use-clipboard';
import { show as showRoute } from '@/routes/placeholders';

/**
 * Palette miroir de App\Http\Controllers\Tools\PlaceholderController (liste fermée).
 */
const palette: Array<{ name: string; base: string }> = [
    { name: 'indigo', base: '#6366f1' },
    { name: 'cyan', base: '#22d3ee' },
    { name: 'violet', base: '#a855f7' },
    { name: 'orange', base: '#eb6834' },
    { name: 'pink', base: '#e87ba4' },
    { name: 'teal', base: '#14b8a6' },
    { name: 'blue', base: '#2a78d6' },
    { name: 'green', base: '#22c55e' },
    { name: 'red', base: '#ef4444' },
    { name: 'gray', base: '#898781' },
];

const sizePresets = ['300x200', '600x400', '150x150', '800x600', '120x90'];

export default function Index() {
    const [copied, copy] = useClipboard();
    const [width, setWidth] = useState(300);
    const [height, setHeight] = useState(200);
    const [color, setColor] = useState('teal');
    const [text, setText] = useState('');

    const size = `${width}x${height}`;

    const params = useMemo(() => {
        const query: Record<string, string> = { color };
        if (text.trim() !== '') {
            query.text = text.trim();
        }
        return query;
    }, [color, text]);

    const path = showRoute(size, { query: params }).url;
    const url = `${window.location.origin}${path}`;
    const imgTag = `<img src="${url}" width="${width}" height="${height}" alt="Placeholder">`;

    return (
        <>
            <Head title="SVG Placeholder" />

            <ToolHero
                step="05"
                title="SVG Placeholder Generator"
                description="Des images de remplacement propres, générées à la volée depuis une simple URL."
                icon={ImageIcon}
                color="#1baf7a"
            />

            <section className="tool-card mb-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="mb-4 text-xs font-bold tracking-widest text-muted-foreground uppercase">
                    URL paramétrée
                </div>

                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <code className="font-mono text-sm text-accent-2">
                        /placeholder/
                    </code>
                    {sizePresets.map((preset) => (
                        <button
                            key={preset}
                            type="button"
                            aria-pressed={size === preset}
                            onClick={() => {
                                const [w, h] = preset.split('x').map(Number);
                                setWidth(w);
                                setHeight(h);
                            }}
                            className="option-pill font-mono"
                            style={{ '--tool': '#1baf7a' } as CSSProperties}
                        >
                            {preset}
                        </button>
                    ))}
                    <code className="font-mono text-sm text-accent-3">
                        ?color={color}
                    </code>
                </div>

                <div className="flex flex-wrap gap-6">
                    <label className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">Largeur</span>
                        <input
                            type="number"
                            min={1}
                            max={4000}
                            value={width}
                            onChange={(event) =>
                                setWidth(
                                    Math.max(
                                        1,
                                        Math.min(
                                            4000,
                                            Number(event.target.value) || 1,
                                        ),
                                    ),
                                )
                            }
                            className="w-24 rounded-lg border border-input bg-background px-3 py-1.5 font-mono text-sm focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                        />
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">Hauteur</span>
                        <input
                            type="number"
                            min={1}
                            max={4000}
                            value={height}
                            onChange={(event) =>
                                setHeight(
                                    Math.max(
                                        1,
                                        Math.min(
                                            4000,
                                            Number(event.target.value) || 1,
                                        ),
                                    ),
                                )
                            }
                            className="w-24 rounded-lg border border-input bg-background px-3 py-1.5 font-mono text-sm focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                        />
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <span className="text-muted-foreground">
                            Texte (optionnel)
                        </span>
                        <input
                            type="text"
                            value={text}
                            maxLength={60}
                            placeholder="Hello"
                            onChange={(event) => setText(event.target.value)}
                            className="w-40 rounded-lg border border-input bg-background px-3 py-1.5 text-sm focus:border-accent-1 focus:ring-4 focus:ring-accent-1/20 focus:outline-none"
                        />
                    </label>
                </div>

                <div className="mt-4 flex flex-wrap items-center gap-2">
                    <span className="text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        Couleur
                    </span>
                    {palette.map((entry) => (
                        <button
                            key={entry.name}
                            type="button"
                            aria-pressed={color === entry.name}
                            aria-label={`Couleur ${entry.name}`}
                            title={entry.name}
                            onClick={() => setColor(entry.name)}
                            className={`size-7 rounded-full border-2 transition-transform hover:-translate-y-0.5 ${
                                color === entry.name
                                    ? 'border-foreground'
                                    : 'border-transparent'
                            }`}
                            style={{ background: entry.base }}
                        />
                    ))}
                </div>
            </section>

            <section className="tool-card mx-auto max-w-xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.3s]">
                <div className="grid place-items-center rounded-xl border border-dashed border-border bg-background p-4">
                    <img
                        src={path}
                        width={Math.min(width, 480)}
                        height={Math.round(
                            (Math.min(width, 480) / width) * height,
                        )}
                        alt={`Image placeholder ${size}`}
                        className="max-w-full animate-float"
                    />
                </div>

                <p className="mt-3 text-center text-xs text-muted-foreground">
                    Rendu{' '}
                    <code className="font-mono text-accent-1">
                        image/svg+xml
                    </code>{' '}
                    · mis en cache · 304 sur re-visites
                </p>

                <div className="mt-4 space-y-2">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <code className="overflow-x-auto font-mono text-xs">
                            {url}
                        </code>
                        <button
                            type="button"
                            onClick={() => copy(url)}
                            className="btn-ghost"
                        >
                            {copied === url ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}
                            {copied === url ? 'Copié !' : 'Copier l’URL'}
                        </button>
                    </div>
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <code className="overflow-x-auto font-mono text-xs">
                            <Code2 className="mr-1 inline size-3.5 text-accent-1" />
                            {imgTag}
                        </code>
                        <button
                            type="button"
                            onClick={() => copy(imgTag)}
                            className="btn-ghost"
                        >
                            {copied === imgTag ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}
                            {copied === imgTag ? 'Copié !' : 'Copier <img>'}
                        </button>
                    </div>
                </div>
            </section>
        </>
    );
}
