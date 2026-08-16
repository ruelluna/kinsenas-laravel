import { Head } from '@inertiajs/react';

export type LearnOpenGraph = {
    title: string;
    description: string;
    url: string;
    image: string | null;
};

type Props = {
    title: string;
    description?: string | null;
    openGraph?: LearnOpenGraph | null;
    ogType?: string;
};

/** Inertia Head rejects fragments and falsy nodes nested in children — keep meta tags flat. */
export default function LearnPageHead({
    title,
    description = null,
    openGraph = null,
    ogType = 'article',
}: Props) {
    return (
        <Head title={title}>
            {description ? (
                <meta head-key="description" name="description" content={description} />
            ) : null}
            {openGraph ? (
                <meta head-key="og:title" property="og:title" content={openGraph.title} />
            ) : null}
            {openGraph ? (
                <meta
                    head-key="og:description"
                    property="og:description"
                    content={openGraph.description}
                />
            ) : null}
            {openGraph ? (
                <meta head-key="og:url" property="og:url" content={openGraph.url} />
            ) : null}
            {openGraph && ogType ? (
                <meta head-key="og:type" property="og:type" content={ogType} />
            ) : null}
            {openGraph?.image ? (
                <meta head-key="og:image" property="og:image" content={openGraph.image} />
            ) : null}
        </Head>
    );
}
