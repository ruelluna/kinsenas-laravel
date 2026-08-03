import type { ReactNode } from 'react';
import { Card as TamaguiCard, styled, Text, YStack } from 'tamagui';

const StyledCard = styled(TamaguiCard, {
    name: 'KinsenasCard',
    backgroundColor: '$card',
    borderWidth: 1,
    borderColor: '$borderColor',
    borderRadius: '$lg',
    padding: 16,
});

export type CardProps = {
    children: ReactNode;
    title?: string;
    description?: string;
};

export function Card({ children, title, description }: CardProps) {
    return (
        <StyledCard>
            {(title || description) && (
                <YStack marginBottom={12} gap={4}>
                    {title && (
                        <Text fontSize={16} fontWeight="600" color="$color">
                            {title}
                        </Text>
                    )}
                    {description && (
                        <Text fontSize={14} color="$mutedForeground">
                            {description}
                        </Text>
                    )}
                </YStack>
            )}
            {children}
        </StyledCard>
    );
}

export function CardHeader({ children }: { children: ReactNode }) {
    return <YStack marginBottom={8}>{children}</YStack>;
}

export function CardContent({ children }: { children: ReactNode }) {
    return <YStack gap={8}>{children}</YStack>;
}

export function CardFooter({ children }: { children: ReactNode }) {
    return <YStack marginTop={12} flexDirection="row" gap={8}>{children}</YStack>;
}
