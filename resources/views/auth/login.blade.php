<x-layouts.site title="Acceso">
    <section class="grid min-h-screen place-items-center px-5 pt-16">
        <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm space-y-5 rounded-2xl border border-white/10 bg-slate-ink/70 p-8">
            @csrf
            <div>
                <h1 class="text-2xl font-bold">Acceso al panel</h1>
                <p class="mt-1 text-sm text-mist">Solo para el equipo de Rejas.dev.</p>
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm text-mist">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="site-input">
                @error('email')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm text-mist">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="site-input">
            </div>
            <label class="flex items-center gap-2 text-sm text-mist"><input type="checkbox" name="remember" class="accent-electric"> Recordarme</label>
            <button class="btn-site w-full justify-center bg-electric text-white hover:bg-electric-700">Ingresar</button>
        </form>
    </section>
</x-layouts.site>
