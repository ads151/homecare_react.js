import { formToken } from "@/lib/token";

export const dynamic = "force-dynamic";

/** Fresh spam-check token for the enquiry forms (pages themselves are cached). */
export function GET() {
  return Response.json({ token: formToken() }, { headers: { "Cache-Control": "no-store" } });
}
