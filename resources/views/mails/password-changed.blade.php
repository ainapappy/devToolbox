@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Confirmation : votre mot de passe DevToolbox vient d\'être mis à jour.',
    'heroColor' => '#0ca30c',
    'heroIcon' => 'password-changed',
    'heroAlt' => 'Coche',
    'heroTitle' => 'Mot de passe mis à jour',
    'heroSubtitle' => 'Votre mot de passe a bien été modifié.<br>Votre compte est de nouveau prêt à l\'emploi.',
    'footerReason' => 'Vous recevez cet email car le mot de passe de votre compte a été modifié.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Le mot de passe de votre compte <strong class="dtb-text" style="color:#0b0b0b;">{{ $userEmail }}</strong>
            vient d'être modifié{{ $at ? ' le '.$at : '' }}.
            Toutes vos autres sessions ont été déconnectées par sécurité.
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td align="center" style="padding:28px 48px 8px 48px;">
            @include('mails.partials.button', [
                'url' => $url,
                'label' => 'Se reconnecter',
            ])
        </td>
    </tr>

    {{-- Encart support --}}
    <tr>
        <td style="padding:24px 48px 36px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'error',
                'slot' => 'Si vous n\'êtes pas à l\'origine de ce changement, votre compte est peut-être compromis : <strong class="dtb-text" style="color:#0b0b0b;">contactez le support immédiatement</strong>.',
            ])
        </td>
    </tr>
@endsection
