import { Head, router } from '@inertiajs/react';
import { TriangleAlert, Unlock } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { reveal as revealRoute } from '@/routes/pastes';
import type { PasteShowProps } from '@/types';
import { Lock } from 'lucide-react';

export default function Show({ hash, burn_after_read }: PasteShowProps) {
    const reveal = () => {
        router.post(revealRoute(hash).url, {}, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Secret protégé" />

            <ToolHero
                step="01"
                title="Secret protégé"
                description="Quelqu'un partage un secret avec vous via Secure Paste."
                icon={Lock}
                color="#a855f7"
            />

            <section className="tool-card mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="flex items-start gap-4">
                    <div className="flex size-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/35 bg-amber-500/10 text-amber-500">
                        <TriangleAlert className="size-5" />
                    </div>
                    <div>
                        <h2 className="font-semibold">Avertissement</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {burn_after_read ? (
                                <>
                                    Ce secret sera{' '}
                                    <strong>détruit définitivement</strong> dès
                                    son affichage. Assurez-vous d'être prêt à le
                                    lire et de pouvoir le conserver.
                                </>
                            ) : (
                                <>
                                    Ce secret expirera automatiquement. Une fois
                                    affiché, il restera accessible jusqu'à son
                                    expiration.
                                </>
                            )}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onClick={reveal}
                    className="btn-gradient mt-6 w-full"
                >
                    <Unlock className="size-4" />
                    Afficher le secret
                </button>
            </section>
        </>
    );
}
