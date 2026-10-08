import type { Card } from "@/lib/api";
import { BoxLink, CallBtn, Icon, Paras, list, pick, img, strings, type H } from "@/lib/site";
import Img from "./Img";

/* Reusable pieces used by the page designs and by the Page Builder. */

export function ServiceCard({ h, card }: { h: H; card: Card }) {
  const t = card.title;
  return (
    <article className="card" data-cat={(card.category || "").trim()}>
      <div className="ph">
        {card.image && <Img src={card.image} alt={card.image_alt || t} width={700} height={440} sizes="(max-width: 640px) 100vw, (max-width: 1100px) 50vw, 400px" />}
        {card.badge && <span className="badge">{card.badge}</span>}
        {card.label && <span className="label">{card.label}</span>}
      </div>
      <div className="body">
        <h3>{t}</h3>
        {card.text && <p className="ctext">{card.text}</p>}
        {card.features.length > 0 && (
          <ul className="ticks-sm">
            {card.features.map((f, i) => (
              <li key={i}>
                <Icon name="check" />
                {f}
              </li>
            ))}
          </ul>
        )}
        {card.price_display && (
          <div className="price">
            {card.price_prefix && <small>{card.price_prefix}</small>}
            <b>{card.price_display}</b>
            {card.old_price_display && <s>{card.old_price_display}</s>}
            {card.price_note && <small>{card.price_note}</small>}
          </div>
        )}
        <div className="card-btns">
          <button type="button" className="btn btn-main" data-popup="" data-item={t} data-details={card.details}>
            {card.button_text || h.s("buttons.main_text", "Book Now")}
          </button>
          {h.s("buttons.card_show_call", true) !== false && (
            <a className="round round-call" href={h.tel} aria-label={`Call about ${t}`} data-track="call">
              <Icon name="phone" />
            </a>
          )}
          {h.s("buttons.card_show_whatsapp", true) !== false && (
            <a className="round round-wa" href={h.wa(t)} target="_blank" rel="noopener" aria-label={`WhatsApp about ${t}`} data-track="whatsapp">
              <Icon name="whatsapp" />
            </a>
          )}
        </div>
      </div>
    </article>
  );
}

export function CardsGrid({ h, filter = false, limit = 0, category = "" }: { h: H; filter?: boolean; limit?: number; category?: string }) {
  let cards = h.site.services;
  if (category) cards = cards.filter((c) => (c.category || "").trim() === category.trim());
  if (limit > 0) cards = cards.slice(0, limit);
  const cats = [...new Set(cards.map((c) => (c.category || "").trim()).filter(Boolean))];
  return (
    <>
      {filter && cats.length >= 2 && (
        <div className="filters" role="group" aria-label="Filter">
          <button type="button" className="on" data-filter="">
            All
          </button>
          {cats.map((c) => (
            <button type="button" data-filter={c} key={c}>
              {c}
            </button>
          ))}
        </div>
      )}
      <div className="cards">
        {cards.map((c) => (
          <ServiceCard h={h} card={c} key={c.title} />
        ))}
      </div>
    </>
  );
}

export function Stats({ items, plain = true }: { items: unknown; plain?: boolean }) {
  const rows = list(items).filter((r) => pick(r, "value", 0) !== "");
  if (!rows.length) return null;
  return (
    <section className={"stats" + (plain ? " plain" : "")}>
      <div className="wrap">
        <div className="stats-grid">
          {rows.map((r, i) => (
            <div className="stat" key={i}>
              <b>{pick(r, "value", 0)}</b>
              <span>{pick(r, "label", 1)}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export function Steps({ items }: { items: unknown }) {
  return (
    <div className="steps">
      {list(items).map((r, i) => (
        <div className="step" key={i}>
          <span className="num">{i + 1}</span>
          <span className="emo">{pick(r, "icon", 0)}</span>
          <h3>{pick(r, "title", 1)}</h3>
          <p>{pick(r, "text", 2)}</p>
        </div>
      ))}
    </div>
  );
}

export function Feat({ items }: { items: unknown }) {
  return (
    <div className="feat">
      {list(items).map((r, i) => (
        <div key={i}>
          <span className="fi">{pick(r, "icon", 0)}</span>
          <div>
            <h3>{pick(r, "title", 1)}</h3>
            <p>{pick(r, "text", 2)}</p>
          </div>
        </div>
      ))}
    </div>
  );
}

export function Boxes({ items }: { items: unknown }) {
  return (
    <div className="boxes3">
      {list(items).map((r, i) => (
        <div className="box" key={i}>
          <span className="fi big">{pick(r, "icon", 0)}</span>
          <h3>{pick(r, "title", 1)}</h3>
          <Paras text={pick(r, "text", 2)} />
        </div>
      ))}
    </div>
  );
}

export function PhotoCards({ items }: { items: unknown }) {
  return (
    <div className="photo-cards">
      {list(items).map((r, i) => {
        const title = pick(r, "title");
        return (
          <BoxLink link={pick(r, "link")} item={title} className="pcard" key={i}>
            {pick(r, "image") && <Img src={pick(r, "image")} alt={pick(r, "image_alt") || title} width={700} height={440} sizes="(max-width: 640px) 100vw, (max-width: 1100px) 50vw, 300px" />}
            <span className="pc-txt">
              <b>{title}</b>
              {pick(r, "text") && <small>{pick(r, "text")}</small>}
            </span>
          </BoxLink>
        );
      })}
    </div>
  );
}

export function Ticks({ items, className = "ticks dark" }: { items: unknown; className?: string }) {
  const rows = strings(items);
  if (!rows.length) return null;
  return (
    <ul className={className}>
      {rows.map((p, i) => (
        <li key={i}>
          <Icon name="check" />
          {p}
        </li>
      ))}
    </ul>
  );
}

export function Areas({ items }: { items: unknown }) {
  return (
    <div className="areas">
      {strings(items).map((a, i) => (
        <span key={i}>
          <Icon name="pin" />
          {a}
        </span>
      ))}
    </div>
  );
}

export function Testimonials({ items }: { items: unknown }) {
  return (
    <div className="tgrid">
      {list(items)
        .filter((r) => pick(r, "name", 0) !== "")
        .map((r, i) => {
          const name = pick(r, "name", 0);
          const ini = name
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((w) => Array.from(w)[0] || "")
            .join("")
            .toUpperCase();
          return (
            <figure className="t" key={i}>
              <span className="stars">
                {[0, 1, 2, 3, 4].map((k) => (
                  <Icon name="star" key={k} />
                ))}
              </span>
              <blockquote>{pick(r, "text", 2)}</blockquote>
              <figcaption>
                <span className="av">{ini}</span>
                <span>
                  <b>{name}</b>
                  <small>{pick(r, "area", 1)}</small>
                </span>
              </figcaption>
            </figure>
          );
        })}
    </div>
  );
}

export function Faq({ items }: { items: unknown }) {
  const rows = list(items).filter((r) => pick(r, "question", 0) !== "");
  return (
    <div className="faq">
      {rows.map((r, i) => (
        <details key={i} open={i === 0}>
          <summary>{pick(r, "question", 0)}</summary>
          <div>
            <Paras text={pick(r, "answer", 1)} />
          </div>
        </details>
      ))}
    </div>
  );
}

export function ContactCards({ h }: { h: H }) {
  return (
    <div className="ccards">
      <a className="ccard" href={h.tel} data-track="call">
        <span className="ci">
          <Icon name="phone" />
        </span>
        <small>Call Us</small>
        <b>{h.s("mobile")}</b>
      </a>
      <a className="ccard" href={h.wa()} target="_blank" rel="noopener" data-track="whatsapp">
        <span className="ci wa">
          <Icon name="whatsapp" />
        </span>
        <small>WhatsApp</small>
        <b>{h.s("mobile")}</b>
      </a>
      <a className="ccard" href={`mailto:${h.s("email")}`}>
        <span className="ci">
          <Icon name="mail" />
        </span>
        <small>Email</small>
        <b>{h.s("email")}</b>
      </a>
      <div className="ccard">
        <span className="ci">
          <Icon name="pin" />
        </span>
        <small>Address</small>
        <b>{h.s("address")}</b>
      </div>
      <div className="ccard">
        <span className="ci">
          <Icon name="clock" />
        </span>
        <small>Working Hours</small>
        <b>{h.s("working_hours")}</b>
      </div>
    </div>
  );
}

export function MapFrame({ h, src }: { h: H; src: unknown }) {
  const s = String(src ?? "");
  if (!/^https:\/\//i.test(s)) return null;
  return (
    <div className="map">
      <iframe src={s} title={`Map – ${h.s("business_name")}`} loading="lazy" referrerPolicy="no-referrer-when-downgrade" allowFullScreen />
    </div>
  );
}

export function CtaBand({ h, heading, text }: { h: H; heading: string; text: string }) {
  const bg = String(h.s("cta_image", ""));
  return (
    <section className="cta-wrap">
      <div className="wrap">
        <div className="cta-band">
          {bg && <img className="cta-bg" src={img(bg)} alt="" loading="lazy" />}
          <div className="cta-txt">
            <h2>{heading}</h2>
            {text && <p>{text}</p>}
          </div>
          <div className="cta-btns">
            <button type="button" className="btn btn-quote" data-popup="" data-item="">
              {h.s("buttons.quote_text", "Get Free Quote")}
            </button>
            <CallBtn h={h} text={String(h.s("mobile"))} />
          </div>
        </div>
      </div>
    </section>
  );
}

export function ContactList({ h }: { h: H }) {
  return (
    <ul className="contact-list">
      <li>
        <span className="ci">
          <Icon name="phone" />
        </span>
        <span>
          <small>Call / WhatsApp</small>
          <a href={h.tel} data-track="call">
            {h.s("mobile")}
          </a>
        </span>
      </li>
      <li>
        <span className="ci">
          <Icon name="mail" />
        </span>
        <span>
          <small>Email</small>
          <a href={`mailto:${h.s("email")}`}>{h.s("email")}</a>
        </span>
      </li>
      <li>
        <span className="ci">
          <Icon name="pin" />
        </span>
        <span>
          <small>Address</small>
          <b>{h.s("address")}</b>
        </span>
      </li>
    </ul>
  );
}
