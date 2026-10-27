import { Code, FileText, Image, Link2, Lock, Zap } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

/**
 * Associe l'identifiant d'icône servi par le backend (App\Tools\Tool::$icon)
 * à son composant Lucide.
 */
const icons: Record<string, LucideIcon> = {
    'lock-closed': Lock,
    code: Code,
    'link-2': Link2,
    'file-text': FileText,
    image: Image,
};

const fallback: LucideIcon = Zap;

export function toolIcon(name: string): LucideIcon {
    return icons[name] ?? fallback;
}
