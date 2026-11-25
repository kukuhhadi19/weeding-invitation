<?php

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

function createAdminUser(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
}

test('category seeder seeds five default indonesian categories idempotently', function () {
    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::count())->toBe(5)
        ->and(Category::where('slug', 'adat-tradisional')->first()?->name)->toBe('Adat Tradisional')
        ->and(Category::active()->count())->toBe(5);
});

test('non-admin users cannot access admin categories page', function () {
    $this->get('/admin/categories')->assertRedirect('/login');

    $unverified = User::factory()->unverified()->create(['role' => 'admin']);
    $this->actingAs($unverified)
        ->get('/admin/categories')
        ->assertRedirect('/verify-email');

    $regularUser = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($regularUser)
        ->get('/admin/categories')
        ->assertForbidden();
});

test('admin can view seeded categories, search, and filter by active status', function () {
    $this->seed(CategorySeeder::class);
    Category::create([
        'name' => 'Vintage Arsip',
        'slug' => 'vintage-arsip',
        'sort_order' => 6,
        'is_active' => false,
    ]);

    $admin = createAdminUser();

    $this->actingAs($admin)
        ->get('/admin/categories')
        ->assertOk()
        ->assertSeeVolt('pages.admin.categories')
        ->assertSee('Klasik')
        ->assertSee('Adat Tradisional')
        ->assertSee('Vintage Arsip');

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('setStatus', 'archived')
        ->assertSee('Vintage Arsip')
        ->assertDontSee('Adat Tradisional')
        ->call('setStatus', 'all')
        ->set('search', 'floral')
        ->assertSee('Floral')
        ->assertDontSee('Minimalis');
});

test('admin can create category with auto-generated slug and optional icon upload', function () {
    Storage::fake('public');
    $admin = createAdminUser();

    $upload = UploadedFile::fake()->image('sundanese-icon.png', 128, 128);

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('openCreateModal')
        ->set('name', 'Adat Sunda Eksklusif')
        ->assertSet('slug', 'adat-sunda-eksklusif')
        ->set('description', 'Ornamen khas Sunda bernuansa lembut')
        ->set('sort_order', 10)
        ->set('is_active', true)
        ->set('iconUpload', $upload)
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::where('slug', 'adat-sunda-eksklusif')->first();

    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Adat Sunda Eksklusif')
        ->and($category->sort_order)->toBe(10)
        ->and($category->hasUploadedIcon())->toBeTrue();

    Storage::disk('public')->assertExists($category->icon);
});

test('category creation validates unique slug and required fields', function () {
    $this->seed(CategorySeeder::class);
    $admin = createAdminUser();

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('openCreateModal')
        ->set('name', '')
        ->set('slug', '')
        ->call('save')
        ->assertHasErrors(['name', 'slug']);

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('openCreateModal')
        ->set('name', 'Klasik Duplikat')
        ->set('slug', 'klasik')
        ->call('save')
        ->assertHasErrors(['slug']);
});

test('admin can edit, reorder, and toggle active status of categories', function () {
    $first = Category::create([
        'name' => 'Pertama',
        'slug' => 'pertama',
        'icon' => 'crown',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $second = Category::create([
        'name' => 'Kedua',
        'slug' => 'kedua',
        'icon' => 'flower',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $admin = createAdminUser();

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('openEditModal', $first->id)
        ->set('name', 'Pertama Diperbarui')
        ->set('slug', 'pertama-baru')
        ->set('icon', 'landmark')
        ->call('save')
        ->assertHasNoErrors()
        ->call('toggleStatus', $first->id)
        ->call('moveUp', $second->id);

    expect($first->fresh()->name)->toBe('Pertama Diperbarui')
        ->and($first->fresh()->slug)->toBe('pertama-baru')
        ->and($first->fresh()->is_active)->toBeFalse()
        ->and($second->fresh()->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);
});

test('admin can delete unused category and is blocked when templates reference category', function () {
    $admin = createAdminUser();

    $unused = Category::create([
        'name' => 'Kategori Kosong',
        'slug' => 'kategori-kosong',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $inUse = Category::create([
        'name' => 'Kategori Terpakai',
        'slug' => 'kategori-terpakai',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('confirmDelete', $unused->id)
        ->call('deleteCategory');

    expect(Category::find($unused->id))->toBeNull();

    if (! Schema::hasTable('templates')) {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    DB::table('templates')->insert([
        'category_id' => $inUse->id,
        'name' => 'Sample Template',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Volt::actingAs($admin)
        ->test('pages.admin.categories')
        ->call('confirmDelete', $inUse->id)
        ->call('deleteCategory')
        ->assertSee('tidak dapat dihapus karena masih memiliki template terdaftar');

    expect(Category::find($inUse->id))->not->toBeNull();

    Schema::dropIfExists('templates');
});
