<?php

use App\Models\User;
use App\Models\Borrower;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use App\Traits\SterilizesPhone;

new #[Layout('layouts.guest')] #[Title('Create Account')] class extends Component
{
    use SterilizesPhone;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    
    public string $organization_id = '';
    public string $searchOrg = '';
    public string $selectedOrgName = '';

    public function with(): array
    {
        return [
            'organizations' => !empty($this->searchOrg) 
                ? \App\Models\Organization::where('name', 'like', '%' . $this->searchOrg . '%')
                    ->where('status', 'active')
                    ->where('kyc_status', 'approved')
                    ->take(5)
                    ->get()
                : [],
        ];
    }

    public function selectOrg($id, $name)
    {
        $this->organization_id = $id;
        $this->selectedOrgName = $name;
        $this->searchOrg = '';
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->phone = $this->sterilize($this->phone);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'size:13', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'organization_id' => ['required', 'exists:organizations,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        // Default to Borrower role for self-registration
        $borrowerRole = Role::findByName('Borrower');
        $user->assignRole($borrowerRole);

        // Create initial borrower record
        Borrower::create([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id,
            'phone' => $user->phone,
            'kyc_status' => 'pending', // Requires completion via profile
        ]);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>
<div class="max-w-[440px] w-full mx-auto">
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-[#131416] dark:text-white text-3xl font-bold tracking-tight mb-2">Create an Account</h1>
        <p class="text-[#6b7180] text-base">Join us and start managing your finances efficiently.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <!-- Organization Selection -->
        <div class="flex flex-col gap-2 relative" x-data="{ open: false }">
            <label class="text-[#131416] dark:text-white text-sm font-semibold">Select Organization</label>
            <div 
                @click="open = !open" 
                class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 flex items-center justify-between cursor-pointer focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all"
            >
                <span class="{{ $selectedOrgName ? 'text-primary dark:text-white' : 'text-[#6b7180]' }} text-sm font-medium">
                    {{ $selectedOrgName ?: 'Search and select organization' }}
                </span>
                <span class="material-symbols-outlined text-[#6b7180] text-[20px]">
                    {{ $selectedOrgName ? 'business' : 'search' }}
                </span>
            </div>
            
            <div 
                x-show="open" 
                @click.outside="open = false" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute top-[105%] left-0 w-full bg-white dark:bg-zinc-900 border border-[#dedfe3] dark:border-zinc-800 rounded-xl shadow-2xl z-50 overflow-hidden"
                style="display: none;"
            >
                <div class="p-3 border-b border-[#dedfe3] dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/50">
                    <input 
                        wire:model.live.debounce.300ms="searchOrg" 
                        type="text" 
                        placeholder="Type organization name..." 
                        class="w-full px-4 py-2 bg-white dark:bg-zinc-900 rounded-lg border border-gray-200 dark:border-zinc-700 focus:ring-1 focus:ring-primary text-sm outline-none"
                        @click.stop
                    >
                </div>
                <div class="max-h-60 overflow-y-auto">
                    @forelse($organizations as $org)
                        <button 
                            type="button"
                            wire:click="selectOrg('{{ $org->id }}', '{{ $org->name }}')" 
                            @click="open = false"
                            class="w-full text-left px-4 py-3 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer flex items-center gap-3 transition-colors border-b border-gray-50 dark:border-zinc-800/50 last:border-0"
                        >
                            <div class="size-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[20px]">business</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#131416] dark:text-white leading-tight">{{ $org->name }}</p>
                                <p class="text-[10px] text-[#6b7180] uppercase tracking-widest font-black mt-0.5">{{ $org->slug }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="p-8 text-center">
                            <div class="size-12 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                                <span class="material-symbols-outlined text-gray-400">search_off</span>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">
                                {{ empty($searchOrg) ? 'Start typing to find your organization' : 'No matching organizations found.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
        </div>

        <!-- Name -->
        <div class="flex flex-col gap-2">
            <label for="name" class="text-[#131416] dark:text-white text-sm font-semibold">Full Name</label>
            <input wire:model="name" id="name" type="text" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="John Doe" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Phone Number -->
        <div class="flex flex-col gap-2">
            <label for="phone" class="text-[#131416] dark:text-white text-sm font-semibold">Phone Number</label>
            <input wire:model="phone" id="phone" type="text" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="08012345678" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="flex flex-col gap-2">
            <label for="email" class="text-[#131416] dark:text-white text-sm font-semibold">Email Address (Optional)</label>
            <input wire:model="email" id="email" type="email" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="name@company.com" autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="flex flex-col gap-2">
            <label for="password" class="text-[#131416] dark:text-white text-sm font-semibold">Password</label>
            <div class="relative flex items-center">
                <input wire:model.defer="password" id="password" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="••••••••" required autocomplete="new-password" />
                <button onclick="togglePasswordVisibility('password', 'password_icon_register')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_icon_register" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="flex flex-col gap-2">
            <label for="password_confirmation" class="text-[#131416] dark:text-white text-sm font-semibold">Confirm Password</label>
            <div class="relative flex items-center">
                <input wire:model.defer="password_confirmation" id="password_confirmation" type="password" class="form-input w-full rounded-lg border border-[#dedfe3] dark:border-white/10 dark:bg-white/5 dark:text-primary h-14 px-4 pr-12 focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-[#6b7180]" placeholder="••••••••" required autocomplete="new-password" />
                <button onclick="togglePasswordVisibility('password_confirmation', 'password_confirmation_icon_register')" class="absolute right-4 text-[#6b7180] flex items-center justify-center" type="button">
                    <span id="password_confirmation_icon_register" class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-75" class="w-full bg-primary text-white rounded-lg h-14 font-bold text-base shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all active:scale-[0.98] mt-4 inline-flex items-center justify-center">
            <span wire:loading.remove>Create Account</span>
            <span wire:loading class="flex items-center justify-center">
                <span class="material-symbols-outlined animate-spin text-[20px] text-white">progress_activity</span>
            </span>
        </button>
    </form>

    <div class="mt-10 text-center">
        <p class="text-[#6b7180] text-sm">
            Already have an account?
            <a href="{{ route('login') }}" class="text-primary dark:text-white font-bold hover:underline ml-1" wire:navigate>
                Sign In
            </a>
        </p>
    </div>


</div>
