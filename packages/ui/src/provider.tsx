import type { ReactNode } from 'react';
import { TamaguiProvider, Theme, useThemeName } from 'tamagui';
import { kinsenasConfig } from './theme';

type KinsenasProviderProps = {
    children: ReactNode;
    defaultTheme?: 'light' | 'dark';
};

export function KinsenasProvider({
    children,
    defaultTheme = 'light',
}: KinsenasProviderProps) {
    return (
        <TamaguiProvider config={kinsenasConfig} defaultTheme={defaultTheme}>
            <Theme name={defaultTheme}>{children}</Theme>
        </TamaguiProvider>
    );
}

export { useThemeName, kinsenasConfig };
