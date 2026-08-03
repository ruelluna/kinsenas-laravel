import type { ReactNode } from 'react';
import { ScrollView, Text, XStack, YStack } from 'tamagui';
import { KinsenasSheet } from './dialog';
import { Button } from './button';

export type NavItem = {
    key: string;
    label: string;
    href?: string;
    onPress?: () => void;
    icon?: ReactNode;
    active?: boolean;
    children?: NavItem[];
};

export type AppShellProps = {
    children: ReactNode;
    title?: string;
    navItems: NavItem[];
    headerActions?: ReactNode;
    sidebarOpen: boolean;
    onSidebarOpenChange: (open: boolean) => void;
    userMenu?: ReactNode;
    isMobile?: boolean;
};

export function AppShell({
    children,
    title,
    navItems,
    headerActions,
    sidebarOpen,
    onSidebarOpenChange,
    userMenu,
    isMobile = false,
}: AppShellProps) {
    return (
        <YStack flex={1} backgroundColor="$background" minHeight="100%">
            <XStack
                height={56}
                paddingHorizontal={16}
                alignItems="center"
                justifyContent="space-between"
                borderBottomWidth={1}
                borderColor="$borderColor"
                backgroundColor="$card"
            >
                <XStack alignItems="center" gap={12}>
                    {isMobile && (
                        <Button
                            variant="ghost"
                            size="icon"
                            onPress={() => onSidebarOpenChange(true)}
                            aria-label="Open menu"
                        >
                            ☰
                        </Button>
                    )}
                    {title && (
                        <Text fontSize={18} fontWeight="600" color="$color">
                            {title}
                        </Text>
                    )}
                </XStack>
                <XStack alignItems="center" gap={8}>
                    {headerActions}
                    {userMenu}
                </XStack>
            </XStack>

            <XStack flex={1}>
                {!isMobile && (
                    <YStack
                        width={240}
                        borderRightWidth={1}
                        borderColor="$borderColor"
                        backgroundColor="$card"
                        padding={12}
                        gap={4}
                    >
                        <NavList items={navItems} onNavigate={() => {}} />
                    </YStack>
                )}

                <ScrollView flex={1} padding={16}>
                    {children}
                </ScrollView>
            </XStack>

            {isMobile && (
                <KinsenasSheet
                    open={sidebarOpen}
                    onOpenChange={onSidebarOpenChange}
                    title="Menu"
                >
                    <NavList
                        items={navItems}
                        onNavigate={() => onSidebarOpenChange(false)}
                    />
                </KinsenasSheet>
            )}
        </YStack>
    );
}

function NavList({
    items,
    onNavigate,
}: {
    items: NavItem[];
    onNavigate: () => void;
}) {
    return (
        <YStack gap={4}>
            {items.map((item) => (
                <YStack key={item.key} gap={2}>
                    <Button
                        variant={item.active ? 'secondary' : 'ghost'}
                        justifyContent="flex-start"
                        onPress={() => {
                            item.onPress?.();
                            onNavigate();
                        }}
                    >
                        {item.icon}
                        {item.label}
                    </Button>
                    {item.children?.map((child) => (
                        <Button
                            key={child.key}
                            variant={child.active ? 'secondary' : 'ghost'}
                            marginLeft={12}
                            justifyContent="flex-start"
                            onPress={() => {
                                child.onPress?.();
                                onNavigate();
                            }}
                        >
                            {child.label}
                        </Button>
                    ))}
                </YStack>
            ))}
        </YStack>
    );
}
