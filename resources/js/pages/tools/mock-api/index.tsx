import { Head, useForm } from '@inertiajs/react';
import { Check, Copy, Zap } from 'lucide-react';
import { useMemo, type CSSProperties } from 'react';
import { ToolHero } from '@/components/front/tool-hero';
import { useClipboard } from '@/hooks/use-clipboard';
import { store as mockApiStore } from '@/actions/App/Http/Controllers/Tools/MockApiController';
import type { MockCreateProps } from '@/types';

const defaultPayload = `{
  "id": 42,
  "name": "Ada Lovelace",
  "role": "admin",
  "verified": true,
  "score": 98.5
}`;

const statusCodes = [200, 201, 202, 204, 301, 400, 403, 404, 418, 500] as const;

/**
 * Coloration syntaxique JSON minimale (maquette : .j-key/.j-str/.j-num/.j-bool).
 */
function highlightJson(source: string): string {
    const escaped = source.replace(/&/g, '&amp;').replace(/</g, '&lt;');

    // Un seul passage : les replace chaînés re-traitaient le balisage injecté.
    return escaped.replace(
        /("(?:\\.|[^"\\])*")(?=\s*:)|("(?:\\.|[^"\\])*")|\b(true|false|null)\b|(-?\b\d+(?:\.\d+)?\b)/g,
        (match, key, str, bool) => {
            const cls = key
                ? 'j-key'
                : str
                  ? 'j-str'
                  : bool
                    ? 'j-bool'
                    : 'j-num';
            return `<span class="${cls}">${match}</span>`;
        },
    );
}

type MockForm = {
    payload: string;
    status_code: number;
};

export default function Index({ created }: MockCreateProps) {
    const [copied, copy] = useClipboard();
    const { data, setData, post, processing, errors } = useForm<MockForm>({
        payload: defaultPayload,
        status_code: 200,
    });

    const valid = useMemo(() => {
        try {
            JSON.parse(data.payload);
            return true;
        } catch {
            return false;
        }
    }, [data.payload]);

    const highlighted = useMemo(
        () => highlightJson(data.payload),
        [data.payload],
    );

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!valid) return;
        post(mockApiStore.url(), { preserveScroll: true });
    };

    const endpoint = created
        ? `${window.location.origin}/api/mock/${created.slug}`
        : null;

    return (
        <>
            <Head title="JSON Mock API" />

            <ToolHero
                step="02"
                title="JSON Mock API"
                description="Collez un objet JSON, obtenez instantanément un endpoint public pour tester votre frontend."
                icon={Zap}
                color="#22d3ee"
            />

            <section className="tool-card mb-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="mb-4 flex items-center justify-between text-xs font-bold tracking-widest text-muted-foreground uppercase">
                    <span>
                        Éditeur JSON —{' '}
                        {valid ? 'syntaxe validée ✓' : 'syntaxe invalide ✗'}
                    </span>
                    <span className="font-mono normal-case">payload.json</span>
                </div>

                <div className="relative font-mono text-sm">
                    <pre
                        aria-hidden
                        className="code-well pointer-events-none min-h-55 overflow-x-auto rounded-xl border border-border p-4 leading-relaxed whitespace-pre-wrap"
                        dangerouslySetInnerHTML={{ __html: highlighted }}
                    />
                    <textarea
                        value={data.payload}
                        onChange={(event) =>
                            setData('payload', event.target.value)
                        }
                        spellCheck={false}
                        aria-label="Éditeur JSON"
                        className="absolute inset-0 size-full resize-none overflow-x-auto rounded-xl border border-transparent bg-transparent p-4 leading-relaxed text-transparent caret-cyan-400 focus:outline-none"
                    />
                </div>

                <div className="mt-4 flex flex-wrap items-center gap-2">
                    <span className="text-xs font-bold tracking-widest text-muted-foreground uppercase">
                        Status de réponse
                    </span>
                    {statusCodes.map((code) => (
                        <button
                            key={code}
                            type="button"
                            role="radio"
                            aria-checked={data.status_code === code}
                            onClick={() => setData('status_code', code)}
                            className="option-pill font-mono"
                            style={{ '--tool': '#22d3ee' } as CSSProperties}
                        >
                            {code}
                        </button>
                    ))}
                </div>

                {errors.payload && (
                    <p className="mt-3 text-sm text-destructive">
                        {errors.payload}
                    </p>
                )}

                <button
                    type="button"
                    onClick={submit}
                    disabled={!valid || processing}
                    className="btn-gradient mt-5"
                >
                    <Zap className="size-4" />
                    {valid ? "Créer l'endpoint" : 'JSON invalide'}
                </button>
            </section>

            {created && endpoint && (
                <section className="tool-card animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.3s]">
                    <div className="flex flex-wrap items-center gap-4">
                        <span className="animate-pop rounded-md bg-emerald-500 px-2.5 py-1 font-mono text-xs font-bold text-white">
                            GET
                        </span>
                        <span className="font-mono text-sm">{endpoint}</span>
                        <button
                            type="button"
                            onClick={() => copy(endpoint)}
                            className="btn-ghost ml-auto"
                        >
                            {copied === endpoint ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}
                            {copied === endpoint ? 'Copié !' : 'Copier'}
                        </button>
                    </div>
                    <p className="mt-4 text-sm text-muted-foreground">
                        Réponse en{' '}
                        <code className="font-mono text-accent-2">
                            application/json
                        </code>{' '}
                        · status <strong>{created.status_code}</strong>
                    </p>
                    <pre className="code-well mt-3 overflow-x-auto rounded-xl border border-border p-3 font-mono text-xs text-muted-foreground">
                        {`curl -i ${endpoint}`}
                    </pre>
                    <button
                        type="button"
                        onClick={() => copy(`curl -i ${endpoint}`)}
                        className="btn-ghost mt-2"
                    >
                        <Copy className="size-3.5" />
                        Copier l'exemple curl
                    </button>
                </section>
            )}
        </>
    );
}
