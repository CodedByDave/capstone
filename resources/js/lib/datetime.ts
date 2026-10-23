/**
 * Formats a date for an `<input type="datetime-local">`. That input holds the user's local wall
 * clock, so this must not use `toISOString()`, which is the UTC clock and shifts the time by the
 * timezone offset.
 */
export function toLocalInputValue(date: Date): string {
    const pad = (value: number) => String(value).padStart(2, '0');

    return (
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}` +
        `T${pad(date.getHours())}:${pad(date.getMinutes())}`
    );
}
