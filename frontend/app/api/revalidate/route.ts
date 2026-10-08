import { revalidateTag } from "next/cache";
import { timingSafeEqual } from "node:crypto";
import { API_SECRET } from "@/lib/api";

/** Called by the admin panel after every save → the website shows the new content. */
export async function POST(request: Request) {
  const given = Buffer.from(request.headers.get("x-api-secret") || "");
  const want = Buffer.from(API_SECRET);
  if (!API_SECRET || given.length !== want.length || !timingSafeEqual(given, want)) {
    return Response.json({ ok: false, message: "Unauthorized" }, { status: 401 });
  }
  revalidateTag("cms", { expire: 0 });
  return Response.json({ ok: true, revalidated: true, now: Date.now() });
}
