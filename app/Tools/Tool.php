<?php

namespace App\Tools;

/**
 * Décrit un outil de la boîte à outils, exposé au dashboard et à la navigation.
 */
final class Tool
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $name,
        public readonly string $description,
        public readonly array $tags,
        public readonly string $color,
        public readonly string $icon,
    ) {}

    /**
     * Registre des outils de DevToolbox (ordre du dashboard).
     *
     * @return list<self>
     */
    public static function all(): array
    {
        return [
            new self(
                slug: 'paste',
                name: 'Secure Paste',
                description: 'Partagez du texte confidentiel via un lien unique qui s\'autodétruit après lecture ou expiration.',
                tags: ['Scheduling', 'Hash'],
                color: '#a855f7',
                icon: 'lock-closed',
            ),
            new self(
                slug: 'mock-api',
                name: 'JSON Mock API',
                description: 'Créez des endpoints JSON publics en quelques secondes pour tester vos applications frontend.',
                tags: ['Eloquent JSON', 'HTTP'],
                color: '#22d3ee',
                icon: 'code',
            ),
            new self(
                slug: 'shortener',
                name: 'URL Shortener',
                description: 'Raccourcissez vos liens et suivez les clics, navigateurs et pays depuis un tableau de bord.',
                tags: ['Analytics', 'One-to-Many'],
                color: '#eb6834',
                icon: 'link-2',
            ),
            new self(
                slug: 'markdown',
                name: 'Markdown Previewer',
                description: 'Éditez du Markdown avec un rendu HTML en temps réel, puis partagez-le via un simple lien.',
                tags: ['CommonMark', 'React'],
                color: '#e87ba4',
                icon: 'file-text',
            ),
            new self(
                slug: 'placeholder',
                name: 'SVG Placeholder',
                description: 'Générez des images de remplacement à la volée, dimensionnées et colorées via l\'URL.',
                tags: ['SVG', 'Cache'],
                color: '#1baf7a',
                icon: 'image',
            ),
            new self(
                slug: 'upload',
                name: 'File Drop',
                description: 'Glissez vos fichiers, puis partagez un lien protégé : téléchargements limités, expiration et mot de passe facultatif.',
                tags: ['Storage', 'Validation'],
                color: '#f59e0b',
                icon: 'upload',
            ),
        ];
    }

    /**
     * Représentation sérialisable pour les props Inertia.
     *
     * @return array{slug: string, name: string, description: string, tags: list<string>, color: string, icon: string}
     */
    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'tags' => $this->tags,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }
}
