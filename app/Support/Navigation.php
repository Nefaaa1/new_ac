<?php

namespace App\Support;

class Navigation
{
    /**
     * Groupes affichés dans la sidebar admin.
     *
     * @return array<int, array{title: string, color: string, items: array<int, array{route: string, label: string, icon: string}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'title' => 'Informations',
                'color' => 'primary',
                'items' => [
                    ['route' => 'admin.sites',    'label' => 'Sites',    'icon' => 'globe'],
                    ['route' => 'admin.contrats', 'label' => 'Contrats', 'icon' => 'file-text'],
                    ['route' => 'admin.clients',  'label' => 'Clients',  'icon' => 'users'],
                    ['route' => 'admin.actions',  'label' => 'Actions',  'icon' => 'zap'],
                    ['route' => 'admin.tickets',  'label' => 'Tickets',  'icon' => 'ticket'],
                    ['route' => 'admin.chatbots', 'label' => 'Chatbots', 'icon' => 'bot'],
                ],
            ],
            [
                'title' => 'Récap mensuel',
                'color' => 'secondary',
                'items' => [
                    ['route' => 'admin.recap.actions', 'label' => 'Récap — Actions', 'icon' => 'trending-up'],
                    ['route' => 'admin.recap.tickets', 'label' => 'Récap — Tickets', 'icon' => 'pie-chart'],
                ],
            ],
            [
                'title' => 'Gestion',
                'color' => 'rose',
                'can' => 'manage-admins', // groupe affiché uniquement aux admins "accès total"
                'items' => [
                    ['route' => 'admin.gestion.admins',          'label' => 'Administrateurs', 'icon' => 'shield-user'],
                    ['route' => 'admin.gestion.equipes',         'label' => 'Équipes',         'icon' => 'users-round'],
                    ['route' => 'admin.gestion.statuts',         'label' => 'Statuts sites',   'icon' => 'tags'],
                    ['route' => 'admin.gestion.ticket-statuts',  'label' => 'Statuts tickets', 'icon' => 'ticket'],
                    ['route' => 'admin.gestion.devis-statuts',   'label' => 'Statuts devis',   'icon' => 'file-check'],
                ],
            ],
        ];
    }

    /**
     * Toutes les pages connues (sidebar + dashboard + profil), indexées par nom de route.
     *
     * @return array<string, array{label: string, icon: string}>
     */
    public static function pages(): array
    {
        $pages = [
            'admin.dashboard' => ['label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            'admin.profil'    => ['label' => 'Profil',    'icon' => 'circle-user'],
        ];

        foreach (static::groups() as $group) {
            foreach ($group['items'] as $item) {
                $pages[$item['route']] = ['label' => $item['label'], 'icon' => $item['icon']];
            }
        }

        return $pages;
    }

    /**
     * Métadonnées (label, icon) d'une page, ou null si inconnue.
     *
     * @return array{label: string, icon: string}|null
     */
    public static function find(?string $route): ?array
    {
        return $route ? (static::pages()[$route] ?? null) : null;
    }
}
