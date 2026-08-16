import type { LucideIcon } from 'lucide-react';
import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState
    
} from 'react';
import type {ReactNode} from 'react';

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
    moreSheetOpen: boolean;
    setMoreSheetOpen: (open: boolean) => void;
};

const MobileNavContext = createContext<MobileNavContextValue | null>(null);

export function MobileNavProvider({ children }: { children: ReactNode }) {
    const [action, setActionState] = useState<MobileNavAction | null>(null);
    const [moreSheetOpen, setMoreSheetOpenState] = useState(false);

    const setAction = useCallback((next: MobileNavAction | null) => {
        setActionState(next);
    }, []);

    const setMoreSheetOpen = useCallback((open: boolean) => {
        setMoreSheetOpenState(open);
    }, []);

    const value = useMemo(
        () => ({
            action,
            setAction,
            moreSheetOpen,
            setMoreSheetOpen,
        }),
        [action, setAction, moreSheetOpen, setMoreSheetOpen],
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
