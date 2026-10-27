@extends('mails.layouts.base', [
    'subject' => $subject,
    'preheader' => 'Votre compte est prêt : découvrez les outils qui vont accélérer votre quotidien de dev.',
    'heroIcon' => 'welcome',
    'heroAlt' => 'Étoile',
    'heroTitle' => 'Bienvenue à bord, '.$userName.'&nbsp;!',
    'heroSubtitle' => 'Votre compte est actif.<br>Il est temps de découvrir tout ce que DevToolbox peut faire pour vous.',
    'footerReason' => 'Vous recevez cet email car votre adresse vient d\'être vérifiée.',
])

@section('body')
    {{-- Corps --}}
    <tr>
        <td class="dtb-secondary" style="padding:32px 48px 8px 48px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
            Bonjour <strong class="dtb-text" style="color:#0b0b0b;">{{ $userName }}</strong>,
            <br><br>
            Votre adresse <strong class="dtb-text" style="color:#0b0b0b;">{{ $userEmail }}</strong> est vérifiée :
            vous venez de rejoindre les développeurs qui gagnent du temps chaque jour.
            Voici un aperçu de ce qui vous attend :
        </td>
    </tr>

    {{-- Grille des outils (tables imbriquées 2 colonnes) --}}
    <tr>
        <td style="padding:20px 48px 8px 48px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    @foreach (array_chunk($tools, 2) as $row)
                        </tr>
                        <tr>
                        @foreach ($row as $tool)
                            <td width="50%" style="padding:6px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dtb-callout" style="background-color:#f9f9f7;border-radius:10px;">
                                    <tr><td style="padding:16px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;line-height:22px;color:#52514e;" class="dtb-callout-text">
                                        <strong class="dtb-text" style="color:#0b0b0b;">{{ $tool['name'] }}</strong><br>{{ $tool['description'] }}
                                    </td></tr>
                                </table>
                            </td>
                        @endforeach
                        @if (count($row) === 1)<td width="50%">&nbsp;</td>@endif
                    @endforeach
                </tr>
            </table>
        </td>
    </tr>

    {{-- Astuce 2FA --}}
    <tr>
        <td style="padding:16px 48px 8px 48px;">
            @include('mails.partials.callout', [
                'variant' => 'info',
                'slot' => '<strong class="dtb-text" style="color:#0b0b0b;">Astuce :</strong> activez l\'authentification à deux facteurs depuis <strong class="dtb-text" style="color:#0b0b0b;">Réglages → Sécurité</strong> pour protéger votre compte en 2 minutes.',
            ])
        </td>
    </tr>

    {{-- CTA --}}
    <tr>
        <td align="center" style="padding:24px 48px 36px 48px;">
            @include('mails.partials.button', [
                'url' => $url,
                'label' => 'Découvrir mes outils',
            ])
        </td>
    </tr>
@endsection
