"use client";

import { usePathname, useRouter } from "next/navigation";
import { useEffect, useRef } from "react";

/* ---------------------------------------------------------------------
   Everything that makes the website interactive — the React version of
   the original main.js. Listeners sit on window, so they keep working
   while pages change without a reload.
     • mobile menu        • enquiry popup      • form check + sending
     • mobile number fix  • service filters    • click / page-view tracking
     • scroll animations  • loading bar        • smooth links in rich text
   --------------------------------------------------------------------- */

declare global {
  interface Window {
    dataLayer?: Record<string, unknown>[];
    gtag?: (...args: unknown[]) => void;
  }
}

const $ = <T extends Element = HTMLElement>(s: string, el: ParentNode = document) => el.querySelector<T>(s);
const $$ = <T extends Element = HTMLElement>(s: string, el: ParentNode = document) => Array.from(el.querySelectorAll<T>(s));

function push(event: string, data: Record<string, unknown> = {}) {
  try {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event, ...data });
    if (typeof window.gtag === "function") window.gtag("event", event, data);
  } catch {
    /* tracking must never break the site */
  }
}

function today() {
  const d = new Date();
  d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
  return d.toISOString().slice(0, 10);
}

function cleanMobile(v: string) {
  let d = String(v || "").replace(/\D/g, "");
  if (d.length > 10 && d.indexOf("91") === 0) d = d.slice(2);
  if (d.length > 10 && d.charAt(0) === "0") d = d.slice(1);
  if (d.length === 11 && d.charAt(0) === "0") d = d.slice(1);
  return d.slice(0, 10);
}
const validMobile = (v: string) => /^[6-9]\d{9}$/.test(v);

type Field = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

function checkField(el: Field) {
  const v = (el.value || "").trim();
  let ok = true;
  if (el.required && v === "") ok = false;
  else if (el.hasAttribute("data-mobile") && v !== "") ok = validMobile(v);
  else if (el.type === "email" && v !== "") ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v);
  else if (el.type === "date" && v !== "") ok = v >= today();
  el.closest(".field")?.classList.toggle("err", !ok);
  return ok;
}

const formFields = (form: HTMLFormElement) =>
  $$<Field>("input,select,textarea", form).filter((el) => el.type !== "hidden" && el.name !== "website");

const REVEAL = [
  ".sec-head", ".card", ".step", ".box", ".feat > div", ".pcard", ".t", ".faq details",
  ".areas span", ".ccard", ".stat", ".about-img", ".why-img", ".form-card", ".custom-box",
  ".cta-band", ".hc-gallery figure", ".hc-video", ".map", ".ty-box", ".legal > *",
].join(",");

export default function SiteBehaviors() {
  const router = useRouter();
  const pathname = usePathname();
  const first = useRef(true);
  const lastFocus = useRef<HTMLElement | null>(null);

  /* ---------------- one-time listeners ---------------- */
  useEffect(() => {
    const body = document.body;

    const closeMenu = () => {
      body.classList.remove("menu-open");
      $(".burger")?.setAttribute("aria-expanded", "false");
      $("#mnav")?.setAttribute("aria-hidden", "true");
    };
    const openMenu = () => {
      body.classList.add("menu-open");
      $(".burger")?.setAttribute("aria-expanded", "true");
      $("#mnav")?.setAttribute("aria-hidden", "false");
    };

    const openPopup = (item: string, details: string) => {
      const pop = $("#hcPop");
      if (!pop) return;
      closeMenu();
      const title = $("#popTitle", pop)!;
      const forBox = $(".pop-for", pop)!;
      const form = $<HTMLFormElement>("form", pop);
      if (item) {
        title.textContent = (title.getAttribute("data-prefix") || "Book") + " " + item;
        $(".pop-item", pop)!.textContent = item;
        const det = $(".pop-det", pop)!;
        det.textContent = details || "";
        det.style.display = details ? "" : "none";
        forBox.hidden = false;
      } else {
        title.textContent = title.getAttribute("data-general");
        forBox.hidden = true;
      }
      if (form) {
        const itemInput = form.elements.namedItem("enquiry_item") as HTMLInputElement | null;
        if (itemInput) itemInput.value = item;
        $$<HTMLSelectElement>("select[data-select]", form).forEach((sel) => {
          if (!item) {
            sel.value = "";
            return;
          }
          const found = Array.from(sel.options).some((o) => {
            if (o.value === item || o.text === item) {
              sel.value = o.value;
              return true;
            }
            return false;
          });
          if (!found) {
            const o = new Option(item, item);
            sel.add(o, sel.options[1] || null);
            sel.value = item;
          }
        });
        $$(".field.err", form).forEach((f) => f.classList.remove("err"));
        const err = $(".form-error", form);
        if (err) err.hidden = true;
      }
      lastFocus.current = document.activeElement as HTMLElement | null;
      pop.classList.add("open");
      pop.setAttribute("aria-hidden", "false");
      body.classList.add("pop-open");
      window.setTimeout(() => {
        const f = form?.querySelector<HTMLInputElement>('input:not([type=hidden]):not([tabindex="-1"])');
        if (f && window.innerWidth > 640) f.focus();
      }, 250);
    };

    const closePopup = () => {
      const pop = $("#hcPop");
      if (!pop || !pop.classList.contains("open")) return;
      pop.classList.remove("open");
      pop.setAttribute("aria-hidden", "true");
      body.classList.remove("pop-open");
      lastFocus.current?.focus?.();
    };

    const startLoading = () => document.documentElement.classList.add("hc-loading");

    /* Clicks: menu, popup, filters, tracking, smooth links */
    const onClick = (e: MouseEvent) => {
      const t = e.target as HTMLElement | null;
      if (!t || !t.closest) return;

      if (t.closest(".burger")) return openMenu();
      if (t.closest("[data-close-menu]")) return closeMenu();

      const popBtn = t.closest<HTMLElement>("[data-popup]");
      if (popBtn) {
        e.preventDefault();
        return openPopup(popBtn.getAttribute("data-item") || "", popBtn.getAttribute("data-details") || "");
      }
      const pop = $("#hcPop");
      if (pop && (t === pop || t.closest("[data-close-pop]"))) return closePopup();

      const fb = t.closest<HTMLButtonElement>(".filters button");
      if (fb) {
        const bar = fb.closest(".filters")!;
        const grid = bar.nextElementSibling;
        $$("button", bar).forEach((x) => x.classList.toggle("on", x === fb));
        const f = fb.getAttribute("data-filter") || "";
        if (grid) $$(".card", grid).forEach((c) => c.classList.toggle("hide", f !== "" && c.getAttribute("data-cat") !== f));
        return;
      }

      const tracked = t.closest<HTMLAnchorElement>("[data-track]");
      if (tracked) push(tracked.getAttribute("data-track") + "_click", { link_url: tracked.href || "", page_path: location.pathname });

      // Website links (also inside admin rich text) → open without reload
      const a = t.closest<HTMLAnchorElement>("a[href]");
      if (!a || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || a.target || a.hasAttribute("download")) return;
      const u = new URL(a.href, location.href);
      if (u.origin !== location.origin || /^\/(api|admin|install|images|uploads)(\/|$)/.test(u.pathname)) return;
      if (u.pathname === location.pathname && u.search === location.search) {
        if (u.hash) return; // same-page #section scroll
      }
      closeMenu();
      if (u.pathname !== location.pathname || u.search !== location.search) startLoading();
      if (!e.defaultPrevented) {
        e.preventDefault();
        router.push(u.pathname + u.search + u.hash);
      }
    };

    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape" || e.key === "Esc") {
        closePopup();
        closeMenu();
      }
    };

    /* Mobile number fields */
    const onInput = (e: Event) => {
      const inp = e.target as HTMLInputElement;
      if (inp?.matches?.("input[data-mobile]")) {
        const c = cleanMobile(inp.value);
        if (c !== inp.value) inp.value = c;
        if (validMobile(c)) inp.closest(".field")?.classList.remove("err");
      }
    };
    const onPaste = (e: ClipboardEvent) => {
      const inp = e.target as HTMLInputElement;
      if (!inp?.matches?.("input[data-mobile]")) return;
      e.preventDefault();
      inp.value = cleanMobile(e.clipboardData?.getData("text") || "");
      inp.dispatchEvent(new Event("input", { bubbles: true }));
    };
    const onBlur = (e: FocusEvent) => {
      const inp = e.target as HTMLInputElement;
      if (inp?.matches?.("input[data-mobile]") && inp.value !== "") inp.closest(".field")?.classList.toggle("err", !validMobile(inp.value));
    };
    const onChange = (e: Event) => {
      const el = e.target as Field;
      if (el?.closest?.("form.hc-form") && el.closest(".field")?.classList.contains("err")) checkField(el);
    };

    /* Enquiry forms: check fields, send, go to thank-you */
    const onSubmit = (e: SubmitEvent) => {
      const form = e.target as HTMLFormElement;
      if (!form?.matches?.("form.hc-form")) return;
      e.preventDefault();
      let ok = true;
      let firstBad: Field | null = null;
      const pageUrl = form.elements.namedItem("page_url") as HTMLInputElement | null;
      if (pageUrl) pageUrl.value = location.href;
      formFields(form).forEach((el) => {
        if (el.hasAttribute("data-mobile")) el.value = cleanMobile(el.value);
        if (!checkField(el)) {
          ok = false;
          firstBad = firstBad || el;
        }
      });
      const errBox = $(".form-error", form);
      if (!ok) {
        (firstBad as Field | null)?.focus();
        return;
      }
      const btn = $<HTMLButtonElement>("button[type=submit]", form)!;
      const txt = btn.innerHTML;
      btn.disabled = true;
      btn.textContent = btn.getAttribute("data-sending") || "Sending…";
      if (errBox) errBox.hidden = true;

      const fd = new FormData(form);
      fd.append("ajax", "1");
      fetch(form.action, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" })
        .then((r) => r.json())
        .then((res) => {
          if (res && res.ok && res.redirect) {
            const name = form.elements.namedItem("form_name") as HTMLInputElement | null;
            push("form_submit", { form_name: name ? name.value : "" });
            // Full page load: Google Ads / Meta conversion codes on the thank-you page always run
            window.location.href = res.redirect;
          } else {
            throw new Error(res && res.message ? res.message : "Error");
          }
        })
        .catch((err: Error) => {
          btn.disabled = false;
          btn.innerHTML = txt;
          if (errBox) {
            errBox.textContent =
              err && err.message && err.message !== "Failed to fetch" && err.message.indexOf("JSON") === -1
                ? err.message
                : "Sorry, something went wrong. Please try again or call us.";
            errBox.hidden = false;
          }
        });
    };

    window.addEventListener("click", onClick);
    window.addEventListener("keydown", onKey);
    window.addEventListener("input", onInput);
    window.addEventListener("paste", onPaste as EventListener);
    window.addEventListener("focusout", onBlur);
    window.addEventListener("change", onChange);
    window.addEventListener("submit", onSubmit as EventListener);
    return () => {
      window.removeEventListener("click", onClick);
      window.removeEventListener("keydown", onKey);
      window.removeEventListener("input", onInput);
      window.removeEventListener("paste", onPaste as EventListener);
      window.removeEventListener("focusout", onBlur);
      window.removeEventListener("change", onChange);
      window.removeEventListener("submit", onSubmit as EventListener);
    };
  }, [router]);

  /* ---------------- every page (first load + each page change) ---------------- */
  useEffect(() => {
    const html = document.documentElement;
    html.classList.remove("hc-loading");
    document.body.classList.remove("menu-open", "pop-open");
    $("#hcPop")?.classList.remove("open");

    $$<HTMLInputElement>("input[type=date]").forEach((i) => (i.min = today()));

    // Fresh spam-check token for all forms (pages are cached, so the one in the HTML can be old)
    fetch("/api/form-token", { cache: "no-store" })
      .then((r) => r.json())
      .then((d) => {
        if (d && d.token) $$<HTMLInputElement>('input[name="hc_ts"]').forEach((i) => (i.value = d.token));
      })
      .catch(() => undefined);

    if (first.current) {
      first.current = false;
    } else {
      html.classList.add("hc-nav"); // fade-in only for page changes, not the first load
      push("page_view", { page_path: location.pathname, page_location: location.href, page_title: document.title });
    }

    // Scroll animations (only for things below the screen, so nothing visible jumps)
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches || !("IntersectionObserver" in window)) return;
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((en) => {
          if (en.isIntersecting) {
            en.target.classList.add("rv-in");
            io.unobserve(en.target);
          }
        });
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.08 },
    );
    const vh = window.innerHeight;
    const hidden: Element[] = [];
    $$("main " + REVEAL.split(",").join(", main ")).forEach((el, i) => {
      if (el.getBoundingClientRect().top < vh) return;
      el.classList.add("rv");
      (el as HTMLElement).style.setProperty("--rv-d", `${(i % 4) * 70}ms`);
      io.observe(el);
      hidden.push(el);
    });

    // Safety net: never leave content hidden (e.g. if a browser skips the
    // observer, or the page is printed / jumped to with a #link)
    const showReached = () => {
      const limit = window.innerHeight * 1.05;
      let left = 0;
      hidden.forEach((el) => {
        if (el.classList.contains("rv-in")) return;
        if (el.getBoundingClientRect().top < limit) el.classList.add("rv-in");
        else left++;
      });
      if (!left) window.clearInterval(timer);
    };
    const showAll = () => hidden.forEach((el) => el.classList.add("rv-in"));
    const timer = window.setInterval(showReached, 700);
    window.addEventListener("beforeprint", showAll);
    window.addEventListener("hashchange", showReached);
    return () => {
      io.disconnect();
      window.clearInterval(timer);
      window.removeEventListener("beforeprint", showAll);
      window.removeEventListener("hashchange", showReached);
    };
  }, [pathname]);

  return null;
}
