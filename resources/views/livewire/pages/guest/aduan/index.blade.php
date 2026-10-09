<x-slot name="aside">
    {{-- Hint panel --}}
    <div class="lg:max-w-sm">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
            Pusat Bantuan &amp; Aduan
        </h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
            Laporkan kendala layanan TIK Anda. Aduan akan diteruskan langsung ke tim kami melalui WhatsApp.
        </p>

        {{-- Langkah-langkah --}}
        <ol class="mt-6 hidden space-y-3.5 sm:block">
            <li class="flex items-start gap-3">
                <span
                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[11px] font-bold text-white">1</span>
                <p class="text-sm text-gray-600 dark:text-gray-300">Isi formulir dengan data yang benar dan lengkap.</p>
            </li>
            <li class="flex items-start gap-3">
                <span
                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[11px] font-bold text-white">2</span>
                <p class="text-sm text-gray-600 dark:text-gray-300">Kirim aduan — otomatis diteruskan ke tim kami via
                    WhatsApp.</p>
            </li>
            <li class="flex items-start gap-3">
                <span
                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[11px] font-bold text-white">3</span>
                <p class="text-sm text-gray-600 dark:text-gray-300">Tim kami memproses dan menghubungi Anda.</p>
            </li>
        </ol>

        {{-- Kepercayaan --}}
        <div
            class="mt-7 hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/60 lg:block">
            <div class="flex items-center gap-3">
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Data Anda terenkripsi</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nama, NIP &amp; no. WhatsApp diamankan.</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <span
                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <svg class="size-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4"></path>
                        <path d="M12 3a9 9 0 0 1 9 9c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9z">
                        </path>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Kode referensi unik</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Memudahkan penelusuran status aduan Anda.</p>
                </div>
            </div>
        </div>

        {{-- Dark mode toggle --}}
        <div class="mt-6 hidden justify-center lg:flex">
            <x-core::dark-mode-toggle />
        </div>
    </div>
</x-slot>

<div x-data="{ ...aduanPage(), charCount: {{ mb_strlen($deskripsi) }} }" x-cloak
    class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl shadow-gray-900/5 dark:border-gray-800 dark:bg-gray-900">

    {{-- Card header --}}
    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
        <h2 class="text-base font-bold text-gray-900 dark:text-white">Formulir Aduan</h2>
        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Semua kolom wajib diisi.</p>
    </div>

    {{-- Skeleton while waiting for reCAPTCHA --}}
    <div x-show="!loaded" x-cloak class="p-5 sm:p-6 space-y-4 animate-pulse">
        <div class="h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
            <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
        </div>
        <div class="h-10 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
        <div class="h-24 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
        <div class="h-11 rounded-xl bg-gray-200 dark:bg-gray-700"></div>
        <p class="text-center text-xs text-gray-400 dark:text-gray-600">Menunggu verifikasi keamanan…</p>
    </div>

    <div x-show="loaded" class="p-5 sm:p-6">
        {{-- Error banner --}}
        @if ($errors->any())
            <div x-data="{ open: true }" x-show="open"
                class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3.5 dark:border-red-800 dark:bg-red-900/20">
                <div class="flex items-start gap-2.5">
                    <svg class="mt-0.5 size-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                        </path>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-red-700 dark:text-red-300">Periksa kembali formulir</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-[11px] text-red-600/80 dark:text-red-400/80">{{ $error }}</p>
                        @endforeach
                    </div>
                    <button type="button" @click="open = false" class="shrink-0 text-red-400 transition hover:text-red-600">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <form wire:submit="submit" autocomplete="off">
            {{-- Nama Lengkap --}}
            <div class="mb-4">
                <label for="nama_lengkap"
                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"></path>
                            <path d="M12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"></path>
                        </svg>
                    </div>
                    <input wire:model="nama_lengkap" type="text" id="nama_lengkap"
                        placeholder="Nama lengkap tanpa gelar"
                        class="block w-full rounded-lg border px-3.5 py-2.5 pl-9 text-sm transition-all duration-200 focus:outline-none
                                        bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100
                                        placeholder-gray-400 dark:placeholder-gray-500 input-purple
                                        @error('nama_lengkap') border-red-400 dark:border-red-500 input-red @enderror" />
                </div>
                @error('nama_lengkap')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIP + No. WhatsApp --}}
            <div class="mb-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="nip"
                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        NIP <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <path d="M3 10h18M7 15h4"></path>
                            </svg>
                        </div>
                        <input wire:model="nip" type="text" id="nip" x-mask="99999999 999999 9 999" inputmode="numeric"
                            placeholder="18 digit NIP" class="block w-full rounded-lg border px-3.5 py-2.5 pl-9 text-sm transition-all duration-200 focus:outline-none
                                            bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100
                                            placeholder-gray-400 dark:placeholder-gray-500 input-purple
                                            @error('nip') border-red-400 dark:border-red-500 input-red @enderror" />
                    </div>
                    @error('nip')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="wa_number"
                        class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                        No. WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                            <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                        </div>
                        <input wire:model="wa_number" type="text" id="wa_number" x-mask="99999999999999"
                            inputmode="numeric" maxlength="15" placeholder="Contoh: 081234567890"
                            class="block w-full rounded-lg border px-3.5 py-2.5 pl-9 text-sm transition-all duration-200 focus:outline-none
                                            bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100
                                            placeholder-gray-400 dark:placeholder-gray-500 input-purple
                                            @error('wa_number') border-red-400 dark:border-red-500 input-red @enderror" />
                    </div>
                    @error('wa_number')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Jenis Aduan --}}
            <div class="mb-4">
                <label for="aduan_category_id"
                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                    Jenis Aduan <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z">
                            </path>
                            <path d="M7 7h.01"></path>
                        </svg>
                    </div>
                    <select wire:model="aduan_category_id" id="aduan_category_id"
                        class="block w-full appearance-none rounded-lg border px-3.5 py-2.5 pl-9 pr-9 text-sm transition-all duration-200 focus:outline-none
                                        bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100
                                        placeholder-gray-400 dark:placeholder-gray-500 input-purple
                                        @error('aduan_category_id') border-red-400 dark:border-red-500 input-red @enderror">
                        <option value="">Pilih jenis aduan</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                        <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                </div>
                @error('aduan_category_id')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-5">
                <label for="deskripsi"
                    class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">
                    Deskripsi Keluhan <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="deskripsi" id="deskripsi" rows="4" maxlength="5000"
                    placeholder="Jelaskan kendala yang Anda alami secara rinci" @input="charCount = $el.value.length"
                    class="block w-full resize-none rounded-lg border px-3.5 py-2.5 text-sm transition-all duration-200 focus:outline-none
                                    bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100
                                    placeholder-gray-400 dark:placeholder-gray-500 input-purple
                                    @error('deskripsi') border-red-400 dark:border-red-500 input-red @enderror"></textarea>
                <div class="mt-1 flex items-center justify-end">
                    <span class="text-[11px] text-gray-400 dark:text-gray-600" x-text="charCount">0</span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-600">/5000</span>
                </div>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- reCAPTCHA v3 --}}
            <div wire:ignore>
                {!! RecaptchaV3::field(config('frasasti.aduan.recaptcha_action', 'aduan')) !!}
            </div>

            {{-- Submit --}}
            <div class="mt-5">
                <button type="submit" :disabled="!recaptchaReady" wire:loading.attr="disabled"
                    :title="!recaptchaReady ? 'Menunggu verifikasi keamanan…' : ''"
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-lg transition-all duration-300 hover:scale-[1.01] hover:shadow-xl active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">

                    <svg wire:loading.remove class="size-4 shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                        </path>
                    </svg>
                    <span wire:loading.remove>Kirim ke WhatsApp</span>

                    <svg wire:loading class="size-4 shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                    </svg>
                    <span wire:loading>Mengirim…</span>
                </button>
            </div>
        </form>

        {{-- Privacy note --}}
        <div class="mt-5 flex items-center justify-center gap-1.5">
            <svg class="size-3.5 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span class="text-xs text-gray-400 dark:text-gray-600">
                Data Anda terenkripsi &middot; Protected by reCAPTCHA
            </span>
        </div>
    </div>
</div>

<script>
    function aduanPage ()
    {
        return {
            recaptchaValue: '',
            recaptchaReady: false,
            loaded: false,
            _observer: null,
            _poll: null,

            init ()
            {
                const el = this.$el.matches( '[wire\\:id]' ) ? this.$el : this.$el.closest( '[wire\\:id]' );
                const $wire = el ? Livewire.find( el.getAttribute( 'wire:id' ) ) : Livewire.first();

                const syncToken = () =>
                {
                    const input = document.querySelector( 'input[name="g-recaptcha-response"]' );
                    if ( input && input.value && input.value !== this.recaptchaValue ) {
                        this.recaptchaValue = input.value;
                        this.recaptchaReady = true;
                        if ( ! this.loaded ) this.loaded = true;
                        $wire.set( 'recaptchaToken', input.value );
                    } else if ( input && ! input.value && this.recaptchaReady ) {
                        // Token expired — keep loaded true, keep inputs, only disable button
                        this.recaptchaReady = false;
                    }
                };

                this._observer = new MutationObserver( syncToken );
                this._observer.observe( document.body, { subtree: true, attributes: true, childList: true } );

                this._poll = setInterval( syncToken, 5000 );
            },

            destroy ()
            {
                if ( this._observer ) this._observer.disconnect();
                if ( this._poll ) clearInterval( this._poll );
            },
        };
    }
</script>