import { connection } from "next/server";
import { cache } from "react";

/* ---------------------------------------------------------------------
   Data from the Laravel backend (admin panel).
   All responses are cached with the tag "cms"; the admin calls
   /api/revalidate after every save, so changes show up straight away.
   --------------------------------------------------------------------- */

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type Data = Record<string, any>;

export type FormField = {
  name: string;
  label: string;
  type: string;
  required: boolean;
  placeholder?: string;
  options: string[];
  short_form: boolean;
  wide?: boolean;
};

export type Card = {
  title: string;
  image?: string;
  image_alt?: string;
  badge?: string;
  label?: string;
  category?: string;
  text?: string;
  features: string[];
  price_prefix?: string;
  price_display: string;
  old_price_display: string;
  price_note?: string;
  button_text?: string;
  details: string;
};

export type Site = {
  settings: Data;
  links: { tel: string; whatsapp: string; site_url: string };
  form_fields: FormField[];
  services: Card[];
};

export type PageData = {
  slug: string;
  title: string;
  template: string;
  content: Data;
  seo: {
    title: string;
    description: string;
    canonical: string;
    index: boolean;
    follow: boolean;
    og_title: string;
    og_description: string;
    og_image: string;
    jsonld: Data[];
  };
  updated_at?: string;
};

export const BACKEND_URL = (process.env.BACKEND_URL || "http://localhost:8000").replace(/\/+$/, "");
export const API_SECRET = process.env.API_SECRET || "";

async function get<T>(path: string): Promise<T | null> {
  const res = await fetch(`${BACKEND_URL}/api${path}`, {
    headers: { Accept: "application/json", "X-Api-Secret": API_SECRET },
    next: { tags: ["cms"], revalidate: 3600 },
  });
  if (res.status === 404) return null;
  if (!res.ok) {
    throw new Error(`Backend error ${res.status} for ${path}. Check BACKEND_URL and API_SECRET.`);
  }
  return (await res.json()) as T;
}

/** Settings, menu, services, form fields (one request per page view). */
export const getSite = cache(async (): Promise<Site> => {
  // While building on the server the admin may not be reachable yet:
  // render such pages on the first visit instead of during the build.
  if (process.env.NEXT_PHASE === "phase-production-build") await connection();
  const site = await get<Site>("/site");
  if (!site) throw new Error("Backend did not return site settings.");
  return site;
});

export const getPage = cache(async (slug: string): Promise<PageData | null> => {
  if (!/^[a-z0-9][a-z0-9-]*$/i.test(slug)) return null;
  return get<PageData>(`/pages/${slug.toLowerCase()}`);
});

export const getSitemap = async (): Promise<{ slug: string; updated_at?: string }[]> => {
  const data = await get<{ pages: { slug: string; updated_at?: string }[] }>("/pages");
  return data?.pages ?? [];
};
