import { createTamagui, createTokens } from '@tamagui/core';
import { defaultConfig } from '@tamagui/config/v4';

const kinsenasTokens = createTokens({
    ...defaultConfig.tokens,
    color: {
        ...defaultConfig.tokens.color,
        background: '#fafdfd',
        backgroundDark: '#1a2428',
        foreground: '#1a3338',
        foregroundDark: '#fafafa',
        primary: '#0D7377',
        primaryDark: '#14a3a8',
        primaryForeground: '#ffffff',
        primaryForegroundDark: '#1a2428',
        secondary: '#eef7f7',
        secondaryDark: '#2a3539',
        muted: '#eef5f5',
        mutedForeground: '#64748b',
        destructive: '#dc2626',
        success: '#059669',
        warning: '#d97706',
        border: '#d9e8e8',
        borderDark: '#2a3539',
        card: '#fafdfd',
        cardDark: '#1a2428',
        input: '#d9e8e8',
        ring: '#0D7377',
    },
    radius: {
        ...defaultConfig.tokens.radius,
        sm: 6,
        md: 8,
        lg: 10,
        true: 10,
    },
    space: defaultConfig.tokens.space,
    size: defaultConfig.tokens.size,
    zIndex: defaultConfig.tokens.zIndex,
});

export const kinsenasConfig = createTamagui({
    ...defaultConfig,
    tokens: kinsenasTokens,
    themes: {
        light: {
            background: kinsenasTokens.color.background,
            color: kinsenasTokens.color.foreground,
            primary: kinsenasTokens.color.primary,
            primaryForeground: kinsenasTokens.color.primaryForeground,
            secondary: kinsenasTokens.color.secondary,
            muted: kinsenasTokens.color.muted,
            mutedForeground: kinsenasTokens.color.mutedForeground,
            destructive: kinsenasTokens.color.destructive,
            success: kinsenasTokens.color.success,
            warning: kinsenasTokens.color.warning,
            borderColor: kinsenasTokens.color.border,
            card: kinsenasTokens.color.card,
            inputBackground: kinsenasTokens.color.input,
            ring: kinsenasTokens.color.ring,
        },
        dark: {
            background: kinsenasTokens.color.backgroundDark,
            color: kinsenasTokens.color.foregroundDark,
            primary: kinsenasTokens.color.primaryDark,
            primaryForeground: kinsenasTokens.color.primaryForegroundDark,
            secondary: kinsenasTokens.color.secondaryDark,
            muted: kinsenasTokens.color.secondaryDark,
            mutedForeground: '#94a3b8',
            destructive: '#ef4444',
            success: '#34d399',
            warning: '#fbbf24',
            borderColor: kinsenasTokens.color.borderDark,
            card: kinsenasTokens.color.cardDark,
            inputBackground: kinsenasTokens.color.borderDark,
            ring: kinsenasTokens.color.primaryDark,
        },
    },
});

export type KinsenasConfig = typeof kinsenasConfig;

declare module '@tamagui/core' {
    interface TamaguiCustomConfig extends KinsenasConfig {}
}

export { kinsenasTokens };
