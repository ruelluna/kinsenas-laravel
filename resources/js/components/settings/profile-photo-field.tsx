import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Props = {
    currentPhotoUrl?: string | null;
    className?: string;
};

export default function ProfilePhotoField({ currentPhotoUrl = null, className }: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(currentPhotoUrl);
    const [removePhoto, setRemovePhoto] = useState(false);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0];

        if (!file) {
            return;
        }

        setRemovePhoto(false);
        setPreviewUrl(URL.createObjectURL(file));
    }

    return (
        <div className={cn('grid gap-3', className)}>
            <Label>Profile photo</Label>
            <div className="flex items-center gap-4">
                <div className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full border bg-muted">
                    {previewUrl && !removePhoto ? (
                        <img
                            src={previewUrl}
                            alt="Profile preview"
                            className="size-full object-cover"
                        />
                    ) : (
                        <span className="text-xs text-muted-foreground">No photo</span>
                    )}
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={() => fileInputRef.current?.click()}
                    >
                        {previewUrl && !removePhoto ? 'Change photo' : 'Upload photo'}
                    </Button>
                    {(previewUrl || currentPhotoUrl) && !removePhoto ? (
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => {
                                setRemovePhoto(true);
                                setPreviewUrl(null);
                                if (fileInputRef.current) {
                                    fileInputRef.current.value = '';
                                }
                            }}
                        >
                            Remove
                        </Button>
                    ) : null}
                </div>
            </div>
            <Input
                ref={fileInputRef}
                type="file"
                name="profile_photo"
                accept="image/jpeg,image/png,image/gif,image/webp"
                className="hidden"
                onChange={handleFileChange}
            />
            {removePhoto ? (
                <input type="hidden" name="remove_profile_photo" value="1" />
            ) : null}
            <p className="text-xs text-muted-foreground">
                Shown on your account and on posts you author.
            </p>
        </div>
    );
}
