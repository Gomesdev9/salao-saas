/**
 * Reduz a foto no navegador antes de enviar: a imagem fica no banco, então quanto menor, melhor.
 * Fotos de celular (4000px, 5 MB) viram ~1600px e algumas centenas de KB.
 * Se o navegador não conseguir abrir a imagem, devolve o arquivo original e o servidor valida.
 */
export async function shrinkImage(file: File, maxSize: number): Promise<File> {
    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, maxSize / Math.max(bitmap.width, bitmap.height));
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d')!.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        // PNG mantém o fundo transparente dos logos; o resto vira JPEG, bem menor
        const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
        const blob = await new Promise<Blob | null>((resolve) => canvas.toBlob(resolve, type, 0.85));

        if (!blob || blob.size >= file.size) {
            return file;
        }

        return new File([blob], file.name.replace(/\.\w+$/, '') + (type === 'image/png' ? '.png' : '.jpg'), { type });
    } catch {
        return file;
    }
}
