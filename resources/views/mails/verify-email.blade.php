@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Un dernier clic et votre compte DevToolbox est actif — l\'offre est valable 60 minutes.',
    'heroIcon' => 'verify-email',
    'heroAlt' => 'Enveloppe',
    'heroTitle' => 'Confirmez votre adresse email',
    'heroSubtitle' => 'Bienvenue dans la boîte à outils des développeurs.<br>Il ne reste qu\'une étape pour activer votre compte.',
    'footerReason' => 'Vous recevez cet email car un compte a été créé avec cette adresse.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Merci de votre inscription ! Cliquez sur le bouton ci-dessous pour vérifier votre adresse
            <strong class="dtb-text" style="color:#0b0b0b;">{{ $userEmail }}</strong> et débloquer l'accès aux outils de la toolbox.
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td align="center" style="padding:28px 48px 8px 48px;">
            @include('mails.partials.button', [
                'url' => $url,
                'label' => 'Vérifier mon adresse email',
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

    {{-- Encart expiration --}}
    <tr>
        <td style="padding:24px 48px 36px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'info',
                'slot' => 'Ce lien est valable <strong class="dtb-text" style="color:#0b0b0b;">60 minutes</strong>. Si vous n\'êtes pas à l\'origine de cette inscription, ignorez simplement cet email.',
            ])
        </td>
    </tr>
@endsection
