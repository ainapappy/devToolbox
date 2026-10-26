/**
 * Outil de la boîte à outils, servi par le DashboardController.
 * Miroir de App\Tools\Tool.php.
 */
export interface Tool {
    slug: string;
    name: string;
    description: string;
    tags: string[];
    color: string;
    icon: string;
}

/** Props de la page dashboard (liste des outils). */
export interface DashboardProps {
    tools: Tool[];
}

/** Slug d'un outil, tel que servi par le backend. */
export type ToolSlug = Tool['slug'];

/* --- Secure Paste --- */

/** Lien d'un paste nouvellement créé (flash `created`). */
export interface PasteCreated {
    hash: string;
    burn_after_read: boolean;
}

/** Props de la page de création Secure Paste. */
export interface PasteCreateProps {
    created: PasteCreated | null;
}

/** Props de la page d'avertissement avant révélation. */
export interface PasteShowProps {
    hash: string;
    burn_after_read: boolean;
}

/** Props de la page de révélation du secret. */
export interface PasteRevealProps {
    content: string;
}
