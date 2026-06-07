<?php

namespace App\Livewire\Admin;

use App\Support\Search\Search;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $term = '';

    public function clear(): void
    {
        $this->term = '';
    }

    public function render()
    {
        $data = Search::query($this->term);

        return view('livewire.admin.global-search', [
            'total'    => $data['total'],
            'groups'   => $data['groups'],
            'limit'    => $data['limit'],
            'hasQuery' => mb_strlen(trim($this->term)) >= Search::MIN_CHARS,
        ]);
    }
}
