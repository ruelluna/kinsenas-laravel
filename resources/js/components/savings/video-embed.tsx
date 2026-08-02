import { normalizeVideoEmbedUrl } from '@/lib/video-embed-url';

type VideoEmbedProps = {
    url: string | null | undefined;
    title?: string;
    className?: string;
};

export default function VideoEmbed({
    url,
    title = 'Video',
    className,
}: VideoEmbedProps) {
    const embedUrl = normalizeVideoEmbedUrl(url);

    if (!embedUrl) {
        return null;
    }

    return (
        <div
            className={[
                'aspect-video overflow-hidden rounded-xl border border-border',
                className,
            ]
                .filter(Boolean)
                .join(' ')}
        >
            <iframe
                src={embedUrl}
                title={title}
                className="size-full"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowFullScreen
            />
        </div>
    );
}
