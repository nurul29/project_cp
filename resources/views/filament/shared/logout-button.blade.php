<form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 p-3 dark:border-gray-700">
    @csrf
    <button
        type="submit"
        class="flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-danger-50 hover:text-danger-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-gray-300 dark:hover:bg-danger-400/10 dark:hover:text-danger-400"
    >
        <x-filament::icon icon="heroicon-o-arrow-left-on-rectangle" class="h-5 w-5" />
        Keluar
    </button>
</form>
