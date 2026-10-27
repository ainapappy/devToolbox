import { Head, Link } from '@inertiajs/react';
import { Hourglass, Sparkles, Upload } from 'lucide-react';
import { ToolHero } from '@/components/front/tool-hero';
import { index as uploadIndex } from '@/routes/tools/upload';

/**
 * Lien File Drop indisponible : UUID inconnu, expiré ou téléchargements
 * épuisés — même UX que la page expired des pastes, accent de l'outil.
 */
export default function Expired() {
    return (
        <>
            <Head title="Lien indisponible" />

            <ToolHero
                step="06"
                title="Lien indisponible"
                description="Ce fichier n'est plus accessible."
                icon={Upload}
                color="#f59e0b"
            />

            <section className="tool-card mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                <div className="flex items-start gap-4">
                    <div className="flex size-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/35 bg-amber-500/10 text-amber-500">
                        <Hourglass className="size-5" />
                    </div>
                    <div>
                        <h2 className="font-semibold">
                            Ce lien n'est plus actif
                        </h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Ce fichier n'est plus disponible : le lien a expiré
                            ou son quota de téléchargements est épuisé. Il ne
                            peut pas être récupéré.
                        </p>
                    </div>
                </div>

                <Link
                    href={uploadIndex.url()}
                    className="btn-gradient mt-6 w-full"
                >
                    <Sparkles className="size-4" />
                    Envoyer un nouveau fichier
                </Link>
            </section>
        </>
    );
}
