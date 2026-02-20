<div x-data="{ open: @entangle('showModal') }" 
     x-show="open" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-4" 
     style="display: none;">
    
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
    
    <div class="relative bg-white dark:bg-[#1a1f2b] w-full max-w-lg rounded-[2.5rem] shadow-2xl flex flex-col overflow-hidden border border-slate-100 dark:border-slate-800">
        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800/50 flex items-center justify-between bg-gradient-to-r from-primary to-blue-900 text-white">
            <div>
                <h3 class="text-xl font-black tracking-tight">Direct Message</h3>
                <p class="text-[10px] font-bold uppercase tracking-widest opacity-70 mt-1">To: {{ $borrower->user->name }}</p>
            </div>
            <button @click="open = false" class="size-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="p-8">
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 px-1 tracking-wider">Message Title</label>
                    <input wire:model="title" type="text" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all font-bold text-slate-900 dark:text-white placeholder:text-slate-400" placeholder="E.g. Important Update Regarding Your Loan">
                    @error('title') <span class="text-[10px] font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 px-1 tracking-wider">Priority Level</label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['low' => 'Low', 'medium' => 'Normal', 'high' => 'High', 'critical' => 'Urgent'] as $val => $label)
                            <button wire:click="$set('priority', '{{ $val }}')" 
                                    class="py-2.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border-2 transition-all
                                    {{ $priority === $val ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20' : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-800 text-slate-400 hover:border-slate-200' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-2 px-1 tracking-wider">Your Message</label>
                    <textarea wire:model="message" rows="5" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-medium text-slate-700 dark:text-slate-300 placeholder:text-slate-400" placeholder="Type your message here..."></textarea>
                    @error('message') <span class="text-[10px] font-bold text-red-500 mt-2 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3">
                <button wire:click="sendMessage" class="w-full py-5 bg-primary text-white rounded-[1.5rem] font-black uppercase tracking-widest text-xs shadow-xl shadow-primary/30 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">send</span>
                    Send Notification
                </button>
                <p class="text-[9px] text-center text-slate-400 font-bold uppercase tracking-tighter">
                    This will trigger a real-time push notification to the borrower's device.
                </p>
            </div>
        </div>
    </div>
</div>
