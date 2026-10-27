import type { PasswordScore } from '@/types/auth-ui';

const LABELS: Record<Exclude<PasswordScore, 0>, string> = {
    1: 'Trop faible',
    2: 'Faible',
    3: 'Correct',
    4: 'Excellent',
};

const LEVEL_COLORS: Record<Exclude<PasswordScore, 0>, string> = {
    1: '#e5484d',
    2: '#f76b15',
    3: '#ffd60a',
    4: '#22c55e',
};

/**
 * Score d'un mot de passe : longueur 8+, casse mixte, chiffre, symbole.
 * Logique identique au design (assets/app.js) et alignée sur les règles
 * serveur `Password::defaults()` (voir app/Providers/AppServiceProvider.php).
 */
export function scorePassword(password: string): PasswordScore {
    if (!password) {
        return 0;
    }

    let score = 0;
    if (password.length >= 8) {
        score++;
    }
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
        score++;
    }
    if (/\d/.test(password)) {
        score++;
    }
    if (/[^A-Za-z0-9]/.test(password)) {
        score++;
    }

    return score as PasswordScore;
}

type Props = {
    password: string;
    className?: string;
};

/**
 * Jauge de force du mot de passe : 4 segments colorés + label
 * (maquette : .strength avec data-level).
 */
export function StrengthMeter({ password, className }: Props) {
    const score = scorePassword(password);
    const level = score as Exclude<PasswordScore, 0>;

    return (
        <div className={className}>
            <div data-level={score} className="flex gap-1.5">
                {[1, 2, 3, 4].map((segment) => (
                    <span
                        key={segment}
                        className="h-1 flex-1 rounded-sm bg-border transition-colors duration-250"
                        style={
                            segment <= score
                                ? { background: LEVEL_COLORS[level] }
                                : undefined
                        }
                    />
                ))}
            </div>
            <p
                aria-live="polite"
                className="mt-1.5 text-xs text-muted-foreground"
            >
                {score > 0 ? `Force : ${LABELS[level]}` : ''}
            </p>
        </div>
    );
}

export { LABELS as strengthLabels, LEVEL_COLORS as strengthLevelColors };
