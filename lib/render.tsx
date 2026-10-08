import type { Metadata, Viewport } from "next";
import { notFound } from "next/navigation";
import { preload } from "react-dom";
import Template from "@/components/Templates";
import { getPage, getSite, type PageData, type Site } from "./api";
import { helpers, img } from "./site";

/** SEO tags for a page (title, description, canonical, robots, social cards). */
export function pageMetadata(page: PageData, site: Site): Metadata {
  const s = site.settings;
  const seo = page.seo;
  const name = String(s.business_name || "");
  const icon = s.favicon ? img(s.favicon) : undefined;
  return {
    title: { absolute: seo.title },
    description: seo.description || undefined,
    alternates: seo.index ? { canonical: seo.canonical } : undefined,
    robots: { index: seo.index, follow: seo.follow, "max-image-preview": seo.index ? "large" : undefined },
    openGraph: {
      type: "website",
      locale: "en_IN",
      siteName: name,
      title: seo.og_title,
      description: seo.og_description || undefined,
      url: seo.canonical,
      images: seo.og_image ? [seo.og_image] : undefined,
    },
    twitter: {
      card: "summary_large_image",
      title: seo.og_title,
      description: seo.og_description || undefined,
      images: seo.og_image ? [seo.og_image] : undefined,
    },
    icons: { icon: icon ? [{ url: icon, type: "image/png" }] : undefined, apple: s.apple_touch_icon ? img(s.apple_touch_icon) : undefined },
    verification: {
      google: s.google_site_verification || undefined,
      other: s.bing_site_verification ? { "msvalidate.01": String(s.bing_site_verification) } : undefined,
    },
  };
}

export async function siteViewport(): Promise<Viewport> {
  const site = await getSite();
  return { width: "device-width", initialScale: 1, themeColor: String(site.settings.colors?.primary || "#1f5c99") };
}

export async function slugMetadata(slug: string): Promise<Metadata> {
  const [site, page] = await Promise.all([getSite(), getPage(slug)]);
  if (!page) return { title: "Page Not Found", robots: { index: false } };
  return pageMetadata(page, site);
}

/** Renders a page from the admin with its design. */
export async function renderSlug(slug: string, query?: { name?: string; item?: string }) {
  const [site, page] = await Promise.all([getSite(), getPage(slug)]);
  if (!page) notFound();

  // Load the banner photo early (faster first paint, good for Google speed score)
  const c = page.content;
  if (c.hero_image) {
    if (c.hero_image_mobile) {
      preload(img(c.hero_image_mobile), { as: "image", fetchPriority: "high", media: "(max-width: 640px)" });
      preload(img(c.hero_image), { as: "image", fetchPriority: "high", media: "(min-width: 641px)" });
    } else {
      preload(img(c.hero_image), { as: "image", fetchPriority: "high" });
    }
  }

  return <Template h={helpers(site)} page={page} query={query} />;
}
