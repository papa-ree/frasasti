<?php

namespace Bale\Frasasti\Livewire\Pages\Landlord\AduanMasuk;

use Bale\Frasasti\Models\Aduan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('rakaca::layouts.app')]
#[Title('Edit Aduan')]
class Form extends Component
{
    use AuthorizesRequests;

    public Aduan $aduan;

    public string $status;

    public function mount(Aduan $aduan): void
    {
        $this->authorize('aduan.update');

        $this->aduan = $aduan;
        $this->status = $aduan->status;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'status' => 'required|in:pending,processed,done',
        ]);

        $updateData = ['status' => $this->status];

        if ($this->status !== 'pending') {
            $updateData['handled_at'] = now();
            $updateData['handled_by'] = Auth::user()->uuid;
        }

        $this->aduan->update($updateData);

        $this->dispatch('toast', message: __('Status berhasil diperbarui.'), type: 'success');
    }

    public function render()
    {
        return view('frasasti::livewire.pages.landlord.aduan-masuk.form');
    }
}
