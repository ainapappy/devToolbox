{{-- Encart latéral coloré (maquette : callout).
     $variant : info (indigo) | error (rouge #e5484d) | success (#0ca30c).
     $slot : contenu. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dtb-callout" style="background-color:#f9f9f7;border-left:4px solid {{ $variant === 'error' ? '#e5484d' : ($variant === 'success' ? '#0ca30c' : '#6366f1') }};border-radius:8px;">
    <tr>
        <td class="dtb-callout-text" style="padding:14px 18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:20px;color:#52514e;">
            {!! $slot !!}
        </td>
    </tr>
</table>
