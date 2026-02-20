<div class="min-h-screen bg-white p-6 flex flex-col">
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold text-brand bg-brand-soft px-2 py-1 rounded">Step 3 of 3</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Employment Details</h1>
        <p class="text-slate-500 mt-1">This helps us determine your credit limit.</p>
    </div>

    <form wire:submit="save" class="flex-1 flex flex-col gap-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Employment Status</label>
            <select wire:model="employment_status" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg bg-white">
                <option value="Employed">Employed (Full-time)</option>
                <option value="Self-Employed">Self-Employed / Business</option>
                <option value="Contract">Contract / Freelance</option>
                <option value="Unemployed">Unemployed</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Employer / Business Name</label>
            <input type="text" wire:model="employer_name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="Company Name">
            @error('employer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Monthly Income (₦)</label>
            <input type="number" wire:model="monthly_income" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="0.00">
             @error('monthly_income') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mt-10 pb-12">
            <button type="submit" class="w-full bg-brand text-white font-bold text-lg py-4 rounded-2xl shadow-xl shadow-brand/20 hover:opacity-90 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                Finish Setup
                <span class="material-symbols-outlined">check</span>
            </button>
        </div>
    </form>
</div>
