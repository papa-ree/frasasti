<?php

namespace Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('rakaca::layouts.app')]
#[Title('Aduan Masuk')]
class Index extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('aduan.read');
    }

    public function render()
    {
        return view('frasasti::livewire.pages.landlord.aduan-masuk.index');
    }
}
