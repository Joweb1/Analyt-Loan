<div class="w-full mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('borrowers.index') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Borrower Profile</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Customer Information</h2>
        </div>
        <div class="flex gap-3">
             <button wire:click="toggleEdit" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <span class="material-symbols-outlined text-lg">{{ $isEditing ? 'close' : 'edit' }}</span>
                {{ $isEditing ? 'Cancel Edit' : 'Edit Profile' }}
            </button>
            @if($isEditing)
                <button wire:click="save" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Save Changes
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-1 lg:px-2 pb-8">
        <!-- Left Column: Photo & Basic Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-8 text-center">
                <div class="relative inline-block mb-6">
                    <div class="size-32 rounded-full border-4 border-slate-50 dark:border-slate-800 shadow-xl overflow-hidden mx-auto">
                        @if($new_photo)
                            <img src="{{ $new_photo->temporaryUrl() }}" class="size-full object-cover">
                        @elseif($photo_url)
                            <img src="{{ $photo_url }}" class="size-full object-cover">
                        @else
                            <div class="size-full bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-5xl">person</span>
                            </div>
                        @endif
                    </div>
                    @if($isEditing)
                        <label class="absolute bottom-0 right-0 size-10 rounded-full bg-primary text-white flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-xl">photo_camera</span>
                            <input type="file" wire:model="new_photo" class="hidden" accept="image/*">
                        </label>
                    @endif
                </div>

                <div class="space-y-1">
                    @if($isEditing)
                        <input wire:model="name" type="text" class="w-full text-center px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-lg font-black focus:ring-2 focus:ring-primary/20">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Full Name (Required, Max 255)</p>
                        @error('name') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @else
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $name }}</h3>
                    @endif
                    <p class="text-sm text-slate-500 font-medium uppercase tracking-widest">{{ $borrower->user->getRoleNames()->first() ?? 'Borrower' }}</p>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800/50 space-y-4">
                    <div class="flex flex-col text-left">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</span>
                            @if($isEditing)
                                <input wire:model="email" type="email" class="w-2/3 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-2 focus:ring-primary/20">
                            @else
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $email }}</span>
                            @endif
                        </div>
                        @if($isEditing)
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1 text-right">Valid Email (Required, Unique)</p>
                            @error('email') <span class="text-[10px] font-bold text-red-500 mt-1 block text-right">{{ $message }}</span> @enderror
                        @endif
                    </div>
                    <div class="flex flex-col text-left">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone</span>
                            @if($isEditing)
                                <input wire:model="phone" type="text" class="w-2/3 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-2 focus:ring-primary/20">
                            @else
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $phone }}</span>
                            @endif
                        </div>
                        @if($isEditing)
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1 text-right">Phone Number (Required, Max 20)</p>
                            @error('phone') <span class="text-[10px] font-bold text-red-500 mt-1 block text-right">{{ $message }}</span> @enderror
                        @endif
                    </div>
                </div>
            </div>

            <!-- KYC Quick Stats -->
            <div class="bg-gradient-to-br from-primary to-blue-700 rounded-2xl p-6 text-white shadow-xl shadow-primary/20">
                <h4 class="text-sm font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">verified_user</span>
                    Trust Verification
                </h4>
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                            <span>KYC Completion</span>
                            <span>85%</span>
                        </div>
                        <div class="w-full h-1.5 bg-white/20 rounded-full">
                            <div class="bg-white h-full rounded-full w-[85%]"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <p class="text-[9px] font-bold uppercase opacity-60">Credit Score</p>
                            <p class="text-lg font-black">{{ $borrower->credit_score ?? 0 }}</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <p class="text-[9px] font-bold uppercase opacity-60">Trust Level</p>
                            <p class="text-lg font-black">Grade A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Info & KYC -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">fingerprint</span>
                        Identification & KYC Data
                    </h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bank Verification Number (BVN)</label>
                        @if($isEditing)
                            <input wire:model="bvn" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">11 Digits (Optional)</p>
                            @error('bvn') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white font-mono">{{ $bvn ?? 'NOT PROVIDED' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">National Identity Number (NIN)</label>
                        @if($isEditing)
                            <input wire:model="national_identity_number" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">11 Digits (Optional)</p>
                            @error('national_identity_number') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white font-mono">{{ $national_identity_number ?? 'NOT PROVIDED' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Gender</label>
                        @if($isEditing)
                            <select wire:model="gender" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('gender') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $gender ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Date of Birth</label>
                        @if($isEditing)
                            <input wire:model="date_of_birth" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('date_of_birth') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white">{{ $date_of_birth ? \Carbon\Carbon::parse($date_of_birth)->format('M d, Y') : 'N/A' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Marital Status</label>
                        @if($isEditing)
                            <select wire:model="marital_status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('marital_status') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $marital_status ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Residential Address</label>
                        @if($isEditing)
                            <textarea wire:model="address" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20"></textarea>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Full address required</p>
                            @error('address') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $address ?? 'NO ADDRESS ON FILE' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">work</span>
                        Employment & Income
                    </h3>
                </div>
                <div class="p-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Employment Information</label>
                        @if($isEditing)
                            <textarea wire:model="employment_information" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20" placeholder="E.g. Senior Software Engineer at Google..."></textarea>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Employer, Role, and duration</p>
                            @error('employment_information') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300 leading-relaxed">{{ $employment_information ?? 'NO EMPLOYMENT DATA RECORDED' }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
