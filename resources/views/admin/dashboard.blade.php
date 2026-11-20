<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Selamat Datang di Admin Platform, {{ Auth::user()->name }}!</h3>
                        <p class="mt-1 text-sm text-gray-600">Role: <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 uppercase">{{ Auth::user()->role }}</span></p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Kelola Kategori & Template</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">Issue #02</div>
                    <div class="mt-1 text-xs text-gray-500">Ready for implementation</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Manajemen Undangan</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">Issue #03</div>
                    <div class="mt-1 text-xs text-gray-500">Ready for implementation</div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Platform Analytics</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">Issue #08</div>
                    <div class="mt-1 text-xs text-gray-500">Ready for implementation</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
