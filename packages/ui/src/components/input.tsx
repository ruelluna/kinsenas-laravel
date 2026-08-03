import type { ReactNode } from 'react';
import { Input as TamaguiInput, styled } from 'tamagui';

const StyledInput = styled(TamaguiInput, {
    name: 'KinsenasInput',
    height: 36,
    borderWidth: 1,
    borderColor: '$borderColor',
    borderRadius: '$md',
    backgroundColor: '$background',
    color: '$color',
    paddingHorizontal: 12,
    fontSize: 14,
    focusStyle: {
        borderColor: '$ring',
        outlineWidth: 0,
    },
});

export type InputProps = {
    value?: string;
    onChangeText?: (text: string) => void;
    placeholder?: string;
    secureTextEntry?: boolean;
    autoCapitalize?: 'none' | 'sentences' | 'words' | 'characters';
    keyboardType?: 'default' | 'email-address' | 'numeric';
    editable?: boolean;
    id?: string;
    className?: string;
};

export function Input({
    value,
    onChangeText,
    placeholder,
    secureTextEntry,
    autoCapitalize,
    keyboardType,
    editable = true,
}: InputProps): ReactNode {
    return (
        <StyledInput
            value={value}
            onChangeText={onChangeText}
            placeholder={placeholder}
            secureTextEntry={secureTextEntry}
            autoCapitalize={autoCapitalize}
            keyboardType={keyboardType}
            editable={editable}
        />
    );
}
