import type { LucideIcon } from 'lucide-react';
import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
    type ReactNode,
} from 'react';

export type MobileNavAction = {
    label: string;
    ariaLabel: string;
    icon: LucideIcon;
    onClick: () => void;
    disabled?: boolean;
};

type MobileNavContextValue = {
    action: MobileNavAction | null;
    setAction: (action: MobileNavAction | null) => void;
};

const MobileNavContext = createContext<MobileNavContextValue | null>(null);

export function MobileNavProvider({ children }: { children: ReactNode }) {
    const [action, setActionState] = useState<MobileNavAction | null>(null);

    const setAction = useCallback((next: MobileNavAction | null) => {
        setActionState(next);
    }, []);

    const value = useMemo(
        () => ({
            action,
            setAction,
        }),
        [action, setAction],
    );

    return (
        <MobileNavContext.Provider value={value}>
            {children}
        </MobileNavContext.Provider>
    );
}

export function useMobileNav(): MobileNavContextValue {
    const context = useContext(MobileNavContext);

    if (!context) {
        throw new Error('useMobileNav must be used within MobileNavProvider');
    }

    return context;
}
