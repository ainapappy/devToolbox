/**
 * Icône archive (zip/rar/gz) — SVG inline repris de la maquette
 * .knowledge/design/file-upload.html : le set Radix/Lucide du projet n'a
 * pas d'équivalent « fichier archive avec glissière ».
 */
export function ArchiveIcon({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.6"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            className={className}
        >
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" />
            <path d="M14 2v6h6" />
            <path d="M12 11v1.5M12 15v1.5M12 18v1.5" />
        </svg>
    );
}
