import type { Driver } from 'driver.js';

let activeDriver: Driver | null = null;
let suppressCompleteOnDestroy = false;

export function getActiveDriver(): Driver | null {
    return activeDriver;
}

export function setActiveDriver(driver: Driver | null): void {
    activeDriver = driver;
}

export function isSuppressCompleteOnDestroy(): boolean {
    return suppressCompleteOnDestroy;
}

export function destroyActiveDriver(options?: {
    suppressComplete?: boolean;
}): void {
    if (!activeDriver) {
        return;
    }

    suppressCompleteOnDestroy = options?.suppressComplete ?? false;
    activeDriver.destroy();
    activeDriver = null;
    suppressCompleteOnDestroy = false;
}

export function isOnboardingTourRunning(): boolean {
    return activeDriver?.isActive() ?? false;
}
