<div>
    <livewire:core-shared-components::data-table
        model="Bale\Frasasti\Models\Aduan"
        rowView="frasasti::livewire.pages.landlord.aduan-masuk.section.aduan-masuk-row"
        :with="['category']"
        :columns="[
            [
                'key' => 'ref_code',
                'label' => __('Kode Ref'),
                'sortable' => true,
            ],
            [
                'key' => 'category',
                'label' => __('Kategori'),
                'hidden' => 'lg',
            ],
            [
                'key' => 'created_at',
                'label' => __('Tanggal'),
                'hidden' => 'md',
                'sortable' => true,
            ],
            [
                'key' => 'status',
                'label' => __('Status'),
                'hidden' => 'sm',
            ],
            [
                'key' => 'actions',
                'label' => '',
                'sortable' => false,
            ],
        ]"
        :searchable="['ref_code']"
        sortField="created_at"
        sortDirection="desc"
        :perPage="20">

        {{-- Filter status memakai mekanisme activeFilters milik data-table --}}
        <x-slot name="filters">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    {{ __('Status') }}
                </label>
                <select wire:model.live="activeFilters.status"
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="pending">{{ __('Menunggu') }}</option>
                    <option value="processed">{{ __('Diproses') }}</option>
                    <option value="done">{{ __('Selesai') }}</option>
                </select>
            </div>
        </x-slot>

    </livewire:core-shared-components::data-table>
</div>
