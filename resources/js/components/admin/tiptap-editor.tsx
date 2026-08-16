import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import { EditorContent, useEditor, useEditorState } from '@tiptap/react';
import type { Editor } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    Quote,
    Redo2,
    Undo2,
} from 'lucide-react';
import { useEffect, useRef, type MouseEvent, type ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { uploadContentImage } from '@/lib/content-image-upload';
import { cn } from '@/lib/utils';

type Props = {
    name?: string;
    defaultValue?: string;
    required?: boolean;
    uploadUrl?: string;
    className?: string;
};

function insertUploadedImage(editor: Editor, url: string): void {
    editor.chain().focus().setImage({ src: url }).run();
}

function runToolbarCommand(handler: () => void) {
    return (event: MouseEvent<HTMLButtonElement>) => {
        event.preventDefault();
        handler();
    };
}

function ToolbarButton({
    active,
    disabled,
    label,
    onClick,
    children,
}: {
    active?: boolean;
    disabled?: boolean;
    label: string;
    onClick: () => void;
    children: ReactNode;
}) {
    return (
        <Button
            type="button"
            size="icon"
            variant={active ? 'secondary' : 'ghost'}
            className="size-8"
            aria-label={label}
            aria-pressed={active}
            disabled={disabled}
            onMouseDown={runToolbarCommand(onClick)}
        >
            {children}
        </Button>
    );
}

type EditorToolbarProps = {
    editor: Editor;
    onInsertImage: () => void;
};

function EditorToolbar({ editor, onInsertImage }: EditorToolbarProps) {
    const toolbarState = useEditorState({
        editor,
        selector: ({ editor: currentEditor }) => ({
            isBold: currentEditor.isActive('bold'),
            canBold: currentEditor.can().chain().toggleBold().run(),
            isItalic: currentEditor.isActive('italic'),
            canItalic: currentEditor.can().chain().toggleItalic().run(),
            isHeading2: currentEditor.isActive('heading', { level: 2 }),
            isBulletList: currentEditor.isActive('bulletList'),
            isOrderedList: currentEditor.isActive('orderedList'),
            isBlockquote: currentEditor.isActive('blockquote'),
            isLink: currentEditor.isActive('link'),
            canUndo: currentEditor.can().chain().undo().run(),
            canRedo: currentEditor.can().chain().redo().run(),
        }),
    });

    function setLink(): void {
        const previousUrl = editor.getAttributes('link').href as string | undefined;
        const url = window.prompt('Link URL', previousUrl ?? 'https://');

        if (url === null) {
            return;
        }

        if (url === '') {
            editor.chain().focus().extendMarkRange('link').unsetLink().run();

            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }

    return (
        <div className="bg-muted/40 flex flex-wrap gap-1 border-b p-1">
            <ToolbarButton
                label="Bold"
                active={toolbarState.isBold}
                disabled={!toolbarState.canBold}
                onClick={() => editor.chain().focus().toggleBold().run()}
            >
                <Bold className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Italic"
                active={toolbarState.isItalic}
                disabled={!toolbarState.canItalic}
                onClick={() => editor.chain().focus().toggleItalic().run()}
            >
                <Italic className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Heading 2"
                active={toolbarState.isHeading2}
                onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
            >
                <Heading2 className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Bullet list"
                active={toolbarState.isBulletList}
                onClick={() => editor.chain().focus().toggleBulletList().run()}
            >
                <List className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Ordered list"
                active={toolbarState.isOrderedList}
                onClick={() => editor.chain().focus().toggleOrderedList().run()}
            >
                <ListOrdered className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Blockquote"
                active={toolbarState.isBlockquote}
                onClick={() => editor.chain().focus().toggleBlockquote().run()}
            >
                <Quote className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Insert link"
                active={toolbarState.isLink}
                onClick={setLink}
            >
                <Link2 className="size-4" />
            </ToolbarButton>
            <ToolbarButton label="Insert image" onClick={onInsertImage}>
                <ImagePlus className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Undo"
                disabled={!toolbarState.canUndo}
                onClick={() => editor.chain().focus().undo().run()}
            >
                <Undo2 className="size-4" />
            </ToolbarButton>
            <ToolbarButton
                label="Redo"
                disabled={!toolbarState.canRedo}
                onClick={() => editor.chain().focus().redo().run()}
            >
                <Redo2 className="size-4" />
            </ToolbarButton>
        </div>
    );
}

export default function TiptapEditor({
    name = 'body',
    defaultValue = '',
    required = false,
    uploadUrl = '/admin/content/uploads',
    className,
}: Props) {
    const inputId = name;
    const hiddenInputRef = useRef<HTMLInputElement>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const editor = useEditor({
        immediatelyRender: false,
        extensions: [
            StarterKit.configure({
                heading: {
                    levels: [2, 3],
                },
                link: {
                    openOnClick: false,
                    autolink: true,
                    HTMLAttributes: {
                        rel: 'noopener noreferrer',
                        target: '_blank',
                    },
                },
            }),
            Image.configure({
                HTMLAttributes: {
                    class: 'rounded-md',
                },
            }),
            Placeholder.configure({
                placeholder: 'Write your post content…',
            }),
        ],
        content: defaultValue,
        editorProps: {
            attributes: {
                class: 'tiptap rich-text-content min-h-56 px-3 py-2 focus:outline-none',
            },
        },
        onUpdate: ({ editor: currentEditor }) => {
            if (hiddenInputRef.current) {
                hiddenInputRef.current.value = currentEditor.getHTML();
            }
        },
    });

    useEffect(() => {
        if (hiddenInputRef.current && editor) {
            hiddenInputRef.current.value = editor.getHTML();
        }
    }, [editor]);

    async function handleImageSelected(event: React.ChangeEvent<HTMLInputElement>): Promise<void> {
        const file = event.target.files?.[0];

        event.target.value = '';

        if (!file || !editor) {
            return;
        }

        try {
            const url = await uploadContentImage(file, uploadUrl);
            insertUploadedImage(editor, url);
        } catch {
            // Upload errors surface via failed fetch; keep editor state unchanged.
        }
    }

    return (
        <div className={cn('grid gap-2', className)} data-test="content-body-editor">
            <input
                ref={hiddenInputRef}
                id={inputId}
                name={name}
                type="hidden"
                defaultValue={defaultValue}
                required={required}
            />
            <div className="border-input overflow-hidden rounded-md border shadow-xs">
                {editor && (
                    <EditorToolbar
                        editor={editor}
                        onInsertImage={() => fileInputRef.current?.click()}
                    />
                )}
                <EditorContent editor={editor} />
            </div>
            <input
                ref={fileInputRef}
                type="file"
                accept="image/jpeg,image/png,image/gif,image/webp"
                className="hidden"
                onChange={handleImageSelected}
            />
        </div>
    );
}
