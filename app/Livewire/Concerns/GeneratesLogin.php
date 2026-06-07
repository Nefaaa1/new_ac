<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;

/**
 * Génère automatiquement le login à partir du prénom + nom (ex. "jean.dupont")
 * tant qu'on crée un utilisateur (editingId === null) et que le login n'a pas
 * été modifié à la main. Toujours éditable ; aucune génération en édition.
 *
 * Le composant hôte doit exposer : ?int $editingId, string $nom, string $prenom, string $login.
 */
trait GeneratesLogin
{
    /** L'utilisateur a saisi le login manuellement → on n'écrase plus. */
    public bool $loginManual = false;

    public function updatedNom(): void
    {
        $this->autoGenerateLogin();
    }

    public function updatedPrenom(): void
    {
        $this->autoGenerateLogin();
    }

    public function updatedLogin(): void
    {
        // Saisie manuelle : on bloque l'auto-génération (réactivée si vidé).
        $this->loginManual = $this->login !== '';
    }

    protected function autoGenerateLogin(): void
    {
        if ($this->editingId !== null || $this->loginManual) {
            return;
        }

        if ($this->nom === '' || $this->prenom === '') {
            return;
        }

        $this->login = Str::slug($this->prenom.' '.$this->nom, '.');
    }
}
