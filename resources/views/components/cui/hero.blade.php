@props([])

@php $hasExtras = isset($badge) || isset($subtitle); @endphp

<section class="cui-hero">
    <div class="relative mx-auto w-full max-w-[1280px] px-4 py-3 md:py-4 md:px-6">
        @isset($breadcrumb)
            <nav aria-label="breadcrumb" class="mb-3">
                {{ $breadcrumb }}
            </nav>
        @endisset

        <div class="cui-hero-row {{ isset($search) ? 'cui-hero-row--search' : '' }}">
            @if(isset($search))
                <div class="cui-hero-titleblock">
                  

                    @if($hasExtras)
                        <div class="cui-hero-extras">
                          
  <h1 class="cui-f1-title text-xl font-black leading-[1.15] text-white md:text-[30px]">
                        {!! $title !!}
                    </h1>
                        </div>
                    @endif

                </div>

                <div class="cui-hero-search">
                    @isset($subtitle)
                        <p class="cui-hs-label mb-2.5 max-w-lg text-[13px] font-bold leading-relaxed text-white md:text-[15px]">
                            {!! $subtitle !!}
                        </p>
                    @endisset
                    {{ $search }}
                </div>

                @isset($side)
                    <div class="cui-hero-side">
                        {{ $side }}
                    </div>
                @endisset
            @else
                @if($hasExtras)
                    <div class="cui-hero-extras">
                       

                        <div class="cui-f1-bottom">
                            @isset($subtitle)
                                <p class="my-2 max-w-lg text-[13px] leading-relaxed text-white md:text-[14px]">
                                    {!! $subtitle !!}
                                </p>
                            @endisset
                        </div>
                    </div>
                @endif

                <div class="cui-hero-title">
                    <h1 class="cui-f1-title text-xl font-black leading-[1.15] text-white md:text-[30px]">
                        {!! $title !!}
                    </h1>
                </div>

                @isset($side)
                    <div class="cui-hero-side">
                        {{ $side }}
                    </div>
                @endisset
            @endif
        </div>

        @isset($info)
            <div class="mt-3 flex justify-center lg:justify-end">
                {{ $info }}
            </div>
        @endisset
    </div>
</section>