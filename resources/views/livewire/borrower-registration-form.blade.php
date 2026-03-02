<form class="space-y-8" wire:submit.prevent="save">
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Oops!</strong>
            <span class="block sm:inline">Something went wrong. Please check the form for errors.</span>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Organization Selection (if not pre-set) --}}
    @if(!Auth::check() || !Auth::user()->organization_id)
        <div class="bg-blue-50 dark:bg-zinc-800 p-6 rounded-2xl border border-blue-100 dark:border-zinc-700 mb-8">
            <h3 class="text-lg font-bold text-primary dark:text-white mb-4">Select Organization</h3>
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Organization</label>
                <select wire:model.live="organization_id" class="w-full px-5 py-4 bg-white dark:bg-zinc-900 border-2 border-zinc-100 dark:border-zinc-700 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                    <option value="">Select an Organization</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
                @error('organization_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif

    @if(empty($configs))
        {{-- FALLBACK HARDCODED FORM (Existing) --}}
        {{-- Identity & Contact --}}
        <div class="space-y-8">
            <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Identity & Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Full Name -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Full Name</label>
                    <input wire:model="name" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="John Olusegun" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Max 255 characters</p>
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Email -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Email Address</label>
                    <input wire:model="email" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="john@example.com" type="email"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional, Valid email format</p>
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Phone Number -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Phone Number</label>
                    <div class="flex gap-3">
                        <div class="flex items-center justify-center px-4 bg-zinc-100 dark:bg-zinc-800 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl font-bold text-sm text-zinc-600 dark:text-zinc-400">
                            +234
                        </div>
                        <input wire:model="phone" class="flex-1 px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="801 234 5678" type="tel"/>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Unique across system</p>
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Date of Birth -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Date of Birth</label>
                    <input wire:model="dob" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="date"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Valid date</p>
                    @error('dob') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Gender -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Gender</label>
                    <select wire:model="gender" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Residential Address -->
                <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Residential Address</label>
                    <textarea wire:model="address" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium resize-none" placeholder="No. 12 Adeola Hopewell St, Victoria Island, Lagos" rows="3"></textarea>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Full residential address</p>
                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Identification Documents --}}
        <div class="space-y-8 pt-8">
            <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Identification Documents</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- BVN -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest">BVN (11 Digits)</label>
                    <input wire:model="bvn" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium tracking-[0.2em]" maxlength="11" placeholder="22345678901" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Exactly 11 digits</p>
                    @error('bvn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- NIN -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest">NIN (11 Digits)</label>
                    <input wire:model="nin" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium tracking-[0.2em]" maxlength="11" placeholder="12345678901" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Exactly 11 digits</p>
                    @error('nin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Passport Photograph -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Passport Photograph</label>
                    <div class="relative">
                        <input wire:key="passport_photo_input" wire:model="passport_photo" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                        <div wire:loading wire:target="passport_photo" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Image file (Max 5MB)</p>
                    @error('passport_photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                 <!-- Biometric Data -->
                 <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Biometric Data (Optional)</label>
                    <div class="relative">
                        <input wire:key="biometric_data_input" wire:model="biometric_data" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                        <div wire:loading wire:target="biometric_data" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional file upload (Max 10MB)</p>
                    @error('biometric_data') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- ID Document -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">ID Document (Passport, Driver's License, etc)</label>
                    <div class="relative">
                        <input wire:key="identity_document_input" wire:model="identity_document" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                        <div wire:loading wire:target="identity_document" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required file upload (Max 10MB)</p>
                    @error('identity_document') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Financial & Employment Details --}}
        <div class="space-y-8 pt-8" x-data="{ is_employed: @entangle('is_employed') }">
            <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Financial & Employment Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Bank Name -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Bank Name</label>
                    <input wire:model="bank_name" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="GTBank" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Account Number -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Account Number</label>
                    <input wire:model="account_number" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="0123456789" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, 10 digits</p>
                    @error('account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Account Name -->
                <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Account Name</label>
                    <input wire:model="bank_account_name" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="John Olusegun" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Name on bank account</p>
                    @error('bank_account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                 <!-- Bank Statement -->
                 <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Bank Statement (Optional)</label>
                    <div class="relative">
                        <input wire:key="bank_statement_input" wire:model="bank_statement" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                        <div wire:loading wire:target="bank_statement" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional, PDF or Image (Max 10MB)</p>
                    @error('bank_statement') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Employment Status Switch -->
                <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Employment Status</label>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium" :class="!is_employed ? 'text-primary' : 'text-zinc-400'">Self-Employed</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="is_employed" class="sr-only peer">
                            <div class="w-14 h-8 bg-zinc-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/10 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-zinc-600 peer-checked:bg-primary"></div>
                        </label>
                        <span class="text-sm font-medium" :class="is_employed ? 'text-primary' : 'text-zinc-400'">Employed</span>
                    </div>
                    @error('is_employed') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Employment fields -->
                <template x-if="is_employed">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:col-span-2">
                        <!-- Employer Name -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Employer Name</label>
                            <input wire:model="employer_name" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="Analytiq Inc." type="text"/>
                            <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required if employed</p>
                            @error('employer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <!-- Job Title -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Job Title</label>
                            <input wire:model="job_title" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="Software Engineer" type="text"/>
                            <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required if employed</p>
                            @error('job_title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <!-- Salary -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Salary</label>
                            <input wire:model="salary" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="350000" type="number"/>
                            <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required if employed, numeric</p>
                            @error('salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <!-- Employer Address -->
                        <div class="flex flex-col gap-2 md:col-span-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Employer Address</label>
                            <input wire:model="employer_address" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="123 Analytiq Avenue, Lagos" type="text"/>
                            <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required if employed</p>
                            @error('employer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </template>
                <!-- Income Proof -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Income Proof (Payslips, Tax Returns)</label>
                    <div class="relative">
                        <input wire:key="income_proof_input" wire:model="income_proof" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                        <div wire:loading wire:target="income_proof" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                    </div>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional, Max 10MB</p>
                    @error('income_proof') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Credit Score -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Credit Score (Optional)</label>
                    <input wire:model="credit_score" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="Enter credit score if available" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional</p>
                    @error('credit_score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Family & Social Information --}}
        <div class="space-y-8 pt-8">
            <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Family & Social Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Marital Status -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Marital Status</label>
                    <select wire:model="marital_status" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                        <option value="">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                    </select>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('marital_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Dependents -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Number of Dependents</label>
                    <input wire:model="dependents" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="0" type="number"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, numeric</p>
                    @error('dependents') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Next of Kin Name -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Next of Kin Name</label>
                    <input wire:model="next_of_kin_name" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="Jane Doe" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('next_of_kin_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Next of Kin Relationship -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Next of Kin Relationship</label>
                    <input wire:model="next_of_kin_relationship" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="Spouse" type="text"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('next_of_kin_relationship') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <!-- Next of Kin Phone -->
                <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Next of Kin Phone</label>
                    <input wire:model="next_of_kin_phone" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" placeholder="+2348012345678" type="tel"/>
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required</p>
                    @error('next_of_kin_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Guarantor Section --}}
        <div class="space-y-8 pt-8">
            <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Guarantor</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col gap-2 md:col-span-2">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Select Guarantor</label>
                    <livewire:components.guarantor-select />
                    <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Optional, Select from registered users or guarantors</p>
                    @error('guarantor_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

    @else
        {{-- DYNAMIC FORM --}}
        @foreach($configs as $sectionKey => $fields)
            <div class="space-y-8 pt-8">
                <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">
                    {{ ucfirst(str_replace('_', ' ', $sectionKey)) }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach($fields as $field)
                        @if($field['is_active'])
                            @php
                                $modelName = $field['is_system'] ? $field['name'] : 'customData.' . $field['name'];
                                $label = $field['label'];
                                $type = $field['type'];
                                $required = $field['is_required'] ? '*' : '';
                            @endphp

                            <div class="flex flex-col gap-2 {{ in_array($type, ['textarea', 'address']) ? 'md:col-span-2' : '' }}">
                                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">
                                    {{ $label }} {{ $required }}
                                </label>

                                @if($type === 'textarea')
                                    <textarea wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium resize-none" rows="3"></textarea>
                                @elseif($type === 'select')
                                    @if($field['name'] === 'guarantor_id')
                                        <livewire:components.guarantor-select />
                                    @else
                                        <select wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                                            <option value="">Select {{ $label }}</option>
                                            @if(isset($field['options']) && is_array($field['options']))
                                                @foreach($field['options'] as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @endif
                                @elseif($type === 'file')
                                    <div class="relative">
                                        <input wire:key="{{ $modelName }}_input" wire:model="{{ $modelName }}" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                                        <div wire:loading wire:target="{{ $modelName }}" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                                    </div>
                                @elseif($type === 'checkbox')
                                     {{-- Simple checkbox for now, not the toggle UI unless specialized --}}
                                     <div class="flex items-center gap-4">
                                        <input type="checkbox" wire:model="{{ $modelName }}" class="w-6 h-6 rounded border-zinc-300 text-primary focus:ring-primary">
                                    </div>
                                @else
                                    <input wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="{{ $type }}" />
                                @endif
                                
                                <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">
                                    {{ $field['is_required'] ? 'Required' : 'Optional' }} 
                                    @if(in_array($field['name'], ['bvn', 'nin'])) | Exactly 11 digits @endif
                                    @if($type === 'number' && !in_array($field['name'], ['bvn', 'nin'])) | Numeric @endif
                                    @if($type === 'email') | Valid email @endif
                                </p>
                                @error($modelName) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    {{-- Set Account Password (Always Required) --}}
    <div class="space-y-8 pt-8">
        <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Set Account Password</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Password -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Password</label>
                <input wire:model="password" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="password"/>
                <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Required, Min 8 characters</p>
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- Confirm Password -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Confirm Password</label>
                <input wire:model="password_confirmation" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="password"/>
                <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">Must match password</p>
                @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- Form Footer Actions -->
    <div class="pt-10 flex flex-col md:flex-row items-center gap-6">
        <button class="w-full md:w-auto min-w-[240px] py-4 bg-primary text-white text-base font-bold rounded-full shadow-xl shadow-primary/30 hover:bg-zinc-800 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed" type="submit" wire:loading.attr="disabled" wire:target="save, passport_photo, biometric_data, identity_document, bank_statement, income_proof">
            <span wire:loading.remove wire:target="save, passport_photo, biometric_data, identity_document, bank_statement, income_proof">
                Register Borrower
            </span>
            <span wire:loading wire:target="save, passport_photo, biometric_data, identity_document, bank_statement, income_proof">
                <span class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </span>
        </button>
        <button class="text-zinc-400 hover:text-zinc-600 font-bold text-sm transition-colors" type="button">
            Cancel &amp; Return
        </button>
    </div>
</form>
