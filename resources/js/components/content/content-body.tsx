import ReactMarkdown from 'react-markdown';
import rehypeSanitize from 'rehype-sanitize';
import remarkGfm from 'remark-gfm';

type Props = {
    content: string;
    className?: string;
};

export default function ContentBody({ content, className }: Props) {
    return (
        <div
            className={`prose prose-neutral dark:prose-invert max-w-none ${className ?? ''}`}
        >
            <ReactMarkdown remarkPlugins={[remarkGfm]} rehypePlugins={[rehypeSanitize]}>
                {content}
            </ReactMarkdown>
        </div>
    );
}
