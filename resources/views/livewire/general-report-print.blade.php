<div class="relative min-h-[1000px] flex flex-col font-sans pt-8">
    <!-- Action Button -->
    <div class="no-print absolute top-0 right-0 flex gap-3">
        <button onclick="window.print()" class="flex items-center gap-2 px-6 py-2 bg-primary text-white rounded-xl font-bold shadow-lg hover:bg-blue-700 transition-all">
            <span class="material-symbols-outlined">print</span>
            Print Report
        </button>
        <a href="{{ route('reports') }}" class="flex items-center gap-2 px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
            Back
        </a>
    </div>

    <!-- Header -->
    <header class="flex justify-between items-start border-b-4 border-slate-900 pb-8 mb-10">
        <div class="flex items-center gap-6">
            @if($organization->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($organization->logo_path) }}" class="h-20 w-auto object-contain">
            @else
                <div class="size-20 bg-primary rounded-2xl flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-5xl">account_balance</span>
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-black uppercase tracking-tighter text-slate-900 leading-none">{{ $organization->name }}</h1>
                <p class="text-lg font-bold text-primary uppercase tracking-widest mt-2">{{ $title }}</p>
                <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-[0.2em]">
                    Reporting Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">RC Number</p>
            <p class="text-sm font-bold text-slate-900">{{ $organization->rc_number ?? 'NOT RECORDED' }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-4">Document Type</p>
            <p class="text-sm font-bold text-slate-900">Official Internal Review</p>
        </div>
    </header>

    <!-- Executive Summary Stats -->
    @if($type !== 'staff_activity')
    <div class="grid grid-cols-4 gap-6 mb-12">
        <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Disbursed Capital</p>
            <p class="text-2xl font-black text-slate-900">₦{{ number_format($metrics['total_disbursed'] ?? 0, 2) }}</p>
            <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-tighter">{{ $metrics['disbursement_count'] ?? 0 }} New Loan(s)</p>
        </div>
        <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Recovered Funds</p>
            <p class="text-2xl font-black text-green-600">₦{{ number_format($metrics['total_collected'] ?? 0, 2) }}</p>
            <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-tighter">{{ $metrics['collection_count'] ?? 0 }} Payment(s)</p>
        </div>
        <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Overdue Exposure</p>
            <p class="text-2xl font-black text-red-600">₦{{ number_format($metrics['overdue_amount'] ?? 0, 2) }}</p>
            <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-tighter">Immediate Attention</p>
        </div>
        <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Growth Metric</p>
            <p class="text-2xl font-black text-primary">{{ $metrics['new_customers'] ?? 0 }}</p>
            <p class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-tighter">New Borrower(s)</p>
        </div>
    </div>
    @endif

    <!-- Detailed Analysis Grid -->
    @if($type !== 'staff_activity')
    <div class="grid grid-cols-2 gap-12 mb-12">
        <!-- Portfolio Snapshot -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-4 py-1.5 uppercase tracking-[0.2em] mb-6 inline-block">Portfolio Vital Signs</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-end border-b border-slate-100 pb-3">
                    <p class="text-xs font-bold text-slate-500 uppercase">Active Loan Count</p>
                    <p class="text-sm font-black text-slate-900">{{ $metrics['active_loans'] ?? 0 }} Loans</p>
                </div>
                <div class="flex justify-between items-end border-b border-slate-100 pb-3">
                    <p class="text-xs font-bold text-slate-500 uppercase">Asset Vault Valuation</p>
                    <p class="text-sm font-black text-slate-900">₦{{ number_format($metrics['vault_value'] ?? 0, 2) }}</p>
                </div>
                <div class="flex justify-between items-end border-b border-slate-100 pb-3">
                    <p class="text-xs font-bold text-slate-500 uppercase">Recovery Efficiency</p>
                    @php 
                        $totalDisbursed = $metrics['total_disbursed'] ?? 0;
                        $totalCollected = $metrics['total_collected'] ?? 0;
                        $efficiency = ($totalDisbursed > 0) ? ($totalCollected / $totalDisbursed) * 100 : 0;
                    @endphp
                    <p class="text-sm font-black text-slate-900">{{ number_format($efficiency, 1) }}% Ratio</p>
                </div>
            </div>
        </section>

        <!-- Commentary/Notes -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-4 py-1.5 uppercase tracking-[0.2em] mb-6 inline-block">Operational Notes</h2>
            <div class="text-[11px] leading-relaxed text-slate-600 bg-slate-50 p-6 rounded-2xl border border-slate-100 h-[140px]">
                <p>This report represents an aggregated view of the organization's lending activities for the specified period. Data is pulled from the Analyt Core Engine. Any discrepancies should be reported to the system administrator immediately.</p>
                <p class="mt-4 font-bold text-slate-400 italic">No manual overrides detected in this reporting cycle.</p>
            </div>
        </section>
    </div>
    @endif

    <!-- Personal Activity Log -->
    @if($type === 'staff_activity')
    <section class="flex-1 mb-12">
        <h2 class="text-xs font-black bg-slate-900 text-white px-4 py-1.5 uppercase tracking-[0.2em] mb-6 inline-block">Personal Action History</h2>
        <table class="w-full text-left border-collapse border border-slate-200">
            <thead>
                <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 border-r border-slate-200 w-32">Date & Time</th>
                    <th class="px-6 py-4 border-r border-slate-200 w-48">Action Category</th>
                    <th class="px-6 py-4">Detailed Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($activityLogs as $log)
                    <tr class="text-xs">
                        <td class="px-6 py-4 border-r border-slate-200 font-bold text-slate-900">{{ $log->created_at->format('M d, H:i') }}</td>
                        <td class="px-6 py-4 border-r border-slate-200 uppercase font-bold text-primary">{{ $log->title }}</td>
                        <td class="px-6 py-4 text-slate-600 leading-relaxed">{{ $log->message }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-bold italic">No records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
    @endif

    <!-- Staff Performance (Expanded for 'staff' type) -->
    @if(count($staffData) > 0 && $type !== 'staff_activity')
    <section class="flex-1 mb-12">
        <h2 class="text-xs font-black bg-slate-900 text-white px-4 py-1.5 uppercase tracking-[0.2em] mb-6 inline-block">Staff Activity Analytics</h2>
        <table class="w-full text-left border-collapse border border-slate-200">
            <thead>
                <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider border-b border-slate-200">
                    <th class="px-6 py-4 border-r border-slate-200">Personnel Name</th>
                    <th class="px-6 py-4 border-r border-slate-200">Designation</th>
                    <th class="px-6 py-4 border-r border-slate-200 text-center">Transactions</th>
                    <th class="px-6 py-4 text-right">Value Collected</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($staffData as $staff)
                    <tr class="text-xs">
                        <td class="px-6 py-4 border-r border-slate-200 font-bold text-slate-900">{{ strtoupper($staff['name']) }}</td>
                        <td class="px-6 py-4 border-r border-slate-200 uppercase font-bold text-slate-400">{{ $staff['role'] }}</td>
                        <td class="px-6 py-4 border-r border-slate-200 text-center font-black">{{ $staff['count'] }}</td>
                        <td class="px-6 py-4 text-right font-black text-green-600">₦{{ number_format($staff['collected'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-900 text-white font-black">
                    <td colspan="3" class="px-6 py-4 text-right uppercase tracking-widest text-[10px]">Aggregated Staff Recovery</td>
                    <td class="px-6 py-4 text-right">₦{{ number_format(collect($staffData)->sum('collected'), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </section>
    @endif

    <!-- Report Validation -->
    <footer class="mt-auto pt-12 grid grid-cols-2 gap-24 border-t border-slate-100">
        <div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Generated By</p>
            <p class="text-sm font-bold text-slate-900">{{ strtoupper(Auth::user()->name) }}</p>
            <p class="text-[10px] text-slate-500 uppercase font-medium">{{ Auth::user()->getRoleNames()->first() }}</p>
        </div>
        <div class="text-right">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8">Organization Signature/Stamp</p>
            <div class="flex justify-end items-end h-16">
                @if($organization->signature_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($organization->signature_path) }}" class="h-16 w-auto object-contain">
                @else
                    <div class="h-12 border-b border-dotted border-slate-400 w-2/3 ml-auto"></div>
                @endif
            </div>
        </div>
    </footer>

    <!-- Footer Address -->
    <div class="mt-12 text-center text-[9px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-100 pt-6">
        {{ $organization->address }} | {{ $organization->phone }} | {{ $organization->email }}
    </div>
</div>
