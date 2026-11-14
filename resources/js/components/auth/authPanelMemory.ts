// Remembers which auth screen the visitor just left so the next one can start its
// blue panel where the last one ended and slide it into place.
export type AuthScreen = 'login' | 'register' | 'shop' | 'otp';
export type PanelSide = 'left' | 'right';

export const panelSideOf: Record<AuthScreen, PanelSide> = {
    login: 'right',
    register: 'left',
    shop: 'left',
    otp: 'right',
};

const FRESH_MS = 4000;
let last: { screen: AuthScreen; side: PanelSide; at: number } | null = null;

/** The screen the visitor came from, and where its panel ended up, if that was a moment ago. */
export function takePreviousScreen(): {
    screen: AuthScreen;
    side: PanelSide;
} | null {
    const previous = last && Date.now() - last.at < FRESH_MS ? last : null;
    last = null;
    return previous && { screen: previous.screen, side: previous.side };
}

export function rememberScreen(
    screen: AuthScreen,
    side: PanelSide = panelSideOf[screen],
): void {
    last = { screen, side, at: Date.now() };
}
