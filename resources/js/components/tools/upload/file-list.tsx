import {
    Code2,
    FileText,
    Image as ImageIcon,
    Timer,
    Trash2,
    Check,
} from 'lucide-react';
import { ArchiveIcon } from '@/components/tools/upload/archive-icon';
import type { CSSProperties, ReactNode } from 'react';

/** Entrée de la liste : un fichier ajouté et validé côté client. */
export interface UploadEntry {
    id: number;
    file: File;
}

/** Taille formatée façon maquette (« 820 Ko », « 1,4 Mo »). */
export function formatBytes(size: number): string {
    if (size >= 1024 * 1024) {
        const mo = size / (1024 * 1024);

        return `${mo.toFixed(1).replace('.', ',')} Mo`;
    }

    return `${Math.max(1, Math.round(size / 1024))} Ko`;
}

/** Icône par type de fichier : SVG inline pour les archives, Lucide sinon. */
function fileIcon(name: string): ReactNode {
    const extension = name.split('.').pop()?.toLowerCase() ?? '';

    if (['zip', 'rar', 'gz'].includes(extension)) {
        return <ArchiveIcon className="size-4" />;
    }
    if (['png', 'jpg', 'jpeg'].includes(extension)) {
        return <ImageIcon className="size-4" />;
    }
    if (['csv', 'json'].includes(extension)) {
        return <Code2 className="size-4" />;
    }

    return <FileText className="size-4" />;
}

/**
 * Liste des fichiers prêts (maquette : .file-list / .file-row).
 * Nom tronqué, taille formatée, badge d'état et suppression individuelle.
 */
export function FileList({
    entries,
    maxFiles,
    sending,
    onRemove,
}: {
    entries: UploadEntry[];
    maxFiles: number;
    sending: boolean;
    onRemove: (id: number) => void;
}) {
    if (entries.length === 0) {
        return null;
    }

    return (
        <section
            className="tool-card animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.25s]"
            style={{ '--tool': '#f59e0b' } as CSSProperties}
        >
            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-sm font-bold tracking-widest text-muted-foreground uppercase">
                    Fichiers
                </h2>
                <span className="font-mono text-xs text-muted-foreground">
                    {entries.length} / {maxFiles} fichiers
                </span>
            </div>

            <ul className="grid gap-3">
                {entries.map((entry) => (
                    <li
                        key={entry.id}
                        className="grid grid-cols-[auto_1fr_auto_auto] items-center gap-3.5 rounded-xl border border-border bg-background px-4 py-3 transition-[border-color,transform,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[color-mix(in_srgb,var(--tool)_40%,transparent)] hover:shadow-[0_10px_26px_-16px_color-mix(in_srgb,var(--tool)_40%,transparent)]"
                    >
                        <div
                            aria-hidden="true"
                            className="grid size-[38px] place-items-center rounded-xl border text-[color:var(--tool)]"
                            style={{
                                borderColor:
                                    'color-mix(in srgb, var(--tool) 30%, transparent)',
                                background:
                                    'linear-gradient(135deg, color-mix(in srgb, var(--tool) 20%, transparent), transparent)',
                            }}
                        >
                            {fileIcon(entry.file.name)}
                        </div>

                        <div className="min-w-0">
                            <p className="truncate text-[0.88rem] font-semibold">
                                {entry.file.name}
                            </p>
                            <p className="font-mono text-xs text-muted-foreground">
                                {formatBytes(entry.file.size)}
                            </p>
                        </div>

                        {sending ? (
                            <span className="inline-flex items-center gap-1.5 rounded-full border border-accent-2/30 bg-accent-2/10 px-2.5 py-1 text-xs font-bold whitespace-nowrap text-accent-2">
                                <Timer className="size-3 animate-spin" />
                                Envoi…
                            </span>
                        ) : (
                            <span className="inline-flex items-center gap-1.5 rounded-full border border-[color-mix(in_srgb,var(--tool)_35%,transparent)] bg-[color-mix(in_srgb,var(--tool)_14%,transparent)] px-2.5 py-1 text-xs font-bold whitespace-nowrap text-[color:var(--tool)]">
                                <Check className="size-3" />
                                Prêt
                            </span>
                        )}

                        <button
                            type="button"
                            aria-label={`Supprimer ${entry.file.name}`}
                            onClick={() => onRemove(entry.id)}
                            className="grid size-8 place-items-center rounded-lg border border-border text-muted-foreground transition-[color,border-color,background-color,transform] duration-200 hover:-translate-y-0.5 hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive"
                        >
                            <Trash2 className="size-4" />
                        </button>
                    </li>
                ))}
            </ul>
        </section>
    );
}
