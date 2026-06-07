<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Notepad extends Component
{
    public string $content = '';

    public function mount(): void
    {
        $this->content = auth()->user()->note?->content ?? '';
    }

    /**
     * Sauvegarde automatique à chaque modification (debounce côté vue).
     */
    public function updatedContent(): void
    {
        auth()->user()->note()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['content' => $this->content],
        );
    }

    public function render()
    {
        return view('livewire.admin.notepad');
    }
}
