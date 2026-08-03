import { Share, Plus, SquarePlus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { KINSENAS_SQUARE_LOGO } from '@/lib/brand';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

const steps = [
    {
        icon: Share,
        title: 'Tap Share',
        description: 'Open the Share menu from the Safari toolbar.',
    },
    {
        icon: SquarePlus,
        title: 'Add to Home Screen',
        description: 'Scroll the share sheet and choose Add to Home Screen.',
    },
    {
        icon: Plus,
        title: 'Tap Add',
        description: 'Confirm the name, then open Kinsenas from your home screen.',
    },
] as const;

export function InstallAppSheet({ open, onOpenChange }: Props) {
    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="bottom"
                className="rounded-t-2xl pb-[calc(1rem+env(safe-area-inset-bottom))]"
            >
                <SheetHeader className="text-left">
                    <SheetTitle>Install Kinsenas</SheetTitle>
                    <SheetDescription>
                        Add Kinsenas to your home screen for quick, app-like
                        access.
                    </SheetDescription>
                </SheetHeader>

                <div className="mt-4 flex items-center gap-3 rounded-xl border bg-muted/30 p-3">
                    <img
                        src={KINSENAS_SQUARE_LOGO}
                        alt=""
                        className="size-12 rounded-xl object-contain"
                    />
                    <div>
                        <p className="font-medium">Kinsenas</p>
                        <p className="text-sm text-muted-foreground">
                            Sweldo with a plan
                        </p>
                    </div>
                </div>

                <ol className="mt-5 space-y-4">
                    {steps.map((step, index) => (
                        <li key={step.title} className="flex gap-3">
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <step.icon className="size-4" />
                            </div>
                            <div>
                                <p className="text-sm font-medium">
                                    {index + 1}. {step.title}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {step.description}
                                </p>
                            </div>
                        </li>
                    ))}
                </ol>

                <Button
                    type="button"
                    className="mt-6 w-full"
                    onClick={() => onOpenChange(false)}
                >
                    Got it
                </Button>
            </SheetContent>
        </Sheet>
    );
}
