# DESIGN.md — NovaStore Design System

## 1. Visual Direction & Philosophy
NovaStore combines two complementary design paradigms within a single cohesive product family:
- **Customer Storefront (`website/`) — Editorial & Corporate Commerce**: Inspired by the clean corporate poise, structured grid composition, strong visual hierarchy, and disciplined whitespace of enterprise leaders (Vietnam Post Logistics visual composure, Apple Store clarity, and Stripe checkout trust).
- **Admin Dashboard (`dashboard/`) — Precision Enterprise Operations**: Inspired by Linear, Stripe Dashboard, Vercel, and Shopify Admin. High information density, scannable tabular numerals, calm neutral surfaces, semantic status indicators, and zero decorative bloat.

### Anti-Slop Commitments (Craft Floor)
1. **No AI Decorative Cliches**: No floating blurry background orbs (`blur-[120px]`), no purple-to-blue gradients, no glassmorphism blanket containers, and no emoji icons (`📝`, `💵`, `📥`, `🚨`) in business interfaces.
2. **No Side-Tab or Top-Tab Card Borders**: Never put `border-left: 4px solid` or `border-top: 4px solid` on rounded cards. Communicate status using semantic pills, subtle 1px borders, or status dots.
3. **No Card-in-Card Nesting**: Use 1px dividers (`border-slate-200`), subtle surface tinting (`bg-slate-50`), or spacing to group inner content inside a card.
4. **Numeric Discipline**: Every price, SKU count, KPI metric, order total, and timestamp uses `font-variant-numeric: tabular-nums`.
5. **Restrained Radii**: Inputs, buttons, and selects use `rounded-lg` (`8px`). Cards and panels use `rounded-xl` (`12px`). Badges use `rounded-md` (`6px`) or `rounded-full` for compact status pills.

---

## 2. Design Tokens

### Color Palette
| Token | Hex | Role |
| :--- | :--- | :--- |
| `--color-brand-navy` | `#004687` | Primary brand interactive color, active controls, primary CTA |
| `--color-brand-dark` | `#09253E` | Deep executive navy for high-contrast headers, hero anchors, and dark panels |
| `--color-brand-gold` | `#FBAE17` | Warm amber-gold accent for high-priority highlights and secondary interactive accents |
| `--color-brand-gold-soft` | `#FFFbeb` | Warm tint background for gold accents |
| `--color-surface-canvas` | `#F8FAFC` | Primary page canvas background (cool alabaster slate) |
| `--color-surface-card` | `#FFFFFF` | Elevated card, table, and modal surface |
| `--color-surface-muted` | `#F1F5F9` | Secondary container, table header, and hover surface |
| `--color-border-subtle` | `#E2E8F0` | Default 1px structural hairline border |
| `--color-border-strong` | `#CBD5E1` | Interactive control and input border |
| `--color-text-primary` | `#0F172A` | Primary ink for headings, body copy, and data values |
| `--color-text-secondary` | `#475569` | Supporting descriptions, table cells, and secondary labels |
| `--color-text-muted` | `#64748B` | Captions, metadata, timestamps, and placeholders |
| `--color-status-success` | `#059669` | Completed, paid, in-stock, healthy SLA (`#ECFDF5` bg / `#A7F3D0` border) |
| `--color-status-warning` | `#D97706` | Pending, processing, low stock, warning (`#FFFBEB` bg / `#FDE68A` border) |
| `--color-status-danger` | `#DC2626` | Cancelled, failed, out of stock, SLA breach (`#FEF2F2` bg / `#FECACA` border) |
| `--color-status-info` | `#0284C7` | Delivering, informational, active workflow (`#F0F9FF` bg / `#BAE6FD` border) |

### Typography & Scale
- **Primary Font Stack**: `'Be Vietnam Pro', 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif`
- **Monospace Stack**: `'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace`
- **Type Scale**:
  - **Display / Hero**: `clamp(1.875rem, 3.2vw, 2.75rem)` (`30px–44px`), weight `700`, tracking `-0.025em`, line-height `1.15`
  - **Page Title (H1)**: `1.5rem–1.875rem` (`24px–30px`), weight `700`, tracking `-0.02em`, line-height `1.25`
  - **Section Heading (H2)**: `1.125rem–1.375rem` (`18px–22px`), weight `600`, tracking `-0.015em`, line-height `1.35`
  - **Card Title (H3)**: `0.9375rem–1rem` (`15px–16px`), weight `600`, line-height `1.4`
  - **Body Default**: `0.875rem–0.9375rem` (`14px–15px`), weight `400`, line-height `1.6`, max measure `65ch`
  - **Compact UI / Table Cell**: `0.8125rem` (`13px`), weight `400–500`, line-height `1.45`
  - **Micro / Table Header**: `0.75rem` (`12px`), weight `600`, uppercase tracking `0.04em`

### Spacing, Borders & Elevation
- **Base Grid Unit**: `4px` (`0.25rem`). Section padding `48px–64px` on storefront, `20px–24px` on admin dashboard.
- **Border Radius**:
  - Controls (Buttons, Inputs, Selects): `8px` (`rounded-lg`)
  - Cards, Modals, Tables: `12px` (`rounded-xl`)
  - Badges & Tags: `6px` (`rounded-md`) or `9999px` (`rounded-full`)
- **Shadows**:
  - `shadow-card`: `0 1px 2px 0 rgba(15, 23, 42, 0.04)`
  - `shadow-elevated`: `0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 4px 10px -6px rgba(15, 23, 42, 0.04)`

### Motion & Micro-Interactions
- **Easing**: `cubic-bezier(0.16, 1, 0.3, 1)` (exponential out) for reveals and drawers; `cubic-bezier(0.4, 0, 0.2, 1)` for hover/color transitions.
- **Durations**: `150ms` for micro-interactions (buttons, links, inputs); `220ms–300ms` for drawers, modals, and section transitions.
- **Accessibility**: Full `@media (prefers-reduced-motion: reduce)` support disabling transforms and animations when requested by the OS.
