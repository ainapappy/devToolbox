import { Link, usePage } from '@inertiajs/react';
import { Link2, Lock, Sparkles, Timer, Zap } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { AuthBackLink } from '@/components/auth/auth-card';
import { ThemeToggle } from '@/components/front/theme-toggle';
import { home } from '@/routes';
import type { FloatCard, Perk } from '@/types/auth-ui';

const PERKS: Perk[] = [
    {
        icon: Lock,
        title: 'Chiffré de bout en bout',
        description: 'Vos secrets ne sont jamais stockés en clair.',
        color: '#a855f7',
    },
    {
        icon: Sparkles,
        title: 'Prêt en quelques secondes',
        description: 'Créez un compte et lancez votre premier outil aussitôt.',
        color: '#22d3ee',
    },
    {
        icon: Timer,
        title: 'Expiration automatique',
        description: 'Liens et données temporaires nettoyés pour vous.',
        color: '#eb6834',
    },
];

const FLOAT_CARDS: FloatCard[] = [
    {
        icon: Lock,
        label: 'secret-9f2a · autodétruit',
        color: '#a855f7',
        position: 'fc-1',
    },
    {
        icon: Link2,
        label: 'dtb.link/x7k2 · 128 clics',
        color: '#eb6834',
        position: 'fc-2',
    },
];

const FLOAT_POSITIONS: Record<FloatCard['position'], string> = {
    'fc-1': 'top-[22%] right-[7%]',
    'fc-2': 'bottom-[20%] right-[18%] [animation-delay:-3.5s]',
};

/**
 * Layout auth split-screen (maquette : .auth-layout) :
 * panneau de marque à gauche, carte formulaire à droite centrée.
 * Responsive : panneau caché < 1024 px, remplacé par le logo compact.
 */
export default function AuthSplitLayout({ children }: PropsWithChildren) {
    const { name } = usePage().props;

    const brandLogo = (className?: string) => (
        <Link
            href={home()}
            className={`inline-flex items-center gap-2 text-[1.05rem] font-extrabold tracking-tight ${className ?? ''}`}
        >
            <Zap className="size-5 text-accent-1" aria-hidden />
            <span className="brand-text">{name}</span>
        </Link>
    );

    return (
        <div className="grid min-h-screen grid-cols-1 lg:grid-cols-[minmax(400px,46%)_1fr]">
            {/* Panneau de marque (maquette : .auth-side) — caché < 1024 px */}
            <aside className="auth-side relative hidden flex-col justify-between gap-8 overflow-hidden border-r border-border p-8 lg:flex xl:p-12">
                {brandLogo()}

                <div className="relative z-1">
                    <span className="mb-4 inline-flex items-center gap-2 rounded-full border border-accent-1/35 px-3 py-1.5 text-xs font-bold tracking-[0.12em] text-accent-1 uppercase">
                        Espace développeur
                    </span>
                    <h2 className="text-3xl leading-[1.12] font-bold tracking-tight xl:text-4xl">
                        Un seul compte pour
                        <br />
                        tous vos outils.
                    </h2>
                    <p className="mt-3 max-w-[46ch] text-muted-foreground">
                        Secure Paste, Mock API, Shortener, Markdown et
                        Placeholder : une seule authentification pour toute la
                        suite DevToolbox.
                    </p>

                    <ul className="mt-7 grid gap-4">
                        {PERKS.map(
                            ({ icon: Icon, title, description, color }) => (
                                <li
                                    key={title}
                                    className="flex items-start gap-3.5"
                                >
                                    <span
                                        className="grid size-[38px] shrink-0 place-items-center rounded-xl border"
                                        style={{
                                            color,
                                            borderColor: `color-mix(in srgb, ${color} 35%, transparent)`,
                                            background: `linear-gradient(135deg, color-mix(in srgb, ${color} 22%, transparent), transparent)`,
                                        }}
                                    >
                                        <Icon
                                            className="size-4.5"
                                            aria-hidden
                                        />
                                    </span>
                                    <div>
                                        <strong className="block text-sm">
                                            {title}
                                        </strong>
                                        <p className="mt-0.5 text-[0.8rem] leading-relaxed text-muted-foreground">
                                            {description}
                                        </p>
                                    </div>
                                </li>
                            ),
                        )}
                    </ul>
                </div>

                {/* Cartes flottantes décoratives (maquette : .float-card) */}
                {FLOAT_CARDS.map(({ icon: Icon, label, color, position }) => (
                    <div
                        key={position}
                        aria-hidden
                        className={`float-card absolute z-0 hidden animate-float items-center gap-2.5 rounded-xl border border-border bg-card/80 px-3.5 py-2.5 text-[0.78rem] font-semibold text-muted-foreground shadow-lg backdrop-blur-sm xl:flex ${FLOAT_POSITIONS[position]}`}
                    >
                        <Icon className="size-4" style={{ color }} />
                        {label}
                    </div>
                ))}

                <p className="font-mono text-xs text-muted-foreground/70">
                    Laravel · Inertia · React · TypeScript · Tailwind
                </p>
            </aside>

            {/* Colonne formulaire (maquette : .auth-main) */}
            <main className="relative flex flex-col items-center justify-center gap-4 px-6 py-8 sm:px-12 lg:px-8">
                <div className="absolute top-4 right-4">
                    <ThemeToggle />
                </div>

                <div className="lg:hidden">{brandLogo()}</div>

                {children}

                <AuthBackLink />
            </main>
        </div>
    );
}
