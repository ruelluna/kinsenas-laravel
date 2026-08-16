import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { uploadContentImage } from '@/lib/content-image-upload';
import { cn } from '@/lib/utils';

type Props = {
    name?: string;
    defaultUrl?: string | null;
    label?: string;
    className?: string;
};

export default function CoverImageField({
    name = 'cover_image_url',
    defaultUrl = null,
    label = 'Cover image',
    className,
}: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [coverUrl, setCoverUrl] = useState(defaultUrl ?? '');
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        setUploading(true);
        setError(null);

        try {
            const url = await uploadContentImage(file);
            setCoverUrl(url);
        } catch {
            setError('Upload failed. Try again or paste a URL below.');
        } finally {
            setUploading(false);
            event.target.value = '';
        }
    }

    return (
        <div className={cn('grid gap-2', className)}>
            <Label htmlFor={`${name}-file`}>{label}</Label>
            <input type="hidden" name={name} value={coverUrl} />
            {coverUrl ? (
                <img
                    src={coverUrl}
                    alt="Cover preview"
                    className="max-h-48 w-full max-w-md rounded-md border object-cover"
                />
            ) : null}
            <div className="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    disabled={uploading}
                    onClick={() => fileInputRef.current?.click()}
                >
                    {uploading ? 'Uploading…' : coverUrl ? 'Replace image' : 'Upload image'}
                </Button>
                {coverUrl ? (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => setCoverUrl('')}
                    >
                        Remove
                    </Button>
                ) : null}
            </div>
            <Input
                ref={fileInputRef}
                id={`${name}-file`}
                type="file"
                accept="image/jpeg,image/png,image/gif,image/webp"
                className="hidden"
                onChange={handleFileChange}
            />
            <div className="grid gap-2">
                <Label htmlFor={`${name}-url`} className="text-xs text-muted-foreground">
                    Or paste image URL
                </Label>
                <Input
                    id={`${name}-url`}
                    type="url"
                    value={coverUrl}
                    placeholder="https://…"
                    onChange={(event) => setCoverUrl(event.target.value)}
                />
            </div>
            {error ? <p className="text-sm text-destructive">{error}</p> : null}
        </div>
    );
}
