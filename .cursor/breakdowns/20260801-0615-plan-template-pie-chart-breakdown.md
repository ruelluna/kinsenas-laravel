# Savings plan template pie chart

**Date:** 2026-08-01

## Summary

Replaced the horizontal percentage bar on the savings plan template chooser with an interactive SVG pie chart. Hovering a slice shows a tooltip with the fund name, percentage, and description. A compact color legend lists fund names beside the chart.

## Changelog

- Template picker cards now show a pie chart of category allocations instead of a thin bar
- Hover or focus a slice to see fund name, percentage, and description in a tooltip
- Legend beside the chart shows color swatch, percentage, and fund name
- Hint text: "Hover a slice to read each fund's description"

## Files touched

### Savings UI

- `resources/js/components/savings/template-allocation-pie-chart.tsx` (new)
- `resources/js/components/savings/plan-template-picker.tsx`

## Deploy steps

```bash
npm run dev
# or
npm run build
```

No backend or migration changes.

## Suggested tests (run manually)

No new automated tests — presentational UI only.

```bash
npm run build
npm run types:check
```

## Visual QA (manual)

**URL:** http://financial-literacy.test  
**Prereqs:** `npm run dev`

### Happy path

1. Log in and open **Savings Plan** (before a plan is chosen, or reset to chooser state)
2. Each formula card shows a pie chart with colored slices
3. Hover a slice — tooltip shows fund name, percentage, and description
4. Legend beside chart matches slice colors
5. Click **Use this formula** — plan still creates successfully

### Checks

- [ ] No console errors
- [ ] Tooltips readable in light and dark mode
- [ ] Single-category templates render as a full circle
- [ ] Mobile: chart and legend stack vertically

### Regression

- [ ] Video embed and **Use this formula** button still work
- [ ] Chooser intro and before-choose alert unchanged

## Suggested application commit

```
Summary: Add pie chart with tooltips to savings plan template chooser

Replace the percentage bar with an SVG pie chart so members can hover slices
to read each fund's description while comparing formulas.
```

## Linear paste block

```
Title: Add pie chart to savings plan template chooser

Description:
The savings plan formula picker now uses an interactive pie chart instead of a
horizontal bar. Hovering a slice shows the fund name, percentage, and description
in a tooltip; a compact legend lists allocations beside the chart.

Comment / instructions:
Run npm run dev after pull. Visual QA: Savings Plan chooser → hover slices →
confirm tooltips and Use this formula still works. No migrations.

Documentation:
N/A
```
