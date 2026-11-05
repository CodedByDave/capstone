/**
 * An overnight shift ends at or before the time it starts, so it finishes the next day.
 * Times are "HH:mm" (seconds are ignored).
 */
export function endsNextDay(
    start: string | null | undefined,
    end: string | null | undefined,
): boolean {
    if (!start || !end) return false;

    return end.slice(0, 5) <= start.slice(0, 5);
}

/** A shift's start and end are the same time, which is not a valid shift. */
export function isZeroLengthShift(start: string, end: string): boolean {
    return start.slice(0, 5) === end.slice(0, 5);
}
