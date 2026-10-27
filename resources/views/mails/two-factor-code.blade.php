@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Votre code de vérification est '.$code.' — il expire dans 10 minutes.',
    'heroIcon' => 'two-factor-code',
    'heroAlt' => 'Bouclier',
    'heroTitle' => 'Votre code de vérification',
    'heroSubtitle' => 'Une tentative de connexion vient d\'être détectée.<br>Saisissez ce code pour confirmer que c\'est bien vous.',
    'footerReason' => 'Vous recevez cet email car une connexion a été initiée avec cette adresse.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Voici votre code de vérification à usage unique :
        </td>
    </tr>

    {{-- Code OTP --}}
    <tr>
        <td align="center" style="padding:12px 48px 8px 48px;">
            @include('mails.partials.otp-code', ['code' => $code])
        </td>
    </tr>

    {{-- Encart expiration --}}
    <tr>
        <td style="padding:24px 48px 36px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'error',
                'slot' => 'Ce code expire dans <strong class="dtb-text" style="color:#0b0b0b;">10 minutes</strong> et ne fonctionne qu\'une seule fois. Vous utilisez déjà une application d\'authentification ? Celle-ci reste la méthode recommandée. Si vous n\'êtes pas à l\'origine de cette tentative, changez votre mot de passe immédiatement.',
            ])
        </td>
    </tr>
@endsection
