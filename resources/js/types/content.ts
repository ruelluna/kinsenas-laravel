export type ContentSeriesSummary = {
    id: string;
    title: string;
    slug: string;
    description: string | null;
    coverImageUrl: string | null;
    status: string;
    publishedAt: string | null;
};

export type ContentPostSummary = {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    body?: string;
    contentType: string;
    contentTypeLabel: string;
    publishScope: string;
    status: string;
    coverImageUrl: string | null;
    videoEmbedUrl: string | null;
    readingTimeMinutes: number;
    publishedAt: string | null;
    series: ContentSeriesSummary | null;
    episodeNumber: number | null;
    authorName: string;
};

export type ContentPostAdmin = ContentPostSummary & {
    contentSeriesId: string | null;
    authorId: string | null;
};

export type ContentSeriesAdmin = ContentSeriesSummary & {
    sortOrder: number;
    postsCount: number;
};

export type PaginatedPosts<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
