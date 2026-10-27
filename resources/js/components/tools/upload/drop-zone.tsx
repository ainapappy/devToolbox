import { Upload } from 'lucide-react';
import { useRef, useState, type DragEvent } from 'react';
import type { CSSProperties } from 'react';

/**
 * Zone de dépôt native (maquette : .drop-zone de file-upload.html).
 * Drag & drop natif + input file caché `multiple`, accessible au clavier
 * (Entrée / Espace ouvrent le sélecteur), état .drag-over équivalent.
 */
export function DropZone({
    disabled = false,
    onFiles,
}: {
    disabled?: boolean;
    onFiles: (files: File[]) => void;
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const dragDepth = useRef(0);
    const [dragOver, setDragOver] = useState(false);

    const collect = (fileList: FileList | null): File[] =>
        Array.from(fileList ?? []);

    const handleDragEnter = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        dragDepth.current += 1;
        setDragOver(true);
    };

    const handleDragOver = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
    };

    const handleDragLeave = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        dragDepth.current = Math.max(0, dragDepth.current - 1);
        if (dragDepth.current === 0) {
            setDragOver(false);
        }
    };

    const handleDrop = (event: DragEvent<HTMLDivElement>) => {
        event.preventDefault();
        dragDepth.current = 0;
        setDragOver(false);
        onFiles(collect(event.dataTransfer.files));
    };

    const openPicker = () => {
        if (!disabled) {
            inputRef.current?.click();
        }
    };

    const handleKeyDown = (event: React.KeyboardEvent<HTMLDivElement>) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openPicker();
        }
    };

    return (
        <div
            role="button"
            tabIndex={disabled ? -1 : 0}
            aria-label="Zone de dépôt : glissez vos fichiers ou appuyez sur Entrée pour parcourir"
            aria-disabled={disabled}
            onClick={openPicker}
            onKeyDown={handleKeyDown}
            onDragEnter={handleDragEnter}
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
            onDrop={handleDrop}
            style={{ '--tool': '#f59e0b' } as CSSProperties}
            className={`grid cursor-pointer justify-items-center gap-3.5 rounded-2xl border-2 border-dashed border-border bg-background px-6 py-12 text-center transition-[border-color,background-color,transform,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[color-mix(in_srgb,var(--tool)_55%,transparent)] hover:bg-[color-mix(in_srgb,var(--tool)_5%,var(--color-background))] focus-visible:border-[color:var(--tool)] focus-visible:ring-4 focus-visible:ring-[color-mix(in_srgb,var(--tool)_18%,transparent)] focus-visible:outline-none ${
                dragOver
                    ? 'border-solid border-[color:var(--tool)] bg-[color-mix(in_srgb,var(--tool)_12%,var(--color-background))] shadow-[0_18px_40px_-18px_color-mix(in_srgb,var(--tool)_45%,transparent)]'
                    : ''
            }`}
        >
            <div
                aria-hidden="true"
                className={`grid size-13 place-items-center rounded-2xl border text-[color:var(--tool)] transition-transform duration-300 ${
                    dragOver ? '-translate-y-1.5 scale-[1.08]' : ''
                }`}
                style={{
                    borderColor:
                        'color-mix(in srgb, var(--tool) 35%, transparent)',
                    background:
                        'linear-gradient(135deg, color-mix(in srgb, var(--tool) 22%, transparent), transparent)',
                }}
            >
                <Upload className="size-6 rotate-180" />
            </div>

            <span
                className={`text-[1.05rem] font-bold tracking-tight transition-colors ${
                    dragOver ? 'text-[color:var(--tool)]' : ''
                }`}
            >
                {dragOver
                    ? 'Relâchez pour ajouter'
                    : 'Glissez vos fichiers ici'}
            </span>

            <p className="max-w-[42ch] text-[0.82rem] text-muted-foreground">
                ou sélectionnez-les depuis votre ordinateur. Les fichiers
                dépassant les limites sont rejetés immédiatement.
            </p>

            <button
                type="button"
                onClick={(event) => {
                    event.stopPropagation();
                    openPicker();
                }}
                className="btn-gradient"
            >
                <Upload className="size-4 rotate-180" />
                Parcourir
            </button>

            <input
                ref={inputRef}
                type="file"
                multiple
                className="sr-only"
                tabIndex={-1}
                aria-hidden="true"
                onChange={(event) => {
                    onFiles(collect(event.target.files));
                    event.target.value = '';
                }}
            />
        </div>
    );
}
