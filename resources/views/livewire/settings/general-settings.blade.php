<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Organization Settings</h1>
        <p class="text-gray-500 mt-1">Manage your organization profile and contact information.</p>
    </div>

    <div class="grid grid-cols-12 gap-8">
        <!-- Sidebar Navigation (Reusable or logic-based) -->
        <nav class="col-span-12 lg:col-span-3 flex flex-col gap-1">
            <a class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-l-4 border-primary rounded-r-lg shadow-sm" href="{{ route('settings') }}">
                <span class="text-primary dark:text-white font-bold text-sm">General</span>
                <span class="material-symbols-outlined text-primary dark:text-white text-[18px]">chevron_right</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.security') }}">
                <span class="font-semibold text-sm">Security</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.team-members') }}">
                <span class="font-semibold text-sm">Team Members</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.roles') }}">
                <span class="font-semibold text-sm">Roles & Permissions</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.form-builder') }}">
                <span class="font-semibold text-sm">Form Customization</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.notifications') }}">
                <span class="font-semibold text-sm">Notifications</span>
            </a>
        </nav>

        <div class="col-span-12 lg:col-span-9 space-y-6">
            @if(!$organization)
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-12 text-center shadow-sm border border-gray-100 dark:border-zinc-800">
                    <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">business_center</span>
                    <h2 class="text-xl font-bold text-primary dark:text-white">No Organization Found</h2>
                    <p class="text-gray-500 mt-2">You are not currently associated with any lending organization.</p>
                    <a href="{{ route('register.org') }}" class="inline-block mt-6 px-6 py-2 bg-primary text-white font-bold rounded-lg text-sm">Register an Organization</a>
                </div>
            @else
                <form wire:submit.prevent="save" class="space-y-6">
                <!-- Organization Profile -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Organization Profile</h2>
                    
                    <div class="flex flex-wrap items-center gap-12 pb-6 border-b border-gray-100 dark:border-zinc-800">
                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div class="w-20 h-20 rounded-xl bg-gray-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 dark:border-zinc-700">
                                    @if($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif($currentLogo)
                                        <img src="{{ asset('storage/' . $currentLogo) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="material-symbols-outlined text-gray-400 text-3xl">add_a_photo</span>
                                    @endif
                                </div>
                                <input type="file" wire:model="logo" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-primary dark:text-white">Organization Logo</h4>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG or SVG. Max 2MB.</p>
                                <div wire:loading wire:target="logo" class="text-xs text-primary animate-pulse">Uploading...</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="relative group">
                                <div class="w-20 h-20 rounded-xl bg-gray-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 dark:border-zinc-700">
                                    @if($signature)
                                        <img src="{{ $signature->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif($currentSignature)
                                        <img src="{{ asset('storage/' . $currentSignature) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="material-symbols-outlined text-gray-400 text-3xl">draw</span>
                                    @endif
                                </div>
                                <input type="file" wire:model="signature" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-primary dark:text-white">Organization Signature</h4>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG or SVG. Max 2MB.</p>
                                <div wire:loading wire:target="signature" class="text-xs text-primary animate-pulse">Uploading...</div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Organization Name</label>
                            <input wire:model="name" type="text" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">RC Number</label>
                            <input wire:model="rc_number" type="text" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm" placeholder="RC-123456">
                            @error('rc_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Public Email</label>
                            <input wire:model="email" type="email" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Phone Number</label>
                            <input wire:model="phone" type="text" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Website</label>
                            <input wire:model="website" type="text" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm" placeholder="https://example.com">
                        </div>
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Physical Address</label>
                            <textarea wire:model="address" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- KYC Documents Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">KYC Compliance</h2>
                    <div class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl mb-6">
                        <p class="text-xs text-blue-700 dark:text-blue-400 font-medium leading-relaxed">
                            Upload your organization's registration documents (CAC, Tax ID, etc.) for verification. 
                            Your current KYC status is: <span class="font-bold uppercase">{{ $organization->kyc_status ?? 'N/A' }}</span>
                        </p>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Registration Document (PDF/Image)</label>
                        <input type="file" wire:model="kyc_document" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        <div wire:loading wire:target="kyc_document" class="text-xs text-primary animate-pulse">Uploading...</div>
                        @error('kyc_document') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Regional Settings -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Regional Settings</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">System Currency</label>
                            <select wire:model="currency" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                                <option value="NGN">Nigerian Naira (₦)</option>
                                <option value="USD">US Dollar ($)</option>
                                <option value="GBP">British Pound (£)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Loan Preferences -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Loan Preferences</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Default Interest Rate (Monthly %)</label>
                            <input wire:model="interest_rate" type="number" step="0.1" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Grace Period (Days)</label>
                            <input wire:model="grace_period" type="number" class="rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white focus:ring-primary focus:border-primary text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="submit" class="px-8 py-3 bg-primary text-white font-bold rounded-xl text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform active:scale-95">
                        Save Changes
                    </button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
