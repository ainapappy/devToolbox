import { Head } from '@inertiajs/react';
import { Eye, EyeOff, Lock, TriangleAlert, Unlock, Upload } from 'lucide-react';
import { useState } from 'react';
import { ToolHero } from '@/components/front/tool-hero';
import { download } from '@/routes/tools/upload';
import type { UploadLockedProps } from '@/types';

/**
 * Déverrouillage d'un fichier protégé : le mot de passe est renvoyé en
 * paramètre de requête `password` sur la route publique de téléchargement
 * (GET). Un formulaire natif est volontairement utilisé pour que la
 * réponse puissent être un flux de téléchargement et non une visite XHR.
 */
export default function Locked({ uuid, error }: UploadLockedProps) {
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);

    return (
        <>
            <Head title="Fichier protégé" />

            <ToolHero
                step="06"
                title="Fichier protégé"
                description="Quelqu'un partage un fichier avec vous via File Drop."
                icon={Upload}
                color="#f59e0b"
            />

            <section className="tool-card mx-auto max-w-2xl animate-reveal rounded-2xl border border-border bg-card p-6 [animation-delay:.15s]">
                {error && (
                    <div role="alert" className="mb-6 flex items-start gap-4">
                        <div className="flex size-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/35 bg-amber-500/10 text-amber-500">
                            <TriangleAlert className="size-5" />
                        </div>
                        <div>
                            <h2 className="font-semibold">Accès refusé</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                {error}
                            </p>
                        </div>
                    </div>
                )}

                <form
                    action={download.url({ uuid })}
                    method="get"
                    className="flex flex-col gap-4"
                >
                    <label
                        htmlFor="unlock-password"
                        className="block text-[0.8rem] font-semibold"
                    >
                        Mot de passe du lien
                    </label>
                    <div className="relative">
                        <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            id="unlock-password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={password}
                            required
                            autoFocus
                            autoComplete="off"
                            placeholder="Saisissez le mot de passe partagé par l'expéditeur"
                            onChange={(event) =>
                                setPassword(event.target.value)
                            }
                            style={
                                { '--tool': '#f59e0b' } as React.CSSProperties
                            }
                            className="w-full rounded-xl border border-input bg-background py-2.5 pr-12 pl-10 text-sm transition-colors focus:border-[color:var(--tool)] focus:ring-4 focus:ring-[color-mix(in_srgb,var(--tool)_18%,transparent)] focus:outline-none"
                        />
                        <button
                            type="button"
                            aria-label={
                                showPassword
                                    ? 'Masquer le mot de passe'
                                    : 'Afficher le mot de passe'
                            }
                            onClick={() =>
                                setShowPassword((visible) => !visible)
                            }
                            className="absolute top-1/2 right-2 grid size-8 -translate-y-1/2 place-items-center rounded-lg text-muted-foreground transition-colors hover:text-foreground"
                        >
                            {showPassword ? (
                                <EyeOff className="size-4" />
                            ) : (
                                <Eye className="size-4" />
                            )}
                        </button>
                    </div>

                    <button
                        type="submit"
                        disabled={password === ''}
                        className="btn-gradient w-full"
                    >
                        <Unlock className="size-4" />
                        Déverrouiller et télécharger
                    </button>
                </form>
            </section>
        </>
    );
}
