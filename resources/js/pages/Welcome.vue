<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login, register} from '@/routes'
import {
    Sparkles,
    WashingMachine,
    Clock,
    CreditCard,
    Store,
    ChevronDown,
    MessageCircle
} from 'lucide-vue-next'

// Navbar dropdown
const showRegister = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)
const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        showRegister.value = false
    }
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))

// Features
const features = [
    { icon: WashingMachine, title: 'Smart Laundry Booking', description: 'Schedule pickups and deliveries easily from any device.' },
    { icon: Clock, title: 'Real-Time Order Tracking', description: 'Track your laundry status from start to finish.' },
    { icon: CreditCard, title: 'Cashless Payments', description: 'Secure and fast online payments.' },
    { icon: Store, title: 'Multi-Tenant Shops', description: 'Each laundry shop manages its own orders and customers.' }
]

// FAQs
const faqs = [
    { q: 'What is a multi-tenant laundry platform?', a: 'It allows multiple laundry shops to operate independently within one system.' },
    { q: 'Can customers choose any laundry shop?', a: 'Yes, customers can browse and select available laundry shops.' },
    { q: 'Is online payment required?', a: 'No, shops can enable or disable cashless payments.' }
]

// =====================
// Mini Chatbot State
// =====================
const showChat = ref(false)
const chatInput = ref('')
const chatMessages = ref<{ text: string; user?: boolean }[]>([])
const botTyping = ref(false)
const chatStarted = ref(false)

// Simple bot response logic for greetings
const botResponses: Record<string, string> = {
    hello: "Hello! 👋 How can I help you today?",
    hi: "Hi there! I'm LaundryBot 🤖, your assistant.",
    hey: "Hey! Need help with your laundry?",
    greetings: "Greetings! How can I assist you today?",
    default: "I'm LaundryBot 🤖. I can answer basic questions or guide you through our platform."
}

function sendMessage() {
    if (!chatInput.value.trim()) return

    // Add user message
    chatMessages.value.push({ text: chatInput.value, user: true })

    const userText = chatInput.value.toLowerCase()
    chatInput.value = ''
    botTyping.value = true

    // Simulate bot typing delay
    setTimeout(() => {
        botTyping.value = false

        // Detect greetings
        let response = botResponses.default
        if (userText.includes("hello")) response = botResponses.hello
        else if (userText.includes("hi")) response = botResponses.hi
        else if (userText.includes("hey")) response = botResponses.hey
        else if (userText.includes("greetings")) response = botResponses.greetings

        chatMessages.value.push({ text: response })

        // Scroll to bottom
        nextTick(() => {
            const chatWindow = document.querySelector(".chat-window")
            if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight
        })
    }, 800)
}

function openChat() {
    showChat.value = true
    if (!chatStarted.value) {
        chatMessages.value.push({ text: "Start your new conversation with LaundryBot 🤖" })
        chatStarted.value = true
        nextTick(() => {
            const chatWindow = document.querySelector(".chat-window")
            if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight
        })
    }
}
</script>

<template>

    <Head title="LaundryHub" />

    <div class="min-h-screen bg-slate-50 text-slate-800 dark:bg-[#0f0f0f] dark:text-[#EDEDEC]">

        <!-- NAVBAR -->
        <header
            class="fixed top-0 inset-x-0 z-50 bg-white border-b border-slate-200 dark:bg-[#0f0f0f] dark:border-[#3E3E3A]">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2 font-semibold text-lg">
                    <Sparkles class="w-5 h-5 text-indigo-500" /> LaundryHub
                </div>
                <nav class="hidden md:flex items-center gap-8 text-sm">
                    <a href="#features" class="hover:text-black dark:hover:text-white">Features</a>
                    <a href="#faq" class="hover:text-black dark:hover:text-white">FAQs</a>
                </nav>

                <template v-if="$page.props.auth?.user">
                    <Link :href="dashboard.url()" class="px-5 py-1.5 text-sm hover:underline">Dashboard</Link>
                </template>
                <template v-else>
                    <div class="relative flex items-center gap-2">
                        <Link :href="login.url()"
                            class="inline-flex items-center rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]">
                            Log in</Link>

                        <div class="relative" ref="dropdownRef">
                            <button @click="showRegister = !showRegister"
                                class="inline-flex items-center gap-1 rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b']">
                                Register
                                <ChevronDown class="w-4 h-4" />
                            </button>

                            <div v-if="showRegister"
                                class="absolute right-0 top-full mt-2 w-44 rounded-md bg-white border border-slate-200 shadow-md z-50 dark:bg-[#0f0f0f] dark:border-[#3E3E3A]">
                                <Link :href="register.url()" @click="showRegister = false"
                                    class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-[#1a1a1a]">
                                    Register as User
                                </Link>
                                <Link href="/register/shop" @click="showRegister = false"
                                    class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-[#1a1a1a]">
                                    Register as Shop
                                </Link>

                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative pt-40 pb-32 px-6 text-center overflow-hidden">
            <!-- Decorative bouncing balls -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-24 left-24 w-24 h-24 rounded-full bg-blue-300 opacity-60 animate-bounce-slow">
                </div>
                <div
                    class="absolute top-52 right-32 w-16 h-16 rounded-full bg-pink-400 opacity-60 animate-bounce-diagonal">
                </div>
                <div
                    class="absolute bottom-32 left-1/2 w-20 h-20 rounded-full bg-green-300 opacity-60 animate-bounce-fast">
                </div>
                <div
                    class="absolute top-12 right-12 w-12 h-12 rounded-full bg-yellow-300 opacity-60 animate-bounce-horizontal">
                </div>
            </div>

            <div class="relative max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Multi-Tenant Laundry Management Platform</h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 mb-10">One platform for customers, laundry shops,
                    and
                    administrators. Simple. Scalable. Reliable.</p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="register()"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 transition">Get Started
                    </Link>
                    <a href="#features"
                        class="px-8 py-3 border border-slate-300 rounded-lg hover:bg-slate-100 dark:border-[#3E3E3A] dark:hover:bg-[#1a1a1a]">Learn
                        More</a>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" class="py-24 px-6 bg-white dark:bg-[#0f0f0f]">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-semibold text-center mb-14">Platform Features</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div v-for="feature in features" :key="feature.title"
                        class="p-6 border border-slate-200 rounded-xl text-center hover:shadow-lg transition dark:border-[#3E3E3A]">
                        <div class="flex justify-center mb-4 text-indigo-500">
                            <component :is="feature.icon" class="w-7 h-7" />
                        </div>
                        <h3 class="font-semibold mb-2">{{ feature.title }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="py-24 px-6 bg-slate-50 dark:bg-[#141414]">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-3xl font-semibold text-center mb-12">Frequently Asked Questions</h2>
                <div class="space-y-6">
                    <div v-for="faq in faqs" :key="faq.q"
                        class="p-6 bg-white border border-slate-200 rounded-xl hover:shadow-md transition dark:bg-[#0f0f0f] dark:border-[#3E3E3A]">
                        <h4 class="font-medium mb-2">{{ faq.q }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ faq.a }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
            © {{ new Date().getFullYear() }} LaundryHub. All rights reserved.
        </footer>

        <!-- ===================== MINI CHATBOT ===================== -->
        <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
            <!-- Chat window -->
            <transition name="fade">
                <div v-show="showChat"
                    class="w-80 bg-white dark:bg-[#1a1a1a] border border-slate-300 dark:border-[#3E3E3A] rounded-2xl shadow-xl flex flex-col overflow-hidden">

                    <!-- Header -->
                    <div class="bg-indigo-600 text-white px-4 py-2 font-semibold flex items-center gap-2 rounded-t-2xl">
                        <img src="/images/robot-avatar.png" alt="LaundryBot" class="w-8 h-8 rounded-full" />
                        <span>LaundryBot</span>
                        <button @click="showChat = false" class="ml-auto font-bold text-lg">&times;</button>
                    </div>

                    <!-- Messages -->
                    <div ref="chatWindowRef" class="chat-window p-3 flex-1 overflow-y-auto max-h-64 space-y-2">
                        <div v-for="(msg, index) in chatMessages" :key="index" class="flex"
                            :class="msg.user ? 'justify-end' : 'justify-start'">
                            <template v-if="!msg.user">
                                <img src="/images/robot-avatar.png" alt="LaundryBot"
                                    class="w-6 h-6 rounded-full mr-2" />
                                <span
                                    class="inline-block bg-slate-200 dark:bg-slate-700 text-black px-3 py-1 rounded-2xl max-w-[70%]">{{
                                        msg.text }}</span>
                            </template>
                            <template v-else>
                                <span
                                    class="inline-block bg-indigo-100 dark:bg-indigo-900 text-black px-3 py-1 rounded-2xl max-w-[70%]">{{
                                        msg.text }}</span>
                            </template>
                        </div>

                        <div v-if="botTyping" class="flex items-center space-x-2">
                            <img src="/images/robot-avatar.png" alt="LaundryBot" class="w-6 h-6 rounded-full" />
                            <span class="text-sm text-slate-500 dark:text-slate-300 animate-pulse">LaundryBot is
                                typing...</span>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="p-3 border-t border-slate-200 dark:border-[#3E3E3A] flex gap-2">
                        <input v-model="chatInput" @keyup.enter="sendMessage" type="text"
                            placeholder="Ask a question..."
                            class="flex-1 px-3 py-2 rounded-2xl border border-slate-300 dark:border-[#3E3E3A] bg-white dark:bg-[#2a2a2a] text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <button @click="sendMessage"
                            class="bg-indigo-600 text-white px-3 py-2 rounded-2xl hover:bg-indigo-500 transition">Send</button>
                    </div>

                </div>
            </transition>

            <!-- Chat button -->
            <button v-show="!showChat" @click="openChat"
                class="bg-indigo-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg hover:bg-indigo-500 transition transform hover:scale-110">
                <MessageCircle class="w-6 h-6" />
            </button>
        </div>
    </div>
</template>

<style lang="postcss">
@layer utilities {
    @keyframes bounce-slow {

        0%,
        100% {
            transform: translateY(0)
        }

        50% {
            transform: translateY(-30px)
        }
    }

    @keyframes bounce-fast {

        0%,
        100% {
            transform: translateY(0)
        }

        50% {
            transform: translateY(-15px)
        }
    }

    @keyframes bounce-diagonal {

        0%,
        100% {
            transform: translate(0, 0)
        }

        25% {
            transform: translate(20px, -20px)
        }

        50% {
            transform: translate(-20px, 20px)
        }

        75% {
            transform: translate(20px, 20px)
        }
    }

    @keyframes bounce-horizontal {

        0%,
        100% {
            transform: translateX(0)
        }

        50% {
            transform: translateX(30px)
        }
    }

    .animate-bounce-slow {
        animation: bounce-slow 4s ease-in-out infinite
    }

    .animate-bounce-fast {
        animation: bounce-fast 2s ease-in-out infinite
    }

    .animate-bounce-diagonal {
        animation: bounce-diagonal 6s ease-in-out infinite
    }

    .animate-bounce-horizontal {
        animation: bounce-horizontal 5s ease-in-out infinite
    }
}
</style>
