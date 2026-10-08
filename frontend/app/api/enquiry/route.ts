import { API_SECRET, BACKEND_URL } from "@/lib/api";
import { checkToken } from "@/lib/token";

/**
 * Enquiry forms post here (same-origin). We check the spam token and pass
 * the enquiry to the Laravel backend, which saves the lead and sends the email.
 */
export async function POST(request: Request) {
  const reply = (ok: boolean, message: string, redirect = "", status = 200) => Response.json({ ok, message, redirect }, { status });

  let form: FormData;
  try {
    form = await request.formData();
  } catch {
    return reply(false, "Please fill the form again.", "", 400);
  }

  // Honeypot filled = bot: pretend success
  if (String(form.get("website") ?? "") !== "") return reply(true, "OK", "/thank-you");

  const t = checkToken(String(form.get("hc_ts") ?? ""));
  if (t === "fast") return reply(false, "That was very fast! Please wait a moment and submit again.");
  if (t === "expired") return reply(false, "This form has expired. Please refresh the page and try again.");

  const body = new URLSearchParams();
  for (const [k, v] of form.entries()) {
    if (typeof v === "string" && k !== "hc_ts" && k !== "website") body.append(k, v);
  }
  const ip = (request.headers.get("x-forwarded-for") || "").split(",")[0].trim() || request.headers.get("x-real-ip") || "";

  try {
    const res = await fetch(`${BACKEND_URL}/api/enquiry`, {
      method: "POST",
      headers: { "X-Api-Secret": API_SECRET, "X-Client-Ip": ip, Accept: "application/json", "Content-Type": "application/x-www-form-urlencoded" },
      body,
      cache: "no-store",
    });
    const data = await res.json();
    return Response.json(data, { status: res.ok ? 200 : res.status });
  } catch {
    return reply(false, "Sorry, your message could not be sent right now. Please call or WhatsApp us.", "", 502);
  }
}
