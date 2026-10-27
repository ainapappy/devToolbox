@component('mail::message')
# Vérifiez votre adresse e-mail

Bonjour,

Bienvenue sur **{{ config('app.name') }}** ! Confirmez votre adresse e-mail
pour accéder à tous les outils :

@component('mail::button', ['url' => $url])
Vérifier mon adresse e-mail
@endcomponent

Ce lien expire dans **{{ $expire }} heure{{ $expire > 1 ? 's' : '' }}**.

Si vous n'êtes pas à l'origine de ce compte, ignorez simplement cet e-mail.

@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }} · DevToolbox
@endcomponent
@endcomponent
