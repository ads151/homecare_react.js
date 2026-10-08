import type { MetadataRoute } from "next";
import { getSitemap, getSite } from "@/lib/api";

export const revalidate = 0;

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [site, pages] = await Promise.all([getSite(), getSitemap()]);
  const base = String(site.links.site_url || "").replace(/\/+$/, "");
  return pages.map((p) => ({
    url: p.slug === "home" ? `${base}/` : `${base}/${p.slug}`,
    lastModified: p.updated_at ? new Date(p.updated_at) : undefined,
    priority: p.slug === "home" ? 1 : 0.8,
  }));
}
