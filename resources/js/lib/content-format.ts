export function isHtmlContent(content: string): boolean {
    const trimmed = content.trim();

    return /^<[a-z][\s\S]*>/i.test(trimmed);
}
