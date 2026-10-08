import { permanentRedirect } from "next/navigation";
import { renderSlug, siteViewport, slugMetadata } from "@/lib/render";

export const revalidate = 0;

export async function generateMetadata({ params }: PageProps<"/[slug]">) {
  const { slug } = await params;
  return slugMetadata(slug);
}

export const generateViewport = siteViewport;

export default async function SlugPage({ params, searchParams }: PageProps<"/[slug]">) {
  const { slug } = await params;
  if (slug === "home" || slug === "index") permanentRedirect("/");
  const q = slug === "thank-you" ? await searchParams : {};
  const one = (v: unknown) => (Array.isArray(v) ? String(v[0] ?? "") : String(v ?? ""));
  return renderSlug(slug, { name: one(q.name), item: one(q.item) });
}
