<div class="max-w-6xl mx-auto px-2 py-2 w-full" 
     x-data="{ 
        filtersVisible: @entangle('showFilters') 
     }" 
     x-init="
        if (localStorage.getItem('analyt_customer_filters') !== null) {
            filtersVisible = localStorage.getItem('analyt_customer_filters') === 'true';
        }
        $watch('filtersVisible', value => localStorage.setItem('analyt_customer_filters', value))
     ">
    @php
        $isKycApproved = Auth::user()->organization && Auth::user()->organization->kyc_status === 'approved';
    @endphp

    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 mb-6">
        <a class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 hover:text-primary transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
        <span class="material-symbols-outlined text-zinc-300 text-xs">chevron_right</span>
        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary dark:text-white">Customers</span>
    </div>
    
    <!-- Section Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <h2 class="text-[#111318] dark:text-white text-[28px] font-bold tracking-tight">Manage Customers</h2>
            <p class="text-[#606b8a] text-sm mt-1 font-medium">Overview and management of your customer base.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-4">
            <!-- Cool Toggle Switch for Filters -->
            <div class="flex items-center gap-3 px-4 py-2 bg-surface rounded-xl border border-border-main shadow-sm">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Filters</span>
                <button 
                    type="button" 
                    @click="filtersVisible = !filtersVisible"
                    class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-all duration-300 ease-in-out focus:outline-none"
                    :class="filtersVisible ? 'bg-primary shadow-lg shadow-primary/20' : 'bg-slate-200 dark:bg-zinc-700'"
                >
                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-in-out"
                          :class="filtersVisible ? 'translate-x-4' : 'translate-x-0'"></span>
                </button>
            </div>

            @if($isKycApproved)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('customer.create', ['type' => 'borrower']) }}" class="flex-1 min-w-[120px] px-4 py-2.5 bg-primary text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-zinc-800 transition-all shadow-md shadow-primary/10 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span> Borrower
                    </a>
                    <a href="{{ route('customer.create', ['type' => 'saver']) }}" class="flex-1 min-w-[100px] px-4 py-2.5 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-zinc-800 transition-all shadow-md shadow-green-600/10 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span> Saver
                    </a>
                    <a href="{{ route('customer.create', ['type' => 'guarantor']) }}" class="flex-1 min-w-[120px] px-4 py-2.5 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-zinc-800 transition-all shadow-md shadow-blue-600/10 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">add</span> Guarantor
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('customer.create', ['type' => 'staff']) }}" class="flex-1 min-w-[100px] px-4 py-2.5 bg-purple-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-zinc-800 transition-all shadow-md shadow-purple-600/10 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-sm">add</span> Staff
                        </a>
                    @endif
                </div>
@endif
        </div>
    </div>

    <div x-show="filtersVisible" x-collapse x-cloak>
        <div class="animate-in fade-in slide-in-from-top-4 duration-500">
            <!-- Row 2: Filters & View Toggle -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 p-4 bg-surface rounded-xl border border-border-main shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Show:</span>
                        <select wire:model.live="roleFilter" class="bg-slate-50 dark:bg-zinc-800 border-none rounded-lg text-xs font-bold px-4 py-2 outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="">All Customers</option>
                            <option value="Borrower">Borrowers Only</option>
                            <option value="Saver">Savers Only</option>
                            <option value="Guarantor">Guarantors Only</option>
                        </select>
                    </div>

                    <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>

                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Portfolio:</span>
                        <x-portfolio-filter :portfolios="$portfolios" :portfolioId="$portfolioId" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex bg-slate-50 dark:bg-zinc-800 rounded-lg p-1">
                        <button wire:click="toggleView('grid')" class="p-2 rounded-md transition-all {{ $viewMode === 'grid' ? 'bg-white dark:bg-zinc-700 text-primary dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                            <span class="material-symbols-outlined text-[20px] block">grid_view</span>
                        </button>
                        <button wire:click="toggleView('list')" class="p-2 rounded-md transition-all {{ $viewMode === 'list' ? 'bg-white dark:bg-zinc-700 text-primary dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                            <span class="material-symbols-outlined text-[20px] block">list</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Row 3: Search -->
            <div class="relative w-full mb-8 group">
                <span class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none transition-colors group-focus-within:text-primary">
                    <span class="material-symbols-outlined text-slate-400 text-[24px]">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name, email, phone or ID..." class="block w-full pl-16 pr-6 py-4 bg-surface border border-border-main rounded-xl text-sm font-medium focus:border-primary/30 focus:ring-4 focus:ring-primary/5 transition-all shadow-sm">
                <div class="absolute inset-y-0 right-4 flex items-center">
                    <div class="px-3 py-1 bg-slate-100 dark:bg-zinc-800 rounded-lg text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] border border-slate-200 dark:border-slate-700">Enter to Search</div>
                </div>
            </div>
        </div>
    </div>
    
    @if($viewMode === 'grid')
        <!-- Grid View -->
        <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-x-6 gap-y-10">
            @foreach($customers as $customer)
                @php
                    $isBorrower = $customer->isBorrower();
                    $isSaver = $customer->isSaver();
                    $isGuarantor = $customer->hasRole('Guarantor');
                    
                    // Prioritize profile in this order
                    $profile = $customer->borrower ?? $customer->saver ?? $customer->guarantor;
                    
                    $uniqueId = 'N/A';
                    $profileRoute = '#';
                    $tagBg = 'bg-slate-400';
                    $cardBorder = 'border-slate-100';
                    $roleLabel = 'Customer';
                    
                    if ($customer->borrower) {
                        $uniqueId = $customer->borrower->custom_id ?? 'BR-'.substr($customer->id, 0, 5);
                        $profileRoute = route('borrower.profile', $customer->borrower->id);
                        $tagBg = 'bg-primary';
                        $cardBorder = 'border-primary/20';
                        $roleLabel = 'Borrower';
                    } elseif ($customer->saver) {
                        $uniqueId = $customer->saver->custom_id ?? 'SV-'.substr($customer->id, 0, 5);
                        $profileRoute = route('saver.profile', $customer->saver->id);
                        $tagBg = 'bg-green-600';
                        $cardBorder = 'border-green-600/20';
                        $roleLabel = 'Saver';
                    } elseif ($customer->guarantor) {
                        $uniqueId = $customer->guarantor->custom_id ?? 'GU-'.substr($customer->id, 0, 5);
                        $profileRoute = route('guarantor.profile', $customer->guarantor->id);
                        $tagBg = 'bg-blue-600';
                        $cardBorder = 'border-blue-600/20';
                        $roleLabel = 'Guarantor';
                    } else {
                        // Fallback for customer with no profile yet
                        $uniqueId = 'CUS-'.substr($customer->id, 0, 5);
                    }
                    
                    $initials = collect(explode(' ', $customer?->name ?? ''))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                    
                    $kycStatus = $profile?->kyc_status ?? 'approved';
                    $kycColor = match($kycStatus) {
                        'approved' => 'green',
                        'pending' => 'amber',
                        'rejected' => 'red',
                        default => 'gray'
                    };

                    $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                    $colorClass = $colors[ord(substr($customer?->name ?? ' ', 0, 1)) % count($colors)];
                    
                    $allRoles = $customer->getRoleNames()->toArray();
                @endphp
                <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 pt-8 rounded-[4px] rounded-tl-xl border {{ $cardBorder }} shadow-sm hover:shadow-md transition-all duration-300">
                    <!-- Modern ID Tag resting on the border (Flush with left edge) -->
                    <div class="absolute -top-3 left-0">
                        <a href="{{ $profileRoute }}" class="inline-flex px-5 py-1.5 rounded-none rounded-tr-xl rounded-bl-xl {{ $tagBg }} text-white text-[9px] font-black uppercase tracking-widest shadow-lg border border-white/20 hover:scale-[1.02] active:scale-95 transition-all">
                            {{ $uniqueId }}
                        </a>
                    </div>

                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="size-14 rounded-lg {{ $colorClass }} flex items-center justify-center border border-white dark:border-[#2d3344] shadow-sm">
                                    <span class="font-black text-lg tracking-tighter">{{ $initials }}</span>
                                </div>
                                <div class="absolute -bottom-0.5 -right-0.5 size-4 rounded-full border-2 border-white dark:border-[#1a1f2e] bg-{{ $kycColor }}-500 shadow-sm"></div>
                            </div>
                            <div>
                                <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight truncate max-w-[110px]">{{ fetch_data($customer?->name ?? null) }}</h3>
                                <p class="text-[#606b8a] text-xs truncate max-w-[110px]">{{ fetch_data($customer?->phone ?? 'N/A' ?? null) }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-1.5">
                            @if($customer->borrower)
                                <a href="{{ fetch_data(route('borrower.loans', $customer?->borrower?->id) ?? null) }}" class="px-2.5 py-1 rounded-lg bg-primary text-white text-[10px] font-black uppercase tracking-tight shadow-sm hover:bg-blue-700 transition-colors text-center">
                                    Loan
                                </a>
                            @endif
                            @if(!$isGuarantor)
                                <a href="{{ fetch_data(route('savings.show', $customer?->id) ?? null) }}" class="px-2.5 py-1 rounded-lg bg-green-600 text-white text-[10px] font-black uppercase tracking-tight shadow-sm hover:bg-green-700 transition-colors text-center">
                                    Savings
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            @if($customer->borrower)
                                <div>
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                                    <p class="text-[#111318] dark:text-white text-lg font-bold">₦{{ fetch_data($customer?->borrower?->total_debt?->format() ?? null) }}</p>
                                </div>
                            @elseif($isSaver)
                                <div>
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Joined</p>
                                    <p class="text-[#111318] dark:text-white text-sm font-bold">{{ fetch_data($customer?->created_at?->format('M Y') ?? null) }}</p>
                                </div>
                            @else
                                <div>
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Role</p>
                                    <p class="text-[#111318] dark:text-white text-sm font-bold">Guarantor</p>
                                </div>
                            @endif
                            
                            @if(!$isGuarantor)
                                <div class="text-right">
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Savings</p>
                                    <p class="text-green-600 text-lg font-bold">₦{{ fetch_data($customer?->savingsAccount?->balance?->format() ?? '0.00' ?? null) }}</p>
                                </div>
                            @else
                                <div class="text-right">
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Registered</p>
                                    <p class="text-zinc-400 text-xs font-bold">{{ fetch_data($customer?->created_at?->format('d/m/Y') ?? null) }}</p>
                                </div>
                            @endif
                        </div>

                        @if($isBorrower && $profile->trust_score > 0)
                            <div class="flex items-center justify-between pt-2 border-t border-zinc-50 dark:border-zinc-800">
                                <div>
                                    <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Score</p>
                                    <span class="text-sm font-black {{ fetch_data($profile?->trust_score >= 80 ? 'text-emerald-500' : ($profile?->trust_score >= 50 ? 'text-amber-500' : 'text-rose-500') ?? null) }}">
                                        {{ fetch_data($profile?->trust_score ?? null) }}%
                                    </span>
                                </div>
                                <div class="w-24 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    <div class="h-full {{ fetch_data($profile?->trust_score >= 80 ? 'bg-emerald-500' : ($profile?->trust_score >= 50 ? 'bg-amber-500' : 'bg-rose-500') ?? null) }}" style="width: {{ fetch_data($profile?->trust_score ?? null) }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- List View -->
        <div class="bg-white dark:bg-[#1a1f2e] rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-6 py-3">Type</th>
                            <th scope="col" class="px-6 py-3">Total Debt</th>
                            <th scope="col" class="px-6 py-3">Savings</th>
                            <th scope="col" class="px-6 py-3">KYC</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                            @php
                                $allRoles = $customer->getRoleNames()->toArray();
                                $roleColors = [
                                    'Borrower' => 'bg-blue-100 text-blue-700',
                                    'Saver' => 'bg-green-100 text-green-700',
                                    'Guarantor' => 'bg-slate-100 text-slate-700',
                                ];
                                
                                $isBorrower = $customer->isBorrower();
                                $isSaver = $customer->isSaver();
                                $isGuarantor = $customer->hasRole('Guarantor');
                                
                                $profile = $customer->borrower ?? $customer->saver ?? $customer->guarantor;
                                $initials = collect(explode(' ', $customer?->name ?? ''))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                                $kycStatus = $profile?->kyc_status ?? 'approved';
                                $kycColor = match($kycStatus) {
                                    'approved' => 'green',
                                    'pending' => 'amber',
                                    'rejected' => 'red',
                                    default => 'gray'
                                };
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <th scope="row" class="px-6 py-4 flex items-center gap-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="size-10 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-black">{{ $initials }}</div>
                                    <div>
                                        <div class="text-sm font-semibold">{{ fetch_data($customer?->name ?? null) }}</div>
                                        <div class="text-xs text-gray-500">{{ fetch_data($customer?->phone ?? 'N/A' ?? null) }}</div>
                                    </div>
                                </th>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($allRoles as $role)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight {{ $roleColors[$role] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $role }}
                                            </span>
                                        @empty
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight bg-gray-100 text-gray-700">
                                                Customer
                                            </span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($customer->borrower)
                                        ₦{{ fetch_data($customer?->borrower?->total_debt?->format() ?? null) }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-green-600 font-bold">
                                    @if(!$isGuarantor)
                                        ₦{{ fetch_data($customer?->savingsAccount?->balance?->format() ?? '0.00' ?? null) }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                     <span class="px-2 py-0.5 rounded-full bg-{{ $kycColor }}-100 dark:bg-{{ $kycColor }}-900/30 text-{{ $kycColor }}-700 dark:text-{{ $kycColor }}-400 text-[10px] font-bold uppercase">{{ $kycStatus }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        @if($customer->borrower)
                                            <a href="{{ fetch_data(route('borrower.loans', $customer?->borrower?->id) ?? null) }}" class="px-3 py-1 rounded-lg bg-primary text-white text-[10px] font-black uppercase tracking-tight">Loan</a>
                                        @endif
                                        @if(!$isGuarantor)
                                            <a href="{{ fetch_data(route('savings.show', $customer?->id) ?? null) }}" class="px-3 py-1 rounded-lg bg-green-600 text-white text-[10px] font-black uppercase tracking-tight">Savings</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Pagination Footer -->
    <div class="mt-12 pb-24">
        {{ fetch_data($customers?->links() ?? null) }}
    </div>
</div>
