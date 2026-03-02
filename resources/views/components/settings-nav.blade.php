@props(['active' => 'general'])

<div class="mb-8">
    <div class="flex flex-wrap items-center gap-2 p-1 bg-slate-100 dark:bg-slate-800/50 rounded-2xl w-fit">
        <a href="{{ route('settings') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'general' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            General
        </a>
        <a href="{{ route('settings.security') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'security' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Security
        </a>
        <a href="{{ route('settings.team-members') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'team' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Team
        </a>
        <a href="{{ route('settings.roles') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'roles' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Roles
        </a>
        <a href="{{ route('settings.loan-products') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'loan-products' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Loan Products
        </a>
        <a href="{{ route('settings.form-builder') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'form' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Borrower Form
        </a>
        <a href="{{ route('settings.guarantor-form') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'guarantor-form' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Guarantor Form
        </a>
        <a href="{{ route('settings.notifications') }}" 
           class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $active === 'notifications' ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
            Notifications
        </a>
    </div>
</div>
