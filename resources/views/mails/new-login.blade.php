@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Une nouvelle connexion à votre compte depuis '.$device.' — '.$date,
    'heroIcon' => 'new-login',
    'heroAlt' => 'Appareil',
    'heroTitle' => 'Nouvelle connexion détectée',
    'heroSubtitle' => 'Votre compte vient d\'être utilisé depuis un nouvel appareil.<br>Vérifiez que c\'était bien vous.',
    'footerReason' => 'Vous recevez cet email car une nouvelle connexion a été détectée sur votre compte.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Une connexion à votre compte <strong class="dtb-text" style="color:#0b0b0b;">{{ $userEmail }}</strong>
            vient d'avoir lieu depuis un appareil que nous ne connaissons pas encore.
        </td>
    </tr>

    {{-- Détails de connexion --}}
    <tr>
        <td style="padding:24px 48px 0 48px;">
            @include('mails.partials.details-list', [
                'rows' => [
                    'Appareil' => $device,
                    'Localisation' => $location,
                    'Adresse IP' => $ip,
                    'Date' => $date,
                ],
            ])
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td align="center" style="padding:28px 48px 8px 48px;">
            @include('mails.partials.button', [
                'url' => $url,
                'label' => 'C\'était moi — Vérifier l\'activité',
            ])
        </td>
    </tr>

    {{-- Encart sécurité --}}
    <tr>
        <td style="padding:24px 48px 36px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'error',
                'slot' => 'Si ce n\'était pas vous, <strong class="dtb-text" style="color:#0b0b0b;">changez votre mot de passe immédiatement</strong> et activez l\'authentification à deux facteurs depuis Réglages → Sécurité.',
            ])
        </td>
    </tr>
@endsection
