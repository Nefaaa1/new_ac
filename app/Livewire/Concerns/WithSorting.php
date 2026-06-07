<?php

namespace App\Livewire\Concerns;

/**
 * Tri de tableau : clic sur une colonne = tri asc, re-clic sur la même = sens inverse.
 * Le composant hôte exploite $sortField / $sortDirection dans sa requête.
 */
trait WithSorting
{
    public string $sortField = '';
    public string $sortDirection = 'asc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    /** Direction normalisée pour les requêtes. */
    protected function sortDir(): string
    {
        return $this->sortDirection === 'desc' ? 'desc' : 'asc';
    }
}
