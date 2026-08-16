import { lazy, Suspense } from 'react';
import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';
import { isHtmlContent } from '@/lib/content-format';

const HtmlContentBody = lazy(() => import('@/components/content/html-content-body'));

type Props = {
    content?: string | null;
    className?: string;
};

export default function ContentBody({ content = '', className }: Props) {
    const safeContent = content ?? '';
    const proseClassName = `rich-text-content max-w-none ${className ?? ''}`;

    if (isHtmlContent(safeContent)) {
        return (
            <Suspense
                fallback={
                    <div className={`${proseClassName} animate-pulse text-muted-foreground`}>
                        Loading content…
                    </div>
                }
            >
                <HtmlContentBody content={safeContent} className={proseClassName} />
            </Suspense>
        );
    }

    return (
        <div className={proseClassName}>
            <ReactMarkdown remarkPlugins={[remarkGfm]} rehypePlugins={[rehypeSanitize]}>
                {safeContent}
            </ReactMarkdown>
        </div>
    );
}
