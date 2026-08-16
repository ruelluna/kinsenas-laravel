export type SideHustleCategorySummary = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    status: string;
    sortOrder: number;
};

export type SideHustleCategoryAdmin = SideHustleCategorySummary & {
    sideHustlesCount: number;
};

export type SideHustleSummary = {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    body?: string;
    coverImageUrl: string | null;
    difficulty: string;
    difficultyLabel: string;
    capitalTier: string;
    capitalTierLabel: string;
    startupCapitalMin: number | null;
    startupCapitalMax: number | null;
    timeCommitmentHoursMin: number | null;
    timeCommitmentHoursMax: number | null;
    skills: string[];
    equipment: string[];
    publishScope: string;
    status: string;
    publishedAt: string | null;
    sortOrder: number;
    postAs: string | null;
    bylineName: string | null;
    category: SideHustleCategorySummary | null;
};

export type SideHustleAdmin = SideHustleSummary & {
    sideHustleCategoryId: string;
};

export type PodcastShowSummary = {
    id: string;
    title: string;
    slug: string;
    description: string | null;
    coverImageUrl: string | null;
    status: string;
    publishedAt: string | null;
    sortOrder: number;
};

export type PodcastShowAdmin = PodcastShowSummary & {
    episodesCount: number;
};

export type PodcastEpisodeSummary = {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    showNotes?: string | null;
    audioEmbedUrl: string | null;
    durationMinutes: number | null;
    episodeNumber: number;
    publishScope: string;
    status: string;
    publishedAt: string | null;
    postAs: string | null;
    bylineName: string | null;
    show: PodcastShowSummary | null;
};

export type PodcastEpisodeAdmin = PodcastEpisodeSummary & {
    podcastShowId: string;
};

export type PaginatedLibrary<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
};
