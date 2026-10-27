import { Head, Link } from '@inertiajs/react';
import { Hourglass, Lock, Sparkles } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { index as pasteIndex } from '@/routes/tools/paste';

export default function Expired() {
    return (
        <>
            <Head title="Lien expiré" />

            <ToolHero
                step="01"
                title="Lien expiré"
                description="Ce secret n'est plus accessible."
                icon={Lock}
                color="#a855f7"
            />

            <section className="tool-card mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="flex items-start gap-4">
                    <div className="flex size-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/35 bg-amber-500/10 text-amber-500">
                        <Hourglass className="size-5" />
                    </div>
                    <div>
                        <h2 className="font-semibold">Ce lien a expiré</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Ce secret n'est plus disponible : le lien a expiré
                            ou a déjà été utilisé. Il ne peut pas être récupéré.
                        </p>
                    </div>
                </div>

                <Link
                    href={pasteIndex.url()}
                    className="btn-gradient mt-6 w-full"
                >
                    <Sparkles className="size-4" />
                    Créer un nouveau secret
                </Link>
            </section>
        </>
    );
}
