<div class="min-h-screen bg-white p-6 flex flex-col">
    <!-- Progress Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold text-brand bg-brand-soft px-2 py-1 rounded">Step 1 of 3</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Let's get to know you</h1>
        <p class="text-slate-500 mt-1">We need your identity details to verify your profile.</p>
    </div>

    <form wire:submit="save" class="flex-1 flex flex-col gap-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Date of Birth</label>
            <input type="date" wire:model="date_of_birth" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg">
            @error('date_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Home Address</label>
            <textarea wire:model="address" rows="3" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-base" placeholder="Enter your full residential address"></textarea>
             @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">NIN (National Identity Number)</label>
            <input type="text" wire:model="national_identity_number" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="11-digit NIN">
             @error('national_identity_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

         <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">BVN (Bank Verification Number)</label>
            <input type="text" wire:model="bvn" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="11-digit BVN">
             @error('bvn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mt-10 pb-12">
            <button type="submit" class="w-full bg-brand text-white font-bold text-lg py-4 rounded-2xl shadow-xl shadow-brand/20 hover:opacity-90 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                Continue
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
    </form>
</div>
