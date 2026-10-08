"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

export type MenuItem = { label: string; href: string; internal: boolean };

/** Main menu — highlights the page you are on, also after changing page without reload. */
export default function NavMenu({ items }: { items: MenuItem[] }) {
  const path = usePathname() || "/";
  return (
    <ul className="">
      {items.map((m, i) => {
        const active = m.internal && (m.href === "/" ? path === "/" : path === m.href);
        const props = { className: active ? "active" : undefined, "aria-current": active ? ("page" as const) : undefined };
        return (
          <li key={i}>
            {m.internal ? (
              <Link href={m.href} {...props}>
                {m.label}
              </Link>
            ) : (
              <a href={m.href} {...props}>
                {m.label}
              </a>
            )}
          </li>
        );
      })}
    </ul>
  );
}
