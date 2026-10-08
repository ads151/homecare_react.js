import type { Data } from "@/lib/api";
import { Button, Paras, SecHead, Title, WaBtn, img, list, on, pick, plainTitle, show, type H } from "@/lib/site";
import EnquiryForm from "./EnquiryForm";
import Img from "./Img";
import { Areas, Boxes, CardsGrid, ContactCards, ContactList, CtaBand, Faq, Feat, MapFrame, PhotoCards, Stats, Steps, Testimonials, Ticks } from "./Sections";

/* Page Builder sections (added from Admin → Pages). Same design classes as the original site. */

type B = { type: string; data: Data };

const anchorOf = (d: Data) => String(d.anchor ?? "").replace(/[^a-z0-9_-]/gi, "") || undefined;

function Sec({ d, extra = "", children }: { d: Data; extra?: string; children: React.ReactNode }) {
  const cls = "sec" + (d.background === "light" ? " bg-light" : "") + (extra ? " " + extra : "");
  return (
    <section className={cls} id={anchorOf(d)}>
      {children}
    </section>
  );
}

const Head = ({ d, center = true }: { d: Data; center?: boolean }) => <SecHead small={d.small} heading={d.heading} text={d.text} center={center} />;

function Block({ h, b, i, page }: { h: H; b: B; i: number; page: string }) {
  const d = b.data || {};
  switch (b.type) {
    case "heading_text": {
      const center = d.align !== "left";
      return (
        <Sec d={d}>
          <div className={"wrap" + (center ? "" : " narrow")}>
            <Head d={d} center={center} />
            {d.button_text && (
              <div className={(center ? "center " : "") + "mt"}>
                <Button text={d.button_text} link={d.button_link ?? "popup"} />
              </div>
            )}
          </div>
        </Sec>
      );
    }
    case "rich_text":
      return (
        <Sec d={d}>
          <div className="wrap narrow legal">
            {d.heading && <SecHead small={d.small} heading={d.heading} center={false} />}
            <div dangerouslySetInnerHTML={{ __html: String(d.body ?? "") }} />
          </div>
        </Sec>
      );
    case "image_text": {
      const right = d.image_position === "right";
      const alt = d.image_alt || plainTitle(d.heading);
      const image = <div className="about-img">{d.image && <Img src={d.image} alt={alt} width={900} height={700} sizes="(max-width: 980px) 100vw, 560px" />}</div>;
      const text = (
        <div>
          <Head d={d} center={false} />
          <Ticks items={d.points} />
          {d.button_text && (
            <div className="mt">
              <Button text={d.button_text} link={d.button_link ?? "popup"} />
            </div>
          )}
        </div>
      );
      return (
        <Sec d={d}>
          <div className={"wrap two about-intro" + (right ? " img-right" : "")}>
            {right ? (
              <>
                {text}
                {image}
              </>
            ) : (
              <>
                {image}
                {text}
              </>
            )}
          </div>
        </Sec>
      );
    }
    case "stats":
      return <Stats items={d.items} plain />;
    case "icon_boxes":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <Boxes items={d.items} />
          </div>
        </Sec>
      );
    case "steps":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <Steps items={d.items} />
          </div>
        </Sec>
      );
    case "why":
      return (
        <Sec d={d} extra="why">
          <div className="wrap two">
            <div className="why-img">
              {d.image && <Img src={d.image} alt={d.image_alt || plainTitle(d.heading)} width={800} height={860} sizes="(max-width: 980px) 100vw, 560px" />}
              {d.badge_big && (
                <div className="float">
                  <b>{d.badge_big}</b>
                  <span>{d.badge_text}</span>
                </div>
              )}
            </div>
            <div>
              <Head d={d} center={false} />
              <Feat items={d.items} />
              {d.button_text && (
                <div className="mt">
                  <Button text={d.button_text} link={d.button_link ?? "popup"} />
                </div>
              )}
            </div>
          </div>
        </Sec>
      );
    case "services":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <CardsGrid h={h} filter={on(d.filter)} limit={Number(d.limit) || 0} category={String(d.category ?? "")} />
            {d.button_text && (
              <div className="center mt">
                <Button text={d.button_text} link={d.button_link ?? ""} className="btn btn-outline" />
              </div>
            )}
          </div>
        </Sec>
      );
    case "photo_cards":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <PhotoCards items={d.items} />
          </div>
        </Sec>
      );
    case "faq":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <Faq items={d.items} />
          </div>
        </Sec>
      );
    case "testimonials":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <Testimonials items={d.items} />
          </div>
        </Sec>
      );
    case "areas":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <Areas items={d.items} />
          </div>
        </Sec>
      );
    case "cta":
      return <CtaBand h={h} heading={d.heading || h.s("cta_heading", "")} text={d.text || h.s("cta_text", "")} />;
    case "form":
      return (
        <section className="sec final" id={anchorOf(d)}>
          <div className="wrap two">
            <div>
              {d.small && <span className="eyebrow light">{d.small}</span>}
              {d.heading && (
                <h2>
                  <Title text={d.heading} />
                </h2>
              )}
              <Paras text={d.text} />
              {show(d.show_contact) && <ContactList h={h} />}
            </div>
            <div className="form-card">
              <h2>{d.form_heading || "Request a Call Back"}</h2>
              {d.form_text && <p className="sub">{d.form_text}</p>}
              <EnquiryForm h={h} id={`blockForm${i}`} name={`${page.charAt(0).toUpperCase()}${page.slice(1).replace(/-/g, " ")} Page Form`} short={on(d.short_form)} item={String(d.enquiry_item ?? "")} />
            </div>
          </div>
        </section>
      );
    case "contact": {
      const formOn = show(d.form_show);
      const mapOn = show(d.map_show) && /^https:\/\//.test(String(d.map_embed ?? ""));
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <ContactCards h={h} />
            {(formOn || mapOn) && (
              <div className="contact-main two">
                {formOn && (
                  <div className="form-card">
                    <h2>{d.form_heading || "Send Us a Message"}</h2>
                    {d.form_text && <p className="sub">{d.form_text}</p>}
                    <EnquiryForm h={h} id="contactBlockForm" name="Contact Form" />
                  </div>
                )}
                {mapOn && <MapFrame h={h} src={d.map_embed} />}
              </div>
            )}
          </div>
        </Sec>
      );
    }
    case "map":
      if (!/^https:\/\//.test(String(d.map_embed ?? ""))) return null;
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <div className="map-full">
              <MapFrame h={h} src={d.map_embed} />
            </div>
          </div>
        </Sec>
      );
    case "custom_box": {
      const item = String(d.enquiry_item ?? "");
      return (
        <section className="sec sec-tight">
          <div className="wrap">
            <div className="custom-box">
              <div>
                <h2>{d.heading}</h2>
                <Paras text={d.text} />
              </div>
              <div className="cta-btns">
                <Button text={d.button_text || "Enquire Now"} link={d.button_link ?? "popup"} item={item} />
                <WaBtn h={h} item={item} />
              </div>
            </div>
          </div>
        </section>
      );
    }
    case "gallery":
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <div className="hc-gallery">
              {list(d.images)
                .filter((g) => pick(g, "image") !== "")
                .map((g, k) => (
                  <figure key={k}>
                    <Img src={pick(g, "image")} alt={pick(g, "alt") || pick(g, "caption")} width={800} height={600} sizes="(max-width: 640px) 100vw, 320px" />
                    {pick(g, "caption") && <figcaption>{pick(g, "caption")}</figcaption>}
                  </figure>
                ))}
            </div>
          </div>
        </Sec>
      );
    case "video":
      if (!d.youtube_id) return null;
      return (
        <Sec d={d}>
          <div className="wrap">
            <Head d={d} />
            <div className="hc-video">
              <iframe src={`https://www.youtube-nocookie.com/embed/${d.youtube_id}`} title={plainTitle(d.heading || "Video")} loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowFullScreen />
            </div>
          </div>
        </Sec>
      );
    case "html":
      return show(d.wrap) ? (
        <Sec d={d}>
          <div className="wrap" dangerouslySetInnerHTML={{ __html: String(d.code ?? "") }} />
        </Sec>
      ) : (
        <div dangerouslySetInnerHTML={{ __html: String(d.code ?? "") }} />
      );
    default:
      return null;
  }
}

export default function Blocks({ h, blocks, page }: { h: H; blocks: unknown; page: string }) {
  return (
    <>
      {list<B>(blocks)
        .filter((b) => b && b.type && show(b.data?.show))
        .map((b, i) => (
          <Block h={h} b={b} i={i} page={page} key={i} />
        ))}
    </>
  );
}
