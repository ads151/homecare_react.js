import type { ReactNode } from "react";
import type { Data, Site } from "./api";
import { ICONS } from "./icons";

/* ---------------------------------------------------------------------
   Small helpers — same behaviour as the original PHP website helpers.
   --------------------------------------------------------------------- */

export function on(v: unknown): boolean {
  return v === true || v === 1 || v === "1" || v === "true" || v === "yes" || v === "on";
}

/** true unless the value is explicitly "off" (missing = on). */
export function show(v: unknown): boolean {
  return v === undefined || v === null || on(v);
}

export function list<T = Data>(v: unknown): T[] {
  if (Array.isArray(v)) return v as T[];
  if (v && typeof v === "object") return Object.values(v) as T[];
  return [];
}

/** Repeater rows can be strings or objects. */
export function strings(v: unknown, key = "text"): string[] {
  return list<unknown>(v)
    .map((r) => (typeof r === "string" ? r : r && typeof r === "object" ? String((r as Data)[key] ?? (r as Data)[0] ?? "") : ""))
    .filter((t) => t.trim() !== "");
}

/** Value from a row that is either {key: …} or [index: …]. */
export function pick(row: unknown, key: string, index?: number, def = ""): string {
  if (typeof row === "string") return index === 0 || index === undefined ? row : def;
  if (!row || typeof row !== "object") return def;
  const r = row as Data;
  if (r[key] !== undefined && r[key] !== null) return String(r[key]);
  if (index !== undefined && r[index] !== undefined && r[index] !== null) return String(r[index]);
  return def;
}

export function url(link: unknown): string {
  let l = String(link ?? "").trim();
  if (/^(https?:|mailto:|tel:|#|\/\/)/i.test(l)) return l;
  l = l.replace(/^\/+|\/+$/g, "");
  if (l === "home" || l === "index") l = "";
  return "/" + l;
}

export function img(path: unknown): string {
  const p = String(path ?? "").trim();
  if (p === "" || /^(https?:)?\/\//i.test(p)) return p;
  return "/" + p.replace(/^\/+/, "");
}

export function plainTitle(text: unknown): string {
  return String(text ?? "").replace(/[[\]]/g, "");
}

/** Helpers bound to the site settings. */
export function helpers(site: Site) {
  const set = site.settings;
  const s = (key: string, def: unknown = ""): any => {
    const [a, b] = key.split(".", 2);
    const v = b !== undefined ? (set[a] && typeof set[a] === "object" ? set[a][b] : undefined) : set[a];
    return v === undefined || v === null ? def : v;
  };
  const digits = (v: unknown) => String(v ?? "").replace(/\D+/g, "");
  const wa = (item = "") => {
    let num = digits(s("whatsapp_number"));
    if (num.length === 10) num = "91" + num;
    const msg = item
      ? String(s("whatsapp_card_message", "Hi, I want to know about {item}.")).replace("{item}", item)
      : String(s("whatsapp_message", "Hi, I need more information."));
    return "https://wa.me/" + num + "?text=" + encodeURIComponent(msg);
  };
  return { s, tel: site.links.tel, wa, site };
}

export type H = ReturnType<typeof helpers>;

/* ---------------------------------------------------------------------
   Tiny components
   --------------------------------------------------------------------- */

export function Icon({ name }: { name: string }) {
  const p = ICONS[name];
  if (!p) return null;
  return <svg className="ic" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor" dangerouslySetInnerHTML={{ __html: p }} />;
}

/** Heading text: words in [square brackets] show in the brand colour. */
export function Title({ text }: { text: unknown }) {
  const parts = String(text ?? "").split(/\[(.+?)\]/);
  return <>{parts.map((p, i) => (i % 2 ? <em key={i}>{p}</em> : p))}</>;
}

const ALLOWED = /<(\/?)(b|strong|i|em|a|br|span|u)(\s[^>]*)?>/i;

/** Paragraph text: empty line = new paragraph, simple tags allowed. */
export function Paras({ text, className }: { text: unknown; className?: string }) {
  const t = String(text ?? "").replace(/\r/g, "").trim();
  if (!t) return null;
  return (
    <>
      {t.split(/\n\s*\n/).map((p, i) => {
        const clean = p
          .trim()
          .replace(/<[^>]*>/g, (tag) => (ALLOWED.test(tag) ? tag.replace(/\son\w+\s*=\s*("[^"]*"|'[^']*'|[^\s>]+)/gi, "") : ""))
          .replace(/\n/g, "<br>");
        return <p key={i} className={className} dangerouslySetInnerHTML={{ __html: clean }} />;
      })}
    </>
  );
}

/** 'popup' = enquiry popup, '#x' = scroll, 'page' = page link, '' = nothing. */
export function Button({ text, link, className = "btn btn-main", item = "", details = "" }: { text: unknown; link: unknown; className?: string; item?: string; details?: string }) {
  const t = String(text ?? "");
  const l = String(link ?? "").trim();
  if (!t) return null;
  if (l === "popup") {
    return (
      <button type="button" className={className} data-popup="" data-item={item} data-details={details}>
        {t}
      </button>
    );
  }
  if (!l) return null;
  return (
    <a className={className} href={url(l)}>
      {t}
    </a>
  );
}

export function BoxLink({ link, item, className, children }: { link: unknown; item: string; className: string; children: ReactNode }) {
  const l = String(link ?? "").trim();
  if (l === "popup") {
    return (
      <button type="button" className={className + " is-link"} data-popup="" data-item={item}>
        {children}
      </button>
    );
  }
  if (l) {
    return (
      <a className={className + " is-link"} href={url(l)}>
        {children}
      </a>
    );
  }
  return <div className={className}>{children}</div>;
}

export function CallBtn({ h, className = "btn btn-call", text }: { h: H; className?: string; text?: string }) {
  return (
    <a className={className} href={h.tel} data-track="call">
      <Icon name="phone" />
      <span>{text ?? h.s("buttons.call_text", "Call Now")}</span>
    </a>
  );
}

export function WaBtn({ h, className = "btn btn-wa", item = "", text }: { h: H; className?: string; item?: string; text?: string }) {
  return (
    <a className={className} href={h.wa(item)} target="_blank" rel="noopener" data-track="whatsapp">
      <Icon name="whatsapp" />
      <span>{text ?? h.s("buttons.whatsapp_text", "WhatsApp")}</span>
    </a>
  );
}

export function SecHead({ small, heading, text, center = true }: { small?: unknown; heading?: unknown; text?: unknown; center?: boolean }) {
  const sm = String(small ?? "");
  const hd = String(heading ?? "");
  const tx = String(text ?? "");
  if (!sm && !hd && !tx.trim()) return null;
  return (
    <div className={"sec-head" + (center ? "" : " left")}>
      {sm && <span className="eyebrow">{sm}</span>}
      {hd && (
        <h2>
          <Title text={hd} />
        </h2>
      )}
      <Paras text={tx} />
    </div>
  );
}

/** Section heading from content keys sectionN_small / _heading / _text. */
export function SecHeadN({ c, n, center = true }: { c: Data; n: number; center?: boolean }) {
  return <SecHead small={c[`section${n}_small`]} heading={c[`section${n}_heading`]} text={c[`section${n}_text`]} center={center} />;
}
