import { Download, Menu, Monitor, Share, Plus, SquarePlus } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { KINSENAS_SQUARE_LOGO } from '@/lib/brand';
import type { InstallGuideVariant } from '@/lib/pwa-install';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    variant: InstallGuideVariant;
};

const iosSteps = [
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

const chromiumSteps = [
    {
        icon: Download,
        title: 'Find Install',
        description:
            'Look for the install icon in the address bar, or open the browser menu.',
    },
    {
        icon: Monitor,
        title: 'Install Kinsenas',
        description: 'Choose Install Kinsenas or Install app, then confirm.',
    },
    {
        icon: Plus,
        title: 'Open the app',
        description:
            'Launch Kinsenas from your home screen, taskbar, or apps list.',
    },
] as const;

const genericSteps = [
    {
        icon: Menu,
        title: 'Open browser menu',
        description: 'Tap the menu button in your browser toolbar.',
    },
    {
        icon: Download,
        title: 'Install or add to home screen',
        description:
            'Choose Install app, Add to Home Screen, or a similar option.',
    },
    {
        icon: Plus,
        title: 'Confirm',
        description: 'Add Kinsenas, then open it from your device.',
    },
] as const;

const guideCopy: Record<
    InstallGuideVariant,
    { description: string; steps: typeof iosSteps }
> = {
    ios: {
        description:
            'Add Kinsenas to your home screen for quick, app-like access.',
        steps: iosSteps,
    },
    chromium: {
        description:
            'Install Kinsenas from your browser for quick, app-like access.',
        steps: chromiumSteps,
    },
    generic: {
        description:
            'Add Kinsenas to this device for quick, app-like access.',
        steps: genericSteps,
    },
};

export function InstallAppSheet({ open, onOpenChange, variant }: Props) {
    const { description, steps } = guideCopy[variant];

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent
                side="bottom"
                className="rounded-t-2xl pb-[calc(1rem+env(safe-area-inset-bottom))]"
            >
                <SheetHeader className="text-left">
                    <SheetTitle>Install Kinsenas</SheetTitle>
                    <SheetDescription>{description}</SheetDescription>
                </SheetHeader>

                <div className="flex flex-col gap-5 px-4">
                    <div className="flex items-center gap-3 rounded-xl border bg-muted/30 p-3">
                        <img
                            src={KINSENAS_SQUARE_LOGO}
                            alt="Kinsenas"
                            className="size-12 shrink-0 object-contain"
                        />
                        <div>
                            <p className="font-medium">Kinsenas</p>
                            <p className="text-sm text-muted-foreground">
                                Sweldo with a plan
                            </p>
                        </div>
                    </div>

                    <ol className="space-y-4">
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
                        className="w-full"
                        onClick={() => onOpenChange(false)}
                    >
                        Got it
                    </Button>
                </div>
            </SheetContent>
        </Sheet>
    );
}
