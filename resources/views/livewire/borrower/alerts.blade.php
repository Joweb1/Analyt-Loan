<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('borrower.home') }}" wire:navigate class="p-2 bg-white rounded-full text-slate-600 shadow-sm">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900">Alerts</h1>
        </div>
        @if($notifications->where('read_at', null)->count() > 0)
            <button wire:click="markAllAsRead" class="text-xs font-bold text-brand uppercase tracking-tighter">
                Mark all read
            </button>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white p-12 rounded-3xl text-center border border-slate-100 mt-10">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-4xl text-slate-300">notifications_off</span>
            </div>
            <h3 class="font-bold text-slate-900">All Clear!</h3>
            <p class="text-slate-500 text-sm">You don't have any notifications at the moment.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
                <div 
                    wire:key="{{ $notif->id }}"
                    wire:click="markAsRead('{{ $notif->id }}')"
                    class="bg-white p-4 rounded-2xl border transition-all {{ $notif->read_at ? 'border-slate-100 opacity-70' : 'border-brand/20 shadow-sm' }}"
                >
                    <div class="flex items-start gap-4">
                        <div class="size-10 rounded-xl flex items-center justify-center shrink-0 {{ $notif->read_at ? 'bg-slate-100 text-slate-400' : 'bg-brand-soft text-brand' }}">
                            <span class="material-symbols-outlined">
                                @if($notif->category === 'loan') monetization_on
                                @elseif($notif->category === 'repayment') payments
                                @elseif($notif->category === 'overdue') warning
                                @else notifications
                                @endif
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="text-sm font-bold text-slate-900 truncate pr-4">{{ $notif->title }}</h3>
                                <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $notif->message }}</p>
                            
                            @if($notif->action_link && !$notif->read_at)
                                <div class="mt-3">
                                    <a href="{{ $notif->action_link }}" wire:navigate class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-brand">
                                        Take Action
                                        <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                        @if(!$notif->read_at)
                            <div class="size-2 bg-brand rounded-full mt-2"></div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
