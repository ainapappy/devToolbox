import { Link, usePage } from '@inertiajs/react';
import { Code, FileText, Image, Link2, Lock, Menu, Zap } from 'lucide-react';
import { useState } from 'react';
import { ThemeToggle } from '@/components/front/theme-toggle';
import { home, login } from '@/routes';
import { edit as profileEdit } from '@/routes/profile';
import { index as mockApi } from '@/routes/tools/mock-api';
import { index as markdown } from '@/routes/tools/markdown';
import { index as paste } from '@/routes/tools/paste';
import { index as placeholder } from '@/routes/tools/placeholder';
import { index as shortener } from '@/routes/tools/shortener';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

/**
 * Navigation des outils du front-office (liens Wayfinder typés).
 */
export const frontNavItems: NavItem[] = [
    { title: 'Dashboard', href: home(), icon: Zap },
    { title: 'Secure Paste', href: paste(), icon: Lock },
    { title: 'Mock API', href: mockApi(), icon: Code },
    { title: 'Shortener', href: shortener(), icon: Link2 },
    { title: 'Markdown', href: markdown(), icon: FileText },
    { title: 'Placeholder', href: placeholder(), icon: Image },
];

/**
 * Navbar du front-office (maquette : .nav) — logo, liens outils, thème, session.
 */
export function FrontNav() {
    const { auth } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();
    const [menuOpen, setMenuOpen] = useState(false);

    const navLinks = (
        <>
            {frontNavItems.map((item) => (
                <Link
                    key={item.title}
                    href={item.href}
                    prefetch
                    className={`rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent/10 hover:text-foreground ${
                        isCurrentUrl(item.href)
                            ? 'font-semibold text-accent-1'
                            : ''
                    }`}
                >
                    {item.title}
                </Link>
            ))}
        </>
    );

    return (
        <header className="sticky top-0 z-50 border-b border-border bg-background/70 backdrop-blur-xl">
            <div className="mx-auto flex w-full max-w-6xl items-center gap-5 px-4 py-3 sm:px-6">
                <Link
                    href={home()}
                    className="inline-flex items-center gap-2 text-base font-extrabold tracking-tight"
                >
                    <span className="flex size-7 items-center justify-center rounded-md bg-linear-to-br from-accent-1 to-accent-3 text-white">
                        <Zap className="size-4" />
                    </span>
                    <span className="bg-linear-to-r from-accent-1 via-accent-2 to-accent-3 bg-clip-text text-transparent">
                        DevToolbox
                    </span>
                </Link>

                <nav className="ml-auto hidden items-center gap-1 md:flex">
                    {navLinks}
                </nav>

                <div className="ml-auto flex items-center gap-2 md:ml-0">
                    {auth.user ? (
                        <Link
                            href={profileEdit()}
                            className="hidden rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground sm:block"
                        >
                            {auth.user.name}
                        </Link>
                    ) : (
                        <Link
                            href={login()}
                            className="hidden rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground sm:block"
                        >
                            Connexion
                        </Link>
                    )}
                    <ThemeToggle />
                    <button
                        type="button"
                        aria-label="Ouvrir le menu"
                        aria-expanded={menuOpen}
                        onClick={() => setMenuOpen((open) => !open)}
                        className="inline-flex size-9 items-center justify-center rounded-lg border border-border bg-card md:hidden"
                    >
                        <Menu className="size-4" />
                    </button>
                </div>
            </div>

            {menuOpen && (
                <nav
                    className="flex flex-col gap-1 border-t border-border px-4 py-3 md:hidden"
                    onClick={() => setMenuOpen(false)}
                >
                    {navLinks}
                </nav>
            )}
        </header>
    );
}
