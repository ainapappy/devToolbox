@component('mail::message')
# Réinitialisation de votre mot de passe

Bonjour,

Vous avez demandé la réinitialisation du mot de passe de votre compte DevToolbox.
Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :

@component('mail::button', ['url' => $url])
Choisir un nouveau mot de passe
@endcomponent

Ce lien expire dans **{{ $expire }} minutes**.

Si vous n'êtes pas à l'origine de cette demande, aucune action n'est requise :
votre mot de passe actuel reste inchangé.

@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }} · DevToolbox
@endcomponent
@endcomponent
