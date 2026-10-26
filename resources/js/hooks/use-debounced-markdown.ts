import { useEffect, useState } from 'react';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

/**
 * Rendu Markdown temps réel côté client : parsing marqué avec debounce
 * (~150 ms) + sanitization DOMPurify contre le XSS. Double barrière avec
 * l'échappement serveur (App\Services\MarkdownRenderer).
 *
 * @param source Contenu Markdown brut saisi par l'utilisateur.
 * @param delay  Délai du debounce en millisecondes.
 */
export function useDebouncedMarkdown(
    source: string,
    delay = 150,
): { html: string; rendering: boolean } {
    const [debounced, setDebounced] = useState(source);
    const [html, setHtml] = useState('');

    useEffect(() => {
        const timer = window.setTimeout(() => setDebounced(source), delay);

        return () => window.clearTimeout(timer);
    }, [source, delay]);

    useEffect(() => {
        const raw = marked.parse(debounced, { async: false });

        setHtml(DOMPurify.sanitize(raw));
    }, [debounced]);

    return { html, rendering: debounced !== source };
}
