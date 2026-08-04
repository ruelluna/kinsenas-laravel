import { router } from '@inertiajs/react';
import {
    createContext,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';

const OVERLAY_DELAY_MS = 150;

type InertiaVisit = {
    url: URL | string;
    method: string;
    only?: string[];
    except?: string[];
};

type NavigationLoadingContextValue = {
    isNavigating: boolean;
    pendingUrl: string | null;
};

const NavigationLoadingContext =
    createContext<NavigationLoadingContextValue | null>(null);

function visitPathname(url: URL | string): string {
    if (typeof url === 'string') {
        try {
            return new URL(url, window.location.origin).pathname;
        } catch {
            return url.split('?')[0] ?? url;
        }
    }

    return url.pathname;
}

function shouldTrackVisit(visit: InertiaVisit): boolean {
    if (visit.method.toLowerCase() !== 'get') {
        return false;
    }

    if (visit.only?.length || visit.except?.length) {
        return false;
    }

    return true;
}

export function NavigationLoadingProvider({ children }: { children: ReactNode }) {
    const [isNavigating, setIsNavigating] = useState(false);
    const [pendingUrl, setPendingUrl] = useState<string | null>(null);
    const overlayTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        const clearOverlayTimeout = () => {
            if (overlayTimeoutRef.current !== null) {
                clearTimeout(overlayTimeoutRef.current);
                overlayTimeoutRef.current = null;
            }
        };

        const resetNavigationState = () => {
            clearOverlayTimeout();
            setIsNavigating(false);
            setPendingUrl(null);
        };

        const removeStartListener = router.on('start', (event) => {
            const visit = (event as CustomEvent).detail?.visit as
                | InertiaVisit
                | undefined;

            if (!visit || !shouldTrackVisit(visit)) {
                return;
            }

            clearOverlayTimeout();
            setIsNavigating(false);
            setPendingUrl(visitPathname(visit.url));

            overlayTimeoutRef.current = setTimeout(() => {
                setIsNavigating(true);
            }, OVERLAY_DELAY_MS);
        });

        const removeFinishListener = router.on('finish', () => {
            resetNavigationState();
        });

        return () => {
            clearOverlayTimeout();
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    const value = useMemo(
        () => ({
            isNavigating,
            pendingUrl,
        }),
        [isNavigating, pendingUrl],
    );

    return (
        <NavigationLoadingContext.Provider value={value}>
            {children}
        </NavigationLoadingContext.Provider>
    );
}

export function useNavigationLoading(): NavigationLoadingContextValue {
    const context = useContext(NavigationLoadingContext);

    if (!context) {
        throw new Error(
            'useNavigationLoading must be used within NavigationLoadingProvider',
        );
    }

    return context;
}
