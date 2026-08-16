export function isHtmlContent(content: string | null | undefined): boolean {
    if (!content) {
        return false;
    }

    const trimmed = content.trim();

    return /^<[a-z][\s\S]*>/i.test(trimmed);
}
