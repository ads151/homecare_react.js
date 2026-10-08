import type { FormField } from "@/lib/api";
import { formToken } from "@/lib/token";
import { Icon, type H } from "@/lib/site";

let counter = 0;

/**
 * Enquiry form — same markup as the original site, so the original
 * main.js handles validation and sending (to /api/enquiry).
 */
export default function EnquiryForm({ h, id, name, short = false, item = "", submitText }: { h: H; id: string; name: string; short?: boolean; item?: string; submitText?: string }) {
  const n = ++counter;
  const fields: FormField[] = h.site.form_fields.length
    ? h.site.form_fields
    : [
        { name: "name", label: "Your Name", type: "text", required: true, options: [], short_form: true },
        { name: "mobile", label: "Mobile Number", type: "tel", required: true, options: [], short_form: true },
      ];
  const today = new Date(Date.now() + 5.5 * 3600 * 1000).toISOString().slice(0, 10);
  const note = String(h.s("privacy_note", ""));
  const btnBg = h.s("buttons.submit_bg", "");
  const btnColor = h.s("buttons.submit_color", "");

  return (
    <form className="hc-form" id={id} action="/api/enquiry" method="post" noValidate>
      <input type="hidden" name="form_name" value={name} />
      <input type="hidden" name="enquiry_item" value={item} />
      <input type="hidden" name="page_url" value="" />
      <input type="hidden" name="hc_ts" value={formToken()} />
      <div className="hp" aria-hidden="true">
        <label>
          Website
          <input type="text" name="website" tabIndex={-1} autoComplete="off" />
        </label>
      </div>
      <div className="fields">
        {fields
          .filter((f) => !(short && f.short_form === false))
          .map((f) => {
            const fid = `f${n}_${f.name}`;
            const wide = f.type === "textarea" || f.wide ? " wide" : "";
            const ph = f.placeholder || undefined;
            return (
              <div className={"field" + wide} key={f.name}>
                <label htmlFor={fid}>
                  {f.label}
                  {f.required && (
                    <>
                      {" "}
                      <i>*</i>
                    </>
                  )}
                </label>
                {f.type === "textarea" && <textarea id={fid} name={f.name} rows={3} placeholder={ph} required={f.required} />}
                {f.type === "select" && (
                  <select id={fid} name={f.name} required={f.required} data-select="" defaultValue={item && f.options.includes(item) ? item : ""}>
                    <option value="">{f.placeholder || "Select"}</option>
                    {f.options.map((o) => (
                      <option key={o}>{o}</option>
                    ))}
                  </select>
                )}
                {f.type === "tel" && (
                  <>
                    <input id={fid} type="tel" name={f.name} inputMode="numeric" maxLength={10} pattern="[6-9][0-9]{9}" autoComplete="tel-national" data-mobile="" placeholder={ph} required={f.required} />
                    <small className="msg">Please enter a valid 10-digit mobile number (e.g. 9876543210).</small>
                  </>
                )}
                {f.type === "email" && (
                  <>
                    <input id={fid} type="email" name={f.name} autoComplete="email" placeholder={ph} required={f.required} />
                    <small className="msg">Please enter a valid email address.</small>
                  </>
                )}
                {f.type === "date" && (
                  <>
                    <input id={fid} type="date" name={f.name} min={today} required={f.required} />
                    <small className="msg">Please choose today or a future date.</small>
                  </>
                )}
                {!["textarea", "select", "tel", "email", "date"].includes(f.type) && (
                  <input id={fid} type="text" name={f.name} maxLength={150} autoComplete={f.name === "name" ? "name" : undefined} placeholder={ph} required={f.required} />
                )}
                {!["tel", "email", "date"].includes(f.type) && <small className="msg">This field is required.</small>}
              </div>
            );
          })}
      </div>
      <div className="form-error" role="alert" hidden />
      <button type="submit" className="btn btn-submit" style={btnBg || btnColor ? { background: btnBg || undefined, color: btnColor || undefined } : undefined} data-sending={h.s("buttons.sending_text", "Sending…")}>
        {submitText || h.s("buttons.submit_text", "Submit")}
      </button>
      {note && (
        <p className="privacy">
          <Icon name="lock" /> {note}
        </p>
      )}
    </form>
  );
}
