interface AgreementAcceptancePdf {
    business_name: string;
    signer_name: string;
    signer_role: string;
    accepted_at: string;
    content_hash: string;
    signature_url?: string | null;
    execution_status?: 'awaiting_platform_signature' | 'fully_executed';
    platform_signature?: {
        signer_name: string;
        signer_role: string;
        signed_at: string;
        signature_url: string;
    } | null;
    agreement: {
        title: string;
        version: string;
        content: string;
        effective_at: string;
    };
}

async function imageData(url: string): Promise<string | null> {
    const response = await fetch(url, { credentials: 'same-origin' });
    if (!response.ok) return null;
    const blob = await response.blob();

    return await new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = () =>
            resolve(typeof reader.result === 'string' ? reader.result : null);
        reader.onerror = () => resolve(null);
        reader.readAsDataURL(blob);
    });
}

export async function downloadBusinessAgreementPdf(
    acceptance: AgreementAcceptancePdf,
): Promise<void> {
    const { jsPDF } = await import('jspdf');
    const document = new jsPDF({
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait',
    });
    const left = 18;
    const width = 174;
    const bottom = 278;
    let y = 18;

    const ensureSpace = (height: number) => {
        if (y + height <= bottom) return;
        document.addPage();
        y = 18;
    };

    const write = (text: string, size = 10, bold = false, gap = 3) => {
        document.setFont('helvetica', bold ? 'bold' : 'normal');
        document.setFontSize(size);
        const lines = document.splitTextToSize(text, width) as string[];
        const height = lines.length * (size * 0.42 + 1.2);
        ensureSpace(height);
        document.text(lines, left, y);
        y += height + gap;
    };

    document.setFont('helvetica', 'bold');
    document.setFontSize(16);
    const title = document.splitTextToSize(
        acceptance.agreement.title,
        width,
    ) as string[];
    document.text(title, 105, y, { align: 'center' });
    y += title.length * 7 + 2;
    document.setFont('helvetica', 'normal');
    document.setFontSize(9);
    document.text(`Version ${acceptance.agreement.version}`, 105, y, {
        align: 'center',
    });
    y += 8;
    document.line(left, y, 192, y);
    y += 8;

    for (const paragraph of acceptance.agreement.content
        .split(/\n\s*\n/)
        .filter(Boolean)) {
        if (paragraph.trim() === acceptance.agreement.title) continue;
        const clause = paragraph.match(
            /^(\d+\.\s+[A-Z][A-Z &/()-]+)\n([\s\S]+)$/,
        );
        if (clause) {
            write(clause[1], 10, true, 1);
            write(clause[2], 10, false, 4);
        } else {
            write(paragraph, 10, false, 4);
        }
    }

    ensureSpace(55);
    y += 4;
    document.line(left, y, 192, y);
    y += 7;
    write('ELECTRONIC SIGNATURE', 9, true, 2);

    if (acceptance.signature_url) {
        const signature = await imageData(acceptance.signature_url);
        if (signature) {
            ensureSpace(24);
            const format = signature.startsWith('data:image/png')
                ? 'PNG'
                : 'JPEG';
            document.addImage(
                signature,
                format,
                left,
                y,
                55,
                20,
                undefined,
                'FAST',
            );
            y += 23;
        }
    }

    write(`${acceptance.signer_name} — ${acceptance.signer_role}`, 10, true, 1);
    write(`Business: ${acceptance.business_name}`, 9, false, 1);
    write(
        `Accepted: ${new Date(acceptance.accepted_at).toLocaleString('en-PH')}`,
        9,
        false,
        1,
    );

    if (acceptance.platform_signature) {
        ensureSpace(42);
        y += 5;
        write('FOR LAUNDRYHUB', 9, true, 2);
        const platformImage = await imageData(
            acceptance.platform_signature.signature_url,
        );
        if (platformImage) {
            ensureSpace(24);
            const format = platformImage.startsWith('data:image/png')
                ? 'PNG'
                : 'JPEG';
            document.addImage(
                platformImage,
                format,
                left,
                y,
                55,
                20,
                undefined,
                'FAST',
            );
            y += 23;
        }
        write(
            `${acceptance.platform_signature.signer_name} — ${acceptance.platform_signature.signer_role}`,
            10,
            true,
            1,
        );
        write(
            `Countersigned: ${new Date(acceptance.platform_signature.signed_at).toLocaleString('en-PH')}`,
            9,
            false,
            1,
        );
    }

    write(`Content fingerprint: ${acceptance.content_hash}`, 7, false, 1);

    document.save(`business-agreement-v${acceptance.agreement.version}.pdf`);
}
