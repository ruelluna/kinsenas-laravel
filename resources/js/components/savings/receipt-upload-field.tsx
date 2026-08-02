import { Camera, ImagePlus, X } from 'lucide-react';
import { useEffect, useId, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Props = {
    error?: string;
    existingImageUrl?: string | null;
};

function isMobileDevice(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(pointer: coarse)').matches;
}

function ReceiptUploadFieldInner({ error, existingImageUrl = null }: Props) {
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [fileName, setFileName] = useState<string | null>(null);
    const [useCamera] = useState(() => isMobileDevice());

    const displayUrl = previewUrl ?? existingImageUrl;

    useEffect(() => {
        return () => {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        };
    }, [previewUrl]);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>) {
        const file = event.target.files?.[0] ?? null;

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        if (file) {
            setPreviewUrl(URL.createObjectURL(file));
            setFileName(file.name);

            return;
        }

        setPreviewUrl(null);
        setFileName(null);
    }

    function clearReceipt() {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        setPreviewUrl(null);
        setFileName(null);

        if (inputRef.current) {
            inputRef.current.value = '';
        }
    }

    function openPicker() {
        inputRef.current?.click();
    }

    return (
        <div className="grid gap-2">
            <Label htmlFor={inputId}>Receipt (optional)</Label>

            <input
                ref={inputRef}
                id={inputId}
                name="receipt_image"
                type="file"
                accept="image/*"
                capture={useCamera ? 'environment' : undefined}
                className="sr-only"
                onChange={handleFileChange}
            />

            {displayUrl ? (
                <div className="space-y-3">
                    <div className="relative overflow-hidden rounded-lg border bg-muted/30">
                        <img
                            src={displayUrl}
                            alt="Receipt preview"
                            className="max-h-48 w-full object-contain"
                        />
                        {previewUrl && (
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon"
                                className="absolute top-2 right-2 size-8"
                                onClick={clearReceipt}
                                aria-label="Remove receipt"
                            >
                                <X className="size-4" />
                            </Button>
                        )}
                    </div>
                    {fileName && (
                        <p className="truncate text-xs text-muted-foreground">
                            {fileName}
                        </p>
                    )}
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={openPicker}
                    >
                        {useCamera ? (
                            <>
                                <Camera className="size-4" /> Retake photo
                            </>
                        ) : (
                            <>
                                <ImagePlus className="size-4" /> Choose
                                different image
                            </>
                        )}
                    </Button>
                </div>
            ) : (
                <button
                    type="button"
                    onClick={openPicker}
                    className={cn(
                        'flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed p-6 text-sm transition-colors',
                        'hover:border-primary/50 hover:bg-muted/30',
                    )}
                >
                    {useCamera ? (
                        <>
                            <Camera className="size-8 text-muted-foreground" />
                            <span>Take a photo of your receipt</span>
                        </>
                    ) : (
                        <>
                            <ImagePlus className="size-8 text-muted-foreground" />
                            <span>Upload a receipt image</span>
                        </>
                    )}
                </button>
            )}

            <InputError message={error} />
        </div>
    );
}

type ReceiptUploadFieldProps = Props & {
    resetKey?: number;
};

export default function ReceiptUploadField({
    resetKey = 0,
    ...props
}: ReceiptUploadFieldProps) {
    return <ReceiptUploadFieldInner key={resetKey} {...props} />;
}
