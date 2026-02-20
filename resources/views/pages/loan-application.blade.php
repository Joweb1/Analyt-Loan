<x-app-layout :title="isset($loan) ? 'Edit Loan Application' : 'New Loan Application'">
    <div class="w-full px-0">
        <div class="flex items-center justify-between mb-6 px-4 md:px-6">
            <div class="flex items-center gap-2">
                <a class="text-gray-500 text-sm font-semibold hover:text-primary transition-colors" href="{{ route('loan') }}">Loans</a>
                <span class="text-gray-400 text-sm">/</span>
                <span class="text-gray-900 text-sm font-bold">{{ isset($loan) ? 'Edit Application' : 'New Application' }}</span>
            </div>
            <a href="{{ isset($loan) ? route('loan.show', $loan->id) : route('loan') }}" class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors" title="Cancel">
                <span class="material-symbols-outlined">close</span>
            </a>
        </div>
        <div class="mb-10 px-4 md:px-6">
            <h1 class="text-gray-900 text-4xl font-extrabold leading-tight tracking-tight">{{ isset($loan) ? 'Edit Loan Application' : 'New Loan Application' }}</h1>
            <p class="text-gray-600 text-base font-medium mt-2">{{ isset($loan) ? 'Update details for existing loan.' : 'Create a new loan record for a registered borrower within the Nigerian market.' }}</p>
        </div>
        
        <livewire:loan-form :loan="$loan ?? null" />
    </div>
</x-app-layout>
