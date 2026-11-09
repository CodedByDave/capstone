const MAX_DIMENSION = 1600;
const JPEG_QUALITY = 0.8;

function loadImage(url: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('The photo could not be read.'));
        image.src = url;
    });
}

function toJpeg(canvas: HTMLCanvasElement): Promise<Blob | null> {
    return new Promise((resolve) =>
        canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY),
    );
}

/**
 * Shrinks a phone camera photo before upload, so it stays under the server's size limit and goes
 * through on a slow connection. An image element applies the photo's rotation, so it stays upright.
 *
 * Never fails: a photo the browser cannot read is returned as it is for the server to judge, and
 * so is one that would not get any smaller.
 */
export async function compressPhoto(file: File): Promise<File> {
    if (!file.type.startsWith('image/')) return file;

    const url = URL.createObjectURL(file);

    try {
        const image = await loadImage(url);
        const scale = Math.min(
            1,
            MAX_DIMENSION / Math.max(image.naturalWidth, image.naturalHeight),
        );

        const canvas = document.createElement('canvas');
        canvas.width = Math.round(image.naturalWidth * scale);
        canvas.height = Math.round(image.naturalHeight * scale);
        canvas
            .getContext('2d')
            ?.drawImage(image, 0, 0, canvas.width, canvas.height);

        const blob = await toJpeg(canvas);

        if (!blob || blob.size >= file.size) return file;

        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', {
            type: 'image/jpeg',
        });
    } catch {
        return file;
    } finally {
        URL.revokeObjectURL(url);
    }
}
