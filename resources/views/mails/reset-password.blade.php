@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Votre lien de réinitialisation est prêt — il expire dans 60 minutes.',
    'heroIcon' => 'reset-password',
    'heroAlt' => 'Clé',
    'heroTitle' => 'Mot de passe oublié&nbsp;?',
    'heroSubtitle' => 'Pas de panique, on s\'en occupe.<br>Choisissez un nouveau mot de passe en un clin d\'œil.',
    'footerReason' => 'Vous recevez cet email car une demande de réinitialisation a été faite avec cette adresse.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Vous avez demandé la réinitialisation du mot de passe de votre compte
            <strong class="dtb-text" style="color:#0b0b0b;">{{ $userEmail }}</strong>.
            Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td align="center" style="padding:28px 48px 8px 48px;">
            @include('mails.partials.button', [
                'url' => $url,
                'label' => 'Réinitialiser mon mot de passe',
            ])
        </td>
    </tr>

    {{-- Lien alternatif --}}
    <tr>
        <td align="center" class="dtb-muted" style="padding:16px 48px 0 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:20px;color:#898781;">
            Le bouton ne fonctionne pas ? Copiez-collez ce lien :<br>
            <a href="{{ $url }}" style="color:#6366f1;text-decoration:underline;word-break:break-all;">{{ $url }}</a>
        </td>
    </tr>

    {{-- Encart expiration + alerte --}}
    <tr>
        <td style="padding:24px 48px 36px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'error',
                'slot' => 'Ce lien est à usage unique et valable <strong class="dtb-text" style="color:#0b0b0b;">60 minutes</strong>. Si vous n\'êtes pas à l\'origine de cette demande, <strong class="dtb-text" style="color:#0b0b0b;">ignorez cet email</strong> : votre mot de passe actuel reste inchangé.',
            ])
        </td>
    </tr>
@endsection
