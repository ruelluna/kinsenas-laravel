import type { ReactNode } from 'react';
import { styled } from 'tamagui';
import { Button } from './button';

const FabButton = styled(Button, {
    position: 'fixed',
    zIndex: 40,
    width: 56,
    height: 56,
    borderRadius: 9999,
    paddingHorizontal: 0,
    shadowColor: 'rgba(0,0,0,0.25)',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 1,
    shadowRadius: 8,
    bottom: 'calc(16px + env(safe-area-inset-bottom, 0px))',
    right: 'calc(16px + env(safe-area-inset-right, 0px))',
});

export type MobileActionFabProps = {
    onPress: () => void;
    accessibilityLabel: string;
    children?: ReactNode;
};

export function MobileActionFab({
    onPress,
    accessibilityLabel,
    children = '+',
}: MobileActionFabProps) {
    return (
        <FabButton
            size="lg"
            aria-label={accessibilityLabel}
            onPress={onPress}
        >
            {children}
        </FabButton>
    );
}
