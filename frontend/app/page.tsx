import { renderSlug, siteViewport, slugMetadata } from "@/lib/render";

// Rendered on each visit; the content itself is cached and refreshed by the admin.
export const revalidate = 0;

export const generateMetadata = () => slugMetadata("home");
export const generateViewport = siteViewport;

export default function HomePage() {
  return renderSlug("home");
}
