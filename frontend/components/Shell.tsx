import type { ReactNode } from "react";
import type { Data, PageData } from "@/lib/api";
import { CallBtn, Icon, Paras, Title, WaBtn, img, list, on, pick, plainTitle, show, url, type H } from "@/lib/site";
import Blocks from "./Blocks";
import EnquiryForm from "./EnquiryForm";
import Img from "./Img";
import NavMenu, { type MenuItem } from "./NavMenu";
import RawCode from "./RawCode";
import { CtaBand } from "./Sections";
import SmartLink, { isInternal } from "./SmartLink";

/* =====================================================================
   SITE CHROME (in the root layout — stays on screen while pages change):
   top bar, header, mobile menu, footer, floating buttons, mobile bar, popup.
   PAGE BODY (per page): banner, page content, extra sections, CTA.
   Same HTML and CSS classes as the original website.
   ===================================================================== */

function Logo({ h, className = "logo" }: { h: H; className?: string }) {
  const name = String(h.s("business_name", "Home Care"));
  return (
    <SmartLink href="/" className={className} aria-label={`${name} home`}>
      {h.s("logo", "") && <Img src={h.s("logo")} alt={`${name} logo`} width={120} height={120} sizes="120px" priority />}
      {on(h.s("show_name_with_logo", true)) && (
        <span>
          <b>{name}</b>
          {h.s("tagline", "") && <small>{h.s("tagline")}</small>}
        </span>
      )}
    </SmartLink>
  );
}

function menuItems(h: H): MenuItem[] {
  return list(h.s("menu", []))
    .map((m) => {
      const label = pick(m, "label", 0);
      const href = url(pick(m, "link", 1));
      return { label, href, internal: isInternal(href) };
    })
    .filter((m) => m.label);
}

export function cssVars(h: H): string {
  const v = (k: string, d: string) => {
    const x = String(h.s(k, "") ?? "");
    return (x || d).replace(/[;{}<>]/g, "");
  };
  return (
    ":root{" +
    `--primary:${v("colors.primary", "#1f5c99")};--secondary:${v("colors.secondary", "#1b8a6b")};--dark:${v("colors.dark", "#0f2a52")};` +
    `--accent:${v("colors.accent", "#7cb93a")};--light:${v("colors.light", "#eef6f6")};` +
    `--main-bg:${v("buttons.main_bg", "#2c9a55")};--main-color:${v("buttons.main_color", "#fff")};` +
    `--call-bg:${v("buttons.call_bg", "#1f5c99")};--call-color:${v("buttons.call_color", "#fff")};` +
    `--wa-bg:${v("buttons.whatsapp_bg", "#25D366")};--wa-color:${v("buttons.whatsapp_color", "#fff")};` +
    `--submit-bg:${v("buttons.submit_bg", "#2c9a55")};--submit-color:${v("buttons.submit_color", "#fff")};` +
    `--quote-bg:${v("buttons.quote_bg", "#ffffff")};--quote-color:${v("buttons.quote_color", "#0f2a52")};` +
    "}"
  );
}

export const tracking = (h: H, box: string) => (on(h.s(`tracking.${box}_code_on`, false)) ? String(h.s(`tracking.${box}_code`, "")) : "");

export function SiteHeader({ h }: { h: H }) {
  const mainText = String(h.s("header_button_text", h.s("buttons.main_text", "Book Now")));
  const items = menuItems(h);
  return (
    <>
      <a className="skip" href="#main">
        Skip to content
      </a>

      {on(h.s("topbar_show", true)) && (
        <div className="topbar">
          <div className="wrap">
            <span className="live">
              <i className="dot" /> {h.s("topbar_text", "")}
            </span>
            <span className="tb-right">
              <a href={`mailto:${h.s("email")}`}>
                <Icon name="mail" />
                {h.s("email")}
              </a>
              <span>
                <Icon name="clock" />
                {h.s("working_hours")}
              </span>
            </span>
          </div>
        </div>
      )}

      <header className="site-header">
        <div className="wrap">
          <Logo h={h} />
          <nav className="main-nav" aria-label="Main menu">
            <NavMenu items={items} />
          </nav>
          <div className="h-actions">
            {on(h.s("header_phone_show", true)) && (
              <a className="h-phone" href={h.tel} data-track="call">
                <span className="h-ic">
                  <Icon name="phone" />
                </span>
                <span className="h-txt">
                  <small>{h.s("header_phone_label", "Call 24×7")}</small>
                  {h.s("mobile")}
                </span>
              </a>
            )}
            {on(h.s("header_button_show", true)) && (
              <button type="button" className="btn btn-main h-btn" data-popup="" data-item="">
                {mainText}
              </button>
            )}
            <button type="button" className="burger" aria-label="Open menu" aria-expanded="false" aria-controls="mnav">
              <span />
              <span />
              <span />
            </button>
          </div>
        </div>
      </header>

      <div className="mnav-overlay" data-close-menu="" />
      <aside className="mnav" id="mnav" aria-label="Mobile menu" aria-hidden="true">
        <div className="mnav-top">
          <Logo h={h} className="logo logo-sm" />
          <button type="button" className="mnav-x" aria-label="Close menu" data-close-menu="">
            ×
          </button>
        </div>
        <nav>
          <NavMenu items={items} />
        </nav>
        <div className="mnav-btns">
          <CallBtn h={h} text={`${h.s("buttons.call_text", "Call Now")} ${h.s("mobile")}`} />
          <WaBtn h={h} />
          <button type="button" className="btn btn-main" data-popup="" data-item="">
            {mainText}
          </button>
        </div>
      </aside>
    </>
  );
}

export function SiteFooter({ h }: { h: H }) {
  const social = (h.s("social", {}) || {}) as Data;
  const cols: [string, unknown][] = [
    [h.s("footer_quick_title", "Quick Links"), h.s("footer_quick_links", [])],
    [h.s("footer_info_title", "Information"), h.s("footer_info_links", [])],
  ];
  const nets = Object.entries(social).filter(([, v]) => String(v ?? "").trim() !== "");
  return (
    <>
      <footer className="site-footer">
        <div className="wrap">
          <div className="fgrid">
            <div className="f-about">
              <Logo h={h} className="logo logo-footer" />
              <Paras text={h.s("footer_about", "")} />
              {nets.length > 0 && (
                <div className="social">
                  {nets.map(([net, link]) => (
                    <a key={net} href={String(link)} target="_blank" rel="noopener" aria-label={net.charAt(0).toUpperCase() + net.slice(1)}>
                      <Icon name={net} />
                    </a>
                  ))}
                </div>
              )}
            </div>
            {cols.map(([title, links], k) => (
              <div key={k}>
                <h3 className="f-h">{title}</h3>
                <ul>
                  {list(links).map((l, i) =>
                    pick(l, "label", 0) ? (
                      <li key={i}>
                        <SmartLink href={url(pick(l, "link", 1))}>{pick(l, "label", 0)}</SmartLink>
                      </li>
                    ) : null,
                  )}
                </ul>
              </div>
            ))}
            <div>
              <h3 className="f-h">{h.s("footer_contact_title", "Contact Us")}</h3>
              <ul className="f-contact">
                <li>
                  <Icon name="phone" />
                  <a href={h.tel} data-track="call">
                    {h.s("mobile")}
                  </a>
                </li>
                <li>
                  <Icon name="whatsapp" />
                  <a href={h.wa()} target="_blank" rel="noopener" data-track="whatsapp">
                    WhatsApp: {h.s("mobile")}
                  </a>
                </li>
                <li>
                  <Icon name="mail" />
                  <a href={`mailto:${h.s("email")}`}>{h.s("email")}</a>
                </li>
                <li>
                  <Icon name="pin" />
                  <span>{h.s("address")}</span>
                </li>
                <li>
                  <Icon name="clock" />
                  <span>{h.s("working_hours")}</span>
                </li>
              </ul>
            </div>
          </div>
          <div className="copy">{String(h.s("copyright", "© {year}")).replace("{year}", String(new Date().getFullYear()))}</div>
        </div>
      </footer>
      <Floating h={h} />
      <MobileBar h={h} />
      <Popup h={h} />
    </>
  );
}

function Floating({ h }: { h: H }) {
  const f = (h.s("floating", {}) || {}) as Data;
  const g = (k: string, d: unknown) => (f[k] === undefined || f[k] === null ? d : f[k]);
  if (!on(g("floating_enabled", true))) return null;
  const call = on(g("call_enabled", true));
  const wa = on(g("whatsapp_enabled", true));
  if (!call && !wa) return null;
  const num = (v: unknown) => parseInt(String(v).replace(/[^0-9]/g, ""), 10) || 0;
  const anim = String(g("animation", "pulse")).replace(/[^a-z]/g, "") || "none";
  let cls = `floating fb-${g("position_side", "right") === "left" ? "left" : "right"} fb-${g("layout", "vertical") === "horizontal" ? "horizontal" : "vertical"} anim-${anim} speed-${String(g("animation_speed", "normal")).replace(/[^a-z]/g, "")}`;
  if (!on(g("show_on_mobile", true))) cls += " hide-mobile";
  if (!on(g("show_on_desktop", true))) cls += " hide-desktop";
  if (on(h.s("mobile_bar.enabled", true))) cls += " has-mbar";
  const style = {
    "--fb-x": `${num(g("position_x", 20))}px`,
    "--fb-y": `${num(g("position_y", 24))}px`,
    "--fb-gap": `${num(g("gap", 12))}px`,
    "--fb-size": `${num(g("size", 58))}px`,
    "--fb-msize": `${num(g("mobile_size", 52))}px`,
    "--fb-icon": `${num(g("icon_size", 26))}px`,
    "--fb-call": String(g("call_color", "#1f5c99")),
    "--fb-wa": String(g("whatsapp_color", "#25D366")),
  } as React.CSSProperties;
  const tip = on(g("tooltip_enabled", true));
  return (
    <div className={cls} style={style}>
      {wa && (
        <a className="fb fb-wa" href={h.wa()} target="_blank" rel="noopener" aria-label={String(g("whatsapp_tooltip_text", "WhatsApp"))} data-track="whatsapp">
          <Icon name="whatsapp" />
          {tip && <span className="tip">{String(g("whatsapp_tooltip_text", "Chat on WhatsApp"))}</span>}
        </a>
      )}
      {call && (
        <a className="fb fb-call" href={h.tel} aria-label={String(g("call_tooltip_text", "Call"))} data-track="call">
          <Icon name="phone" />
          {tip && <span className="tip">{String(g("call_tooltip_text", "Call Now"))}</span>}
        </a>
      )}
    </div>
  );
}

function MobileBar({ h }: { h: H }) {
  if (!on(h.s("mobile_bar.enabled", true))) return null;
  return (
    <div className="mbar">
      <a className="mb-call" href={h.tel} data-track="call">
        <Icon name="phone" />
        <span>{h.s("mobile_bar.call_text", "Call")}</span>
      </a>
      <a className="mb-wa" href={h.wa()} target="_blank" rel="noopener" data-track="whatsapp">
        <Icon name="whatsapp" />
        <span>{h.s("mobile_bar.whatsapp_text", "WhatsApp")}</span>
      </a>
      <button type="button" className="mb-main" data-popup="" data-item="">
        {h.s("mobile_bar.main_text", "Book Now")}
      </button>
    </div>
  );
}

function Popup({ h }: { h: H }) {
  const heading = String(h.s("popup_heading", "Get a Free Call Back"));
  return (
    <div className="pop" id="hcPop" aria-hidden="true">
      <div className="pop-box" role="dialog" aria-modal="true" aria-labelledby="popTitle">
        <button type="button" className="pop-x" aria-label="Close" data-close-pop="">
          ×
        </button>
        <h2 id="popTitle" data-general={heading} data-prefix={h.s("popup_item_prefix", "Book")}>
          {heading}
        </h2>
        <p className="pop-sub">{h.s("popup_subtext", "")}</p>
        <div className="pop-for" hidden>
          <small>You are enquiring for</small>
          <b className="pop-item" />
          <span className="pop-det" />
        </div>
        <EnquiryForm h={h} id="popForm" name="Popup Form" short />
      </div>
    </div>
  );
}

function InnerHero({ c }: { c: Data }) {
  const name = c.page_name || plainTitle(c.hero_title);
  const bg = String(c.hero_image || "images/pages/page-hero.jpg");
  return (
    <section className="page-hero">
      <picture className="hero-bg">
        {c.hero_image_mobile && <source media="(max-width: 640px)" srcSet={img(c.hero_image_mobile)} />}
        <img src={img(bg)} alt={c.hero_image_alt || name} fetchPriority="high" decoding="async" />
      </picture>
      <div className="wrap">
        <nav className="crumbs" aria-label="Breadcrumb">
          <SmartLink href="/">Home</SmartLink> <span>›</span> <span aria-current="page">{name}</span>
        </nav>
        <h1>
          <Title text={c.hero_title || name} />
        </h1>
        {c.hero_text && <p>{c.hero_text}</p>}
      </div>
    </section>
  );
}

/** The changing part of every page (inside <main>). */
export default function Shell({ h, page, children, showCta = true }: { h: H; page: PageData; children: ReactNode; showCta?: boolean }) {
  const c = page.content;
  const slug = page.slug === "home" ? "home" : page.slug;
  const ctaHeading = c.cta_heading || h.s("cta_heading", "Need care at home today?");
  const ctaText = c.cta_text || h.s("cta_text", "");

  return (
    <div className={`page-${slug}`}>
      {page.seo.jsonld.map((j, i) => (
        <script key={i} type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(j).replace(/</g, "\\u003c") }} />
      ))}
      <RawCode html={c.head_code} id={`tc-page-${slug}`} />
      {slug === "thank-you" && <RawCode html={tracking(h, "thankyou")} id="tc-ty" />}

      {page.template !== "home" && show(c.hero_show) && <InnerHero c={c} />}
      {children}
      <Blocks h={h} blocks={c.extra_blocks} page={slug} />
      {showCta && <CtaBand h={h} heading={String(ctaHeading)} text={String(ctaText)} />}
    </div>
  );
}
