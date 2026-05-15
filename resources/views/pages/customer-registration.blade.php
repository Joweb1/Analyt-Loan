<x-app-layout title="Customer Onboarding">
    <div class="max-w-4xl mx-auto">
        <livewire:customer-registration-form :type="$type ?? 'borrower'" />
        
        <!-- Additional Contextual Card -->
        <div class="mt-8 p-6 bg-blue-50 dark:bg-zinc-800/30 border border-blue-100 dark:border-zinc-800 rounded-3xl flex items-start gap-4">
            <div class="bg-blue-100 dark:bg-zinc-800 p-3 rounded-2xl">
                <span class="material-symbols-outlined text-blue-600">verified_user</span>
            </div>
            <div>
                <h4 class="text-blue-900 dark:text-zinc-300 font-bold">Secure Registration</h4>
                <p class="text-blue-700/70 dark:text-zinc-500 text-sm mt-1 leading-relaxed">All information provided will be encrypted and stored in accordance with Nigerian Data Protection Regulations (NDPR). Customer details are verified against central databases in real-time.</p>
            </div>
        </div>
    </div>
</x-app-layout>
