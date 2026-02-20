<div class="pb-12" x-data="{ tab: 'overview' }">
    <!-- Profile Header / Hero -->
    <div class="relative bg-[#050811] dark:bg-black rounded-b-[3rem] shadow-2xl border-b border-white/5 mb-8 overflow-hidden">
        <!-- Cover Gradient -->
        <div class="h-64 bg-gradient-to-br from-[#0f1729] via-[#1e1b4b] to-[#2e1065] relative">
            <!-- Background Pattern (Fixed visibility) -->
            <div class="absolute inset-0 opacity-[0.15] mix-blend-soft-light" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-[#050811] via-transparent to-transparent"></div>
            
            <!-- Logout Button (Top Right) -->
            <button wire:click="logout" class="absolute top-8 right-8 px-5 py-2.5 bg-white/5 hover:bg-white/10 backdrop-blur-md text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 border border-white/10 shadow-xl">
                <span class="material-symbols-outlined text-[18px]">logout</span> Log Out
            </button>
        </div>
        
        <div class="px-10 pb-10">
            <div class="flex flex-col lg:flex-row items-center lg:items-end -mt-20 lg:-mt-16 gap-8 relative z-10 text-center lg:text-left">
                <!-- Avatar -->
                <div class="relative group">
                    <div class="size-40 rounded-[2.5rem] bg-[#050811] p-1.5 shadow-2xl ring-1 ring-white/10">
                        <div class="w-full h-full bg-gradient-to-br from-primary to-indigo-600 rounded-[2rem] flex items-center justify-center text-white text-6xl font-black shadow-inner">
                            {{ substr($name, 0, 1) }}
                        </div>
                    </div>
                    <!-- Online Status -->
                    <div class="absolute bottom-3 right-0 size-8 rounded-2xl bg-[#050811] flex items-center justify-center shadow-2xl ring-1 ring-white/10" title="Online Status">
                         <div class="size-4 rounded-full {{ auth()->user()->isOnline() ? 'bg-green-500 shadow-[0_0_12px_rgba(34,197,94,0.8)]' : 'bg-slate-600' }} border-2 border-[#050811]"></div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="flex-1 pb-4">
                    <div class="flex flex-col lg:flex-row items-center gap-4 mb-3">
                        <h1 class="text-4xl font-black text-white tracking-tighter leading-none">{{ $name }}</h1>
                        <span class="px-4 py-1.5 bg-blue-500/10 text-blue-400 rounded-xl text-[10px] font-black border border-blue-500/20 uppercase tracking-[0.2em] shadow-[0_0_20px_rgba(59,130,246,0.1)]">
                            {{ $role }}
                        </span>
                    </div>
                    <div class="flex flex-wrap justify-center lg:justify-start items-center gap-6 text-sm text-slate-400 font-medium">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">business</span>
                            {{ $organization }}
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">mail</span>
                            {{ $email }}
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">calendar_today</span>
                            Member since {{ $created_at }}
                        </span>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex flex-wrap justify-center bg-white/5 backdrop-blur-md p-1.5 rounded-2xl w-full lg:w-auto border border-white/5 shadow-2xl">
                    <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-white text-primary shadow-xl' : 'text-slate-400 hover:text-white'" class="flex-1 lg:flex-none px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                        Overview
                    </button>
                    <button @click="tab = 'edit'" :class="tab === 'edit' ? 'bg-white text-primary shadow-xl' : 'text-slate-400 hover:text-white'" class="flex-1 lg:flex-none px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                        Settings
                    </button>
                    <button @click="tab = 'security'" :class="tab === 'security' ? 'bg-white text-primary shadow-xl' : 'text-slate-400 hover:text-white'" class="flex-1 lg:flex-none px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                        Security
                    </button>
                    @if($is_borrower)
                        <button @click="tab = 'kyc'" :class="tab === 'kyc' ? 'bg-white text-primary shadow-xl' : 'text-slate-400 hover:text-white'" class="flex-1 lg:flex-none px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                            KYC Documents
                        </button>
                    @endif
                    <button @click="tab = 'activity'" :class="tab === 'activity' ? 'bg-white text-primary shadow-xl' : 'text-slate-400 hover:text-white'" class="flex-1 lg:flex-none px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition-all whitespace-nowrap">
                        Activity
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Overview Tab -->
        <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Stats -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if(!$is_borrower)
                        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <span class="material-symbols-outlined text-[100px] text-primary">assignment_ind</span>
                            </div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Assigned Loans</p>
                            <h3 class="text-3xl font-black text-primary dark:text-white mt-1">{{ $loans_assigned_count }}</h3>
                            <p class="text-xs text-green-600 mt-2 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">trending_up</span> Active Portfolio
                            </p>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <span class="material-symbols-outlined text-[100px] text-purple-600">groups</span>
                            </div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Customers Managed</p>
                            <h3 class="text-3xl font-black text-purple-600 dark:text-white mt-1">{{ $customers_managed_count }}</h3>
                            <p class="text-xs text-purple-600 mt-2 font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">verified</span> Verified Profiles
                            </p>
                        </div>
                    @else
                        <!-- Borrower Specific Stats -->
                        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <span class="material-symbols-outlined text-[100px] text-primary">payments</span>
                            </div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Active Loans</p>
                            <h3 class="text-3xl font-black text-primary dark:text-white mt-1">{{ auth()->user()->borrower?->loans()->where('status', 'active')->count() ?? 0 }}</h3>
                            <p class="text-xs text-blue-600 mt-2 font-medium flex items-center gap-1">
                                Current Liabilities
                            </p>
                        </div>

                        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 relative overflow-hidden group">
                            <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                <span class="material-symbols-outlined text-[100px] text-emerald-600">verified_user</span>
                            </div>
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Trust Score</p>
                            <h3 class="text-3xl font-black text-emerald-600 dark:text-white mt-1">{{ auth()->user()->borrower?->trust_score ?? 0 }}</h3>
                            <p class="text-xs text-emerald-600 mt-2 font-medium flex items-center gap-1">
                                Financial Reliability
                            </p>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                            <span class="material-symbols-outlined text-[100px] text-orange-600">history</span>
                        </div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide">Days Active</p>
                        <h3 class="text-3xl font-black text-orange-600 dark:text-white mt-1 text-2xl whitespace-nowrap">{{ $days_active_string }}</h3>
                        <p class="text-xs text-orange-600 mt-2 font-medium flex items-center gap-1">
                            Since {{ $created_at }}
                        </p>
                    </div>
                </div>

                <!-- Recent Activity Preview -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Activity</h3>
                        <button @click="tab = 'activity'" class="text-sm font-bold text-primary hover:text-primary/80">View All</button>
                    </div>
                    <div class="space-y-6">
                        @forelse($activity_logs->take(3) as $log)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div class="size-3 rounded-full bg-gray-200 dark:bg-zinc-700"></div>
                                    <div class="w-0.5 h-full bg-gray-100 dark:bg-zinc-800 mt-1"></div>
                                </div>
                                <div class="pb-6">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $log->title }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $log->message }}</p>
                                    <p class="text-[10px] text-gray-400 mt-2 font-medium uppercase tracking-wider">{{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <span class="material-symbols-outlined text-4xl text-gray-300">history_toggle_off</span>
                                <p class="text-gray-500 text-sm mt-2">No recent activity found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Contact & Identification Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Verified Information</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                            <div class="size-10 rounded-lg bg-white dark:bg-zinc-700 flex items-center justify-center text-gray-500 shadow-sm">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Phone</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $phone ?? 'Not set' }}</p>
                            </div>
                        </div>

                        @if($is_borrower && $kyc_status === 'approved')
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="size-10 rounded-lg bg-white dark:bg-zinc-700 flex items-center justify-center text-primary shadow-sm">
                                    <span class="material-symbols-outlined">fingerprint</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold">BVN / NIN</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $bvn }} / {{ $nin }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="size-10 rounded-lg bg-white dark:bg-zinc-700 flex items-center justify-center text-purple-600 shadow-sm">
                                    <span class="material-symbols-outlined">work</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold">Employment</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[180px]">{{ $employment_information ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                                <div class="size-10 rounded-lg bg-white dark:bg-zinc-700 flex items-center justify-center text-gray-500 shadow-sm">
                                    <span class="material-symbols-outlined">mail</span>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold">Email Address</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $email }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                            <div class="size-10 rounded-lg bg-white dark:bg-zinc-700 flex items-center justify-center text-gray-500 shadow-sm">
                                <span class="material-symbols-outlined">schedule</span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Last Active</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $last_seen_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Setup Progress -->
                <div class="bg-gradient-to-br from-primary to-indigo-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute right-0 top-0 opacity-10">
                         <span class="material-symbols-outlined text-[150px]">verified_user</span>
                    </div>
                    <h3 class="text-lg font-bold mb-2 relative z-10">Profile Strength</h3>
                    <div class="w-full bg-white/20 rounded-full h-2 mb-4 relative z-10">
                        <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: {{ $profile_strength }}%"></div>
                    </div>
                    <p class="text-sm font-medium relative z-10 mb-4">Your profile is {{ $profile_strength }}% complete. {{ $profile_strength < 100 ? 'Update your phone or secondary details to reach 100%.' : 'Your profile is fully optimized!' }}</p>
                    <button @click="tab = 'edit'" class="px-4 py-2 bg-white text-primary rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 transition relative z-10">
                        Complete Setup
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Profile Tab -->
        <div x-show="tab === 'edit'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-2xl mx-auto">
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                <div class="mb-8 border-b border-gray-100 dark:border-zinc-800 pb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Edit Personal Details</h3>
                    <p class="text-gray-500 text-sm mt-1">Update your personal information. Phone number is locked after registration for security.</p>
                </div>

                <form wire:submit.prevent="updateProfile" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                        <input type="text" wire:model="name" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email Address</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-400 dark:text-gray-500 mb-2 flex items-center gap-1.5">
                                Phone Number <span class="material-symbols-outlined text-[14px]">lock</span>
                            </label>
                            <input type="text" wire:model="phone" readonly class="w-full rounded-xl border-gray-200 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/50 text-gray-400 dark:text-gray-500 cursor-not-allowed text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6">
                        <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 transition shadow-lg shadow-primary/20">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KYC Tab (Borrowers Only) -->
        @if($is_borrower)
        <div x-show="tab === 'kyc'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto space-y-8">
            <!-- KYC Status Header -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4 text-center md:text-left">
                    <div class="size-16 rounded-2xl {{ $kyc_status === 'approved' ? 'bg-green-100 text-green-600' : ($kyc_status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600') }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[32px]">{{ $kyc_status === 'approved' ? 'verified' : ($kyc_status === 'rejected' ? 'report' : 'pending_actions') }}</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">KYC Verification: {{ ucfirst($kyc_status) }}</h3>
                        <p class="text-gray-500 text-sm">
                            @if($kyc_status === 'approved')
                                Your identity has been verified. You have full access to lending features.
                            @elseif($kyc_status === 'pending')
                                Your documents are currently under review by our compliance team.
                            @else
                                Your KYC was rejected. Please update your details and resubmit.
                            @endif
                        </p>
                    </div>
                </div>
                @if($kyc_status !== 'approved')
                    <div class="px-4 py-2 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 rounded-lg text-xs font-bold uppercase border border-yellow-100 dark:border-yellow-800/30">
                        Restricted Access
                    </div>
                @endif
            </div>

            <!-- KYC Form -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                <div class="mb-8 border-b border-gray-100 dark:border-zinc-800 pb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Complete Verification</h3>
                    <p class="text-gray-500 text-sm mt-1">Please provide accurate information to unlock higher loan limits.</p>
                </div>

                <form wire:submit.prevent="completeKyc" class="space-y-8">
                    <!-- Identification -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Personal Identification</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Date of Birth</label>
                            <input type="date" wire:model="dob" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                            @error('dob') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                            <select wire:model="gender" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            @error('gender') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">BVN (11 Digits)</label>
                            <input type="text" wire:model="bvn" maxlength="11" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm" placeholder="222XXXXXXXX">
                            @error('bvn') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">NIN (11 Digits)</label>
                            <input type="text" wire:model="nin" maxlength="11" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm" placeholder="123XXXXXXXX">
                            @error('nin') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Residence -->
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Residential Details</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Home Address</label>
                            <textarea wire:model="address" rows="2" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm"></textarea>
                            @error('address') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Financial & Employment -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Financial Background</h4>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Marital Status</label>
                            <select wire:model="marital_status" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                            @error('marital_status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Number of Dependents</label>
                            <input type="number" wire:model="dependents" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Employment Info (Employer & Role)</label>
                            <textarea wire:model="employment_information" rows="2" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm" placeholder="e.g. Acme Corp - Sales Manager"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Bank Account Details (Bank & Account Number)</label>
                            <input type="text" wire:model="bank_account_details" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm" placeholder="GTBank - 0123456789">
                            @error('bank_account_details') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-6">
                        <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold hover:bg-primary/90 transition shadow-lg shadow-primary/20">
                            {{ $kyc_status === 'rejected' ? 'Resubmit for Review' : 'Submit for Verification' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Security Tab -->
        <div x-show="tab === 'security'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-2xl mx-auto space-y-8">
            <!-- Password Change -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                <div class="mb-8 border-b border-gray-100 dark:border-zinc-800 pb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Change Password</h3>
                    <p class="text-gray-500 text-sm mt-1">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <form wire:submit.prevent="updatePassword" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                        <input type="password" wire:model="current_password" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                        @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                        <input type="password" wire:model="new_password" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                        @error('new_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Confirm New Password</label>
                        <input type="password" wire:model="new_password_confirmation" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 focus:border-primary focus:ring-primary shadow-sm text-sm">
                        @error('new_password_confirmation') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-6">
                        <button type="submit" class="px-8 py-3 bg-gray-900 dark:bg-white dark:text-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition shadow-lg">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Active Sessions (Mock) -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                 <div class="mb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Active Sessions</h3>
                    <p class="text-gray-500 text-sm mt-1">Where you're logged in.</p>
                </div>
                
                <div class="flex items-center gap-4 p-4 bg-green-50 dark:bg-green-900/10 border border-green-100 dark:border-green-800/30 rounded-xl">
                    <div class="size-10 bg-green-100 dark:bg-green-900/30 text-green-600 rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined">devices</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">This Browser (Chrome on Android)</p>
                        <p class="text-xs text-green-600 font-medium">Active Now</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Tab -->
        <div x-show="tab === 'activity'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="max-w-4xl mx-auto">
             <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 p-8">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white">My Activity History</h3>
                        <p class="text-gray-500 text-sm mt-1">A personal timeline of your actions.</p>
                    </div>
                    <button wire:click="exportLog" wire:loading.attr="disabled" class="flex items-center gap-2 text-sm font-bold text-primary hover:underline disabled:opacity-50">
                        <span wire:loading.remove wire:target="exportLog">Export & Print Log</span>
                        <span wire:loading wire:target="exportLog">Preparing...</span>
                    </button>
                </div>

                <div class="relative border-l border-gray-200 dark:border-zinc-800 ml-4 space-y-8">
                    @forelse($activity_logs as $log)
                        <div class="ml-8 relative">
                            <!-- Dot -->
                            <div class="absolute -left-[41px] top-1 size-5 bg-white dark:bg-zinc-900 border-4 border-gray-200 dark:border-zinc-700 rounded-full"></div>
                            
                            <div class="bg-gray-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-gray-100 dark:border-zinc-800/50">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $log->title }}</h4>
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white dark:bg-zinc-800 px-2 py-1 rounded border border-gray-100 dark:border-zinc-700">
                                        {{ $log->created_at->format('M d, H:i') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $log->message }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <span class="material-symbols-outlined text-5xl text-gray-200 dark:text-zinc-700">history</span>
                            <p class="text-gray-500 mt-4 font-medium">No activity records found.</p>
                        </div>
                    @endforelse
                </div>
             </div>
        </div>
    </div>
</div>
    </div>
</div>

