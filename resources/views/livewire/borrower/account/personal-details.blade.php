<div class="min-h-screen bg-white p-6 pb-32">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('borrower.account') }}" wire:navigate class="p-2 bg-slate-50 rounded-full text-slate-600">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Personal Details</h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Full Name</label>
            <input type="text" wire:model="name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Email Address</label>
            <input type="email" wire:model="email" disabled class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 text-lg">
            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold">Email cannot be changed</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Phone Number</label>
            <input type="text" wire:model="phone" disabled class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 text-lg">
            <p class="text-[10px] text-slate-400 mt-1 uppercase font-bold">Phone number cannot be changed</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Date of Birth</label>
            <input type="date" wire:model="dob" disabled class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-400 text-lg">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Home Address</label>
            <textarea wire:model="address" rows="3" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-base"></textarea>
            @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-brand text-white font-bold py-4 rounded-xl shadow-lg shadow-brand/20 hover:opacity-90 transition-all">
            Save Changes
        </button>
    </form>
</div>
