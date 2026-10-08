import { renderSlug, siteViewport, slugMetadata } from "@/lib/render";

/* Pages are built on first visit and then served from cache (very fast).
   The admin refreshes the cache after every save; time limit as a safety net. */
export const revalidate = 3600;
export const dynamicParams = true;

export async function generateStaticParams() {
  return []; // nothing at build time (the admin may not be reachable then)
}

export async function generateMetadata({ params }: PageProps<"/[slug]">) {
  const { slug } = await params;
  return slugMetadata(slug);
}

export const generateViewport = siteViewport;

export default async function SlugPage({ params }: PageProps<"/[slug]">) {
  const { slug } = await params;
  return renderSlug(slug);
}
