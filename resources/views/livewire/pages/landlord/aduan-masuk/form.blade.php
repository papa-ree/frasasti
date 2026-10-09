<div>
    <x-core::breadcrumb
        :items="[['label' => __('Aduan Masuk'), 'route' => 'frasasti.aduan-masuk.index']]"
        :active="__('Detail Aduan')" />

    <div class="max-w-4xl mx-auto mt-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-100 dark:border-slate-800 shadow-xl overflow-hidden">

            {{-- Header Banner --}}
            <div class="p-6 border-b border-gray-100 dark:border-slate-800 bg-linear-to-r from-indigo-50/50 to-purple-50/50 dark:from-indigo-900/10 dark:to-purple-900/10">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ __('Detail Aduan') }}
                    <span class="ml-2 font-mono text-sm font-semibold text-gray-500 dark:text-gray-400">
                        {{ $aduan->ref_code }}
                    </span>
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ __('Data pengirim ditampilkan apa adanya dan terenkripsi di database.') }}
                </p>
            </div>

            <div class="p-8 space-y-6">

                {{-- Informasi aduan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('Nama Lengkap') }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $aduan->nama_lengkap }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('NIP') }}
                        </label>
                        <p class="text-sm font-mono text-gray-900 dark:text-white">{{ $aduan->nip }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('Kategori') }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $aduan->category?->name ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                            {{ __('Waktu Masuk') }}
                        </label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $aduan->created_at }}</p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-4">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                        {{ __('Deskripsi Keluhan') }}
                    </label>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $aduan->deskripsi }}</p>
                </div>

                {{-- Form update status --}}
                <form wire:submit="updateStatus" class="pt-6 border-t border-gray-100 dark:border-slate-800 space-y-4">
                    <div class="max-w-xs">
                        <label for="status" class="block text-sm font-bold text-gray-900 dark:text-white mb-1.5">
                            {{ __('Status') }}
                        </label>
                        <select id="status" wire:model="status"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-slate-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="pending">{{ __('Menunggu') }}</option>
                            <option value="processed">{{ __('Diproses') }}</option>
                            <option value="done">{{ __('Selesai') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($aduan->handled_at)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Ditangani pada') }} {{ $aduan->handled_at }}
                        </p>
                    @endif

                    {{-- Footer Actions --}}
                    <div class="pt-2 flex items-center justify-between">
                        <x-core::secondary-button link href="{{ route('frasasti.aduan-masuk.index') }}"
                            label="{{ __('Kembali') }}" />
                        <x-core::button type="submit" label="{{ __('Simpan Status') }}" spinner="updateStatus">
                            <x-slot name="icon"><x-lucide-check class="w-4 h-4" /></x-slot>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
