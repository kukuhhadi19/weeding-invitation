<?php

use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    #[Url]
    public string $status = 'all';

    public string $search = '';

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public bool $slugManuallyEdited = false;

    public ?string $feedbackMessage = null;

    public ?string $feedbackError = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public string $icon = 'sparkles';

    public int|string $sort_order = 1;

    public bool $is_active = true;

    public $iconUpload = null;

    public ?string $existingUploadedIcon = null;

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, ['all', 'active', 'archived'], true) ? $status : 'all';
    }

    public function updatedName(string $value): void
    {
        if (! $this->editingId && ! $this->slugManuallyEdited) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugManuallyEdited = true;
    }

    public function openCreateModal(): void
    {
        $this->resetFormState();
        $this->sort_order = ((int) Category::max('sort_order')) + 1;
        $this->showFormModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->resetFormState();
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = (string) ($category->description ?? '');
        $this->sort_order = (int) $category->sort_order;
        $this->is_active = (bool) $category->is_active;
        $this->slugManuallyEdited = true;

        if ($category->hasUploadedIcon()) {
            $this->existingUploadedIcon = $category->icon;
            $this->icon = 'sparkles';
        } else {
            $this->icon = array_key_exists((string) $category->icon, Category::ICON_OPTIONS)
                ? (string) $category->icon
                : 'sparkles';
        }

        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->showFormModal = false;
        $this->resetFormState();
    }

    public function removeUploadedIcon(): void
    {
        $this->existingUploadedIcon = null;
        $this->iconUpload = null;
    }

    public function save(): void
    {
        $this->feedbackError = null;
        $this->slug = Str::slug($this->slug !== '' ? $this->slug : $this->name);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['required', 'string', Rule::in(array_keys(Category::ICON_OPTIONS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'iconUpload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'slug.required' => 'Slug kategori wajib diisi.',
            'slug.regex' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'slug.unique' => 'Slug kategori sudah digunakan.',
            'iconUpload.image' => 'File ikon harus berupa gambar yang valid.',
            'iconUpload.mimes' => 'Format ikon yang didukung adalah PNG, JPG, JPEG, atau WebP.',
            'iconUpload.max' => 'Ukuran file ikon maksimal 1 MB.',
        ]);

        $category = $this->editingId ? Category::findOrFail($this->editingId) : null;
        $iconValue = $this->resolveIconForSave($category, $validated['icon']);

        $payload = [
            'name' => trim($validated['name']),
            'slug' => $validated['slug'],
            'description' => filled($validated['description']) ? trim((string) $validated['description']) : null,
            'icon' => $iconValue,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => (bool) $validated['is_active'],
        ];

        if ($category) {
            $category->update($payload);
            $this->feedbackMessage = "Kategori \"{$category->name}\" berhasil diperbarui.";
        } else {
            $created = Category::create($payload);
            $this->feedbackMessage = "Kategori \"{$created->name}\" berhasil ditambahkan.";
        }

        $this->closeFormModal();
    }

    public function toggleStatus(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $category->update(['is_active' => ! $category->is_active]);

        $this->feedbackError = null;
        $this->feedbackMessage = $category->is_active
            ? "Kategori \"{$category->name}\" diaktifkan kembali."
            : "Kategori \"{$category->name}\" berhasil diarsipkan.";
    }

    public function moveUp(int $categoryId): void
    {
        $this->shiftOrder($categoryId, -1);
    }

    public function moveDown(int $categoryId): void
    {
        $this->shiftOrder($categoryId, 1);
    }

    public function confirmDelete(int $categoryId): void
    {
        $this->deletingId = $categoryId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function deleteCategory(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $category = Category::findOrFail($this->deletingId);

        if ($category->templatesCount() > 0) {
            $this->closeDeleteModal();
            $this->feedbackMessage = null;
            $this->feedbackError = "Kategori \"{$category->name}\" tidak dapat dihapus karena masih memiliki template terdaftar.";

            return;
        }

        if ($category->hasUploadedIcon() && $category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $deletedName = $category->name;
        $category->delete();

        $this->closeDeleteModal();
        $this->feedbackError = null;
        $this->feedbackMessage = "Kategori \"{$deletedName}\" berhasil dihapus.";
    }

    public function with(): array
    {
        $query = Category::ordered();

        if ($this->status === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status === 'archived') {
            $query->where('is_active', false);
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where(function ($subQuery) use ($term): void {
                $subQuery->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $publicRoot = Storage::disk('public')->path('');
        $storageWritable = is_dir($publicRoot) && is_writable($publicRoot);
        $symlinkReady = file_exists(public_path('storage'));

        return [
            'categories' => $query->get(),
            'totalCount' => Category::count(),
            'activeCount' => Category::where('is_active', true)->count(),
            'archivedCount' => Category::where('is_active', false)->count(),
            'iconOptions' => Category::iconOptions(),
            'deletingCategory' => $this->deletingId ? Category::find($this->deletingId) : null,
            'storageWritable' => $storageWritable,
            'symlinkReady' => $symlinkReady,
        ];
    }

    private function resolveIconForSave(?Category $existingCategory, string $presetIcon): string
    {
        if ($this->iconUpload) {
            if ($existingCategory?->hasUploadedIcon() && $existingCategory->icon) {
                Storage::disk('public')->delete($existingCategory->icon);
            }

            return $this->iconUpload->store('categories/icons', 'public');
        }

        if ($this->existingUploadedIcon) {
            return $this->existingUploadedIcon;
        }

        if ($existingCategory?->hasUploadedIcon() && $existingCategory->icon) {
            Storage::disk('public')->delete($existingCategory->icon);
        }

        return $presetIcon;
    }

    private function shiftOrder(int $categoryId, int $direction): void
    {
        $ordered = Category::ordered()->get()->values();
        $currentIndex = $ordered->search(fn (Category $item) => $item->id === $categoryId);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $currentIndex + $direction;

        if ($targetIndex < 0 || $targetIndex >= $ordered->count()) {
            return;
        }

        $swapped = $ordered->all();
        [$swapped[$currentIndex], $swapped[$targetIndex]] = [$swapped[$targetIndex], $swapped[$currentIndex]];

        foreach ($swapped as $index => $item) {
            $item->update(['sort_order' => $index + 1]);
        }

        $this->feedbackError = null;
        $this->feedbackMessage = 'Urutan kategori berhasil diperbarui.';
    }

    private function resetFormState(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->icon = 'sparkles';
        $this->sort_order = 1;
        $this->is_active = true;
        $this->iconUpload = null;
        $this->existingUploadedIcon = null;
        $this->slugManuallyEdited = false;
    }
}; ?>

<div>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Kelola kategori template undangan dan konfigurasi tema terkunci.
    </x-slot>

    <div class="flex flex-col gap-6">
        @if ($feedbackMessage)
            <div class="p-4 rounded-2xl bg-success/10 border border-success/25 text-sm text-foreground flex items-center justify-between gap-3">
                <span class="font-medium">{{ $feedbackMessage }}</span>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-xs font-bold text-secondary hover:text-foreground cursor-pointer">Tutup</button>
            </div>
        @endif

        @if ($feedbackError)
            <div class="p-4 rounded-2xl bg-error/10 border border-error/25 text-sm text-foreground flex items-center justify-between gap-3">
                <span class="font-medium text-error">{{ $feedbackError }}</span>
                <button type="button" wire:click="$set('feedbackError', null)" class="text-xs font-bold text-secondary hover:text-foreground cursor-pointer">Tutup</button>
            </div>
        @endif

        <!-- Template Categories Card Container (Matched with Reference Studio Card) -->
        <section class="bg-white rounded-3xl border border-border p-6 md:p-8 flex flex-col gap-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Kategori Template</p>
                    <h2 class="text-2xl font-bold text-foreground mt-1">
                        Buat, ubah, arsipkan, atau hapus kategori.
                    </h2>
                    <p class="text-sm text-secondary mt-1 max-w-2xl">
                        Arsip mempertahankan kategori di panel admin namun menyembunyikannya dari pemilihan template baru di katalog user.
                    </p>
                </div>

                <button
                    type="button"
                    wire:click="openCreateModal"
                    class="shrink-0 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-sm shadow-primary/20 cursor-pointer"
                >
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    + Kategori Baru
                </button>
            </div>

            <!-- Filter Pills + Search -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2 border-t border-border">
                <div class="overflow-x-auto scrollbar-hide">
                    <div class="flex gap-2.5 min-w-max">
                        @foreach ([
                            'all' => 'Semua (' . $totalCount . ')',
                            'active' => 'Aktif (' . $activeCount . ')',
                            'archived' => 'Diarsipkan (' . $archivedCount . ')',
                        ] as $key => $label)
                            <button
                                type="button"
                                wire:click="setStatus('{{ $key }}')"
                                class="px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition-all cursor-pointer {{ $status === $key ? 'bg-foreground text-white shadow-sm' : 'bg-muted/70 text-secondary hover:text-foreground' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="relative w-full md:w-72">
                    <input
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Cari nama atau slug kategori..."
                        class="w-full px-4 py-2.5 bg-muted/50 border border-border rounded-full text-sm text-foreground placeholder:text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
            </div>

            <!-- Category Grid Cards -->
            @if ($categories->isEmpty())
                <div class="rounded-2xl border border-dashed border-border bg-card-grey/30 p-10 text-center flex flex-col items-center">
                    <div class="size-14 rounded-full bg-muted flex items-center justify-center text-secondary mb-3">
                        <x-category-icon icon="layout" class="size-7" />
                    </div>
                    <h3 class="font-bold text-lg text-foreground">Belum Ada Kategori Ditemukan</h3>
                    <p class="text-sm text-secondary mt-1 max-w-md">
                        Ubah kata kunci pencarian/filter status atau tambahkan kategori baru untuk katalog template Anda.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($categories as $category)
                        @php
                            $templateCount = $category->templatesCount();
                        @endphp
                        <article class="rounded-2xl border border-border bg-card-grey/25 p-5 flex flex-col justify-between gap-4 hover:border-primary/40 transition-colors">
                            <div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="size-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 overflow-hidden">
                                            @if ($category->hasUploadedIcon() && $category->iconUrl())
                                                <img src="{{ $category->iconUrl() }}" alt="{{ $category->name }}" class="size-full object-cover">
                                            @else
                                                <x-category-icon :icon="$category->icon ?? 'sparkles'" class="size-5" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="font-bold text-base text-foreground truncate">{{ $category->name }}</h3>
                                                @if ($category->is_active)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-success/10 text-success">Aktif</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary">Diarsipkan</span>
                                                @endif
                                            </div>
                                            <p class="text-xs font-mono text-secondary mt-0.5 truncate">Slug: {{ $category->slug }}</p>
                                        </div>
                                    </div>

                                    <span class="px-2.5 py-1 rounded-full bg-white border border-border text-[11px] font-semibold text-foreground shrink-0">
                                        {{ $templateCount }} template
                                    </span>
                                </div>

                                @if ($category->description)
                                    <p class="text-xs text-secondary leading-relaxed mt-3 line-clamp-2">
                                        {{ $category->description }}
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-border/70">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $category->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-white ring-1 ring-border text-xs font-semibold text-foreground hover:bg-muted transition-colors cursor-pointer"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="toggleStatus({{ $category->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-white ring-1 ring-border text-xs font-semibold text-foreground hover:bg-muted transition-colors cursor-pointer"
                                    >
                                        {{ $category->is_active ? 'Arsipkan' : 'Aktifkan' }}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $category->id }})"
                                        class="px-3 py-1.5 rounded-xl bg-error/10 text-xs font-semibold text-error hover:bg-error/20 transition-colors cursor-pointer"
                                    >
                                        Hapus
                                    </button>
                                </div>

                                <div class="flex items-center gap-1 text-secondary">
                                    <span class="text-[11px] font-semibold mr-1">#{{ $category->sort_order }}</span>
                                    <button
                                        type="button"
                                        wire:click="moveUp({{ $category->id }})"
                                        aria-label="Naikkan urutan {{ $category->name }}"
                                        class="size-7 rounded-lg bg-white ring-1 ring-border flex items-center justify-center hover:bg-muted text-secondary hover:text-foreground cursor-pointer"
                                    >
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="moveDown({{ $category->id }})"
                                        aria-label="Turunkan urutan {{ $category->name }}"
                                        class="size-7 rounded-lg bg-white ring-1 ring-border flex items-center justify-center hover:bg-muted text-secondary hover:text-foreground cursor-pointer"
                                    >
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Bottom Upload & Storage Readiness Cards (Matched with Reference Admin Studio) -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-3xl border border-border p-6 flex flex-col justify-between gap-5">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Pipeline Upload Ikon &amp; Preview</h3>
                    <p class="text-sm text-secondary mt-1.5 leading-relaxed">
                        Ikon kategori (opsional) dan thumbnail template pada tahap berikutnya disimpan ke disk <code class="font-mono text-foreground">public</code> dengan validasi MIME ketat di sisi server.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-card-grey/50 border border-border p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Format Diizinkan</p>
                        <p class="text-sm font-bold text-foreground mt-1">PNG, JPG, WebP</p>
                    </div>
                    <div class="rounded-2xl bg-card-grey/50 border border-border p-3.5">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-secondary">Ukuran Maksimal</p>
                        <p class="text-sm font-bold text-foreground mt-1">1.0 MB (Ikon)</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-border p-6 flex flex-col justify-between gap-5">
                <div>
                    <h3 class="font-bold text-lg text-foreground">Kesiapan Storage Publik</h3>
                    <div class="mt-3 rounded-2xl bg-card-grey/40 border border-border p-4 text-sm text-secondary">
                        @if ($storageWritable && $symlinkReady)
                            Direktori <code class="font-mono text-foreground">storage/app/public</code> dan symlink <code class="font-mono text-foreground">public/storage</code> aktif serta siap melayani aset publik.
                        @else
                            Pastikan direktori storage dapat ditulis dan jalankan <code class="font-mono text-foreground">php artisan storage:link</code> jika aset upload belum tampil di browser.
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $storageWritable ? 'bg-success/10 text-success' : 'bg-error/10 text-error' }}">
                        {{ $storageWritable ? 'Disk Public Siap' : 'Disk Belum Siap' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider {{ $symlinkReady ? 'bg-success/10 text-success' : 'bg-warning/20 text-amber-800' }}">
                        {{ $symlinkReady ? 'Symlink Aktif' : 'Perlu storage:link' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-muted text-secondary">
                        URL Aset Lokal
                    </span>
                </div>
            </div>
        </section>
    </div>

    <!-- Create / Edit Category Modal -->
    @if ($showFormModal)
        <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="p-6 border-b border-border flex items-center justify-between sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="font-bold text-xl text-foreground">
                            {{ $editingId ? 'Edit Kategori Template' : 'Tambah Kategori Baru' }}
                        </h3>
                        <p class="text-xs text-secondary mt-0.5">Atur nama, slug unik, ikon, urutan tampil, dan status kategori.</p>
                    </div>
                    <button type="button" wire:click="closeFormModal" class="size-8 rounded-full hover:bg-muted flex items-center justify-center text-secondary cursor-pointer">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="p-6 flex flex-col gap-5">
                    <div class="flex flex-col gap-1.5">
                        <label for="cat-name" class="text-sm font-medium text-secondary">Nama Kategori</label>
                        <input
                            wire:model.live="name"
                            id="cat-name"
                            type="text"
                            placeholder="Contoh: Adat Sunda"
                            class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="cat-slug" class="text-sm font-medium text-secondary">Slug URL</label>
                        <input
                            wire:model="slug"
                            id="cat-slug"
                            type="text"
                            placeholder="adat-sunda"
                            class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm font-mono text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                        />
                        <p class="text-[11px] text-secondary">Terisi otomatis dari nama kategori; dapat diubah manual.</p>
                        <x-input-error :messages="$errors->get('slug')" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="cat-description" class="text-sm font-medium text-secondary">Deskripsi Singkat</label>
                        <textarea
                            wire:model="description"
                            id="cat-description"
                            rows="2"
                            placeholder="Jelaskan karakter visual kategori ini..."
                            class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 resize-none"
                        ></textarea>
                        <x-input-error :messages="$errors->get('description')" />
                    </div>

                    <!-- Icon Preset Selection -->
                    <div class="flex flex-col gap-2">
                        <span class="text-sm font-medium text-secondary">Pilih Ikon Bawaan</span>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                            @foreach ($iconOptions as $key => $label)
                                <button
                                    type="button"
                                    wire:click="$set('icon', '{{ $key }}')"
                                    title="{{ $label }}"
                                    class="p-3 rounded-xl border flex flex-col items-center justify-center gap-1 transition-all cursor-pointer {{ $icon === $key && ! $existingUploadedIcon && ! $iconUpload ? 'border-primary bg-primary/10 text-primary' : 'border-border bg-white text-secondary hover:bg-muted' }}"
                                >
                                    <x-category-icon :icon="$key" class="size-5" />
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Optional Custom Uploaded Icon -->
                    <div class="flex flex-col gap-2">
                        <label for="cat-icon-upload" class="text-sm font-medium text-secondary">Atau Unggah Ikon Gambar (Opsional)</label>
                        @if ($existingUploadedIcon)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-muted/50 border border-border text-xs">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingUploadedIcon) }}" alt="Ikon" class="size-9 rounded-lg object-cover border border-border">
                                    <span class="font-medium text-foreground">Ikon gambar kustom aktif</span>
                                </div>
                                <button type="button" wire:click="removeUploadedIcon" class="text-error font-bold hover:underline cursor-pointer">
                                    Hapus Gambar
                                </button>
                            </div>
                        @endif

                        <input
                            wire:model="iconUpload"
                            id="cat-icon-upload"
                            type="file"
                            accept="image/png,image/jpeg,image/webp"
                            class="w-full text-xs text-secondary file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"
                        />
                        <x-input-error :messages="$errors->get('iconUpload')" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div class="flex flex-col gap-1.5">
                            <label for="cat-sort-order" class="text-sm font-medium text-secondary">Urutan Tampil</label>
                            <input
                                wire:model="sort_order"
                                id="cat-sort-order"
                                type="number"
                                min="0"
                                class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                            <x-input-error :messages="$errors->get('sort_order')" />
                        </div>

                        <label class="flex items-center gap-3 p-3.5 rounded-xl border border-border bg-card-grey/30 cursor-pointer sm:mt-5">
                            <input wire:model="is_active" type="checkbox" class="custom-checkbox">
                            <span class="text-sm font-medium text-foreground">Kategori Aktif</span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-border flex justify-end gap-3">
                        <button type="button" wire:click="closeFormModal" class="px-5 py-2.5 rounded-full ring-1 ring-border text-sm font-semibold text-foreground hover:bg-muted cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-full bg-primary hover:bg-primary-hover text-white text-sm font-bold cursor-pointer">
                            {{ $editingId ? 'Simpan Perubahan' : 'Buat Kategori' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal && $deletingCategory)
        <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl p-6 text-center">
                <div class="size-14 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-4">
                    <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                </div>
                <h3 class="font-bold text-xl text-foreground">Hapus Kategori?</h3>
                <p class="text-sm text-secondary mt-2 leading-relaxed">
                    Kategori <strong class="text-foreground">{{ $deletingCategory->name }}</strong> akan dihapus permanen. Tindakan ini hanya diizinkan bila tidak ada template di dalamnya.
                </p>
                <div class="mt-6 flex flex-col gap-2.5">
                    <button type="button" wire:click="deleteCategory" class="w-full py-3 rounded-full bg-error text-white text-sm font-bold hover:opacity-90 cursor-pointer">
                        Ya, Hapus Kategori
                    </button>
                    <button type="button" wire:click="closeDeleteModal" class="w-full py-3 rounded-full ring-1 ring-border text-sm font-medium text-foreground hover:bg-muted cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
