<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.chatbot_page_title') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/icon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        @keyframes blink {
            0%, 85%, 100% { transform: scaleY(1); transform-origin: center; }
            90% { transform: scaleY(0.1); transform-origin: center; }
        }
        .animate-blink {
            animation: blink 3.5s infinite;
        }
        body::before {
    content: "";
    position: fixed;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='%237FA8D6' stroke-width='1.4'%3E%3Cellipse cx='40' cy='20' rx='9' ry='18'/%3E%3Cellipse cx='40' cy='60' rx='9' ry='18'/%3E%3Cellipse cx='20' cy='40' rx='18' ry='9'/%3E%3Cellipse cx='60' cy='40' rx='18' ry='9'/%3E%3C/g%3E%3Cg fill='%23E0A93B'%3E%3Ccircle cx='40' cy='40' r='3'/%3E%3Ccircle cx='0' cy='0' r='2'/%3E%3Ccircle cx='80' cy='0' r='2'/%3E%3Ccircle cx='0' cy='80' r='2'/%3E%3Ccircle cx='80' cy='80' r='2'/%3E%3C/g%3E%3C/svg%3E");
    background-size: 80px 80px;
    opacity: 0.35;
    /* pola jelas di atas, memudar ke bawah supaya teks tetap nyaman dibaca */
    -webkit-mask-image: linear-gradient(to bottom, #000 0%, rgba(0,0,0,.25) 100%);
    mask-image: linear-gradient(to bottom, #000 0%, rgba(0,0,0,.25) 100%);
}
    </style>
</head>
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Chat Area -->
    <main class="w-full max-w-4xl mx-auto px-4 py-12 flex-grow flex flex-col">
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-[#7FA8D6] mb-2 drop-shadow-sm">
                {{ __('messages.chatbot_heading') }}
            </h1>
            <p class="text-slate-600 text-sm md:text-base">
                {{ __('messages.chatbot_sub') }}
            </p>
        </div>

        <!-- Chat Container -->
        <div class="w-full bg-white/70 backdrop-blur-md rounded-3xl border-4 border-white shadow-xl flex flex-col flex-grow p-6 justify-between min-h-[450px]">
            
            <!-- Daftar Pesan / Percakapan -->
            <div id="chat-container" class="space-y-4 overflow-y-auto pr-2 flex-grow max-h-[400px]">
                <!-- Pesan sambutan awal bot -->
            </div>

            <!-- Sugesti Cepat Pertanyaan -->
            <div class="flex flex-wrap gap-2 mt-3 pt-2 text-xs items-center">
                <span class="text-slate-500 font-medium">{{ __('messages.chatbot_example_label') }}</span>
                <button type="button" class="suggestion-chip bg-white/90 hover:bg-[#FFE08A] border border-[#A8C6E7]/70 text-slate-700 px-3 py-1 rounded-full transition-all duration-200 shadow-sm hover:scale-105 active:scale-95" data-prompt="Bandara yang dekat Medan apa saja?">
                    {{ __('messages.chatbot_chip_medan') }}
                </button>
                <button type="button" class="suggestion-chip bg-white/90 hover:bg-[#FFE08A] border border-[#A8C6E7]/70 text-slate-700 px-3 py-1 rounded-full transition-all duration-200 shadow-sm hover:scale-105 active:scale-95" data-prompt="Profil bandara Kualanamu">
                    {{ __('messages.chatbot_chip_kno') }}
                </button>
                <button type="button" class="suggestion-chip bg-white/90 hover:bg-[#FFE08A] border border-[#A8C6E7]/70 text-slate-700 px-3 py-1 rounded-full transition-all duration-200 shadow-sm hover:scale-105 active:scale-95" data-prompt="Bandara daerah Deli Serdang">
                    {{ __('messages.chatbot_chip_deli') }}
                </button>
                <button type="button" class="suggestion-chip bg-white/90 hover:bg-[#FFE08A] border border-[#A8C6E7]/70 text-slate-700 px-3 py-1 rounded-full transition-all duration-200 shadow-sm hover:scale-105 active:scale-95" data-prompt="Berapa jumlah bandara di Indonesia?">
                    {{ __('messages.chatbot_chip_total') }}
                </button>
            </div>

            <!-- Form Input Pesan -->
            <form id="chat-form" class="mt-6 pt-4 border-t border-slate-200/60 flex gap-3">
                @csrf
                <input 
                    type="text" 
                    id="user-input"
                    name="message" 
                    placeholder="{{ __('messages.chatbot_input_placeholder') }}" 
                    class="flex-1 px-6 py-3.5 rounded-full border-2 border-white/80 bg-white/90 backdrop-blur-sm focus:outline-none focus:border-[#7FA8D6] focus:ring-4 focus:ring-[#A8C6E7]/50 text-sm shadow-inner transition-all duration-300"
                    autocomplete="off"
                    required
                >
                <button type="submit" 
                        class="bg-[#FFE08A] hover:bg-[#FFF2B2] text-slate-700 font-bold px-8 py-3.5 rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95 flex items-center gap-2">
                    <span>{{ __('messages.chatbot_btn_send') }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    <!-- Script Interaktif + Efek Mengetik (Typing Effect) -->
    <script>
            
    document.addEventListener("DOMContentLoaded", function () {

        const chatContainer = document.getElementById('chat-container');
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');

        if (!chatContainer || !chatForm || !userInput) {
            console.error('Chatbot elements not found!');
            return;
        }

        const chatbotUrl = @json(route('chatbot.send'));

        const csrfToken = chatForm.querySelector(
            'input[name="_token"]'
        )?.value;

        const submitButton = chatForm.querySelector(
            'button[type="submit"]'
        );

        if (!csrfToken || !submitButton) {
            console.error('CSRF token or submit button not found!');
            return;
        }

        // SVG AeroBot
        const aeroBotSvg = `
            <svg viewBox="0 0 64 64" class="w-10 h-10 drop-shadow-md"
                fill="none" xmlns="http://www.w3.org/2000/svg">

                <path d="M32 12V6" stroke="#7FA8D6"
                    stroke-width="4" stroke-linecap="round"/>

                <circle cx="32" cy="4" r="3"
                        fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>

                <rect x="8" y="26" width="6" height="12" rx="3"
                    fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>

                <rect x="50" y="26" width="6" height="12" rx="3"
                    fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>

                <rect x="12" y="14" width="40" height="30" rx="12"
                    fill="#F8FAFC" stroke="#7FA8D6" stroke-width="3"/>

                <rect x="17" y="19" width="30" height="20" rx="8"
                    fill="#1E293B"/>

                <g class="animate-blink">
                    <ellipse cx="26" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                    <circle cx="25" cy="26" r="1.5" fill="white"/>
                    <ellipse cx="38" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                    <circle cx="37" cy="26" r="1.5" fill="white"/>
                </g>

                <path d="M30 33C30 33 31.5 34.5 32 34.5C32.5 34.5 34 33 34 33"
                    stroke="#38BDF8" stroke-width="2"
                    stroke-linecap="round"/>
            </svg>
        `;

        // Menampilkan pesan bot
        function appendBotMessage(text) {

            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-start gap-3';

            const avatar = document.createElement('div');
            avatar.className = 'shrink-0';
            avatar.innerHTML = aeroBotSvg;

            const bubble = document.createElement('div');
            bubble.className =
                'bg-white/90 border border-[#A8C6E7]/50 text-slate-700 p-4 rounded-2xl max-w-lg text-sm leading-relaxed shadow-sm whitespace-pre-wrap';

            const message = document.createElement('span');
            const cursor = document.createElement('span');

            cursor.textContent = '|';
            cursor.className = 'animate-pulse font-bold text-[#7FA8D6]';

            bubble.append(message, cursor);
            wrapper.append(avatar, bubble);

            chatContainer.appendChild(wrapper);

            chatContainer.scrollTop = chatContainer.scrollHeight;

            let index = 0;

            function typeWriter() {

                if (index < text.length) {

                    message.textContent += text.charAt(index);
                    index++;

                    chatContainer.scrollTop = chatContainer.scrollHeight;

                    setTimeout(typeWriter, 15);

                } else {
                    cursor.remove();
                }
            }

            typeWriter();

            return wrapper;
        }

        // Menampilkan pesan user
        function appendUserMessage(text) {

            const wrapper = document.createElement('div');

            wrapper.className =
                'flex items-start justify-end gap-3';

            const bubble = document.createElement('div');

            bubble.className =
                'bg-[#7FA8D6] text-white p-4 rounded-2xl max-w-lg text-sm leading-relaxed shadow-sm';

            const message = document.createElement('span');

            message.textContent = text;

            bubble.appendChild(message);

            const avatar = document.createElement('div');

            avatar.className =
                'h-10 w-10 shrink-0 rounded-full bg-[#FFE08A] text-slate-700 flex items-center justify-center font-bold shadow-sm';

            avatar.textContent = 'U';

            wrapper.append(bubble, avatar);

            chatContainer.appendChild(wrapper);

            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Pesan sambutan
        appendBotMessage(
            @json(__('messages.chatbot_welcome_msg'))
        );

        // Event listener tombol contoh pertanyaan
        document.querySelectorAll('.suggestion-chip').forEach(button => {
            button.addEventListener('click', function () {
                const prompt = this.getAttribute('data-prompt');
                if (prompt) {
                    userInput.value = prompt;
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });
        });

        // Mengirim pertanyaan ke Laravel
        chatForm.addEventListener('submit', async function (e) {

            e.preventDefault();

            const text = userInput.value.trim();

            if (!text) return;

            appendUserMessage(text);

            userInput.value = '';

            submitButton.disabled = true;

            submitButton.classList.add(
                'opacity-50',
                'cursor-not-allowed'
            );

            const loadingMessage = appendBotMessage(
                'Please wait a moment, I am searching for airport information...'
            );

            try {

                console.log('Sending question:', text);

                const response = await fetch(chatbotUrl, {

                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },

                    body: JSON.stringify({
                        message: text
                    })

                });

                const data = await response.json();

                console.log('Laravel response:', response.status, data);

                if (!response.ok) {

                    const errorMessage =
                        data.message ||
                        Object.values(data.errors || {}).flat().join(' ') ||
                        'An error occurred on the server.';

                    throw new Error(errorMessage);
                }

                if (!data.reply) {
                    throw new Error('Server did not return a response.');
                }

                loadingMessage.remove();

                appendBotMessage(data.reply);

            } catch (error) {

                console.error('Chatbot error:', error);

                loadingMessage.remove();

                appendBotMessage(
                    'Sorry, an error occurred: ' + error.message
                );

            } finally {

                submitButton.disabled = false;

                submitButton.classList.remove(
                    'opacity-50',
                    'cursor-not-allowed'
                );

                userInput.focus();
            }

        });

    });

    </script>
</body>
</html>