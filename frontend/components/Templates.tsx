import type { PageData } from "@/lib/api";
import { Button, CallBtn, Icon, Paras, SecHeadN, Title, WaBtn, img, on, plainTitle, show, type H } from "@/lib/site";
import Blocks from "./Blocks";
import EnquiryForm from "./EnquiryForm";
import { Areas, Boxes, CardsGrid, ContactCards, ContactList, CtaBand, Faq, Feat, MapFrame, PhotoCards, Stats, Steps, Testimonials, Ticks } from "./Sections";
import Shell from "./Shell";

type P = { h: H; page: PageData };

function Home({ h, page }: P) {
  const c = page.content;
  const sh = (n: number) => show(c[`section${n}_show`]);
  return (
    <Shell h={h} page={page} showCta={false}>
      <section className="hero">
        {c.hero_image && (
          <picture className="hero-bg">
            {c.hero_image_mobile && <source media="(max-width: 640px)" srcSet={img(c.hero_image_mobile)} />}
            <img src={img(c.hero_image)} alt={c.hero_image_alt || plainTitle(c.hero_title)} fetchPriority="high" decoding="async" />
          </picture>
        )}
        <div className="wrap">
          <div className="hero-txt">
            {c.hero_badge && <span className="pill">{c.hero_badge}</span>}
            <h1>
              <Title text={c.hero_title || h.s("business_name")} />
            </h1>
            <Paras text={c.hero_text} className="lead" />
            <Ticks items={c.hero_points} className="ticks" />
            <div className="hero-btns">
              <CallBtn h={h} text={c.hero_call_text || h.s("buttons.call_text", "Call Now")} />
              <WaBtn h={h} />
            </div>
            {c.hero_trust_line && (
              <p className="trust">
                <span className="stars">
                  {[0, 1, 2, 3, 4].map((k) => (
                    <Icon name="star" key={k} />
                  ))}
                </span>{" "}
                {c.hero_trust_line}
              </p>
            )}
          </div>
          {show(c.hero_form_show) && (
            <div className="form-card" id="book">
              <h2>{c.hero_form_heading || "Get a Free Call Back"}</h2>
              {c.hero_form_text && (
                <p className="sub">
                  <Title text={c.hero_form_text} />
                </p>
              )}
              <EnquiryForm h={h} id="heroForm" name="Home Page – Banner Form" short />
            </div>
          )}
        </div>
      </section>

      {sh(1) && <Stats items={c.section1_items} plain={false} />}

      {sh(2) && (
        <section className="sec" id="services">
          <div className="wrap">
            <SecHeadN c={c} n={2} />
            <CardsGrid h={h} filter={on(c.section2_filter)} limit={Number(c.section2_limit) || 0} />
            {c.section2_button_text && (
              <div className="center mt">
                <Button text={c.section2_button_text} link={c.section2_button_link ?? ""} className="btn btn-outline" />
              </div>
            )}
          </div>
        </section>
      )}

      {sh(3) && (
        <section className="sec bg-light" id="how-it-works">
          <div className="wrap">
            <SecHeadN c={c} n={3} />
            <Steps items={c.section3_items} />
          </div>
        </section>
      )}

      {sh(4) && (
        <section className="sec why" id="why-us">
          <div className="wrap two">
            <div className="why-img">
              {c.section4_image && <img src={img(c.section4_image)} alt={c.section4_image_alt || plainTitle(c.section4_heading)} loading="lazy" decoding="async" width={800} height={860} />}
              {c.section4_badge_big && (
                <div className="float">
                  <b>{c.section4_badge_big}</b>
                  <span>{c.section4_badge_text}</span>
                </div>
              )}
            </div>
            <div>
              <SecHeadN c={c} n={4} center={false} />
              <Feat items={c.section4_items} />
              {c.section4_button_text && (
                <div className="mt">
                  <Button text={c.section4_button_text} link={c.section4_button_link ?? "popup"} />
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {sh(5) && <CtaBand h={h} heading={c.section5_heading || h.s("cta_heading")} text={c.section5_text || h.s("cta_text")} />}

      {sh(6) && (
        <section className="sec" id="areas">
          <div className="wrap">
            <SecHeadN c={c} n={6} />
            <Areas items={c.section6_areas} />
          </div>
        </section>
      )}

      {sh(7) && (
        <section className="sec bg-light" id="testimonials">
          <div className="wrap">
            <SecHeadN c={c} n={7} />
            <Testimonials items={c.section7_items} />
          </div>
        </section>
      )}

      {sh(8) && (
        <section className="sec" id="faq">
          <div className="wrap">
            <SecHeadN c={c} n={8} />
            <Faq items={c.section8_items} />
          </div>
        </section>
      )}

      {sh(9) && (
        <section className="sec final" id="enquiry">
          <div className="wrap two">
            <div>
              {c.section9_small && <span className="eyebrow light">{c.section9_small}</span>}
              <h2>
                <Title text={c.section9_heading} />
              </h2>
              <Paras text={c.section9_text} />
              <ContactList h={h} />
            </div>
            <div className="form-card">
              <h2>{c.section9_form_heading || "Request a Call Back"}</h2>
              {c.section9_form_text && <p className="sub">{c.section9_form_text}</p>}
              <EnquiryForm h={h} id="homeForm" name="Home Page – Bottom Form" />
            </div>
          </div>
        </section>
      )}
    </Shell>
  );
}

function About({ h, page }: P) {
  const c = page.content;
  return (
    <Shell h={h} page={page} showCta={show(c.cta_show)}>
      {show(c.section1_show) && (
        <section className="sec">
          <div className="wrap two about-intro">
            <div className="about-img">{c.section1_image && <img src={img(c.section1_image)} alt={c.section1_image_alt || plainTitle(c.section1_heading)} loading="lazy" decoding="async" width={900} height={700} />}</div>
            <div>
              <SecHeadN c={c} n={1} center={false} />
              <Ticks items={c.section1_items} />
              {c.section1_button_text && (
                <div className="mt">
                  <Button text={c.section1_button_text} link={c.section1_button_link ?? "popup"} />
                </div>
              )}
            </div>
          </div>
        </section>
      )}
      {show(c.section2_show) && <Stats items={c.section2_items} plain />}
      {show(c.section3_show) && (
        <section className="sec">
          <div className="wrap">
            <SecHeadN c={c} n={3} />
            <Boxes items={c.section3_items} />
          </div>
        </section>
      )}
      {show(c.section4_show) && (
        <section className="sec bg-light">
          <div className="wrap">
            <SecHeadN c={c} n={4} />
            <PhotoCards items={c.section4_items} />
          </div>
        </section>
      )}
    </Shell>
  );
}

function Contact({ h, page }: P) {
  const c = page.content;
  return (
    <Shell h={h} page={page} showCta={show(c.cta_show)}>
      <section className="sec">
        <div className="wrap">
          <SecHeadN c={c} n={1} />
          <ContactCards h={h} />
          <div className="contact-main two">
            <div className="form-card">
              <h2>{c.form_heading || "Send Us a Message"}</h2>
              {c.form_text && <p className="sub">{c.form_text}</p>}
              <EnquiryForm h={h} id="contactForm" name="Contact Page Form" />
            </div>
            {show(c.map_show) && <MapFrame h={h} src={c.map_embed} />}
          </div>
        </div>
      </section>
    </Shell>
  );
}

function Listing({ h, page }: P) {
  const c = page.content;
  return (
    <Shell h={h} page={page} showCta={show(c.cta_show)}>
      <section className="sec">
        <div className="wrap">
          <SecHeadN c={c} n={1} />
          <CardsGrid h={h} filter={show(c.filter_show)} category={String(c.category_filter ?? "")} />
          {show(c.section2_show) && (
            <div className="custom-box">
              <div>
                <h2>{c.section2_heading}</h2>
                <Paras text={c.section2_text} />
              </div>
              <div className="cta-btns">
                <Button text={c.section2_button_text || "Enquire Now"} link={c.section2_button_link ?? "popup"} item={String(c.section2_item ?? "")} />
                <WaBtn h={h} item={String(c.section2_item ?? "")} />
              </div>
            </div>
          )}
        </div>
      </section>
    </Shell>
  );
}

function Legal({ h, page }: P) {
  const c = page.content;
  return (
    <Shell h={h} page={page} showCta={show(c.cta_show)}>
      <section className="sec">
        <div className="wrap narrow legal">
          {c.last_updated && <p className="updated">Last updated: {c.last_updated}</p>}
          <div dangerouslySetInnerHTML={{ __html: String(c.body ?? "") }} />
        </div>
      </section>
    </Shell>
  );
}

function ThankYou({ h, page, name, item }: P & { name: string; item: string }) {
  const c = page.content;
  const first = name ? name.split(/\s+/)[0] : "";
  return (
    <Shell h={h} page={page} showCta={false}>
      <section className="sec">
        <div className="wrap narrow">
          <div className="ty-box">
            <div className="ty-tick">
              <Icon name="check" />
            </div>
            <h2>{name ? String(c.heading_name || "Thank You, {name}!").replace("{name}", first) : c.heading_general || "Thank You!"}</h2>
            <p className="ty-msg">{item ? String(c.message_item || "We received your enquiry for {item}.").replace("{item}", item) : c.message_general || "We received your enquiry."}</p>
            {c.small_line && <p className="ty-small">{c.small_line}</p>}
            <div className="ty-btns">
              <a className="btn btn-ty" href={h.tel} data-track="call" style={{ background: h.s("thankyou.call_bg", "#1f5c99"), color: h.s("thankyou.call_color", "#fff") }}>
                <Icon name="phone" />
                <span>
                  {h.s("thankyou.call_text", "CALL NOW")} – {h.s("mobile")}
                </span>
              </a>
              <WaBtn h={h} item={item} />
              <a className="btn btn-outline" href="/">
                {h.s("thankyou.home_text", "Back to Home")}
              </a>
            </div>
          </div>
        </div>
      </section>
    </Shell>
  );
}

function Builder({ h, page }: P) {
  return (
    <Shell h={h} page={page} showCta={show(page.content.cta_show)}>
      <Blocks h={h} blocks={page.content.blocks} page={page.slug} />
    </Shell>
  );
}

export function NotFound({ h }: { h: H }) {
  const page: PageData = {
    slug: "404",
    title: "Page Not Found",
    template: "404",
    content: {
      page_name: "Page Not Found",
      hero_title: "Page Not Found",
      hero_text: "Sorry, the page you are looking for does not exist or has been moved.",
      hero_image: "images/pages/page-hero.jpg",
    },
    seo: { title: "", description: "", canonical: "", index: false, follow: true, og_title: "", og_description: "", og_image: "", jsonld: [] },
  };
  return (
    <Shell h={h} page={page} showCta={false}>
      <section className="sec">
        <div className="wrap narrow">
          <div className="ty-box">
            <div className="ty-tick err">404</div>
            <h2>Oops! This page is not available.</h2>
            <p className="ty-msg">The link may be old or typed wrongly. Please go back to the home page, or call us — we are happy to help.</p>
            <div className="ty-btns">
              <a className="btn btn-main" href="/">
                Go to Home Page
              </a>
              <CallBtn h={h} text={`${h.s("buttons.call_text", "Call Now")} ${h.s("mobile")}`} />
            </div>
          </div>
        </div>
      </section>
    </Shell>
  );
}

export default function Template({ h, page, query }: P & { query?: { name?: string; item?: string } }) {
  switch (page.template) {
    case "home":
      return <Home h={h} page={page} />;
    case "about":
      return <About h={h} page={page} />;
    case "contact":
      return <Contact h={h} page={page} />;
    case "listing":
      return <Listing h={h} page={page} />;
    case "legal":
      return <Legal h={h} page={page} />;
    case "thank-you": {
      const clean = (v?: string) => String(v ?? "").replace(/<[^>]*>/g, "").slice(0, 80).trim();
      return <ThankYou h={h} page={page} name={clean(query?.name)} item={clean(query?.item)} />;
    }
    default:
      return <Builder h={h} page={page} />;
  }
}
