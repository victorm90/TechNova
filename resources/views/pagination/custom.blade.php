
@if ($paginator->hasPages())

    <nav class="flex items-center justify-between gap-3"
         aria-label="Paginación">

        {{-- ANTERIOR --}}
        <div>

            @if ($paginator->onFirstPage())

                <span
                    class="inline-flex items-center justify-center
                           size-9 rounded-lg
                           border border-slate-200
                           text-slate-300
                           cursor-not-allowed
                           dark:border-zink-500
                           dark:text-zink-500">

                    <i data-lucide="chevron-left" class="size-4"></i>

                </span>

            @else

                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="inline-flex items-center justify-center
                           size-9 rounded-lg
                           border border-slate-200
                           text-slate-500
                           transition
                           hover:border-custom-500
                           hover:text-custom-500
                           hover:bg-custom-500/5
                           dark:border-zink-500
                           dark:text-zink-200">

                    <i data-lucide="chevron-left" class="size-4"></i>

                </a>

            @endif

        </div>


        {{-- PAGINAS --}}
        <div class="flex items-center gap-1">

            @foreach ($elements as $element)

                {{-- Separador --}}
                @if (is_string($element))

                    <span
                        class="inline-flex items-center justify-center
                               size-9 text-sm
                               text-slate-400">

                        {{ $element }}

                    </span>

                @endif


                {{-- Enlaces --}}
                @if (is_array($element))

                    @foreach ($element as $page => $url)

                        @if ($page == $paginator->currentPage())

                            <span
                                aria-current="page"
                                class="inline-flex items-center justify-center
                                       size-9 rounded-lg
                                       text-sm font-medium
                                       text-white
                                       bg-custom-500
                                       border border-custom-500">

                                {{ $page }}

                            </span>

                        @else

                            <a
                                href="{{ $url }}"
                                class="inline-flex items-center justify-center
                                       size-9 rounded-lg
                                       border border-transparent
                                       text-sm text-slate-500
                                       transition
                                       hover:border-slate-200
                                       hover:bg-slate-50
                                       hover:text-custom-500
                                       dark:text-zink-200
                                       dark:hover:border-zink-500
                                       dark:hover:bg-zink-600">

                                {{ $page }}

                            </a>

                        @endif

                    @endforeach

                @endif

            @endforeach

        </div>


        {{-- SIGUIENTE --}}
        <div>

            @if ($paginator->hasMorePages())

                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="inline-flex items-center justify-center
                           size-9 rounded-lg
                           border border-slate-200
                           text-slate-500
                           transition
                           hover:border-custom-500
                           hover:text-custom-500
                           hover:bg-custom-500/5
                           dark:border-zink-500
                           dark:text-zink-200">

                    <i data-lucide="chevron-right" class="size-4"></i>

                </a>

            @else

                <span
                    class="inline-flex items-center justify-center
                           size-9 rounded-lg
                           border border-slate-200
                           text-slate-300
                           cursor-not-allowed
                           dark:border-zink-500
                           dark:text-zink-500">

                    <i data-lucide="chevron-right" class="size-4"></i>

                </span>

            @endif

        </div>

    </nav>

@endif

