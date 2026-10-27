{{-- Bouton CTA en table (compatible clients de messagerie).
     $url : lien ; $label : texte ; $color : fond (défaut indigo). --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center" style="background-color:{{ $color ?? '#6366f1' }};border-radius:10px;">
            <a href="{{ $url }}" style="display:inline-block;padding:15px 40px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:16px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:10px;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
