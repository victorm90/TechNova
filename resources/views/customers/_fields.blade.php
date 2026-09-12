{{-- NOMBRE --}}
<div>
    <label for="{{ $fieldPrefix }}name"
        class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-zink-100">
        Nombre completo
    </label>

    <div class="relative">
        <i data-lucide="user"
            class="absolute size-4 text-slate-400 ltr:left-3 rtl:right-3 top-1/2 -translate-y-1/2">
        </i>

        <input
            id="{{ $fieldPrefix }}name"
            name="name"
            type="text"
            value="{{ old('name', $customer->name) }}"
            class="ltr:pl-10 rtl:pr-10 form-input w-full border-slate-200
                   dark:border-zink-500 dark:bg-zink-700
                   focus:border-custom-500 focus:ring-0"
            placeholder="Ej. Juan Pérez"
            autocomplete="name"
            required>
    </div>

    @error('name')
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>


{{-- TELEFONO --}}
<div>
    <label for="{{ $fieldPrefix }}phone"
        class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-zink-100">
        Número de teléfono
    </label>

    <div class="relative">
        <i data-lucide="phone"
            class="absolute size-4 text-slate-400 ltr:left-3 rtl:right-3 top-1/2 -translate-y-1/2">
        </i>

        <input
            id="{{ $fieldPrefix }}phone"
            name="phone"
            type="text"
            value="{{ old('phone', $customer->phone) }}"
            class="ltr:pl-10 rtl:pr-10 form-input w-full border-slate-200
                   dark:border-zink-500 dark:bg-zink-700
                   focus:border-custom-500 focus:ring-0"
            placeholder="Ej. 55555555"
            autocomplete="tel"
            required>
    </div>

    @error('phone')
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>


{{-- DIRECCION --}}
<div class="md:col-span-2">
    <label for="{{ $fieldPrefix }}address"
        class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-zink-100">
        Dirección particular
    </label>

    <div class="relative">
        <i data-lucide="map-pin"
            class="absolute size-4 text-slate-400 ltr:left-3 rtl:right-3 top-3">
        </i>

        <textarea
            id="{{ $fieldPrefix }}address"
            name="address"
            rows="3"
            class="ltr:pl-10 rtl:pr-10 form-input w-full border-slate-200
                   dark:border-zink-500 dark:bg-zink-700
                   focus:border-custom-500 focus:ring-0 resize-none"
            placeholder="Escriba la dirección particular..."
            autocomplete="street-address"
            required>{{ old('address', $customer->address) }}</textarea>
    </div>

    @error('address')
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>


{{-- CARNET --}}
<div>
    <label for="{{ $fieldPrefix }}identity_card"
        class="inline-block mb-2 text-sm font-medium text-slate-700 dark:text-zink-100">
        Número de carnet de identidad
    </label>

    <div class="relative">
        <i data-lucide="id-card"
            class="absolute size-4 text-slate-400 ltr:left-3 rtl:right-3 top-1/2 -translate-y-1/2">
        </i>

        <input
            id="{{ $fieldPrefix }}identity_card"
            name="identity_card"
            type="text"
            value="{{ old('identity_card', $customer->identity_card) }}"
            class="ltr:pl-10 rtl:pr-10 form-input w-full border-slate-200
                   dark:border-zink-500 dark:bg-zink-700
                   focus:border-custom-500 focus:ring-0"
            placeholder="Ej. 90010112345"
            required>
    </div>

    @error('identity_card')
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror
</div>

