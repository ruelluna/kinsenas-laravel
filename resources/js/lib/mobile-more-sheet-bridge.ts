type MobileMoreSheetController = {
    open: () => void;
    close: () => void;
};

let moreSheetController: MobileMoreSheetController | null = null;

export function registerMobileMoreSheetController(
    controller: MobileMoreSheetController,
): () => void {
    moreSheetController = controller;

    return () => {
        if (moreSheetController === controller) {
            moreSheetController = null;
        }
    };
}

export function openMobileMoreSheet(): void {
    moreSheetController?.open();
}

export function closeMobileMoreSheet(): void {
    moreSheetController?.close();
}
