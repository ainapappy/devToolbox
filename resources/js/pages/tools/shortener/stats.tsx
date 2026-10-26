import { Head } from '@inertiajs/react';
import {
    ArrowLeft,
    Globe,
    MousePointerClick,
    Plus,
    Trophy,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { ClicksChart } from '@/components/front/clicks-chart';
import { ToolHero } from '@/components/front/tool-hero';
import { index as indexRoute } from '@/routes/tools/shortener';
import type { ShortenerStatsProps } from '@/types';
import type { ReactNode } from 'react';

function StatTile({
    label,
    value,
    delta,
    icon: Icon,
}: {
    label: string;
    value: ReactNode;
    delta?: ReactNode;
    icon: LucideIcon;
}) {
    return (
        <div className="tool-card rounded-2xl border border-border bg-card p-6">
            <div className="flex items-center justify-between">
                <span className="text-xs font-bold tracking-widest text-muted-foreground uppercase">
                    {label}
                </span>
                <Icon className="size-4 text-accent-1" />
            </div>
            <div className="mt-2 text-3xl font-bold tracking-tight">
                {value}
            </div>
            {delta ? (
                <div className="mt-1 text-sm font-semibold text-emerald-500">
                    {delta}
                </div>
            ) : null}
        </div>
    );
}

export default function Stats({ stats }: ShortenerStatsProps) {
    return (
        <>
            <Head title="Statistiques — URL Shortener" />

            <ToolHero
                step="03"
                title="Statistiques des liens"
                description="Vue d'ensemble des clics : 30 derniers jours, navigateurs, pays et tendance hebdomadaire."
                icon={Trophy}
                color="#eb6834"
            >
                <a
                    href={indexRoute().url}
                    className="btn-ghost mt-4 inline-flex"
                >
                    <ArrowLeft className="size-4" />
                    Retour au raccourcisseur
                </a>
            </ToolHero>

            <section
                aria-label="Indicateurs clés"
                className="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div
                    className="animate-reveal"
                    style={{ animationDelay: '0.15s' }}
                >
                    <StatTile
                        label="Clics (30 jours)"
                        value={stats.clicks_30d.toLocaleString('fr-FR')}
                        icon={MousePointerClick}
                    />
                </div>
                <div
                    className="animate-reveal"
                    style={{ animationDelay: '0.25s' }}
                >
                    <StatTile
                        label="Liens actifs"
                        value={stats.active_links.toLocaleString('fr-FR')}
                        icon={Plus}
                    />
                </div>
                <div
                    className="animate-reveal"
                    style={{ animationDelay: '0.35s' }}
                >
                    <StatTile
                        label="Navigateur n°1"
                        value={stats.top_browser ?? '—'}
                        delta={
                            stats.top_browser_share !== null
                                ? `${stats.top_browser_share} % des clics`
                                : undefined
                        }
                        icon={Trophy}
                    />
                </div>
                <div
                    className="animate-reveal"
                    style={{ animationDelay: '0.45s' }}
                >
                    <StatTile
                        label="Top pays"
                        value={stats.top_country ?? '—'}
                        delta={
                            stats.top_country_share !== null
                                ? `${stats.top_country_share} % des clics`
                                : undefined
                        }
                        icon={Globe}
                    />
                </div>
            </section>

            <section className="tool-card animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.4s]">
                <div className="mb-4 flex items-center justify-between text-xs font-bold tracking-widest text-muted-foreground uppercase">
                    <span>Clics — 7 derniers jours</span>
                    <span className="font-mono normal-case">
                        table clicks · agrégation quotidienne SQL
                    </span>
                </div>
                <ClicksChart data={stats.daily} />
            </section>
        </>
    );
}
