import type { ReactNode } from 'react';
import { ScrollView, Text, XStack, YStack } from 'tamagui';
import { Card } from './card';

export type Column<T> = {
    key: string;
    header: string;
    render: (item: T) => ReactNode;
    width?: number | string;
};

export type ResponsiveDataViewProps<T> = {
    data: T[];
    columns: Column<T>[];
    keyExtractor: (item: T) => string;
    emptyMessage?: string;
    isCompact?: boolean;
};

export function ResponsiveDataView<T>({
    data,
    columns,
    keyExtractor,
    emptyMessage = 'No records found.',
    isCompact = false,
}: ResponsiveDataViewProps<T>) {
    if (data.length === 0) {
        return (
            <Text color="$mutedForeground" textAlign="center" padding={24}>
                {emptyMessage}
            </Text>
        );
    }

    if (isCompact) {
        return (
            <YStack gap={12}>
                {data.map((item) => (
                    <Card key={keyExtractor(item)}>
                        <YStack gap={8}>
                            {columns.map((column) => (
                                <XStack
                                    key={column.key}
                                    justifyContent="space-between"
                                    gap={8}
                                >
                                    <Text
                                        fontSize={13}
                                        color="$mutedForeground"
                                        flexShrink={0}
                                    >
                                        {column.header}
                                    </Text>
                                    <YStack flex={1} alignItems="flex-end">
                                        {column.render(item)}
                                    </YStack>
                                </XStack>
                            ))}
                        </YStack>
                    </Card>
                ))}
            </YStack>
        );
    }

    return (
        <ScrollView horizontal showsHorizontalScrollIndicator>
            <YStack minWidth="100%">
                <XStack
                    borderBottomWidth={1}
                    borderColor="$borderColor"
                    paddingVertical={8}
                    paddingHorizontal={12}
                    backgroundColor="$secondary"
                >
                    {columns.map((column) => (
                        <Text
                            key={column.key}
                            fontWeight="600"
                            fontSize={13}
                            width={column.width ?? 140}
                            paddingRight={12}
                        >
                            {column.header}
                        </Text>
                    ))}
                </XStack>
                {data.map((item) => (
                    <XStack
                        key={keyExtractor(item)}
                        borderBottomWidth={1}
                        borderColor="$borderColor"
                        paddingVertical={10}
                        paddingHorizontal={12}
                    >
                        {columns.map((column) => (
                            <YStack
                                key={column.key}
                                width={column.width ?? 140}
                                paddingRight={12}
                            >
                                {column.render(item)}
                            </YStack>
                        ))}
                    </XStack>
                ))}
            </YStack>
        </ScrollView>
    );
}
