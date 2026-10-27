{{-- Liste de détails clé/valeur (maquette : alerte de connexion).
     $rows : tableau label => valeur (échappés). --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="dtb-callout" style="background-color:#f9f9f7;border-radius:10px;">
    <tr>
        <td style="padding:18px 22px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;line-height:24px;color:#52514e;" class="dtb-callout-text">
            @foreach ($rows as $label => $value)
                <strong class="dtb-text" style="color:#0b0b0b;">{{ $label }} :</strong> {{ $value }}@if (!($loop->last))<br>@endif
            @endforeach
        </td>
    </tr>
</table>
