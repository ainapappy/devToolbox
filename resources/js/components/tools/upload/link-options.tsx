import { Eye, EyeOff, Lock, Share2, Timer, Upload } from 'lucide-react';
import { useState } from 'react';
import type { CSSProperties } from 'react';

/** Libellés lisibles des durées servies par le backend ('1h', '24h', '7j'). */
export const durationLabels: Record<string, string> = {
    '1h': '1 h',
    '24h': '24 h',
    '7j': '7 jours',
};

/**
 * Options du lien partageable (maquette : section « Options du lien » de
 * file-upload.html, pattern Secure Paste) : quota de téléchargements,
 * durée de validité et mot de passe facultatif avec bascule de visibilité.
 */
export function LinkOptions({
    downloadOptions,
    durations,
    maxDownloads,
    onMaxDownloadsChange,
    expiration,
    onExpirationChange,
    password,
    onPasswordChange,
}: {
    downloadOptions: number[];
    durations: string[];
    maxDownloads: number | null;
    onMaxDownloadsChange: (value: number | null) => void;
    expiration: string;
    onExpirationChange: (value: string) => void;
    password: string;
    onPasswordChange: (value: string) => void;
}) {
    const [showPassword, setShowPassword] = useState(false);

    return (
        <div style={{ '--tool': '#f59e0b' } as CSSProperties}>
            <div className="grid gap-5 sm:grid-cols-2">
                <div>
                    <span
                        id="lbl-downloads"
                        className="mb-2 block text-[0.8rem] font-semibold"
                    >
                        Téléchargements maximum
                    </span>
                    <div
                        role="radiogroup"
                        aria-labelledby="lbl-downloads"
                        className="flex flex-wrap items-center gap-2"
                    >
                        {downloadOptions.map((option) => (
                            <button
                                key={option}
                                type="button"
                                role="radio"
                                aria-checked={maxDownloads === option}
                                onClick={() => onMaxDownloadsChange(option)}
                                className="option-pill"
                            >
                                <Upload className="size-3.5 rotate-180 text-[color:var(--tool)]" />
                                {option}
                            </button>
                        ))}
                        <button
                            type="button"
                            role="radio"
                            aria-checked={maxDownloads === null}
                            onClick={() => onMaxDownloadsChange(null)}
                            className="option-pill"
                        >
                            <Share2 className="size-3.5 text-[color:var(--tool)]" />
                            Illimité
                        </button>
                    </div>
                </div>

                <div>
                    <span
                        id="lbl-duration"
                        className="mb-2 block text-[0.8rem] font-semibold"
                    >
                        Durée de validité
                    </span>
                    <div
                        role="radiogroup"
                        aria-labelledby="lbl-duration"
                        className="flex flex-wrap items-center gap-2"
                    >
                        {durations.map((duration) => (
                            <button
                                key={duration}
                                type="button"
                                role="radio"
                                aria-checked={expiration === duration}
                                onClick={() => onExpirationChange(duration)}
                                className="option-pill"
                            >
                                <Timer className="size-3.5 text-[color:var(--tool)]" />
                                {durationLabels[duration] ?? duration}
                            </button>
                        ))}
                    </div>
                </div>
            </div>

            <div className="mt-5">
                <label
                    htmlFor="link-password"
                    className="mb-2 block text-[0.8rem] font-semibold"
                >
                    Mot de passe
                </label>
                <div className="relative">
                    <Lock className="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        id="link-password"
                        type={showPassword ? 'text' : 'password'}
                        value={password}
                        maxLength={255}
                        autoComplete="new-password"
                        placeholder="facultatif — protège l'ouverture du lien"
                        onChange={(event) =>
                            onPasswordChange(event.target.value)
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
                        onClick={() => setShowPassword((visible) => !visible)}
                        className="absolute top-1/2 right-2 grid size-8 -translate-y-1/2 place-items-center rounded-lg text-muted-foreground transition-colors hover:text-foreground"
                    >
                        {showPassword ? (
                            <EyeOff className="size-4" />
                        ) : (
                            <Eye className="size-4" />
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}
