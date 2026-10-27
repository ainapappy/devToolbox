{{-- Code OTP à 6 chiffres encadré (maquette : two-factor-code).
     $code : code en clair (formaté avec espaces par groupes de 3). --}}
@php($formatted = trim(chunk_split($code, 3, ' ')))
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
    <tr>
        <td align="center" style="background-color:#f9f9f7;border:2px solid #6366f1;border-radius:12px;padding:18px 32px;font-family:'Courier New',Courier,monospace;font-size:40px;font-weight:700;letter-spacing:12px;color:#0b0b0b;" class="dtb-text">
            {{ $formatted }}
        </td>
    </tr>
</table>
