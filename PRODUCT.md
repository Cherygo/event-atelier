# Event Atelier

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- Individuals and couples planning their own weddings or other gatherings.
- Corporate hosts coordinating company events.
- Professional event planners managing multiple client events and collaborating with clients and colleagues.

The product must accommodate both self-planners and professionals. Neither audience is an exclusive target.

## Product Purpose

Bring tasks, vendor comparisons, budgets, and shared planning information into one event workspace. Help hosts and planners coordinate decisions and collaborators without losing the details of an event.

Success means users can see what needs attention, compare their options, understand spending, and share the relevant plan with the people involved.

## Operating Context

- Responsive web use on laptops and mobile phones is required.
- One account can manage multiple event workspaces.
- Event types include weddings, corporate events, private parties, conferences, and custom occasions.
- Each event brings together its own planning records and collaborators.
- Public planning pages and private collaborator workspaces are separate experiences.
- The chosen implementation stack is Laravel, Inertia.js, Vue 3, JavaScript, and Tailwind CSS.

## Capabilities and Constraints

### Confirmed product scope

- Open self-service registration: anyone can register and create their own event workspace.
- Free access for everyone at launch. No paid plans or billing in the current scope; enterprise plans may be considered later.
- Event creation, switching, settings, and an overview of tasks, spending, deadlines, and vendor decisions.
- Task planning with categories, assignees, due dates, status, and event-type templates.
- Per-event vendor records, shortlists, quotes, comparison, and booking status.
- Budget categories, expenses, planned versus actual totals, payments, and due dates.
- Collaborator invitations and event-scoped roles: owner, planner/admin, editor, and viewer.
- A configurable read-only shared planning page. Public guests see only explicitly shared information; budget information is hidden by default.
- Keep core entities event-type neutral, with specialized behaviour in templates and presentation.

### Deferred scope

Organization-wide administration, enterprise billing, vendor marketplaces or portals, RSVP workflows, calendar integrations, advanced reporting, native mobile apps, and a separate platform administration panel are outside the agreed initial scope.

### Implementation status

The repository contains a Laravel authentication scaffold and interactive design previews. Workspace and homepage previews use sample data. Their planning interactions do not establish completed backend features or persistence.

The user selected one cohesive homepage and workspace combining directions 1 (Editorial) and 2 (Estate), with distinctive design balanced against practical usability. That combined interactive sample now replaces the alternative previews. Wedding and corporate examples have separate temporary task, vendor, and sharing state; planning persistence and production workspace workflows remain outstanding.

### Open decisions

- Future enterprise scope and commercial model.
- Production hosting, operational setup, and launch timing.
- Any specific accessibility conformance target beyond the confirmed laptop/mobile requirement.

## Brand Commitments

- Current project name: Event Atelier.
- The user requested an elegant, calm, editorial character, with Notion and a wedding magazine as initial references.
- Formal by default, with flexibility for more informal occasions.
- Preserve the user's selected Editorial–Estate direction and its soft beige/cream and forest-green foundation. Dusty rose was part of the original palette; restrained warm accents appear in the selected previews.
- The interface must have a distinctive identity while remaining useful for weddings and corporate events.

These are user-established commitments, not a new design specification. Detailed visual-system choices belong outside this product record.

## Evidence on Hand

- Agreed planning scope and contributor constraints: `AGENTS.md`.
- Cohesive workspace sample: `resources/js/Components/DesignPreview.vue`.
- Cohesive homepage: `resources/js/Components/HomePreview.vue`.
- Shared visual system: `resources/css/atelier.css`, `resources/js/Components/AtelierBrand.vue`, and `resources/js/Components/AtelierIcon.vue`.
- Illustrative event data: `resources/js/atelierDemo.js`.
- Sample workspace illustration: `resources/js/Components/PlanningIllustration.vue`.
- Preview photography: `public/images/preview-estate.jpg`, `public/images/preview-garden.jpg`, and `public/images/preview-table.jpg`.
- The preview names, vendors, budgets, dates, and progress figures are illustrative, not customer evidence.
- No verified testimonials, customer counts, case studies, or performance claims have been supplied. Do not invent them.

## Product Principles

1. Support self-planners and professionals within the same event workspace model.
2. Keep tasks, decisions, vendors, and spending understandable together.
3. Make collaboration deliberate: access and shared information must respect event boundaries.
4. Make laptop and phone use part of the core experience.
5. Keep the initial product free and focused, while preserving room for future organizations.
