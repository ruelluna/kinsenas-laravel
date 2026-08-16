import DOMPurify from 'dompurify';

type Props = {
    content: string;
    className: string;
};

export default function HtmlContentBody({ content, className }: Props) {
    return (
        <div
            className={className}
            dangerouslySetInnerHTML={{
                __html: DOMPurify.sanitize(content, {
                    USE_PROFILES: { html: true },
                }),
            }}
        />
    );
}
