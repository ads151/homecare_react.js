import type { MetadataRoute } from "next";
import { getSite } from "@/lib/api";

export const revalidate = 0;

export default async function robots(): Promise<MetadataRoute.Robots> {
  const site = await getSite();
  const base = String(site.links.site_url || "").replace(/\/+$/, "");
  return {
    rules: { userAgent: "*", allow: "/", disallow: ["/api/", "/thank-you"] },
    sitemap: `${base}/sitemap.xml`,
  };
}
