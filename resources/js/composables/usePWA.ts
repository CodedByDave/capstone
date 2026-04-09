import { ref } from 'vue';

interface BeforeInstallPromptEvent extends Event {
    prompt(): Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt.value = e as BeforeInstallPromptEvent;
});

export function usePWA() {
    const installApp = async () => {
        if (!deferredPrompt.value) {
            // Already installed or not supported — do nothing or show a message
            return;
        }
        await deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;
        if (outcome === 'accepted') {
            deferredPrompt.value = null;
        }
    };

    return { installApp };
}