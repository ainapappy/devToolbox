import { index as mockApi } from '@/routes/tools/mock-api';
import { index as markdown } from '@/routes/tools/markdown';
import { index as paste } from '@/routes/tools/paste';
import { index as placeholder } from '@/routes/tools/placeholder';
import { index as shortener } from '@/routes/tools/shortener';
import type { RouteDefinition } from '@/wayfinder';

/**
 * Résout la route Wayfinder d'un outil à partir de son slug.
 */
const routes: Record<string, () => RouteDefinition<'get'>> = {
    paste: paste,
    'mock-api': mockApi,
    shortener: shortener,
    markdown: markdown,
    placeholder: placeholder,
};

export function toolRoute(slug: string): RouteDefinition<'get'> {
    const route = routes[slug] ?? paste;

    return route();
}
