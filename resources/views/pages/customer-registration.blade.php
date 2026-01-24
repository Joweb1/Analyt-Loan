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
                <!-- Profile Upload Section -->
                <div class="flex flex-col items-center mb-12">
                    <div class="relative">
                        <div class="size-32 rounded-full border-4 border-zinc-50 dark:border-zinc-800 bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden mb-4 shadow-inner">
                            <span class="material-symbols-outlined text-zinc-300 text-5xl">person</span>
                        </div>
                        <button class="absolute bottom-4 right-0 bg-primary text-white size-8 rounded-full flex items-center justify-center shadow-lg hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </button>
                    </div>
                    <div class="text-center">
                        <p class="text-primary dark:text-white font-bold text-lg">Borrower Photo</p>
                        <p class="text-zinc-400 text-sm">Upload a clear portrait (PNG or JPG, max 5MB)</p>
                        <button class="mt-4 px-6 py-2 bg-zinc-100 dark:bg-zinc-800 text-primary dark:text-white text-xs font-bold rounded-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                            Change Photo
                        </button>
                    </div>
                </div>
                <!-- Registration Form -->
                <form class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Full Name -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Full Name</label>
                            <div class="relative">
                                <input class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="John Olusegun" type="text"/>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Email Address</label>
                            <div class="relative">
                                <input class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="john@example.com" type="email"/>
                            </div>
                        </div>
                        <!-- Phone Number -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Phone Number</label>
                            <div class="flex gap-3">
                                <div class="flex items-center justify-center px-4 bg-zinc-100 dark:bg-zinc-800 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl font-bold text-sm text-zinc-600 dark:text-zinc-400">
                                    +234
                                </div>
                                <input class="flex-1 px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="801 234 5678" type="tel"/>
                            </div>
                        </div>
                        <!-- BVN -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between px-1">
                                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest">BVN (11 Digits)</label>
                                <div class="group relative cursor-help">
                                    <span class="material-symbols-outlined text-zinc-400 text-lg">info</span>
                                    <div class="absolute bottom-full right-0 mb-2 w-48 bg-primary text-white text-[10px] p-2 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none font-medium leading-relaxed">
                                        Bank Verification Number is required for identity verification and credit history checks.
                                    </div>
                                </div>
                            </div>
                            <input class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium tracking-[0.2em]" maxlength="11" placeholder="22345678901" type="text"/>
                        </div>
                    </div>
                    <!-- Residential Address -->
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Residential Address</label>
                        <textarea class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium resize-none" placeholder="No. 12 Adeola Hopewell St, Victoria Island, Lagos" rows="3"></textarea>
                    </div>
                    <!-- Form Footer Actions -->
                    <div class="pt-10 flex flex-col md:flex-row items-center gap-6">
                        <button class="w-full md:w-auto min-w-[240px] py-4 bg-primary text-white text-base font-bold rounded-full shadow-xl shadow-primary/30 hover:bg-zinc-800 hover:scale-[1.02] active:scale-95 transition-all" type="submit">
                            Register Borrower
                        </button>
                        <button class="text-zinc-400 hover:text-zinc-600 font-bold text-sm transition-colors" type="button">
                            Cancel &amp; Return
                        </button>
                    </div>
                </form>
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
