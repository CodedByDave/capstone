import type { VariantProps } from "class-variance-authority"
import { cva } from "class-variance-authority"

export { default as Button } from "./Button.vue"

export const buttonVariants = cva(
  "inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 shrink-0 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
  {
    variants: {
      variant: {
        default:
          "bg-primary text-primary-foreground hover:bg-primary/90",
        destructive:
          "bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:focus-visible:ring-destructive/40 dark:bg-destructive/60",
        // Approve, accept, restore, enable.
        success:
          "bg-emerald-700 text-white hover:bg-emerald-800 focus-visible:ring-emerald-600/30",
        // Reversible take-downs: archive, disable, suspend.
        warning:
          "bg-amber-700 text-white hover:bg-amber-800 focus-visible:ring-amber-600/30",
        "destructive-outline":
          "border border-red-300 bg-background text-red-700 shadow-xs hover:bg-red-50 hover:text-red-800 dark:border-red-800 dark:bg-transparent dark:text-red-300 dark:hover:bg-red-900/40",
        "success-outline":
          "border border-emerald-300 bg-background text-emerald-700 shadow-xs hover:bg-emerald-50 hover:text-emerald-800 dark:border-emerald-800 dark:bg-transparent dark:text-emerald-300 dark:hover:bg-emerald-900/40",
        "warning-outline":
          "border border-amber-300 bg-background text-amber-800 shadow-xs hover:bg-amber-50 hover:text-amber-900 dark:border-amber-800 dark:bg-transparent dark:text-amber-300 dark:hover:bg-amber-900/40",
        // Secondary actions next to a primary one: Import, Manage, Settings, a second Create.
        "primary-outline":
          "border border-blue-300 bg-background text-blue-700 shadow-xs hover:bg-blue-50 hover:text-blue-800 dark:border-blue-800 dark:bg-transparent dark:text-blue-300 dark:hover:bg-blue-900/40",
        // Navigation back to a previous page.
        back:
          "border border-slate-300 bg-background text-slate-700 shadow-xs hover:bg-slate-100 hover:text-slate-900 dark:border-slate-600 dark:bg-transparent dark:text-slate-300 dark:hover:bg-slate-800",
        outline:
          "border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50",
        secondary:
          "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        ghost:
          "hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        "default": "h-9 px-4 py-2 has-[>svg]:px-3",
        "sm": "h-8 rounded-md gap-1.5 px-3 has-[>svg]:px-2.5",
        "lg": "h-10 rounded-md px-6 has-[>svg]:px-4",
        "icon": "size-9",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)
export type ButtonVariants = VariantProps<typeof buttonVariants>
