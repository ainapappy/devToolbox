import type { PropsWithChildren } from 'react';
import AuthSplitLayout from '@/layouts/auth/auth-split-layout';

/**
 * Layout des pages d'authentification (Phase 09) : split-screen fidèle
 * aux maquettes .knowledge/design — panneau de marque + carte formulaire.
 */
export default function AuthLayout({ children }: PropsWithChildren) {
    return <AuthSplitLayout>{children}</AuthSplitLayout>;
}
