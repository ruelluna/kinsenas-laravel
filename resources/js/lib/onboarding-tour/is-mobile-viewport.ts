const MOBILE_BREAKPOINT = 768;

export function isMobileViewport(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT - 1}px)`).matches;
}

export function getTourResumeDelayMs(): number {
    return isMobileViewport() ? 500 : 350;
}
