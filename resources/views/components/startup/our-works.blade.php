@props(['startupProjects'])

<section class="relative py-32 bg-dark-base overflow-hidden" data-our-works data-total="{{ count($startupProjects) }}">
    <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1/4 w-[500px] h-[500px] bg-neon-purple/5 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-orbitron text-4xl sm:text-5xl font-bold mb-4 text-white">Our Works</h2>
            <p class="font-inter text-white/50 text-lg max-w-2xl mx-auto">
                A comprehensive showcase of our digital conquests across various
                domains.
            </p>
        </div>

        <div>
            <div class="relative overflow-hidden rounded-3xl border border-neon-cyan/20 bg-dark-secondary/40 backdrop-blur-xl shadow-[0_0_30px_rgba(0,240,255,0.08),0_0_60px_rgba(0,240,255,0.04),inset_0_1px_0_rgba(255,255,255,0.05)]">
                @foreach ($startupProjects as $index => $project)
                    @php
                        $videoUrl = asset(str_replace(' ', '%20', $project['video']));
                    @endphp
                    <div class="our-work-panel {{ $index === 0 ? '' : 'hidden' }}" data-our-work-panel="{{ $index }}">
                        <a
                            href="{{ $project['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col md:flex-row group/slide"
                        >
                            <div class="relative w-full md:w-3/5 h-[300px] md:h-[500px] overflow-hidden bg-dark-base shadow-[4px_0_30px_rgba(0,240,255,0.06)]">
                                <video
                                    src="{{ $videoUrl }}"
                                    autoplay
                                    loop
                                    muted
                                    playsinline
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover/slide:scale-105"
                                ></video>
                                <div class="absolute inset-0 bg-linear-to-br {{ $project['gradient'] }} mix-blend-overlay opacity-30 transition-opacity duration-500 group-hover/slide:opacity-40"></div>
                                <div class="absolute inset-0 bg-linear-to-t from-dark-secondary via-transparent to-transparent md:bg-linear-to-r md:from-transparent md:via-dark-secondary/50 md:to-dark-secondary"></div>
                                <div class="absolute top-6 left-6 z-10">
                                    <span class="px-4 py-1.5 rounded-full bg-dark-base/80 backdrop-blur-md border border-white/10 text-white/90 text-xs font-inter font-medium tracking-wide shadow-[0_0_15px_rgba(255,255,255,0.1)]">
                                        {{ $project['tag'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="w-full md:w-2/5 p-8 md:p-12 flex flex-col justify-center relative">
                                <div class="absolute inset-0 bg-linear-to-br {{ $project['gradient'] }} opacity-[0.03] pointer-events-none"></div>
                                <div class="relative z-10">
                                    <div class="mb-6 flex items-center justify-start {{ ($project['tall_logo'] ?? false) ? 'h-28 md:h-32' : 'h-20 md:h-24' }}">
                                        <img
                                            src="{{ asset($project['logo']) }}"
                                            alt="{{ $project['title'] }} logo"
                                            class="h-full w-auto max-w-[85%] object-contain drop-shadow-[0_0_20px_rgba(0,240,255,0.2)]"
                                        >
                                    </div>
                                    <h3 class="font-orbitron text-2xl md:text-3xl font-bold text-white mb-3 tracking-wide">{{ $project['title'] }}</h3>
                                    <p class="font-inter text-white/65 leading-relaxed text-base md:text-lg font-light">{{ $project['description'] }}</p>
                                    <div class="mt-6 inline-flex items-center gap-2 text-neon-cyan/60 group-hover/slide:text-neon-cyan transition-colors duration-300">
                                        <span class="font-inter text-sm font-medium tracking-wide">Visit Live Project</span>
                                        <svg class="w-3.5 h-3.5 group-hover/slide:translate-x-0.5 group-hover/slide:-translate-y-0.5 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between mt-10">
                <button
                    type="button"
                    data-our-work-prev
                    class="p-4 rounded-full border border-white/10 bg-white/5 text-white/70 hover:bg-white/10 hover:text-white hover:shadow-[0_0_20px_rgba(255,255,255,0.1)] hover:scale-105 transition-all duration-300 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:shadow-none disabled:cursor-not-allowed"
                    aria-label="Previous project"
                >
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <div class="flex items-center gap-3 overflow-x-auto px-4 max-w-[200px] sm:max-w-none" data-our-work-dots>
                    @foreach ($startupProjects as $i => $p)
                        <button
                            type="button"
                            data-our-work-dot="{{ $i }}"
                            class="relative p-2 shrink-0 group"
                            aria-label="Go to project {{ $i + 1 }}"
                        >
                            <div class="h-1.5 rounded-full transition-all duration-500 {{ $i === 0 ? 'w-10 bg-neon-cyan shadow-[0_0_15px_rgba(0,240,255,0.6)]' : 'w-2 bg-white/20 group-hover:bg-white/50' }}"></div>
                        </button>
                    @endforeach
                </div>
                <button
                    type="button"
                    data-our-work-next
                    class="p-4 rounded-full border border-white/10 bg-white/5 text-white/70 hover:bg-white/10 hover:text-white hover:shadow-[0_0_20px_rgba(255,255,255,0.1)] hover:scale-105 transition-all duration-300 disabled:opacity-30 disabled:hover:scale-100 disabled:hover:shadow-none disabled:cursor-not-allowed"
                    aria-label="Next project"
                >
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>
