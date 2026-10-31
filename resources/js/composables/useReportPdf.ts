import html2pdf from 'html2pdf.js';
import { ref } from 'vue';

/** Saves the element bound to `content` as an A4 PDF named `<prefix>-<date>.pdf`. */
export function useReportPdf(prefix: string) {
    const content = ref<HTMLElement | null>(null);
    const downloading = ref(false);

    async function download() {
        if (!content.value || downloading.value) return;

        downloading.value = true;
        try {
            await html2pdf()
                .set({
                    margin: [10, 10, 10, 10],
                    filename: `${prefix}-${new Date().toISOString().slice(0, 10)}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: {
                        unit: 'mm',
                        format: 'a4',
                        orientation: 'portrait',
                    },
                })
                .from(content.value)
                .save();
        } finally {
            downloading.value = false;
        }
    }

    return { content, downloading, download };
}
