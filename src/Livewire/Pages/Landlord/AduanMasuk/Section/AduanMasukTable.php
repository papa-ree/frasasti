<?php

namespace Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk\Section;

use Livewire\Attributes\On;
use Livewire\Component;

class AduanMasukTable extends Component
{
    #[On('refresh-aduan-table')]
    public function render()
    {
        return view('frasasti::livewire.pages.landlord.aduan-masuk.section.aduan-masuk-table');
    }
}
