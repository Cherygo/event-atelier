---
name: Event Atelier
description: Editorial–Estate event planning, with cream paper and a practical forest-green working room.
colors:
  paper: "#f8f5ed"
  white: "#fffdf7"
  forest: "#243f31"
  forest-hover: "#355440"
  ink: "#263d30"
  muted: "#65715f"
  line: "#d5d9c9"
  sage: "#e9eddf"
  rose: "#efded5"
  rose-ink: "#7c4e43"
  gold: "#e1cfaa"
  light-ink: "#c7d3bb"
typography:
  display:
    fontFamily: "'Atelier Display', Georgia, serif"
    fontSize: "clamp(64px, 6.6vw, 96px)"
    fontWeight: 400
    lineHeight: 0.94
    letterSpacing: "-0.025em"
  headline:
    fontFamily: "'Atelier Display', Georgia, serif"
    fontSize: "52px"
    fontWeight: 400
    lineHeight: 1.04
    letterSpacing: "-0.025em"
  workspace-title:
    fontFamily: "'Atelier Display', Georgia, serif"
    fontSize: "40px"
    fontWeight: 400
    lineHeight: 1.04
    letterSpacing: "-0.025em"
  panel-title:
    fontFamily: "'Atelier Display', Georgia, serif"
    fontSize: "29px"
    fontWeight: 400
    lineHeight: 1.04
    letterSpacing: "-0.025em"
  body:
    fontFamily: "'Atelier Sans', system-ui, sans-serif"
    fontSize: "16px"
    fontWeight: 400
    lineHeight: 1.5
  button:
    fontFamily: "'Atelier Sans', system-ui, sans-serif"
    fontSize: "15px"
    fontWeight: 600
    lineHeight: 1.35
  tag:
    fontFamily: "'Atelier Sans', system-ui, sans-serif"
    fontSize: "12px"
    lineHeight: 1.4
rounded:
  control: "4px"
  segmented: "6px"
  panel: "8px"
  sample: "12px"
spacing:
  icon-gap: "12px"
  control-gap: "20px"
  form-inset: "24px"
components:
  button-primary:
    backgroundColor: "{colors.forest}"
    textColor: "{colors.white}"
    typography: "{typography.button}"
    rounded: "{rounded.control}"
    padding: "12px 20px"
  button-primary-hover:
    backgroundColor: "{colors.forest-hover}"
  button-outline:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    typography: "{typography.button}"
    rounded: "{rounded.control}"
    padding: "12px 20px"
  button-outline-hover:
    backgroundColor: "{colors.sage}"
  button-light:
    backgroundColor: "{colors.gold}"
    textColor: "{colors.forest}"
    typography: "{typography.button}"
    rounded: "{rounded.control}"
    padding: "12px 20px"
  button-light-hover:
    backgroundColor: "#ecdcba"
  text-link:
    textColor: "{colors.ink}"
  input:
    backgroundColor: "{colors.white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.control}"
    padding: "10px 12px"
  tag-rose:
    backgroundColor: "{colors.rose}"
    textColor: "{colors.rose-ink}"
    typography: "{typography.tag}"
    rounded: "{rounded.control}"
    padding: "5px 10px"
  navigation-active:
    backgroundColor: "#dbe4ce"
    textColor: "#263f2e"
    rounded: "5px"
    padding: "13px 12px"
  budget-panel:
    backgroundColor: "#eef0e5"
    textColor: "{colors.ink}"
    rounded: "{rounded.panel}"
    padding: "23px 25px"
---

# Design System: Event Atelier

## Overview

**Creative North Star: "Editorial–Estate"**

Calm cream paper, architectural arches, muted occasion photography, and forest-green fields give Event Atelier a formal editorial character. Expressive serif names and headlines establish identity; readable sans-serif controls keep the planning work practical. The same world accommodates weddings, company gatherings, and private occasions.

This records the implemented homepage and interactive sample workspace, grounded in `resources/css/atelier.css` and the Atelier Vue components. The samples demonstrate a visual and interaction direction; they do not establish persisted planning, production sharing, or completed backend workflows. Existing Breeze authentication pages still use their scaffold styling and are outside the applied system.

**Key Characteristics:**

- Cream paper and deep forest surfaces with restrained rose and gold accents.
- Editorial serif headings paired with readable sans-serif working text.
- Architectural arches at identity moments; compact, ruled planning surfaces.
- Visible state, clear focus, and layouts adapted to laptop and phone use.

## Colors

The palette combines warm paper with garden greens, using restrained warm accents to distinguish moments and statuses. Frontmatter values are normative; names below refer to those tokens.

### Primary

Forest anchors primary actions, the workspace navigation, and event banners. Forest Hover supplies the primary button hover state. Light Ink supports secondary text on dark fields; White supplies their main text.

### Secondary

Sage provides quiet supporting surfaces. Rose and Rose Ink form the warm status pair. Gold supports the light action variant on dark sections; it is not a universal success or warning color.

### Neutral

Paper is the page canvas; White is the brighter input and sample surface. Ink is the main working text, Muted supports secondary information, and Line divides tasks, sections, and financial rows.

**The Scoped Palette Rule.** The CSS custom properties live on the homepage and workspace wrappers, not on `:root`. New children need that scope or explicit token values; do not assume the authentication scaffold inherits this palette.

The sidecar's tonal strips are derived previews for the documentation panel, not additional implemented color tokens.

## Typography

Atelier Display is the locally hosted Libre Caslon Display face, falling back to Georgia and serif. Atelier Sans is locally hosted Source Sans 3, falling back to system-ui and sans-serif. The source supplies a display face at regular weight and regular/semibold sans faces; the existing italic treatment uses the browser's rendered italic style.

The pairing gives occasion names and invitations a magazine-like voice while leaving navigation, labels, tasks, and budget figures easy to scan. Frontmatter records the default desktop roles, not a mathematical type scale.

- Display: homepage hero; its size contracts at responsive breakpoints.
- Headline: homepage section headings.
- Workspace title: the current planning section.
- Panel title: task and planning sections.
- Body: the baseline working text; paragraphs are capped at 72 characters where the shared paragraph rule applies.
- Button and tag: compact sans-serif action and status roles.

At phone widths the hero is (65px), falling to (57px) at the narrowest breakpoint; homepage section headings are typically (41px), and workspace titles (35px). Budget amounts use tabular numerals where figures align for comparison.

**The Working Type Rule.** Keep actions and dense planning records in the sans face; use the serif to establish identity and section hierarchy.

## Layout

Public content uses a centered container capped at (1440px), typically with (5%) horizontal gutters. Homepage sections have generous desktop vertical padding (88px), falling to (51px) with (22px) side gutters on phones. These are observed surface defaults, not a rule that every screen must share the homepage composition.

The desktop workspace pairs a (236px) forest sidebar with a flexible working canvas; the sidebar narrows to (210px) below (1150px). The main area is capped at (1440px), with default padding (30px 34px 0). Task and budget sections share a practical two-column layout, and repeated rows use thin rules rather than individual floating cards.

Below (900px), navigation becomes a disclosure above the workspace. Below (640px), the main planning columns and forms stack, task metadata wraps, and summary metrics use two columns. Financial tables retain a horizontal scroll container. The narrow-screen refinement is (360px); a wide-screen adjustment begins at (1600px).

Preserve the information order as columns collapse. The homepage sample, workspace navigation, and budget content have their own responsive behavior; do not equate responsive design with uniformly shrinking type.

## Elevation & Depth

The implemented system is flat: tonal fields, thin borders, photography, and whitespace supply depth. It defines no decorative box-shadow vocabulary. Focus uses an offset warm outline (3px solid #987249, offset 4px), making interaction visible without adding surface elevation.

Buttons change background and settle by one pixel over the shared exponential ease (`cubic-bezier(0.16, 1, 0.3, 1)`). In-page navigation scrolls smoothly, mobile navigation reveals progressively, and capability descriptions expand within the document flow. The homepage occasion transition uses opacity and a small blur over (0.18s) with the same ease. Reduced-motion preferences remove transitions and use immediate scrolling.

## Shapes

Controls have gently curved corners; larger planning panels gain slightly more rounding. The frontmatter holds the recurring control, segmented-container, panel, and sample radii. One-pixel rules provide the working structure.

The arch is the identity signature: the brand enclosure, venue imagery, selected vendor initials, and invitation sections repeat a rounded crown with a flat base. Keep it at these expressive moments. Ordinary inputs, tasks, and data tables stay straightforward rectangles.

Outline icons use a (24 × 24) view box, rounded caps and joins, and a (1.5px) stroke. Their ordinary rendered size is (20px), with smaller contextual variants. The “a.” mark is a typographic brand monogram, not a replacement for interface icons.

## Components

### Buttons and links

Primary, outline, and light buttons share the same compact shape, semibold text, and icon alignment. The default button has a minimum height (48px); workspace toolbar variants use (44px) with tighter padding. The outline variant has a muted green border (#a8b19c). The light variant is gold on dark fields; the homepage's final invitation deliberately uses the forest treatment.

Text links carry an inline arrow and underline on hover, with a minimum height (44px). Icon-only controls normally occupy (44 × 44px). Disabled buttons reduce opacity and use a not-allowed cursor. All interactive variants retain the shared visible focus treatment.

### Segmented controls and status tags

Occasion and task-filter controls sit within a subdued green track. The selected button uses forest and white and exposes its pressed state. Unselected buttons gain a quiet tonal hover.

Status tags are compact and rectangular, not large pills. The default neutral tag, completed/booked green tag, and rose attention tag always include readable text; color is supporting information.

### Cards and containers

The sample plan uses a bright paper surface with a border and a larger corner radius. The budget summary uses a sage-tinted field without a shadow. Task lists stay ruled rows, and vendor comparisons use aligned information with explicit actions. Do not promote every row to a card.

### Inputs and fields

Inputs use bright paper, ink text, a muted green border (#aab49d), and the control radius. Inline fields have a minimum height (44px). Labels remain visible; placeholder text supplements them. Invalid task input gains a warm border (#a55346) and a textual error (#853c30), preserving meaning beyond color.

### Navigation

The public header stays available while scrolling and marks the section currently in view with a restrained underline. Its links follow the page's reading order and scroll to their destination. The phone disclosure opens within the same header and preserves the same destinations.

The forest workspace sidebar uses muted light text for inactive items, a deeper green hover, and a pale green active row with dark text. Active navigation exposes the current page. The phone disclosure preserves the same destinations and state rather than substituting a separate navigation model.

### Occasion and task patterns

The photographic event banner leads with the event name, followed by place and event-type metadata. Names should remain the visual anchor. The homepage's supporting photograph and caption follow its selected occasion.

Native checkboxes drive task rows; completed tasks gain a strike-through and muted text while the summary updates. The homepage illustration labels its figures as sample content. Workspace changes are temporary and belong to the selected example, not to a persisted customer event.

## Do's and Don'ts

### Do:

- Do use the same cream-and-forest identity across public and planning surfaces.
- Do give task text, labels, figures, and action states clear sans-serif treatment.
- Do preserve visible keyboard focus, meaningful state labels, and reduced-motion behavior.
- Do keep occasion names prominent and supporting metadata subordinate.
- Do identify illustrative plans and temporary interactions as samples.

### Don't:

- Don't turn every working row into an elevated card.
- Don't use color alone to communicate selection, completion, or an error.
- Don't treat the sample interface as evidence of persisted backend features.
- Don't assume the scoped Atelier tokens apply to the existing authentication pages.
