<x-app-layout>
    @section('title', 'Add New Borrower')
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumbs -->
        <div class="flex items-center gap-2 mb-6">
            <a class="text-sm font-semibold text-zinc-400 hover:text-primary transition-colors" href="#">Dashboard</a>
            <span class="material-symbols-outlined text-zinc-300 text-sm">chevron_right</span>
            <a class="text-sm font-semibold text-zinc-400 hover:text-primary transition-colors" href="#">Borrowers</a>
            <span class="material-symbols-outlined text-zinc-300 text-sm">chevron_right</span>
            <span class="text-sm font-bold text-primary dark:text-white">Add New Borrower</span>
        </div>
        <!-- Page Heading -->
        <div class="mb-10">
            <h2 class="text-3xl font-black text-primary dark:text-white tracking-tight">Add New Borrower</h2>
            <p class="text-zinc-500 mt-2 font-medium">Fill in the registration details to onboard a new borrower onto the Analyt Loan system.</p>
        </div>
        <!-- Form Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-[2rem] shadow-xl shadow-primary/5 border border-zinc-100 dark:border-zinc-800 overflow-hidden">
            <div class="p-10">
                <!-- Livewire Form -->
                <livewire:borrower-registration-form />
            </div>
        </div>
        <!-- Additional Contextual Card -->
        <div class="mt-8 p-6 bg-blue-50 dark:bg-zinc-800/30 border border-blue-100 dark:border-zinc-800 rounded-3xl flex items-start gap-4">
            <div class="bg-blue-100 dark:bg-zinc-800 p-3 rounded-2xl">
                <span class="material-symbols-outlined text-blue-600">verified_user</span>
            </div>
            <div>
                <h4 class="text-blue-900 dark:text-zinc-300 font-bold">Secure Registration</h4>
                <p class="text-blue-700/70 dark:text-zinc-500 text-sm mt-1 leading-relaxed">All information provided will be encrypted and stored in accordance with Nigerian Data Protection Regulations (NDPR). Borrower details are verified against central databases in real-time.</p>
            </div>
        </div>
    </div>
</x-app-layout>
