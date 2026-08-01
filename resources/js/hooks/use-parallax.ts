import { useEffect, useState, type RefObject } from 'react';

const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

export function usePrefersReducedMotion(): boolean {
    const [prefersReducedMotion, setPrefersReducedMotion] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia(REDUCED_MOTION_QUERY);
        const update = (): void => {
            setPrefersReducedMotion(mediaQuery.matches);
        };

        update();
        mediaQuery.addEventListener('change', update);

        return () => mediaQuery.removeEventListener('change', update);
    }, []);

    return prefersReducedMotion;
}

export function useParallaxOffset(
    sectionRef: RefObject<HTMLElement | null>,
    speed = 0.35,
): number {
    const prefersReducedMotion = usePrefersReducedMotion();
    const [offset, setOffset] = useState(0);

    useEffect(() => {
        if (prefersReducedMotion) {
            setOffset(0);

            return;
        }

        const element = sectionRef.current;

        if (!element) {
            return;
        }

        let frame = 0;

        const updateOffset = (): void => {
            const rect = element.getBoundingClientRect();
            const sectionTop = rect.top + window.scrollY;
            const distance = window.scrollY - sectionTop;

            setOffset(distance * speed);
        };

        const onScroll = (): void => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(updateOffset);
        };

        updateOffset();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll, { passive: true });

        return () => {
            cancelAnimationFrame(frame);
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
        };
    }, [prefersReducedMotion, sectionRef, speed]);

    return offset;
}
