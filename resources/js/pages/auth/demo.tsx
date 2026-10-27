import { Lock } from 'lucide-react';
import { useState } from 'react';
import { Alert } from '@/components/auth/alert';
import { AuthCard, AuthHeader } from '@/components/auth/auth-card';
import { CheckboxField } from '@/components/auth/checkbox-field';
import { Divider } from '@/components/auth/divider';
import { InputGroup } from '@/components/auth/input-group';
import { OtpInput } from '@/components/auth/otp-input';
import { PasswordInput } from '@/components/auth/password-input';
import { SocialRow } from '@/components/auth/social-buttons';
import { StrengthMeter } from '@/components/auth/strength-meter';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Head } from '@inertiajs/react';

/**
 * Page de démonstration des composants auth (Phase 09, temporaire).
 * À retirer une fois les flux Fortify intégrés (Phases 10→12).
 */
export default function AuthDemo() {
    const [password, setPassword] = useState('');
    const [otp, setOtp] = useState('');

    return (
        <>
            <Head title="Démo des composants auth" />

            <AuthCard>
                <AuthHeader
                    icon={Lock}
                    title="Composants auth"
                    subtitle="Démonstration visuelle avant intégration des flux Fortify."
                />

                <div className="grid gap-6">
                    {/* États des alertes */}
                    <div className="grid gap-2">
                        <Alert variant="info">
                            Info — votre lien expire dans 60 minutes.
                        </Alert>
                        <Alert variant="success">
                            Succès — e-mail de vérification envoyé.
                        </Alert>
                        <Alert variant="error">
                            Erreur — ces identifiants ne correspondent à aucun
                            compte.
                        </Alert>
                    </div>

                    <Divider>Champs</Divider>

                    {/* InputGroup + PasswordInput */}
                    <div className="grid gap-2">
                        <Label htmlFor="demo-email">Adresse e-mail</Label>
                        <InputGroup
                            icon={Lock}
                            id="demo-email"
                            type="email"
                            placeholder="vous@exemple.com"
                            autoComplete="off"
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="demo-password">Mot de passe</Label>
                        <PasswordInput
                            id="demo-password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="••••••••"
                            autoComplete="new-password"
                        />
                        <StrengthMeter password={password} />
                    </div>

                    <CheckboxField
                        id="demo-terms"
                        name="terms"
                        label={
                            <>
                                J'accepte les conditions d'utilisation du
                                service.
                            </>
                        }
                    />

                    <Divider>Code OTP</Divider>

                    {/* OtpInput */}
                    <div className="grid justify-items-center gap-2">
                        <OtpInput value={otp} onChange={setOtp} />
                        <p className="text-xs text-muted-foreground">
                            Collage d'un code complet supporté ({otp.length}/6)
                        </p>
                    </div>

                    <Divider>Connexion sociale</Divider>
                    <SocialRow />

                    <Button
                        type="button"
                        className="btn-gradient h-11 w-full rounded-xl"
                    >
                        Bouton démo
                    </Button>
                </div>
            </AuthCard>
        </>
    );
}
