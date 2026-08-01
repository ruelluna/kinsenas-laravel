import { useRef } from 'react';
import { useParallaxOffset } from '@/hooks/use-parallax';
import { KINSENAS_HERO_ILLUSTRATION } from '@/lib/brand';

export default function HeroParallaxBackground() {
    const sectionRef = useRef<HTMLDivElement>(null);
    const offset = useParallaxOffset(sectionRef, 0.28);

    return (
        <div
            ref={sectionRef}
            className="pointer-events-none absolute inset-0 overflow-hidden"
            aria-hidden
        >
            <div
                className="absolute inset-0 scale-105 will-change-transform motion-reduce:transform-none"
                style={{ transform: `translate3d(0, ${offset}px, 0)` }}
            >
                <img
                    src={KINSENAS_HERO_ILLUSTRATION}
                    alt=""
                    className="size-full object-cover object-[75%_center] lg:object-[88%_42%]"
                    fetchPriority="high"
                    decoding="async"
                />
            </div>

            <div className="absolute inset-0 bg-linear-to-r from-black/75 from-40% via-black/40 via-58% to-black/5 lg:from-black/70 lg:from-45% lg:to-transparent" />
            <div className="absolute inset-0 bg-linear-to-t from-black/50 via-transparent to-black/20" />
        </div>
    );
}
