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
