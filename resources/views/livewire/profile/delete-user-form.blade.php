<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="p-5 md:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-5">
        <div>
            <h3 class="font-bold text-lg text-foreground">Tindakan Akun</h3>
            <p class="text-sm text-secondary mt-1 max-w-lg">
                Kelola sesi login Anda atau hapus akun beserta seluruh data undangan dan galeri foto secara permanen.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto shrink-0">
            <button
                type="button"
                wire:click="logout"
                class="ring-1 ring-border rounded-full py-3 px-6 text-sm font-medium hover:bg-muted text-foreground transition-all w-full sm:w-auto flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" x2="9" y1="12" y2="12" />
                </svg>
                Keluar
            </button>

            <button
                type="button"
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                class="bg-error/10 text-error rounded-full py-3 px-6 text-sm font-bold hover:bg-error/20 transition-colors w-full sm:w-auto shrink-0 cursor-pointer"
            >
                Hapus Akun
            </button>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6 text-center sm:text-left">
            <div class="size-14 bg-error/10 rounded-full flex items-center justify-center mx-auto sm:mx-0 mb-4 text-error">
                <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-foreground">
                Yakin ingin menghapus akun?
            </h2>

            <p class="mt-2 text-sm text-secondary leading-relaxed">
                Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun secara permanen.
            </p>

            <div class="mt-5 text-left">
                <label for="delete_account_password" class="sr-only">Kata Sandi</label>
                <input
                    wire:model="password"
                    id="delete_account_password"
                    name="password"
                    type="password"
                    placeholder="Masukkan kata sandi Anda"
                    class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="py-3 px-6 ring-1 ring-border rounded-full text-sm font-medium hover:bg-muted text-foreground transition-colors cursor-pointer"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="py-3 px-6 bg-error text-white rounded-full text-sm font-bold hover:opacity-90 transition-opacity cursor-pointer"
                >
                    Ya, Hapus Akun Saya
                </button>
            </div>
        </form>
    </x-modal>
</section>
