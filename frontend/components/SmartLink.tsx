import Link from "next/link";
import type { AnchorHTMLAttributes, ReactNode } from "react";

/** Website pages open without a page reload (Next.js Link); phone, WhatsApp,
 *  e-mail, outside links and the admin panel stay normal links. */
export function isInternal(href: string): boolean {
  return href.startsWith("/") && !href.startsWith("//") && !/^\/(api|admin|install|images|uploads)(\/|$)/.test(href);
}

export default function SmartLink({ href, children, ...rest }: AnchorHTMLAttributes<HTMLAnchorElement> & { href: string; children?: ReactNode }) {
  if (isInternal(href) && !rest.target) {
    return (
      <Link href={href} {...rest}>
        {children}
      </Link>
    );
  }
  return (
    <a href={href} {...rest}>
      {children}
    </a>
  );
}
