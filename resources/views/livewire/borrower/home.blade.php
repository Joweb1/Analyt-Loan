<div class="flex flex-col gap-6 p-6 min-h-screen bg-slate-50">
    <!-- Header / Organization Brand -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-slate-500 font-medium">{{ $greeting }},</p>
            <h1 class="text-2xl font-bold text-slate-900">{{ Auth::user()->first_name }}</h1>
        </div>
        @if($organization->logo_path)
            <img src="{{ $organization->logo_url }}" class="h-10 w-10 object-contain rounded-full border border-slate-200 bg-white" alt="Logo">
        @else
             <div class="h-10 w-10 rounded-full bg-brand flex items-center justify-center text-white font-bold">
                {{ substr($organization->name, 0, 1) }}
             </div>
        @endif
    </div>

    <!-- Credit Summary Card -->
    <div class="relative overflow-hidden rounded-3xl bg-slate-900 text-white p-6 shadow-xl shadow-slate-200">
        <!-- Decorative Background -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-t from-black/20 to-transparent"></div>

        <div class="relative z-10">
            @if($activeLoan)
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Outstanding Balance</span>
                    @php
                        $currency = $activeLoan->amount->getCurrency();
                        $repaidMinor = (int) $activeLoan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                        $repaid = new \App\ValueObjects\Money($repaidMinor, $currency);
                        $schedules = $activeLoan->scheduledRepayments;
                        
                        if ($schedules->isNotEmpty()) {
                            $totalDueMinor = (int) $schedules->sum(fn($s) => 
                                $s->principal_amount->getMinorAmount() + 
                                $s->interest_amount->getMinorAmount() + 
                                $s->penalty_amount->getMinorAmount()
                            );
                            $totalDue = new \App\ValueObjects\Money($totalDueMinor, $currency);
                        } else {
                            $totalDue = $activeLoan->amount->add($activeLoan->getTotalExpectedInterest());
                        }
                        
                        $balance = $totalDue->subtract($repaid);
                        if ($balance->getMinorAmount() < 0) $balance = new \App\ValueObjects\Money(0, $currency);
                        
                        $progress = $totalDue->isPositive() ? ($repaid->getMajorAmount() / $totalDue->getMajorAmount()) * 100 : 0;
                    @endphp
                    <h2 class="text-4xl font-bold tracking-tight">₦{{ $balance->format() }}</h2>
                    <div class="flex items-center gap-2 mt-2">
                        <div class="h-1.5 flex-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-emerald-400">{{ round($progress) }}% paid</span>
                    </div>
                    <div class="mt-6 flex gap-3">
                         <a href="{{ route('borrower.repayment') }}" wire:navigate class="flex-1 bg-white text-slate-900 text-sm font-bold py-3 rounded-xl flex items-center justify-center shadow-lg hover:bg-slate-100 transition-colors">
                            Repay Now
                        </a>
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Available Limit</span>
                    <h2 class="text-4xl font-bold tracking-tight">₦{{ $creditLimit->format() }}</h2>
                    <p class="text-sm text-slate-400 mt-1">You are eligible for a new loan.</p>
                    
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('borrower.borrow') }}" wire:navigate class="flex-1 bg-brand text-white text-sm font-bold py-3 rounded-xl flex items-center justify-center shadow-lg shadow-brand/20 hover:opacity-90 transition-colors">
                            Get Loan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-4 gap-4">
        <a href="{{ route('borrower.activity') }}" wire:navigate class="flex flex-col items-center gap-2 group">
            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-600 shadow-sm group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">receipt_long</span>
            </div>
            <span class="text-xs font-medium text-slate-600">History</span>
        </a>
        <a href="{{ route('borrower.account.support') }}" wire:navigate class="flex flex-col items-center gap-2 group">
            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-600 shadow-sm group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">support_agent</span>
            </div>
            <span class="text-xs font-medium text-slate-600">Support</span>
        </a>
        <a href="{{ route('borrower.account') }}" wire:navigate class="flex flex-col items-center gap-2 group">
            <div class="w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-600 shadow-sm group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">person</span>
            </div>
            <span class="text-xs font-medium text-slate-600">Profile</span>
        </a>
         <a href="{{ route('borrower.alerts') }}" wire:navigate class="flex flex-col items-center gap-2 group">
            <div class="relative w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-600 shadow-sm group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined">notifications</span>
                @if($unreadAlertsCount > 0)
                    <span class="absolute -top-1 -right-1 size-5 bg-brand text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-white">
                        {{ $unreadAlertsCount > 9 ? '9+' : $unreadAlertsCount }}
                    </span>
                @endif
            </div>
            <span class="text-xs font-medium text-slate-600">Alerts</span>
        </a>
    </div>

    <!-- Recent Activity Preview -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-900">Recent Alerts</h3>
            <a href="{{ route('borrower.alerts') }}" wire:navigate class="text-xs font-bold text-brand">See All</a>
        </div>
        
        <div class="flex flex-col gap-3">
            @forelse($recentAlerts as $alert)
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full {{ $alert->read_at ? 'bg-slate-100 text-slate-400' : 'bg-brand-soft text-brand' }} flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">
                                @if($alert->category === 'loan') monetization_on
                                @elseif($alert->category === 'repayment') payments
                                @else notifications
                                @endif
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $alert->title }}</p>
                            <p class="text-xs text-slate-500 truncate max-w-[200px]">{{ $alert->message }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-medium text-slate-400">{{ $alert->created_at->format('M d') }}</span>
                </div>
            @empty
                 <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm text-center">
                    <p class="text-xs text-slate-400">No recent alerts</p>
                 </div>
            @endforelse
        </div>
    </div>

    <!-- Push Notification Opt-in (Browser) -->
    <div id="push-prompt" class="hidden bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col gap-4">
        <div class="flex items-start gap-4">
            <div class="size-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">notifications_active</span>
            </div>
            <div>
                <h3 class="font-bold text-slate-900">Stay Updated</h3>
                <p class="text-xs text-slate-500 leading-relaxed">Enable push notifications to get instant updates on your loan approval and repayment status.</p>
            </div>
        </div>
        <button onclick="requestPushPermission()" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl text-sm">
            Enable Notifications
        </button>
    </div>

    <script>
        function checkNotificationPermission() {
            if ('Notification' in window) {
                const prompt = document.getElementById('push-prompt');
                if (Notification.permission === 'default') {
                    prompt.classList.remove('hidden');
                } else {
                    prompt.classList.add('hidden');
                }
            }
        }

        function requestPushPermission() {
            if (!('Notification' in window)) return;
            
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    // initWebPush is defined in webpush.js and auto-runs, 
                    // but we can trigger a manual check/subscribe here if needed
                    if (typeof window.initWebPush === 'function') {
                        window.initWebPush();
                    }
                    document.getElementById('push-prompt').classList.add('hidden');
                }
            });
        }

        document.addEventListener('livewire:navigated', checkNotificationPermission);
        checkNotificationPermission();
    </script>
</div>
