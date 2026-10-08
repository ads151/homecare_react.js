import { renderSlug, siteViewport, slugMetadata } from "@/lib/render";

// Shows the customer's name / service, so it is made fresh every time.
export const dynamic = "force-dynamic";

export const generateMetadata = () => slugMetadata("thank-you");
export const generateViewport = siteViewport;

export default async function ThankYouPage({ searchParams }: PageProps<"/thank-you">) {
  const q = await searchParams;
  const one = (v: unknown) => (Array.isArray(v) ? String(v[0] ?? "") : String(v ?? ""));
  return renderSlug("thank-you", { name: one(q.name), item: one(q.item) });
}
