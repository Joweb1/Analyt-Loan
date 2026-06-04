<div class="relative min-h-[1000px] flex flex-col pt-8">
    <!-- Action Button (Floating/Fixed) -->
    <div class="no-print absolute top-0 right-0 flex gap-3">
        <button onclick="window.print()" class="flex items-center gap-2 px-6 py-2 bg-primary text-white rounded-xl font-bold shadow-lg hover:bg-blue-700 transition-all">
            <span class="material-symbols-outlined">print</span>
            Print Document
        </button>
        <a href="{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}" class="flex items-center gap-2 px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
            Back
        </a>
    </div>

    <!-- Header -->
    <header class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-8">
        <div class="flex items-center gap-4">
            @if($loan->organization->logo_path)
                <img src="{{ fetch_data($loan?->organization?->logo_url ?? null) }}" class="h-16 w-auto object-contain">
            @else
                <div class="size-16 bg-primary rounded-xl flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-4xl">account_balance</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">{{ fetch_data($loan?->organization?->name ?? null) }}</h1>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mt-1">Loan Agreement & Disbursement Advice</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">RC Number</p>
            <p class="text-sm font-bold text-slate-900">{{ fetch_data($loan?->organization?->rc_number ?? 'N/A' ?? null) }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-3">Date Generated</p>
            <p class="text-sm font-bold text-slate-900">{{ fetch_data(now()?->format('M d, Y') ?? null) }}</p>
        </div>
    </header>

    <div class="grid grid-cols-2 gap-12 mb-10">
        <!-- Section 1: Customer Information -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Borrower Information</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Full Name</p>
                    <p class="text-sm font-bold text-slate-900 uppercase">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Phone</p>
                        <p class="text-sm font-bold text-slate-900">{{ fetch_data($loan?->borrower?->phone ?? null) }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Email</p>
                        <p class="text-sm font-bold text-slate-900 truncate">{{ fetch_data($loan?->borrower?->user?->email ?? null) }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">BVN</p>
                        <p class="text-sm font-bold text-slate-900 font-mono">{{ fetch_data($loan?->borrower?->bvn ?? 'N/A' ?? null) }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">NIN</p>
                        <p class="text-sm font-bold text-slate-900 font-mono">{{ fetch_data($loan?->borrower?->national_identity_number ?? 'N/A' ?? null) }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Residential Address</p>
                    <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ fetch_data($loan?->borrower?->address ?? 'N/A' ?? null) }}</p>
                </div>
            </div>
        </section>

        <!-- Section 2: Tracking Details -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Tracking & Identification</h2>
            <div class="space-y-3">
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1">Loan Tracking ID</p>
                    <p class="text-xl font-black text-primary font-mono tracking-tighter">{{ fetch_data($loan?->loan_number ?? null) }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Product Type</p>
                    <p class="text-sm font-bold text-slate-900">{{ fetch_data($loan?->loan_product ?? null) }}</p>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Loan Status</p>
                    <p class="text-sm font-black text-{{ fetch_data(in_array($loan?->status, ['active', 'approved', 'repaid']) ? 'green' : 'red' ?? null) }}-600 uppercase tracking-widest">{{ fetch_data(str_replace('_', ' ', $loan?->status) ?? null) }}</p>
                </div>
            </div>
        </section>
    </div>

    <!-- Section 3: Detailed Loan Terms -->
    <section class="mb-10">
        <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Financial Terms & Breakdown</h2>
        <div class="grid grid-cols-4 gap-6 p-6 border border-slate-200 rounded-xl">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Principal Amount</p>
                <p class="text-lg font-black text-slate-900">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Interest Rate</p>
                <p class="text-lg font-black text-slate-900">{{ fetch_data($loan?->interest_rate ?? null) }}% <span class="text-[10px] text-slate-400 font-bold uppercase">{{ fetch_data($loan?->interest_type ?? null) }}</span></p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Loan Duration</p>
                <p class="text-lg font-black text-slate-900">{{ fetch_data($loan?->duration ?? null) }} {{ fetch_data(Str::plural($loan?->duration_unit, $loan?->duration) ?? null) }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Repayment Cycle</p>
                <p class="text-lg font-black text-slate-900 uppercase">{{ fetch_data($loan?->repayment_cycle ?? null) }}</p>
            </div>
            
            <div class="col-span-4 h-px bg-slate-100 my-2"></div>

            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Processing Fee</p>
                <p class="text-sm font-bold text-slate-900">₦{{ fetch_data($loan?->getCalculatedProcessingFee()?->format() ?? null) }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Insurance Fee</p>
                <p class="text-sm font-bold text-slate-900">₦{{ fetch_data($loan?->getCalculatedInsuranceFee()?->format() ?? null) }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Total Interest</p>
                @php $totalInterest = $loan->getTotalExpectedInterest(); @endphp
                <p class="text-sm font-bold text-slate-900">₦{{ fetch_data($totalInterest?->format() ?? null) }}</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Total Payable</p>
                <p class="text-sm font-black text-primary">₦{{ fetch_data($loan?->getTotalCost()?->format() ?? null) }}</p>
            </div>
        </div>
    </section>

    <!-- Section 4: Collateral (If any) -->
    @if($loan->collateral)
    <section class="mb-10">
        <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Security / Collateral</h2>
        <div class="flex gap-6 p-4 border border-dashed border-slate-200 rounded-xl">
            <div class="flex-1">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Asset Name</p>
                <p class="text-sm font-bold text-slate-900">{{ fetch_data($loan?->collateral?->name ?? null) }}</p>
                <p class="text-[10px] text-slate-500 mt-1">{{ fetch_data($loan?->collateral?->description ?? null) }}</p>
            </div>
            <div class="w-32">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Estimated Value</p>
                <p class="text-sm font-black text-slate-900">₦{{ fetch_data(number_format($loan?->collateral?->value, 2) ?? null) }}</p>
            </div>
            <div class="w-32">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Asset Status</p>
                <p class="text-sm font-bold text-slate-900 uppercase">{{ fetch_data(str_replace('_', ' ', $loan?->collateral?->status) ?? null) }}</p>
            </div>
        </div>
    </section>
    @endif

    <!-- Section 5: Agreement & Consent -->
    <section class="mb-12">
        <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Agreement & Declaration</h2>
        <div class="bg-slate-50 p-6 rounded-xl text-[11px] leading-relaxed text-slate-600 border border-slate-100">
            <p class="mb-4">I, <strong>{{ fetch_data(strtoupper($loan?->borrower?->user?->name) ?? null) }}</strong>, hereby confirm that the information provided above is true and accurate. I acknowledge receipt of the loan amount stated and agree to the repayment terms, including interest and fees as outlined in this document.</p>
            <p class="mb-4">I authorize <strong>{{ fetch_data(strtoupper($loan?->organization?->name) ?? null) }}</strong> to deduct repayments as scheduled and to take necessary legal actions, including the liquidation of any provided collateral, in the event of default. I understand that late payments may attract additional penalties as per the organization's policy.</p>
            <p>This agreement is governed by the laws of the Federal Republic of Nigeria.</p>
        </div>
    </section>

    <!-- Signatures -->
    <footer class="mt-auto pt-12 grid grid-cols-2 gap-24">
        <div class="border-t border-slate-300 pt-4">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Borrower Signature & Date</p>
            <div class="h-12 border-b border-dotted border-slate-400 w-2/3"></div>
        </div>
        <div class="border-t border-slate-300 pt-4 text-right">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Authorized Organization Signature</p>
            <div class="flex justify-end items-end h-16">
                @if($loan->organization->signature_path)
                    <img src="{{ fetch_data($loan?->organization?->signature_url ?? null) }}" class="h-16 w-auto object-contain">
                @else
                    <div class="h-12 border-b border-dotted border-slate-400 w-2/3 ml-auto"></div>
                @endif
            </div>
            <p class="text-xs font-bold text-slate-900 mt-2">{{ fetch_data($loan?->organization?->name ?? null) }} Official Stamp</p>
        </div>
    </footer>

    <!-- Footer Address -->
    <div class="mt-12 text-center text-[9px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-100 pt-6">
        {{ fetch_data($loan?->organization?->address ?? null) }} | {{ fetch_data($loan?->organization?->phone ?? null) }} | {{ fetch_data($loan?->organization?->email ?? null) }}
    </div>
</div>
