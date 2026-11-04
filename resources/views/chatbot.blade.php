<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Asisten — AeroSearch</title>
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
    </style>
</head>
<body class="bg-gradient-to-b from-[#FFF7D6] via-[#FFF2B2]/60 to-[#A8C6E7]/40 min-h-screen flex flex-col text-slate-800">

    <!-- Navbar -->
    @include('layouts.navbar')

    <!-- Main Chat Area -->
    <main class="w-full max-w-4xl mx-auto px-4 py-12 flex-grow flex flex-col">
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold text-[#7FA8D6] mb-2 drop-shadow-sm">
                Asisten AI Bandara ASEAN
            </h1>
            <p class="text-slate-600 text-sm md:text-base">
                Tanyakan informasi seputar profil, lokasi, kontak, atau data semantik bandara menggunakan bahasa alami.
            </p>
        </div>

        <!-- Chat Container -->
        <div class="w-full bg-white/70 backdrop-blur-md rounded-3xl border-4 border-white shadow-xl flex flex-col flex-grow p-6 justify-between min-h-[450px]">
            
            <!-- Daftar Pesan / Percakapan -->
            <div id="chat-container" class="space-y-4 overflow-y-auto pr-2 flex-grow max-h-[400px]">
                <!-- Pesan sambutan awal bot -->
            </div>

            <!-- Form Input Pesan -->
            <form id="chat-form" class="mt-6 pt-4 border-t border-slate-200/60 flex gap-3">
                @csrf
                <input 
                    type="text" 
                    id="user-input"
                    name="message" 
                    placeholder="Ketik pertanyaan atau pencarian bandara di sini..." 
                    class="flex-1 px-6 py-3.5 rounded-full border-2 border-white/80 bg-white/90 backdrop-blur-sm focus:outline-none focus:border-[#7FA8D6] focus:ring-4 focus:ring-[#A8C6E7]/50 text-sm shadow-inner transition-all duration-300"
                    autocomplete="off"
                    required
                >
                <button type="submit" 
                        class="bg-[#FFE08A] hover:bg-[#FFF2B2] text-slate-700 font-bold px-8 py-3.5 rounded-full transition-all duration-300 hover:scale-105 hover:shadow-lg active:scale-95 flex items-center gap-2">
                    <span>Kirim</span>
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
        document.addEventListener("DOMContentLoaded", function() {
            const chatContainer = document.getElementById('chat-container');
            const chatForm = document.getElementById('chat-form');
            const userInput = document.getElementById('user-input');

            // SVG Wajah AeroBot Tanpa Lingkaran
            const aeroBotSvg = `
                <svg viewBox="0 0 64 64" class="w-10 h-10 drop-shadow-md" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 12V6" stroke="#7FA8D6" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="32" cy="4" r="3" fill="#FFE08A" stroke="#7FA8D6" stroke-width="2"/>
                    <rect x="8" y="26" width="6" height="12" rx="3" fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>
                    <rect x="50" y="26" width="6" height="12" rx="3" fill="#CBD5E1" stroke="#7FA8D6" stroke-width="2"/>
                    <rect x="12" y="14" width="40" height="30" rx="12" fill="#F8FAFC" stroke="#7FA8D6" stroke-width="3"/>
                    <rect x="17" y="19" width="30" height="20" rx="8" fill="#1E293B"/>
                    <g class="animate-blink">
                        <ellipse cx="26" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                        <circle cx="25" cy="26" r="1.5" fill="#FFFFFF"/>
                        <ellipse cx="38" cy="27" rx="4" ry="4" fill="#38BDF8"/>
                        <circle cx="37" cy="26" r="1.5" fill="#FFFFFF"/>
                    </g>
                    <path d="M30 33C30 33 31.5 34.5 32 34.5C32.5 34.5 34 33 34 33" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
                </svg>
            `;

            // Fungsi nambahin pesan bot dengan efek mengetik (typing effect)
            function appendBotMessage(text) {
                const messageWrapper = document.createElement('div');
                messageWrapper.className = "flex items-start gap-3";
                messageWrapper.innerHTML = `
                    <div class="shrink-0 flex items-center justify-center">
                        ${aeroBotSvg}
                    </div>
                    <div class="bg-white/90 border border-[#A8C6E7]/50 text-slate-700 p-4 rounded-2xl max-w-lg text-sm leading-relaxed shadow-sm">
                        <span class="typing-text"></span><span class="animate-pulse font-bold text-[#7FA8D6]">|</span>
                    </div>
                `;
                chatContainer.appendChild(messageWrapper);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                const textSpan = messageWrapper.querySelector('.typing-text');
                const cursorSpan = messageWrapper.querySelector('.animate-pulse');
                let charIndex = 0;

                function typeWriter() {
                    if (charIndex < text.length) {
                        textSpan.textContent += text.charAt(charIndex);
                        charIndex++;
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        setTimeout(typeWriter, 20); 
                    } else {
                        if (cursorSpan) cursorSpan.remove(); 
                    }
                }

                typeWriter();
            }

            // Fungsi nambahin pesan user ke container (posisi di kanan)
            function appendUserMessage(text) {
                const messageWrapper = document.createElement('div');
                messageWrapper.className = "flex items-start justify-end gap-3";
                messageWrapper.innerHTML = `
                    <div class="bg-[#7FA8D6] text-white p-4 rounded-2xl max-w-lg text-sm leading-relaxed shadow-sm">
                        <span>${text}</span>
                    </div>
                    <div class="h-10 w-10 shrink-0 rounded-full bg-[#FFE08A] text-slate-700 flex items-center justify-center font-bold shadow-sm">
                        U
                    </div>
                `;
                chatContainer.appendChild(messageWrapper);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            // Sambutan awal saat halaman dibuka
            setTimeout(() => {
                appendBotMessage("Halo! Aku AeroBot. Ada yang bisa saya bantu terkait direktori bandara di Asia Tenggara hari ini?");
            }, 500);

            // Event listener pas form dikirim / tombol kirim diklik
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const text = userInput.value.trim();
                if (!text) return;

                // 1. Tampilkan pesan user ke layar
                appendUserMessage(text);
                userInput.value = '';

                // 2. Simulasi bot merespons dengan efek mengetik setelah 1 detik
                setTimeout(() => {
                    appendBotMessage(`Baik, aku AeroBot sedang mencari informasi mengenai "${text}" berdasarkan data semantik bandara.`);
                }, 1000);
            });
        });
    </script>
</body>
</html>