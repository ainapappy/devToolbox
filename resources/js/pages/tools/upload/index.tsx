import { Head, useHttp } from '@inertiajs/react';
import { Check, Copy, FileText, Sparkles, Upload, Wand2 } from 'lucide-react';
import { useState, type CSSProperties } from 'react';
import { toast } from 'sonner';
import { ToolHero } from '@/components/front/tool-hero';
import { DropZone } from '@/components/tools/upload/drop-zone';
import {
    FileList,
    formatBytes,
    type UploadEntry,
} from '@/components/tools/upload/file-list';
import {
    durationLabels,
    LinkOptions,
} from '@/components/tools/upload/link-options';
import { useClipboard } from '@/hooks/use-clipboard';
import { download, store } from '@/routes/tools/upload';
import type { FileUploadCreated, UploadIndexProps } from '@/types';

/** Limite de taille lisible (« 2 Mo », « 512 Ko »). */
function formatLimit(maxSizeKb: number): string {
    if (maxSizeKb >= 1024) {
        const mo = maxSizeKb / 1024;

        return `${Number.isInteger(mo) ? mo : mo.toFixed(1).replace('.', ',')} Mo`;
    }

    return `${maxSizeKb} Ko`;
}

/** Données du formulaire d'envoi (endpoint JSON du FileUploadController). */
interface UploadFormData {
    files: File[];
    max_downloads: number | null;
    expiration: string;
    password: string;
}

export default function Index({
    maxSizeKb,
    maxFiles,
    allowedMimes,
    durations,
    downloadOptions,
}: UploadIndexProps) {
    const [copied, copy] = useClipboard();
    const [hint, setHint] = useState<{ message: string; error: boolean }>({
        message: '',
        error: false,
    });
    const [uploaded, setUploaded] = useState<FileUploadCreated[] | null>(null);
    const [summary, setSummary] = useState('');
    const [formError, setFormError] = useState('');
    const [percentage, setPercentage] = useState<number | null>(null);

    const { data, setData, post, processing, errors, clearErrors } =
        useHttp<UploadFormData>({
            files: [],
            max_downloads: downloadOptions[1] ?? downloadOptions[0] ?? null,
            expiration: durations[1] ?? durations[0] ?? '',
            password: '',
        });

    const toolStyle = { '--tool': '#f59e0b' } as CSSProperties;
    const sending = processing;

    // Liste prête pour FileList : identifiant stable = position courante.
    const entries: UploadEntry[] = data.files.map((file, index) => ({
        id: index,
        file,
    }));

    const addFiles = (files: File[]) => {
        if (files.length === 0) {
            return;
        }

        const accepted: File[] = [];
        const rejected: string[] = [];

        for (const file of files) {
            if (data.files.length + accepted.length >= maxFiles) {
                rejected.push(
                    `Limite de ${maxFiles} fichiers atteinte — supprimez un fichier avant de continuer.`,
                );
                break;
            }

            const extension = file.name.split('.').pop()?.toLowerCase() ?? '';

            if (!allowedMimes.includes(extension)) {
                rejected.push(
                    `« ${file.name} » : format non autorisé (${allowedMimes.join(', ')}).`,
                );
                continue;
            }

            if (file.size > maxSizeKb * 1024) {
                rejected.push(
                    `« ${file.name} » dépasse la limite de ${formatLimit(maxSizeKb)} par fichier.`,
                );
                continue;
            }

            accepted.push(file);
        }

        if (accepted.length > 0) {
            setData('files', [...data.files, ...accepted]);
        }

        if (rejected.length > 0) {
            setHint({ message: rejected.join(' '), error: true });
        } else {
            setHint({
                message: `${accepted.length} fichier${accepted.length > 1 ? 's' : ''} ajouté${accepted.length > 1 ? 's' : ''}.`,
                error: false,
            });
        }
    };

    const removeEntry = (id: number) => {
        setData(
            'files',
            data.files.filter((_, index) => index !== id),
        );
        setHint({ message: '', error: false });
    };

    const canSubmit = data.files.length > 0 && !sending;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        if (!canSubmit) {
            return;
        }

        // Résumé figé au moment de l'envoi (les données sont réinitialisées
        // au succès) ; le contrôleur renvoie les métadonnées en JSON.
        const downloadsText =
            data.max_downloads === null
                ? 'des téléchargements illimités'
                : `${data.max_downloads} téléchargement${data.max_downloads > 1 ? 's' : ''} maximum`;
        const durationText =
            data.expiration === ''
                ? 'sans expiration'
                : `expire dans ${durationLabels[data.expiration] ?? data.expiration}`;
        const passwordText = data.password
            ? ' Il est protégé par mot de passe.'
            : ' Aucun mot de passe requis.';

        setUploaded(null);
        setFormError('');
        clearErrors();

        void post(store.url(), {
            onProgress: (event) => {
                const total = event?.total ?? 0;

                setPercentage(
                    total > 0 ? Math.round((event.loaded / total) * 100) : null,
                );
            },
            onSuccess: (responseData) => {
                const files =
                    (
                        responseData as {
                            files?: FileUploadCreated[];
                        } | null
                    )?.files ?? [];

                setSummary(
                    `Ce lien autorise ${downloadsText} et ${durationText}.${passwordText}`,
                );
                setUploaded(files);
                setData('files', []);
                setData('password', '');
                setHint({ message: '', error: false });
                toast.success('Lien de partage généré.');
            },
            onError: (validationErrors) => {
                setFormError(
                    validationErrors.files ??
                        validationErrors['files.0'] ??
                        validationErrors.expiration ??
                        validationErrors.max_downloads ??
                        validationErrors.password ??
                        'Une erreur est survenue pendant l’envoi.',
                );
            },
            onFinish: () => {
                setPercentage(null);
            },
        });
    };

    const shareUrl =
        uploaded && uploaded.length > 0
            ? `${window.location.origin}${download.url({ uuid: uploaded[0].batch_uuid })}`
            : null;

    return (
        <>
            <Head title="File Drop" />

            <ToolHero
                step="06"
                title="File Drop"
                description="Glissez un ou plusieurs fichiers, respectez les limites, récupérez un lien. Tout est piloté par la configuration."
                icon={Upload}
                color="#f59e0b"
            />

            <section
                className="tool-card mb-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.05s]"
                style={toolStyle}
            >
                <div className="mb-4 flex items-center justify-between gap-4">
                    <h2 className="text-sm font-bold tracking-widest text-muted-foreground uppercase">
                        Limites actives
                    </h2>
                    <span className="font-mono text-[0.78rem] text-muted-foreground">
                        config/devtoolbox.php → uploads
                    </span>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-[color-mix(in_srgb,var(--tool)_30%,transparent)] bg-[color-mix(in_srgb,var(--tool)_10%,transparent)] px-3 py-1.5 text-xs font-semibold text-muted-foreground">
                        <Upload className="size-3 rotate-180 text-[color:var(--tool)]" />
                        max {formatLimit(maxSizeKb)} par fichier
                    </span>
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-[color-mix(in_srgb,var(--tool)_30%,transparent)] bg-[color-mix(in_srgb,var(--tool)_10%,transparent)] px-3 py-1.5 text-xs font-semibold text-muted-foreground">
                        <FileText className="size-3 text-[color:var(--tool)]" />
                        {maxFiles} fichiers maximum
                    </span>
                    <span className="inline-flex items-center gap-1.5 rounded-full border border-[color-mix(in_srgb,var(--tool)_30%,transparent)] bg-[color-mix(in_srgb,var(--tool)_10%,transparent)] px-3 py-1.5 text-xs font-semibold text-muted-foreground">
                        <Check className="size-3 text-[color:var(--tool)]" />
                        {allowedMimes.join(', ')}
                    </span>
                </div>
            </section>

            <section
                className="tool-card mb-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]"
                style={toolStyle}
            >
                <DropZone disabled={sending} onFiles={addFiles} />
                <p
                    aria-live="polite"
                    className={`mt-3 min-h-5 text-[0.78rem] ${
                        hint.error
                            ? 'font-semibold text-destructive'
                            : 'text-muted-foreground'
                    }`}
                >
                    {hint.message}
                </p>
            </section>

            <FileList
                entries={entries}
                maxFiles={maxFiles}
                sending={sending}
                onRemove={removeEntry}
            />

            <form
                onSubmit={submit}
                className="tool-card mt-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.3s]"
                style={toolStyle}
            >
                <div className="mb-4 flex items-center justify-between gap-4">
                    <h2 className="text-sm font-bold tracking-widest text-muted-foreground uppercase">
                        Options du lien
                    </h2>
                    <span className="text-xs text-muted-foreground">
                        comme Secure Paste, pour vos fichiers
                    </span>
                </div>

                <LinkOptions
                    downloadOptions={downloadOptions}
                    durations={durations}
                    maxDownloads={data.max_downloads}
                    onMaxDownloadsChange={(value) =>
                        setData('max_downloads', value)
                    }
                    expiration={data.expiration}
                    onExpirationChange={(value) => setData('expiration', value)}
                    password={data.password}
                    onPasswordChange={(value) => setData('password', value)}
                />

                <button
                    type="submit"
                    disabled={!canSubmit}
                    className="btn-gradient mt-6 w-full"
                >
                    <Wand2 className="size-4" />
                    {sending ? 'Envoi en cours…' : 'Générer le lien'}
                </button>

                {sending && percentage !== null && (
                    <div className="mt-4 h-1.5 overflow-hidden rounded-full bg-background">
                        <span
                            className="block h-full rounded-full bg-linear-to-r from-[#f59e0b] to-accent-2 transition-[width] duration-300"
                            style={{ width: `${percentage}%` }}
                        />
                    </div>
                )}

                {(formError || errors.files) && (
                    <p role="alert" className="mt-4 text-sm text-destructive">
                        {formError || errors.files}
                    </p>
                )}
            </form>

            {uploaded && uploaded.length > 0 && shareUrl && (
                <section
                    className="tool-card mt-6 animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]"
                    style={toolStyle}
                >
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
                            {copied === shareUrl ? (
                                <Check className="size-4" />
                            ) : (
                                <Copy className="size-4" />
                            )}
                            {copied === shareUrl ? 'Copié !' : 'Copier'}
                        </button>
                    </div>
                    <p className="mt-3 text-[0.85rem] text-muted-foreground">
                        {summary}
                    </p>
                    <p className="mt-2 inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Sparkles className="size-3.5 text-[color:var(--tool)]" />
                        {uploaded.length} fichier
                        {uploaded.length > 1 ? 's' : ''} envoyé
                        {uploaded.length > 1 ? 's' : ''} (
                        {formatBytes(uploaded[0].size)} pour{' '}
                        {uploaded[0].original_name})
                    </p>
                </section>
            )}
        </>
    );
}
