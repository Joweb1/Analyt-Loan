<div class="bg-white min-h-screen">
    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print sticky top-0 bg-slate-900 text-white p-4 flex justify-between items-center z-50 shadow-xl">
        <a href="{{ route('reports') }}" class="flex items-center gap-2 text-xs font-black uppercase tracking-widest hover:text-primary transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back to Dashboard
        </a>
        <div class="flex items-center gap-4">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Ready to print?</span>
            <button onclick="window.print()" class="px-6 py-2 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">print</span>
                Print Report
            </button>
        </div>
    </div>

    <div class="p-8 max-w-4xl mx-auto text-slate-900" id="printable-area">
        <!-- Header -->
        <div class="flex justify-between items-start border-b-4 border-slate-900 pb-6 mb-8">
            <div class="flex items-center gap-6">
                @if($organization->logo_url)
                    <img src="{{ $organization->logo_url }}" class="w-20 h-20 object-contain rounded-2xl">
                @else
                    <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-300">
                        <span class="material-symbols-outlined text-4xl">domain</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter leading-none mb-1">{{ $organization->name }}</h1>
                    @if($organization->rc_number)
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">RC: {{ $organization->rc_number }}</p>
                    @endif
                    <p class="text-xs font-bold text-slate-600 italic">{{ $organization->tagline ?? 'Professional Lending Services' }}</p>
                    <div class="mt-2 text-[9px] font-medium text-slate-400 space-y-0.5">
                        <p>{{ $organization->address }}</p>
                        <p>{{ $organization->phone }} | {{ $organization->email }}</p>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-block px-4 py-2 bg-slate-900 text-white rounded-xl mb-3">
                    <p class="text-[10px] font-black uppercase tracking-widest">{{ $type }} Report</p>
                </div>
                <p class="text-xs font-black text-slate-900">{{ $title }}</p>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter mt-1">
                    {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                </p>
            </div>
        </div>

        <!-- Global Summary -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-[2rem]">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Org Balance (Outstanding)</p>
                <h2 class="text-3xl font-black text-slate-900">₦ {{ number_format($metrics['orgBalance'], 2) }}</h2>
                <p class="text-[8px] font-bold text-slate-400 mt-1 italic">Snapshot of all active capital + expected interest.</p>
            </div>
            <div class="p-5 bg-slate-50 border border-slate-200 rounded-[2rem] relative">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Expected Interest (Lifetime)</p>
                <h2 class="text-3xl font-black text-blue-600">₦ {{ number_format($metrics['totalInterest'], 2) }}</h2>
                <div class="mt-3 flex gap-4 text-[9px] font-black uppercase">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        <span class="text-slate-500">Paid:</span>
                        <span class="text-green-600">₦{{ number_format($metrics['totalPaidInterest'], 2) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <span class="text-slate-500">Rem:</span>
                        <span class="text-blue-600">₦{{ number_format($metrics['remainingInterest'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            @php
                $perfItems = [
                    ['label' => 'Total Disbursed', 'value' => '₦ '.number_format($metrics['disbursed'], 2), 'color' => 'text-slate-900'],
                    ['label' => 'Total Collected', 'value' => '₦ '.number_format($metrics['collected'], 2), 'color' => 'text-green-600'],
                    ['label' => 'Net Savings', 'value' => '₦ '.number_format($metrics['totalSavings'], 2), 'color' => $metrics['totalSavings'] >= 0 ? 'text-emerald-600' : 'text-red-600'],
                    ['label' => 'Loans Issued', 'value' => number_format($metrics['totalLoansCount']), 'color' => 'text-slate-900'],
                    ['label' => 'New Customers', 'value' => number_format($metrics['newCustomers']), 'color' => 'text-slate-900'],
                    ['label' => 'Portfolio at Risk', 'value' => '₦ '.number_format($metrics['totalPAR'], 2), 'color' => 'text-red-600'],
                    ['label' => 'Gross Profit (PnL)', 'value' => '₦ '.number_format($metrics['totalPnL'], 2), 'color' => 'text-green-600', 'span' => 'col-span-2'],
                ];
            @endphp

            @foreach($perfItems as $item)
                <div class="p-3 border border-slate-100 rounded-2xl {{ $item['span'] ?? '' }}">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $item['label'] }}</p>
                    <p class="text-sm font-black {{ $item['color'] }}">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="border border-slate-100 p-4 rounded-[2rem]">
                <p class="text-[9px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Cash Flow Trends
                </p>
                <div class="h-44">
                    <canvas id="printCashFlowChart"></canvas>
                </div>
            </div>
            <div class="border border-slate-100 p-4 rounded-[2rem]">
                <p class="text-[9px] font-black uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Interest Performance
                </p>
                <div class="h-44">
                    <canvas id="printInterestChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Staff Highlights & Generated By -->
        <div class="grid grid-cols-3 gap-8 mb-12">
            <div class="col-span-2">
                <h3 class="text-[10px] font-black uppercase tracking-widest mb-4 border-b border-slate-100 pb-2">Top Staff Collections (Period)</h3>
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[8px] font-black text-slate-400 uppercase">
                            <th class="pb-2">Staff Member</th>
                            <th class="pb-2">Designation</th>
                            <th class="pb-2 text-right">Amount Collected</th>
                        </tr>
                    </thead>
                    <tbody class="text-[10px] font-bold text-slate-700">
                        @forelse($staffPerformance as $staff)
                            <tr class="border-b border-slate-50">
                                <td class="py-2">{{ $staff['name'] }}</td>
                                <td class="py-2 text-slate-400">{{ $staff['role'] }}</td>
                                <td class="py-2 text-right text-green-600 font-black">₦ {{ number_format($staff['collected'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-300 italic">No collections recorded in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Signature Section -->
            <div class="flex flex-col justify-end text-center">
                @if($organization->signature_url)
                    <img src="{{ $organization->signature_url }}" class="w-32 h-16 object-contain mx-auto mb-2 mix-blend-multiply">
                @else
                    <div class="h-16 mb-2"></div>
                @endif
                <div class="border-t-2 border-slate-900 pt-2">
                    <p class="text-xs font-black uppercase tracking-tighter">{{ $generatedBy->name }}</p>
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">{{ $generatedBy->getRoleNames()->first() ?? 'Authorized Officer' }}</p>
                    <p class="text-[8px] text-slate-400 mt-1">Generated: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-6 border-t border-slate-100 text-center">
            <p class="text-[8px] text-slate-400 font-medium italic">
                This document is a computer-generated financial report from the Analyt Loan Management System. 
                All data presented is subject to internal audit and verification. 
                &copy; {{ date('Y') }} {{ $organization->name }}. All rights reserved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const data = @json($chartData);
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 8, padding: 10, font: { size: 8, weight: 'bold' } } } },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 7 } }, grid: { display: false } },
                    x: { ticks: { font: { size: 7 } }, grid: { display: false } }
                }
            };

            new Chart(document.getElementById('printCashFlowChart'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Disbursed', data: data.disbursed, borderColor: '#0f172a', backgroundColor: '#0f172a05', fill: true, tension: 0.4, borderWidth: 1.5, pointRadius: 0 },
                        { label: 'Collected', data: data.collected, borderColor: '#22c55e', backgroundColor: '#22c55e05', fill: true, tension: 0.4, borderWidth: 1.5, pointRadius: 0 }
                    ]
                },
                options: commonOptions
            });

            new Chart(document.getElementById('printInterestChart'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'Expected', data: data.interestExpected, backgroundColor: '#3b82f6', borderRadius: 4 },
                        { label: 'Paid', data: data.interestPaid, backgroundColor: '#22c55e', borderRadius: 4 }
                    ]
                },
                options: commonOptions
            });
        });
    </script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
            #printable-area { width: 100% !important; max-width: none !important; padding: 0 !important; margin: 0 !important; }
            .rounded-[2rem], .rounded-2xl { border-radius: 1rem !important; }
            .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
            .bg-slate-900 { background-color: #0f172a !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
        @page { size: auto; margin: 15mm; }
    </style>
</div>
