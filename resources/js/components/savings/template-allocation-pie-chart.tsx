import { useState } from 'react';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import type { FormulaTemplateCategory } from '@/types/savings';

const sliceColors = [
    'fill-allocation-1',
    'fill-allocation-2',
    'fill-allocation-3',
    'fill-allocation-4',
    'fill-allocation-5',
    'fill-allocation-6',
    'fill-allocation-7',
] as const;

const legendColors = [
    'bg-allocation-1',
    'bg-allocation-2',
    'bg-allocation-3',
    'bg-allocation-4',
    'bg-allocation-5',
    'bg-allocation-6',
    'bg-allocation-7',
] as const;

const EXPLODE_DISTANCE = 22;
const ACTIVE_SCALE = 1.15;
const SLICE_TRANSITION =
    'transform 350ms cubic-bezier(0.34, 1.25, 0.64, 1), opacity 300ms ease';

type Slice = FormulaTemplateCategory & {
    value: number;
    index: number;
    startAngle: number;
    endAngle: number;
};

function polarToCartesian(
    centerX: number,
    centerY: number,
    radius: number,
    angleDegrees: number,
): { x: number; y: number } {
    const angleRadians = ((angleDegrees - 90) * Math.PI) / 180;

    return {
        x: centerX + radius * Math.cos(angleRadians),
        y: centerY + radius * Math.sin(angleRadians),
    };
}

function describeSlice(
    centerX: number,
    centerY: number,
    radius: number,
    startAngle: number,
    endAngle: number,
): string {
    const start = polarToCartesian(centerX, centerY, radius, endAngle);
    const end = polarToCartesian(centerX, centerY, radius, startAngle);
    const largeArcFlag = endAngle - startAngle > 180 ? 1 : 0;

    return [
        `M ${centerX} ${centerY}`,
        `L ${start.x} ${start.y}`,
        `A ${radius} ${radius} 0 ${largeArcFlag} 0 ${end.x} ${end.y}`,
        'Z',
    ].join(' ');
}

function explodeOffset(
    startAngle: number,
    endAngle: number,
    distance: number,
): { x: number; y: number } {
    if (distance === 0) {
        return { x: 0, y: 0 };
    }

    const midAngle = (startAngle + endAngle) / 2;
    const radians = ((midAngle - 90) * Math.PI) / 180;

    return {
        x: distance * Math.cos(radians),
        y: distance * Math.sin(radians),
    };
}

function buildSlices(categories: FormulaTemplateCategory[]): Slice[] {
    const parsed = categories
        .map((category, index) => ({
            ...category,
            value: parseFloat(category.percentage),
            index,
        }))
        .filter(
            (category) => Number.isFinite(category.value) && category.value > 0,
        );

    let currentAngle = 0;

    return parsed.map((category) => {
        const sweep = (category.value / 100) * 360;
        const slice: Slice = {
            ...category,
            startAngle: currentAngle,
            endAngle: currentAngle + sweep,
        };

        currentAngle += sweep;

        return slice;
    });
}

type Props = {
    categories: FormulaTemplateCategory[];
    className?: string;
};

export default function TemplateAllocationPieChart({
    categories,
    className,
}: Props) {
    const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);
    const slices = buildSlices(categories);
    const size = 256;
    const center = size / 2;
    const radius = center - 6;

    if (slices.length === 0) {
        return (
            <div
                className={cn(
                    'flex size-64 items-center justify-center rounded-full bg-muted text-sm text-muted-foreground',
                    className,
                )}
            >
                No allocations
            </div>
        );
    }

    function sliceTransform(slice: Slice, isActive: boolean): string {
        const { x, y } = explodeOffset(
            slice.startAngle,
            slice.endAngle,
            isActive ? EXPLODE_DISTANCE : 0,
        );
        const scale = isActive ? ACTIVE_SCALE : 1;

        return `translate(${x.toFixed(2)} ${y.toFixed(2)}) scale(${scale})`;
    }

    return (
        <div
            className={cn(
                'flex flex-col items-center gap-4 sm:flex-row sm:items-center',
                className,
            )}
        >
            <svg
                viewBox={`0 0 ${size} ${size}`}
                className="size-64 shrink-0"
                role="img"
                aria-label="Savings plan allocation chart"
                onMouseLeave={() => setHoveredIndex(null)}
            >
                <g transform={`translate(${center} ${center})`}>
                    {slices.length === 1 && slices[0].value >= 99.99 ? (
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <g
                                    style={{
                                        transform:
                                            hoveredIndex === slices[0].index
                                                ? `scale(${ACTIVE_SCALE})`
                                                : 'scale(1)',
                                        transition: SLICE_TRANSITION,
                                    }}
                                    onMouseEnter={() =>
                                        setHoveredIndex(slices[0].index)
                                    }
                                    onMouseLeave={() => setHoveredIndex(null)}
                                    className="cursor-pointer"
                                >
                                    <circle
                                        cx={0}
                                        cy={0}
                                        r={radius}
                                        className={cn(
                                            sliceColors[
                                                slices[0].index %
                                                    sliceColors.length
                                            ],
                                            'transition-[opacity]',
                                            hoveredIndex !== null &&
                                                hoveredIndex !==
                                                    slices[0].index &&
                                                'opacity-50',
                                        )}
                                        tabIndex={0}
                                        aria-label={`${slices[0].name}, ${slices[0].percentage}%`}
                                    />
                                </g>
                            </TooltipTrigger>
                            <TooltipContent
                                side="top"
                                className="max-w-xs text-left"
                            >
                                <p className="font-medium">
                                    {slices[0].name} — {slices[0].percentage}%
                                </p>
                                {slices[0].description && (
                                    <p className="mt-1 text-primary-foreground/90">
                                        {slices[0].description}
                                    </p>
                                )}
                            </TooltipContent>
                        </Tooltip>
                    ) : (
                        slices.map((slice) => {
                            const isActive = hoveredIndex === slice.index;

                            return (
                                <Tooltip key={`${slice.name}-${slice.index}`}>
                                    <TooltipTrigger asChild>
                                        <g
                                            style={{
                                                transform: sliceTransform(
                                                    slice,
                                                    isActive,
                                                ),
                                                transition: SLICE_TRANSITION,
                                            }}
                                            onMouseEnter={() =>
                                                setHoveredIndex(slice.index)
                                            }
                                            onMouseLeave={() =>
                                                setHoveredIndex(null)
                                            }
                                            className="cursor-pointer"
                                        >
                                            <path
                                                d={describeSlice(
                                                    0,
                                                    0,
                                                    radius,
                                                    slice.startAngle,
                                                    slice.endAngle,
                                                )}
                                                className={cn(
                                                    sliceColors[
                                                        slice.index %
                                                            sliceColors.length
                                                    ],
                                                    'transition-[opacity]',
                                                    hoveredIndex !== null &&
                                                        !isActive &&
                                                        'opacity-45',
                                                )}
                                                tabIndex={0}
                                                aria-label={`${slice.name}, ${slice.percentage}%`}
                                            />
                                        </g>
                                    </TooltipTrigger>
                                    <TooltipContent
                                        side="top"
                                        className="max-w-xs text-left"
                                    >
                                        <p className="font-medium">
                                            {slice.name} — {slice.percentage}%
                                        </p>
                                        {slice.description ? (
                                            <p className="mt-1 text-primary-foreground/90">
                                                {slice.description}
                                            </p>
                                        ) : (
                                            <p className="mt-1 text-primary-foreground/70">
                                                No description provided.
                                            </p>
                                        )}
                                    </TooltipContent>
                                </Tooltip>
                            );
                        })
                    )}
                </g>
            </svg>

            <ul className="grid w-full gap-2.5 text-base sm:min-w-0 sm:flex-1">
                {slices.map((slice) => {
                    const isActive = hoveredIndex === slice.index;

                    return (
                        <li
                            key={`${slice.name}-${slice.index}`}
                            className={cn(
                                'flex cursor-default items-center gap-2.5 rounded-md px-1 py-0.5 transition-colors',
                                isActive && 'bg-muted',
                            )}
                            onMouseEnter={() => setHoveredIndex(slice.index)}
                            onMouseLeave={() => setHoveredIndex(null)}
                        >
                            <span
                                className={cn(
                                    'size-3 shrink-0 rounded-full transition-transform duration-350 ease-out',
                                    legendColors[
                                        slice.index % legendColors.length
                                    ],
                                    isActive && 'scale-150',
                                )}
                                aria-hidden
                            />
                            <span
                                className={cn(
                                    'font-medium tabular-nums',
                                    isActive && 'text-foreground',
                                )}
                            >
                                {slice.percentage}%
                            </span>
                            <span
                                className={cn(
                                    'truncate text-muted-foreground',
                                    isActive && 'font-medium text-foreground',
                                )}
                            >
                                {slice.name}
                            </span>
                        </li>
                    );
                })}
            </ul>
        </div>
    );
}
