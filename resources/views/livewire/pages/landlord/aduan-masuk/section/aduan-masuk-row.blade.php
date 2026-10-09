<tr wire:key="aduan-row-{{ $record->id }}"
    class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors duration-150">

    {{-- Kode Ref + Nama (data sensitif) --}}
    <td class="px-4 py-3.5 w-full max-w-0 sm:max-w-none sm:w-auto">
        <div class="flex items-center gap-3">
            <div
                class="size-9 rounded-lg bg-linear-to-br from-indigo-50 to-purple-100 dark:from-indigo-900/20 dark:to-purple-800/30 flex items-center justify-center shrink-0 border border-indigo-200/50 dark:border-indigo-700/30">
                <x-lucide-inbox class="size-4 text-indigo-600 dark:text-indigo-400" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="block font-mono text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                    {{ $record->ref_code }}
                </span>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    {{ $record->nama_lengkap }}
                </p>
            </div>
        </div>
    </td>

    {{-- Kategori --}}
    <td class="px-4 py-3.5 hidden lg:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs">
            {{ $record->category?->name ?? '—' }}
        </div>
    </td>

    {{-- Tanggal --}}
    <td class="px-4 py-3.5 hidden md:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ $record->created_at }}
        </div>
    </td>

    {{-- Status --}}
    <td class="px-4 py-3.5 hidden sm:table-cell">
        <span @class([
            'inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium ring-1 ring-inset',
            'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400' => $record->status === 'pending',
            'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-400' => $record->status === 'processed',
            'bg-teal-50 text-teal-700 ring-teal-600/20 dark:bg-teal-900/30 dark:text-teal-400' => $record->status === 'done',
            'bg-gray-50 text-gray-600 ring-gray-500/20 dark:bg-gray-800/50 dark:text-gray-400' => ! in_array($record->status, ['pending', 'processed', 'done'], true),
        ])>
            <span class="size-1.5 rounded-full bg-current opacity-70"></span>
            {{ $record->status_label }}
        </span>
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3.5 whitespace-nowrap w-px">
        <livewire:core.shared-components.item-actions
            :editUrl="route('frasasti.aduan-masuk.edit', $record->id)"
            :deleteId="$record->id"
            :navigate="true"
            confirmMessage="{{ __('Yakin ingin menghapus aduan ini?') }}"
            wire:key="item-actions-{{ $record->id }}" />
    </td>
</tr>
