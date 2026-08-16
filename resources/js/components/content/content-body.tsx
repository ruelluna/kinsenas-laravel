import { lazy, Suspense } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';
import { isHtmlContent } from '@/lib/content-format';

const HtmlContentBody = lazy(() => import('@/components/content/html-content-body'));

type Props = {
    content: string;
    className?: string;
};

export default function ContentBody({ content, className }: Props) {
    const proseClassName = `rich-text-content max-w-none ${className ?? ''}`;

    if (isHtmlContent(content)) {
        return (
            <Suspense
                fallback={
                    <div className={`${proseClassName} animate-pulse text-muted-foreground`}>
                        Loading content…
                    </div>
                }
            >
                <HtmlContentBody content={content} className={proseClassName} />
            </Suspense>
        );
    }

    return (
        <div className={proseClassName}>
            <ReactMarkdown remarkPlugins={[remarkGfm]} rehypePlugins={[rehypeSanitize]}>
                {content}
            </ReactMarkdown>
        </div>
    );
}
