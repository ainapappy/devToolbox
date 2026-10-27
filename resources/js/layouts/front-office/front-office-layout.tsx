import { FrontNav } from '@/components/front/front-nav';
import type { AppLayoutProps } from '@/types';

/**
 * Template front-office : navbar publique + footer, sur fond à halo animé.
 * (Le back-office garde le layout sidebar du starter kit.)
 */
export default function FrontOfficeLayout({ children }: AppLayoutProps) {
    return (
        <div className="relative flex min-h-screen flex-col">
            {/* Halo animé en fond de page (maquette : .bg-glow) */}
            <div
                aria-hidden
                className="pointer-events-none fixed inset-0 -z-10 overflow-hidden"
            >
                <div className="absolute -top-[12vw] -left-[8vw] size-[45vw] animate-drift rounded-full bg-accent-1 opacity-[0.22] blur-[120px]" />
                <div className="absolute -right-[10vw] -bottom-[14vw] size-[45vw] animate-drift rounded-full bg-accent-3 opacity-[0.22] blur-[120px] [animation-delay:-9s]" />
            </div>

            <FrontNav />

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 sm:py-12">
                {children}
            </main>

            <footer className="flex items-center justify-center gap-2 px-4 py-8 text-center text-sm text-muted-foreground">
                <span
                    aria-hidden
                    className="size-2 shrink-0 animate-led rounded-full"
                />
                Développé par{' '}
                <a
                    href="https://ainatrix.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    className="underline-offset-2 transition-colors hover:text-foreground hover:underline"
                >
                    ainatrix.com
                </a>
            </footer>
        </div>
    );
}
