export function normalizeVideoEmbedUrl(
    url: string | null | undefined,
): string | null {
    if (!url || url.trim() === '') {
        return null;
    }

    try {
        const parsed = new URL(url.trim());

        if (parsed.hostname === 'youtu.be') {
            const videoId = parsed.pathname.replace(/^\//, '');

            return videoId
                ? `https://www.youtube-nocookie.com/embed/${videoId}`
                : null;
        }

        if (parsed.hostname.includes('youtube.com')) {
            const videoId = parsed.searchParams.get('v');

            if (videoId) {
                return `https://www.youtube-nocookie.com/embed/${videoId}`;
            }

            if (parsed.pathname.startsWith('/embed/')) {
                return `https://www.youtube-nocookie.com${parsed.pathname}`;
            }
        }

        if (parsed.hostname === 'vimeo.com') {
            const match = parsed.pathname.match(/^\/(\d+)/);

            if (match?.[1]) {
                return `https://player.vimeo.com/video/${match[1]}`;
            }
        }

        if (parsed.hostname === 'player.vimeo.com') {
            return url.trim();
        }

        if (
            parsed.hostname.includes('youtube-nocookie.com') &&
            parsed.pathname.startsWith('/embed/')
        ) {
            return url.trim();
        }

        return url.trim();
    } catch {
        return null;
    }
}
