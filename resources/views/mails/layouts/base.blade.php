{{-- Layout des emails transactionnels DevToolbox.
     Fidèle à .knowledge/design/mails/ : 100 % tables + styles inline,
     largeur 600 px, fond gris #f3f3f1, carte blanche, bandeau accent.
     Dark mode basique via <style> embarqué (dégradation propre acceptée). --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? 'DevToolbox' }}</title>
    <style>
        @media (prefers-color-scheme: dark) {
            .dtb-body { background-color: #0d0d0d !important; }
            .dtb-card { background-color: #1a1a19 !important; }
            .dtb-hero { background-color: #232338 !important; }
            .dtb-text { color: #f0efec !important; }
            .dtb-secondary { color: #b8b6ad !important; }
            .dtb-muted { color: #898781 !important; }
            .dtb-callout { background-color: #232322 !important; }
            .dtb-callout-text { color: #c3c2b7 !important; }
        }
    </style>
</head>
<body class="dtb-body" style="margin:0;padding:0;background-color:#f3f3f1;">
{{-- Preheader masqué (aperçu des clients de messagerie) --}}
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
    {{ $preheader ?? '' }}
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dtb-body" style="background-color:#f3f3f1;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            {{-- Marque --}}
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
                <tr>
                    <td style="padding-right:8px;vertical-align:middle;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                            <td width="14" height="14" style="width:14px;height:14px;background-color:#6366f1;border-radius:4px;font-size:0;line-height:0;">&nbsp;</td>
                        </tr></table>
                    </td>
                    <td style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:18px;font-weight:700;color:#0b0b0b;vertical-align:middle;" class="dtb-text">
                        Dev<span style="color:#6366f1;">Toolbox</span>
                    </td>
                </tr>
            </table>

            {{-- Carte principale --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="dtb-card" style="width:600px;max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;">
                {{-- Bandeau accent --}}
                <tr>
                    <td height="6" style="height:6px;background-color:{{ $heroColor ?? '#6366f1' }};font-size:0;line-height:0;">&nbsp;</td>
                </tr>

                {{-- Héros --}}
                <tr>
                    <td align="center" class="dtb-hero" style="padding:40px 48px 8px 48px;background-color:#eef2ff;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                            <td width="72" height="72" align="center" valign="middle" style="width:72px;height:72px;">
                                @if (!empty($heroIcon))
                                    <img src="{{ asset('images/mails/icon-'.$heroIcon.'.png') }}" width="72" height="72" alt="{{ $heroAlt ?? '' }}" style="display:block;border:none;">
                                @endif
                            </td>
                        </tr></table>
                        <h1 class="dtb-text" style="margin:20px 0 8px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:26px;line-height:34px;font-weight:700;color:#0b0b0b;">
                            {{ $heroTitle ?? '' }}
                        </h1>
                        <p class="dtb-secondary" style="margin:0 0 28px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:24px;color:#52514e;">
                            {!! $heroSubtitle ?? '' !!}
                        </p>
                    </td>
                </tr>

                {{-- Corps (slot) --}}
                @yield('body')
            </table>

            {{-- Pied de page --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;">
                <tr>
                    <td align="center" class="dtb-muted" style="padding:28px 24px 0 24px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;line-height:20px;color:#898781;">
                        DevToolbox · Bâtissez plus vite, codez moins.<br>
                        {{ $footerReason ?? 'Vous recevez cet email car un compte a été créé avec cette adresse.' }}
                        <br><br>
                        <a href="{{ config('app.url') }}" class="dtb-muted" style="color:#898781;text-decoration:underline;">Support</a> &nbsp;·&nbsp;
                        <a href="{{ config('app.url') }}" class="dtb-muted" style="color:#898781;text-decoration:underline;">Confidentialité</a>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>
