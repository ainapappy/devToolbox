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

/* --- JSON Mock API --- */

/** Endpoint d'un mock nouvellement créé (flash `created`). */
export interface MockCreated {
    slug: string;
    status_code: number;
}

/** Props de la page de création JSON Mock API. */
export interface MockCreateProps {
    created: MockCreated | null;
}

/* --- URL Shortener & Analytics --- */

/** Lien court nouvellement créé (flash `created`). */
export interface ShortenerCreated {
    code: string;
    target_url: string;
}

/** Lien court listé sur la page principale. */
export interface ShortenerLink {
    id: number;
    code: string;
    target_url: string;
    clicks_count: number;
    created_at: string;
}

/** Props de la page principale URL Shortener. */
export interface ShortenerIndexProps {
    created: ShortenerCreated | null;
    links: ShortenerLink[];
}

/** Une journée du graphique de clics. */
export interface DailyClicks {
    date: string;
    count: number;
}

/** Statistiques agrégées côté serveur (agrégats SQL). */
export interface ShortenerStats {
    clicks_30d: number;
    active_links: number;
    top_browser: string | null;
    top_browser_share: number | null;
    top_country: string | null;
    top_country_share: number | null;
    daily: DailyClicks[];
}

/** Props de la page de statistiques URL Shortener. */
export interface ShortenerStatsProps {
    stats: ShortenerStats;
}

/* --- Markdown Previewer --- */

/** Document nouvellement partagé (flash `created`). */
export interface MarkdownCreated {
    slug: string;
}

/** Props de la page éditeur Markdown. */
export interface MarkdownIndexProps {
    created: MarkdownCreated | null;
}

/* --- File Drop --- */

/** Métadonnées d'un fichier nouvellement envoyé (flash `uploaded`). */
export interface FileUploadCreated {
    uuid: string;
    original_name: string;
    mime_type: string;
    size: number;
    max_downloads: number | null;
    expires_at: string | null;
}

/** Props de la page d'upload File Drop (limites de config/devtoolbox.php). */
export interface UploadIndexProps {
    maxSizeKb: number;
    maxFiles: number;
    allowedMimes: string[];
    durations: string[];
    downloadOptions: number[];
}

/** Props de la page de déverrouillage par mot de passe. */
export interface UploadLockedProps {
    uuid: string;
    error: string | null;
}

/** Props de la page d'un document partagé. */
export interface MarkdownShowProps {
    markdown: string;
    html: string;
}
