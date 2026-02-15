<div class="max-w-6xl mx-auto px-2 py-2 w-full">
    <!-- Section Header & Filters -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[#111318] dark:text-white text-[28px] font-bold tracking-tight">Manage Customers</h2>
            <p class="text-[#606b8a] text-sm mt-1">Manage {{ $borrowers->total() }} active borrowers</p>
        </div>
        <div class="flex gap-3 items-center flex-1 max-w-md">
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-[20px]">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name, email, phone, BVN, NIN..." class="block w-full pl-10 pr-4 py-2 bg-white dark:bg-[#1a1f2e] border border-[#e5e7eb] dark:border-[#2d3344] rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
            <div class="h-8 w-[1px] bg-[#e5e7eb] dark:bg-[#2d3344] mx-1"></div>
            <div class="flex bg-white dark:bg-[#1a1f2e] border border-[#e5e7eb] dark:border-[#2d3344] rounded-xl p-1 shadow-sm">
                <button wire:click="toggleView('grid')" class="p-1.5 rounded-lg {{ $viewMode === 'grid' ? 'bg-primary/10 text-primary' : 'text-[#606b8a] hover:bg-[#f0f1f5] dark:hover:bg-[#2d3344]' }}">
                    <span class="material-symbols-outlined text-[20px]">grid_view</span>
                </button>
                <button wire:click="toggleView('list')" class="p-1.5 rounded-lg {{ $viewMode === 'list' ? 'bg-primary/10 text-primary' : 'text-[#606b8a] hover:bg-[#f0f1f5] dark:hover:bg-[#2d3344]' }}">
                    <span class="material-symbols-outlined text-[20px]">list</span>
                </button>
            </div>
        </div>
    </div>
    
    @php
        $isKycApproved = Auth::user()->organization && Auth::user()->organization->kyc_status === 'approved';
    @endphp
    <div class="w-full max-w-[600px] mx-auto my-8 md:hidden">
        @if($isKycApproved)
            <a href="{{ route('customer.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-base">person_add</span> Add New Customer/Borrower
            </a>
        @else
            <div class="w-full px-4 py-3 bg-yellow-50 text-yellow-700 text-center text-xs font-bold rounded-lg border border-yellow-100">
                KYC Approval Required to Onboard Borrowers
            </div>
        @endif
    </div>

    @if($viewMode === 'grid')
        <!-- Grid of Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($borrowers as $borrower)
                @php
                    $totalDebt = $borrower->loans->sum('amount');
                    $initials = collect(explode(' ', $borrower->user->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                    $riskColor = match(true) {
                        $borrower->credit_score >= 750 => 'green',
                        $borrower->credit_score >= 600 => 'amber',
                        default => 'red'
                    };
                    $riskLabel = match(true) {
                        $borrower->credit_score >= 750 => 'Low Risk',
                        $borrower->credit_score >= 600 => 'Medium Risk',
                        default => 'High Risk'
                    };
                    $scorePercent = min(100, ($borrower->credit_score / 850) * 100);
                    
                    // Generate a consistent soft background color based on name
                    $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                    $colorClass = $colors[ord(substr($borrower->user->name, 0, 1)) % count($colors)];
                @endphp
                <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-3">
                            @if($borrower->photo_url)
                                <div class="size-14 rounded-full bg-cover bg-center border-2 border-white dark:border-[#2d3344] shadow-sm ring-1 ring-gray-100 dark:ring-white/5" style="background-image: url('{{ $borrower->photo_url }}')"></div>
                            @else
                                <div class="size-14 rounded-full {{ $colorClass }} flex items-center justify-center border-2 border-white dark:border-[#2d3344] shadow-sm ring-1 ring-gray-100 dark:ring-white/5">
                                    <span class="font-black text-lg tracking-tighter">{{ $initials }}</span>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight truncate max-w-[100px]">{{ $borrower->user->name }}</h3>
                                <p class="text-[#606b8a] text-xs truncate max-w-[100px]">{{ $borrower->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-1.5">
                            @if($isKycApproved)
                                <a href="{{ route('borrower.loans', $borrower->id) }}" class="px-2.5 py-1 rounded-lg bg-primary text-white text-[10px] font-black uppercase tracking-tight shadow-sm hover:bg-blue-700 transition-colors text-center">
                                    Loan
                                </a>
                            @else
                                <button disabled class="px-2.5 py-1 rounded-lg bg-gray-200 text-gray-400 text-[10px] font-black uppercase tracking-tight shadow-sm cursor-not-allowed">
                                    Locked
                                </button>
                            @endif
                            <a href="{{ route('savings.show', $borrower->id) }}" class="px-2.5 py-1 rounded-lg bg-green-600 text-white text-[10px] font-black uppercase tracking-tight shadow-sm hover:bg-green-700 transition-colors text-center">
                                Savings
                            </a>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                                <p class="text-[#111318] dark:text-white text-lg font-bold">₦{{ number_format($totalDebt, 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Savings</p>
                                <p class="text-green-600 text-lg font-bold">₦{{ number_format($borrower->savingsAccount->balance ?? 0, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <div>
                                <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p>
                                <span class="px-2 py-0.5 rounded-full bg-{{ $riskColor }}-100 dark:bg-{{ $riskColor }}-900/30 text-{{ $riskColor }}-700 dark:text-{{ $riskColor }}-400 text-[10px] font-bold uppercase">{{ $riskLabel }}</span>
                            </div>
                            <div class="relative size-12">
                                <svg class="size-full -rotate-90" viewbox="0 0 36 36">
                                    <circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
                                    <circle class="stroke-{{ $riskColor }}-500" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $scorePercent }}" stroke-linecap="round" stroke-width="3"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[#111318] dark:text-white text-[10px] font-bold">{{ round($scorePercent) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Borrower Card -->
            @if($isKycApproved)
                <a href="{{ route('customer.create') }}" class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-dashed border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden flex flex-col items-center justify-center cursor-pointer min-h-[240px]">
                    <div class="size-16 rounded-full bg-[#f0f1f5] dark:bg-[#2d3344] flex items-center justify-center mb-4 group-hover:bg-primary/10 transition-colors">
                        <span class="material-symbols-outlined text-[#606b8a] text-[32px] group-hover:text-primary transition-colors">person_add</span>
                    </div>
                    <p class="text-sm font-bold text-[#111318] dark:text-white group-hover:text-primary transition-colors">Add New Borrower</p>
                    <p class="text-xs text-[#606b8a] text-center mt-2 px-4">Register a new customer to the system</p>
                </a>
            @else
                <div class="group relative bg-gray-50 dark:bg-[#1a1f2e] p-5 rounded-xl border border-dashed border-[#e5e7eb] dark:border-[#2d3344] shadow-sm flex flex-col items-center justify-center min-h-[240px]">
                    <div class="size-16 rounded-full bg-[#f0f1f5] dark:bg-[#2d3344] flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-gray-400 text-[32px]">lock</span>
                    </div>
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500">Locked</p>
                    <p class="text-[10px] text-yellow-600 font-bold text-center mt-2 px-4 uppercase">KYC Approval Required</p>
                </div>
            @endif
        </div>
    @else
        <!-- List View -->
        <div class="bg-white dark:bg-[#1a1f2e] rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Borrower</th>
                            <th scope="col" class="px-6 py-3">Total Debt</th>
                            <th scope="col" class="px-6 py-3">Savings</th>
                            <th scope="col" class="px-6 py-3">Risk Level</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowers as $borrower)
                            @php
                                $totalDebt = $borrower->loans->sum('amount');
                                $savingsBalance = $borrower->savingsAccount->balance ?? 0;
                                $initials = collect(explode(' ', $borrower->user->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                                $riskColor = match(true) {
                                    $borrower->credit_score >= 750 => 'green',
                                    $borrower->credit_score >= 600 => 'amber',
                                    default => 'red'
                                };
                                $riskLabel = match(true) {
                                    $borrower->credit_score >= 750 => 'Low Risk',
                                    $borrower->credit_score >= 600 => 'Medium Risk',
                                    default => 'High Risk'
                                };
                                $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                                $colorClass = $colors[ord(substr($borrower->user->name, 0, 1)) % count($colors)];
                            @endphp
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <th scope="row" class="px-6 py-4 flex items-center gap-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    @if($borrower->photo_url)
                                        <div class="size-10 rounded-full bg-cover bg-center border border-gray-100" style="background-image: url('{{ $borrower->photo_url }}')"></div>
                                    @else
                                        <div class="size-10 rounded-full {{ $colorClass }} flex items-center justify-center border border-gray-100 text-[10px] font-black tracking-tighter">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-semibold">{{ $borrower->user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $borrower->phone ?? 'N/A' }}</div>
                                    </div>
                                </th>
                                <td class="px-6 py-4">
                                    ₦{{ number_format($totalDebt, 2) }}
                                </td>
                                <td class="px-6 py-4 text-green-600 font-bold">
                                    ₦{{ number_format($savingsBalance, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                     <span class="px-2 py-0.5 rounded-full bg-{{ $riskColor }}-100 dark:bg-{{ $riskColor }}-900/30 text-{{ $riskColor }}-700 dark:text-{{ $riskColor }}-400 text-[10px] font-bold uppercase">{{ $riskLabel }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex gap-2 justify-end">
                                        @if($isKycApproved)
                                            <a href="{{ route('borrower.loans', $borrower->id) }}" class="px-3 py-1 rounded-lg bg-primary text-white text-[10px] font-black uppercase tracking-tight hover:bg-blue-700 transition-colors text-center">Loan</a>
                                        @else
                                            <button disabled class="px-3 py-1 rounded-lg bg-gray-200 text-gray-400 text-[10px] font-black uppercase tracking-tight cursor-not-allowed">Locked</button>
                                        @endif
                                        <a href="{{ route('savings.show', $borrower->id) }}" class="px-3 py-1 rounded-lg bg-green-600 text-white text-[10px] font-black uppercase tracking-tight hover:bg-green-700 transition-colors text-center">Savings</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-[#e5e7eb] dark:border-[#2d3344] text-center">
                 @if($isKycApproved)
                    <a href="{{ route('customer.create') }}" class="text-sm font-bold text-primary hover:underline">Add New Borrower</a>
                 @else
                    <span class="text-xs font-bold text-yellow-600 uppercase">KYC Approval Required</span>
                 @endif
            </div>
        </div>
    @endif

    <!-- Pagination Footer -->
    <div class="mt-12 pb-24">
        {{ $borrowers->links() }}
    </div>
</div>