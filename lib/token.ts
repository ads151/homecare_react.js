import { createHmac, timingSafeEqual } from "node:crypto";
import { API_SECRET } from "./api";

/* Spam protection for the enquiry forms: a signed time stamp.
   Too fast (< 3 s) or too old (> 2 days) submissions are rejected. */

const sign = (ts: string) => createHmac("sha256", API_SECRET || "hc-form").update(ts).digest("hex").slice(0, 24);

export function formToken(): string {
  const ts = String(Math.floor(Date.now() / 1000));
  return `${ts}.${sign(ts)}`;
}

export function checkToken(token: string): "ok" | "fast" | "expired" {
  const [ts, sig] = String(token || "").split(".");
  if (!ts || !sig || !/^\d+$/.test(ts)) return "expired";
  const want = Buffer.from(sign(ts));
  const got = Buffer.from(sig);
  if (want.length !== got.length || !timingSafeEqual(want, got)) return "expired";
  const age = Math.floor(Date.now() / 1000) - Number(ts);
  if (age < 3) return "fast";
  if (age > 172800) return "expired";
  return "ok";
}
