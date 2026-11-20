<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Selamat datang, {{ Auth::user()->name }}!</h3>
                        <p class="mt-1 text-sm text-gray-600">Email: {{ Auth::user()->email }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Paket: {{ Auth::user()->subscriptionTier?->name ?? 'Free' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-medium text-gray-900">Undangan Aktif</h4>
                    <p class="mt-2 text-3xl font-bold text-gray-900">0 / {{ Auth::user()->subscriptionTier?->max_active_invitations == -1 ? 'Unlimited' : (Auth::user()->subscriptionTier?->max_active_invitations ?? 1) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Maksimum undangan aktif sesuai paket saat ini.</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="font-medium text-gray-900">Galeri Foto</h4>
                    <p class="mt-2 text-3xl font-bold text-gray-900">Maks {{ Auth::user()->subscriptionTier?->max_gallery_photos == -1 ? 'Unlimited' : (Auth::user()->subscriptionTier?->max_gallery_photos ?? 5) }} Foto</p>
                    <p class="mt-1 text-xs text-gray-500">Maksimum upload foto per undangan.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
