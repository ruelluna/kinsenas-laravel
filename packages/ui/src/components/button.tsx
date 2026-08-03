import type { ReactNode } from 'react';
import {
    Button as TamaguiButton,
    type ButtonProps as TamaguiButtonProps,
    styled,
} from 'tamagui';

const StyledButton = styled(TamaguiButton, {
    name: 'KinsenasButton',
    borderRadius: '$md',
    fontWeight: '500',
    cursor: 'pointer',
    variants: {
        variant: {
            default: {
                backgroundColor: '$primary',
                color: '$primaryForeground',
                hoverStyle: { opacity: 0.9 },
            },
            destructive: {
                backgroundColor: '$destructive',
                color: 'white',
            },
            outline: {
                backgroundColor: 'transparent',
                borderWidth: 1,
                borderColor: '$borderColor',
                color: '$color',
            },
            secondary: {
                backgroundColor: '$secondary',
                color: '$color',
            },
            ghost: {
                backgroundColor: 'transparent',
                color: '$color',
                hoverStyle: { backgroundColor: '$secondary' },
            },
            link: {
                backgroundColor: 'transparent',
                color: '$primary',
                textDecorationLine: 'underline',
            },
        },
        size: {
            default: { height: 36, paddingHorizontal: 16, fontSize: 14 },
            sm: { height: 32, paddingHorizontal: 12, fontSize: 13 },
            lg: { height: 40, paddingHorizontal: 24, fontSize: 15 },
            icon: { height: 36, width: 36, paddingHorizontal: 0 },
        },
    } as const,
    defaultVariants: {
        variant: 'default',
        size: 'default',
    },
});

export type ButtonProps = TamaguiButtonProps & {
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    size?: 'default' | 'sm' | 'lg' | 'icon';
    children?: ReactNode;
};

export function Button({ variant = 'default', size = 'default', ...props }: ButtonProps) {
    return <StyledButton variant={variant} size={size} {...props} />;
}
