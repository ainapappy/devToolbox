import { Link } from '@inertiajs/react';
import type { CSSProperties } from 'react';
import { Tag } from '@/components/front/tag';
import { toolIcon } from '@/lib/tool-icons';
import { toolRoute } from '@/lib/tool-routes';
import type { Tool } from '@/types';

/**
 * Carte d'un outil sur le dashboard (maquette : .card de index.html).
 * La couleur d'accent est injectée via la variable CSS --tool.
 */
export function ToolCard({ tool, index }: { tool: Tool; index: number }) {
    const Icon = toolIcon(tool.icon);

    return (
        <Link
            href={toolRoute(tool.slug)}
            prefetch
            style={
                {
                    '--tool': tool.color,
                    animationDelay: `${0.05 + index * 0.1}s`,
                } as CSSProperties
            }
            className="tool-card animate-reveal rounded-2xl border border-border bg-card p-6 transition-[transform,border-color,box-shadow] duration-300"
        >
            <div
                className="mb-4 flex size-11 items-center justify-center rounded-xl border"
                style={{
                    color: 'var(--tool)',
                    borderColor:
                        'color-mix(in srgb, var(--tool) 35%, transparent)',
                    background:
                        'linear-gradient(135deg, color-mix(in srgb, var(--tool) 22%, transparent), transparent)',
                }}
            >
                <Icon className="size-5" />
            </div>

            <h3 className="font-semibold tracking-tight">{tool.name}</h3>
            <p className="mt-1.5 text-sm leading-relaxed text-muted-foreground">
                {tool.description}
            </p>

            <div className="mt-4 flex flex-wrap gap-1.5">
                {tool.tags.map((tag) => (
                    <Tag key={tag}>{tag}</Tag>
                ))}
            </div>

            <span className="tool-card-arrow mt-4 inline-block text-sm font-semibold">
                Ouvrir →
            </span>
        </Link>
    );
}
