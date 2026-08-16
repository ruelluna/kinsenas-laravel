export async function uploadContentImage(
    file: File,
    uploadUrl = '/admin/content/uploads',
): Promise<string> {
    const formData = new FormData();
    formData.append('image', file);

    const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN':
                document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                    ?.content ?? '',
        },
        credentials: 'same-origin',
        body: formData,
    });

    if (!response.ok) {
        throw new Error('Image upload failed.');
    }

    const data = (await response.json()) as { url: string };

    return data.url;
}
