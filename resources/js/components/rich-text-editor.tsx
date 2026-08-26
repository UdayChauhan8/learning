// resources/js/components/rich-text-editor.tsx
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import { useEffect } from 'react';

interface Props {
    value: string;                        // current HTML content
    onChange: (html: string) => void;     // called whenever content changes
    placeholder?: string;
}

export default function RichTextEditor({ value, onChange, placeholder }: Props) {

    const editor = useEditor({
        extensions: [
            // StarterKit includes: Bold, Italic, Headings, BulletList,
            // OrderedList, Blockquote, Code, CodeBlock, HorizontalRule
            StarterKit,

            // Link extension — allows adding hyperlinks
            Link.configure({
                openOnClick: false,        // don't follow links while editing
                HTMLAttributes: {
                    class: 'text-blue-600 underline',
                },
            }),

            // Placeholder shown when editor is empty
            Placeholder.configure({
                placeholder: placeholder ?? 'Write event description here...',
            }),
        ],

        // Initial content from parent (e.g. when editing existing event)
        content: value,

        // Called on every keystroke — sends HTML string up to parent form
        onUpdate: ({ editor }) => {
            onChange(editor.getHTML());
        },
    });

    // Sync external value changes (e.g. form reset after save)
    useEffect(() => {
        if (editor && value !== editor.getHTML()) {
            editor.commands.setContent(value || '');
        }
    }, [value]);

    if (!editor) return null;

    return (
        <div className="rounded-xl border border-slate-300 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/10">

            {/* Toolbar */}
            <div className="flex flex-wrap gap-1 border-b border-slate-200 p-2">
                {/* Bold */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleBold().run()}
                    active={editor.isActive('bold')}
                    title="Bold"
                >
                    <strong>B</strong>
                </ToolbarButton>

                {/* Italic */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                    active={editor.isActive('italic')}
                    title="Italic"
                >
                    <em>I</em>
                </ToolbarButton>

                {/* Heading 2 */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
                    active={editor.isActive('heading', { level: 2 })}
                    title="Heading"
                >
                    H2
                </ToolbarButton>

                {/* Bullet list */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleBulletList().run()}
                    active={editor.isActive('bulletList')}
                    title="Bullet list"
                >
                    • List
                </ToolbarButton>

                {/* Ordered list */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleOrderedList().run()}
                    active={editor.isActive('orderedList')}
                    title="Numbered list"
                >
                    1. List
                </ToolbarButton>

                {/* Blockquote */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().toggleBlockquote().run()}
                    active={editor.isActive('blockquote')}
                    title="Blockquote"
                >
                    " Quote
                </ToolbarButton>

                <div className="mx-1 w-px bg-slate-200" /> {/* divider */}

                {/* Clear formatting */}
                <ToolbarButton
                    onClick={() => editor.chain().focus().clearNodes().unsetAllMarks().run()}
                    active={false}
                    title="Clear formatting"
                >
                    Clear
                </ToolbarButton>
            </div>

            {/* Editable area — Tiptap renders a contenteditable div here */}
            <EditorContent
                editor={editor}
                className="prose prose-sm max-w-none p-4 focus:outline-none min-h-[200px]
                           [&_.tiptap]:outline-none [&_.tiptap]:min-h-[200px]"
            />
        </div>
    );
}

// Reusable toolbar button
function ToolbarButton({
    onClick,
    active,
    title,
    children,
}: {
    onClick: () => void;
    active: boolean;
    title: string;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"          // IMPORTANT: prevent form submission on click
            title={title}
            onClick={onClick}
            className={`rounded px-2.5 py-1.5 text-xs font-medium transition ${
                active
                    ? 'bg-slate-900 text-white'
                    : 'text-slate-600 hover:bg-slate-100'
            }`}
        >
            {children}
        </button>
    );
}