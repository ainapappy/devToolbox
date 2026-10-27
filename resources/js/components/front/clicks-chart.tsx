import { useState, type MouseEvent } from 'react';
import type { DailyClicks } from '@/types';

/**
 * Libellé court du jour (ex. "lun.") depuis la date ISO.
 */
const dayLabel = (date: string): string =>
    new Date(`${date}T00:00:00`).toLocaleDateString('fr-FR', {
        weekday: 'short',
    });

/**
 * Point de la série survolé (pour le tooltip).
 */
interface HoveredBar {
    index: number;
    x: number;
    y: number;
}

/**
 * Graphique en barres des clics sur 7 jours (maquette url-shortener.html) :
 * série unique, gridlines, label sur le maximum uniquement, tooltip au survol.
 */
export function ClicksChart({ data }: { data: DailyClicks[] }) {
    const [hovered, setHovered] = useState<HoveredBar | null>(null);

    const width = 760;
    const height = 250;
    const padding = { top: 20, right: 20, bottom: 30, left: 40 };
    const innerWidth = width - padding.left - padding.right;
    const innerHeight = height - padding.top - padding.bottom;

    const max = Math.max(...data.map((day) => day.count), 1);
    const maxIndex = data.findIndex((day) => day.count === max);

    const step = innerWidth / data.length;
    const barWidth = step * 0.6;

    const yTicks = [0, 0.25, 0.5, 0.75, 1].map((ratio) => ({
        value: Math.round(max * ratio),
        y: padding.top + innerHeight * (1 - ratio),
    }));

    const handleMove = (event: MouseEvent<SVGRectElement>, index: number) => {
        const bounds = event.currentTarget.getBoundingClientRect();
        const container = event.currentTarget
            .closest('svg')
            ?.getBoundingClientRect();
        if (!container) return;
        setHovered({
            index,
            x: bounds.left - container.left + bounds.width / 2,
            y: bounds.top - container.top,
        });
    };

    return (
        <div className="chart-wrap relative">
            <svg
                viewBox={`0 0 ${width} ${height}`}
                role="img"
                aria-label={`Clics par jour sur les 7 derniers jours, maximum ${max}`}
                className="w-full"
            >
                {yTicks.map((tick) => (
                    <line
                        key={tick.value}
                        x1={padding.left}
                        x2={width - padding.right}
                        y1={tick.y}
                        y2={tick.y}
                        className="stroke-border"
                    />
                ))}
                <line
                    x1={padding.left}
                    x2={width - padding.right}
                    y1={height - padding.bottom}
                    y2={height - padding.bottom}
                    className="stroke-foreground/40"
                />

                {yTicks.map((tick) => (
                    <text
                        key={tick.value}
                        x={padding.left - 8}
                        y={tick.y + 4}
                        textAnchor="end"
                        className="fill-muted-foreground text-[11px]"
                    >
                        {tick.value}
                    </text>
                ))}

                {data.map((day, index) => {
                    const barHeight = (day.count / max) * innerHeight;
                    const x =
                        padding.left + step * index + (step - barWidth) / 2;
                    const y = height - padding.bottom - Math.max(barHeight, 2);
                    const delay = 0.2 + index * 0.08;

                    return (
                        <g key={day.date}>
                            <rect
                                x={x}
                                y={y}
                                width={barWidth}
                                height={height - padding.bottom - y}
                                rx={4}
                                fill="var(--chart-1)"
                                className="[transform-origin:bottom] animate-grow [transform-box:fill-box]"
                                style={{ animationDelay: `${delay}s` }}
                            />
                            <text
                                x={x + barWidth / 2}
                                y={height - 8}
                                textAnchor="middle"
                                className="fill-muted-foreground text-[11px]"
                            >
                                {dayLabel(day.date)}
                            </text>
                            {index === maxIndex && (
                                <text
                                    x={x + barWidth / 2}
                                    y={y - 8}
                                    textAnchor="middle"
                                    className="fill-foreground text-xs font-bold"
                                >
                                    {day.count}
                                </text>
                            )}
                            <rect
                                x={padding.left + step * index}
                                y={padding.top}
                                width={step}
                                height={innerHeight}
                                fill="transparent"
                                onMouseMove={(event) =>
                                    handleMove(event, index)
                                }
                                onMouseLeave={() => setHovered(null)}
                            />
                        </g>
                    );
                })}
            </svg>

            {hovered && (
                <div
                    className="pointer-events-none absolute z-10 -translate-x-1/2 rounded-lg border border-border bg-card px-3 py-1.5 text-xs shadow-lg"
                    style={{ left: hovered.x, top: hovered.y - 36 }}
                >
                    <span className="font-semibold">
                        {data[hovered.index].count} clic
                        {data[hovered.index].count > 1 ? 's' : ''}
                    </span>{' '}
                    · {dayLabel(data[hovered.index].date)}
                </div>
            )}
        </div>
    );
}
