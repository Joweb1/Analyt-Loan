<div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
    <div class="flex items-center gap-3 px-2 py-3 rounded-2xl hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors group cursor-pointer" onclick="window.location='{{ route('profile') }}'">
        <div class="size-10 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-zinc-900 dark:text-white truncate">{{ $user->name }}</p>
            <p class="text-[10px] font-medium text-zinc-500 truncate">{{ $user->getRoleNames()->first() ?? 'User' }}</p>
        </div>
        <a href="{{ route('profile') }}" class="material-symbols-outlined text-zinc-400 group-hover:text-primary transition-colors text-lg">
            settings
        </a>
    </div>
</div>
