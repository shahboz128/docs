# UI-kit — навбати электронии идораи нотариалӣ (Лаб 9)

Файли Figma: https://www.figma.com/design/bWMuMHAajRtmSG2LpC2QNL
(Share → «Anyone with the link — can view» -ро худатон гузоред.)

Саҳифаҳо (нақшаи Starter танҳо 3 саҳифа медиҳад):
1. **01 Flow + Wireframes** — user flow (3 нақш) + 6 wireframe
2. **02 UI-kit** — компонентҳо бо вариантҳо
3. **03 Mockups** — 6 mockup + прототип

## Рангҳо (мувофиқ ба Bootstrap 5.3)
| Услуб | HEX |
|---|---|
| primary | #0D6EFD |
| primary-dark | #0A58CA |
| secondary / text-muted | #6C757D |
| success | #198754 |
| danger | #DC3545 |
| warning | #FFC107 |
| text | #212529 |
| bg | #F8F9FA |
| border | #DEE2E6 |

Рангҳои сабзу сурху зард барои тоза (light) ва матни тира (success-dark #0F5132, danger-dark #842029, warning-dark #664D03, info-dark #055160, primary-deep #084298) дар Figma ҳамчун Color style сабт шудаанд.

## Типографика — Noto Sans (кириллицаи пурра)
H1 32/40 Bold · H2 24/32 Bold · H3 20/28 SemiBold · Body 16/24 · Body-strong 16/24 SemiBold · Small 14/20 · Label 14/20 Medium · Button 16/24 Medium · Ticket-number 56/64 Bold.

## Компонентҳо
Button (Type: Primary/Secondary/Outline/Danger; property Label) · Input (State: Default/Error; property Label) · Status badge (waiting, called, in_service, done, no_show, cancelled — бо матн, на танҳо ранг) · Slot chip (Free/Busy/Selected) · Navbar (Role: Guest/Client/Notary/Admin).

## Экранҳо (роутҳо мувофиқи ТФ)
/login · / (хизматҳо) · /book · /ticket · /notary · /admin/schedule

## Санҷиши контраст (WCAG AA, ≥ 4.5:1)
| Ҷуфт | Нисбат |
|---|---|
| #212529 / #F8F9FA (матн/замина) | 14.63 |
| #FFFFFF / #0D6EFD (тугмаи Primary) | 4.50 |
| #6C757D / #FFFFFF (матни ёрирасон) | 4.69 |
| #664D03 / #FFF3CD (badge «Интизорӣ») | 7.21 |
| #FFFFFF / #DC3545 (тугмаи Danger) | 4.53 |

## Дастрасӣ
Матн ≥ 14–16px, тугмаҳо ва слотҳо баландии 44px, майдонҳо бо label, ҳолат бо матн (на танҳо ранг), хато бо матн + ҳошияи ғафс.

## Прототип
Flow starting points: Мизоҷ (/login), Нотариус (/notary), Маъмур (/admin/schedule). Пайвандҳо: Ворид шудан → хизматҳо → Сабт шудан → /book → Тасдиқи сабт → /ticket → Бекор кардан → хизматҳо.

## Экспорт
PNG 1x-и mockup-ҳо дар `screens/` ҳанӯз гузошта нашудааст — дар Figma: экранро интихоб → Export → PNG 1x.
