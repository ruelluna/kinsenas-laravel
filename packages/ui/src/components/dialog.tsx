import type { ReactNode } from 'react';
import { Adapt, Dialog, Sheet, Text, YStack } from 'tamagui';
import { Button } from './button';

export type DialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    children: ReactNode;
    confirmLabel?: string;
    onConfirm?: () => void;
    cancelLabel?: string;
};

export function KinsenasDialog({
    open,
    onOpenChange,
    title,
    description,
    children,
    confirmLabel = 'Confirm',
    onConfirm,
    cancelLabel = 'Cancel',
}: DialogProps) {
    return (
        <Dialog modal open={open} onOpenChange={onOpenChange}>
            <Adapt when="sm" platform="touch">
                <Sheet animation="medium" zIndex={200000} modal dismissOnSnapToBottom>
                    <Sheet.Frame padding={16} gap={12}>
                        <Adapt.Contents />
                    </Sheet.Frame>
                    <Sheet.Overlay animation="lazy" opacity={0.5} />
                </Sheet>
            </Adapt>

            <Dialog.Portal>
                <Dialog.Overlay
                    key="overlay"
                    animation="quick"
                    opacity={0.5}
                    backgroundColor="black"
                />
                <Dialog.Content
                    bordered
                    elevate
                    key="content"
                    animation="quick"
                    padding={16}
                    gap={12}
                    maxWidth={400}
                    width="90%"
                    backgroundColor="$card"
                    borderRadius="$lg"
                >
                    <Dialog.Title fontSize={18} fontWeight="600">
                        {title}
                    </Dialog.Title>
                    {description && (
                        <Dialog.Description color="$mutedForeground">
                            {description}
                        </Dialog.Description>
                    )}
                    {children}
                    <YStack flexDirection="row" gap={8} justifyContent="flex-end">
                        <Dialog.Close asChild>
                            <Button variant="outline">{cancelLabel}</Button>
                        </Dialog.Close>
                        {onConfirm && (
                            <Button variant="default" onPress={onConfirm}>
                                {confirmLabel}
                            </Button>
                        )}
                    </YStack>
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog>
    );
}

export type SheetProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title?: string;
    children: ReactNode;
    side?: 'left' | 'right';
};

export function KinsenasSheet({
    open,
    onOpenChange,
    title,
    children,
    side = 'left',
}: SheetProps) {
    return (
        <Sheet
            modal
            open={open}
            onOpenChange={onOpenChange}
            snapPoints={[85]}
            dismissOnSnapToBottom
            zIndex={100000}
        >
            <Sheet.Overlay animation="lazy" opacity={0.5} />
            <Sheet.Frame
                padding={16}
                gap={12}
                backgroundColor="$card"
                borderTopLeftRadius={side === 'left' ? 0 : 12}
                borderTopRightRadius={side === 'right' ? 0 : 12}
            >
                {title && (
                    <Text fontSize={18} fontWeight="600" marginBottom={8}>
                        {title}
                    </Text>
                )}
                {children}
            </Sheet.Frame>
        </Sheet>
    );
}
