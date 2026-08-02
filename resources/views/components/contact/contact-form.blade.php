<section class="relative py-32 bg-dark-base overflow-hidden">
    <div class="absolute top-1/2 right-0 -translate-y-1/2 translate-x-1/4 w-[600px] h-[600px] bg-neon-purple/5 rounded-full blur-[150px] pointer-events-none"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-orbitron text-4xl sm:text-5xl font-bold mb-4 text-white">Send a Message</h2>
            <p class="font-inter text-white/50 text-lg max-w-2xl mx-auto">
                Ready to start your next project? Fill out the form below and we'll
                get back to you within 24 hours.
            </p>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl p-8 sm:p-12 backdrop-blur-sm relative overflow-hidden">
            <div class="absolute inset-0 animated-grid opacity-[0.05] pointer-events-none"></div>

            @if (session('contact_success'))
                <div class="relative z-10 flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-20 h-20 rounded-full bg-neon-cyan/10 flex items-center justify-center mb-6 border border-neon-cyan/30 shadow-[0_0_30px_rgba(0,240,255,0.2)]">
                        <svg class="text-neon-cyan w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h3 class="font-orbitron text-3xl font-bold text-white mb-4">Message Sent!</h3>
                    <p class="font-inter text-white/60 text-lg max-w-md">
                        Thank you for reaching out. Our team will review your message
                        and get back to you shortly.
                    </p>
                </div>
            @else
                @if (session('contact_error'))
                    <div class="relative z-10 rounded-3xl border border-red-500/20 bg-red-500/10 p-6 mb-6 text-white">
                        <h3 class="font-orbitron text-lg font-bold text-red-200 mb-2">Unable to send message</h3>
                        <p class="font-inter text-sm text-red-100 max-w-2xl">
                            {{ session('contact_error') }}
                        </p>
                    </div>
                @endif

                <form method="post" action="{{ route('contact.store') }}" class="relative z-10 space-y-6" id="contact-form">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="font-inter text-sm font-medium text-white/70 ml-1">Full Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                class="w-full bg-dark-base border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:outline-none focus:border-neon-cyan focus:ring-1 focus:ring-neon-cyan transition-all duration-300 @error('name') border-red-500 @enderror"
                                placeholder="John Doe"
                            >
                            @error('name')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="font-inter text-sm font-medium text-white/70 ml-1">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full bg-dark-base border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:outline-none focus:border-neon-cyan focus:ring-1 focus:ring-neon-cyan transition-all duration-300 @error('email') border-red-500 @enderror"
                                placeholder="john@example.com"
                            >
                            @error('email')
                                <p class="text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="subject" class="font-inter text-sm font-medium text-white/70 ml-1">Subject</label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            required
                            class="w-full bg-dark-base border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:outline-none focus:border-neon-cyan focus:ring-1 focus:ring-neon-cyan transition-all duration-300 @error('subject') border-red-500 @enderror"
                            placeholder="How can we help you?"
                        >
                        @error('subject')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="message" class="font-inter text-sm font-medium text-white/70 ml-1">Message</label>
                        <textarea
                            id="message"
                            name="message"
                            required
                            rows="6"
                            class="w-full bg-dark-base border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:outline-none focus:border-neon-cyan focus:ring-1 focus:ring-neon-cyan transition-all duration-300 resize-none @error('message') border-red-500 @enderror"
                            placeholder="Tell us about your project..."
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        id="contact-submit-button"
                        type="submit"
                        class="w-full group relative px-8 py-4 bg-neon-cyan text-dark-base font-orbitron font-bold text-lg rounded-xl overflow-hidden transition-all duration-300 shadow-[0_0_20px_rgba(0,240,255,0.3)] hover:shadow-[0_0_30px_rgba(0,240,255,0.5)] disabled:opacity-70 disabled:cursor-not-allowed mt-4"
                    >
                        <span id="contact-submit-label" class="relative z-10 flex items-center justify-center gap-3">
                            Send Message
                            <svg class="w-5 h-5 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </span>
                        <span id="contact-submit-loading" class="hidden absolute inset-0 z-10 flex items-center justify-center gap-3 text-dark-base">
                            <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0m16.97-5.03-2.83 2.83M5.05 18.95l2.83-2.83m0-8.49-2.83 2.83M18.95 18.95l-2.83-2.83" />
                            </svg>
                            Sending...
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-neon-cyan via-white/20 to-neon-blue opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </button>
                </form>
            @endif
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('contact-form');
            const button = document.getElementById('contact-submit-button');
            const label = document.getElementById('contact-submit-label');
            const loading = document.getElementById('contact-submit-loading');

            if (!form || !button || !label || !loading) {
                return;
            }

            form.addEventListener('submit', function () {
                button.disabled = true;
                button.classList.add('opacity-70', 'cursor-not-allowed');
                label.classList.add('hidden');
                loading.classList.remove('hidden');
            });
        });
    </script>
</section>
