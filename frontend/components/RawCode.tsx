import Script from "next/script";

/**
 * Tracking codes pasted in the admin (Google Tag Manager, GA4, Meta Pixel…).
 * <script> tags become next/script so they run; <noscript>/<style> are kept as they are.
 */
const ATTR = /([^\s=/>]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s>]+)))?/g;

function attrs(raw: string): Record<string, string | boolean> {
  const out: Record<string, string | boolean> = {};
  for (const m of raw.matchAll(ATTR)) {
    const name = m[1];
    const val = m[2] ?? m[3] ?? m[4];
    out[name] = val === undefined ? true : val;
  }
  return out;
}

export default function RawCode({ html, id }: { html: unknown; id: string }) {
  const code = String(html ?? "");
  if (!code.trim()) return null;
  const parts: React.ReactNode[] = [];
  const re = /<script\b([^>]*)>([\s\S]*?)<\/script>|<noscript\b[^>]*>([\s\S]*?)<\/noscript>|<style\b[^>]*>([\s\S]*?)<\/style>/gi;
  let i = 0;
  for (const m of code.matchAll(re)) {
    const k = `${id}-${i++}`;
    if (m[1] !== undefined) {
      const a = attrs(m[1]);
      const type = typeof a.type === "string" ? a.type : undefined;
      if (type && type !== "text/javascript" && type !== "module") {
        parts.push(<script key={k} type={type} dangerouslySetInnerHTML={{ __html: m[2] }} />);
      } else if (typeof a.src === "string") {
        parts.push(<Script key={k} id={k} src={a.src} strategy="afterInteractive" />);
      } else {
        parts.push(<Script key={k} id={k} strategy="afterInteractive" dangerouslySetInnerHTML={{ __html: m[2] }} />);
      }
    } else if (m[3] !== undefined) {
      parts.push(<noscript key={k} dangerouslySetInnerHTML={{ __html: m[3] }} />);
    } else if (m[4] !== undefined) {
      parts.push(<style key={k} dangerouslySetInnerHTML={{ __html: m[4] }} />);
    }
  }
  return <>{parts}</>;
}
