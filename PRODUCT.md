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
- Local development is the current distribution model. Anyone evaluating the application runs their own checkout and database on their own machine. No hosted deployment is planned in the near term.

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

The application has persisted, event-scoped workspaces with open registration, authentication, sessions, ownership, collaborator roles, and email invitations for registered or new users. Tasks support categories, statuses, due dates, assignees, and overview metrics. Vendors support quotes, shortlisting, booking status, and side-by-side comparison. Budgets support categorized expenses, estimates, actual costs, manual payment records, forecasts, and overview summaries; the application does not process payments.

Owners can configure, preview, publish, replace, and revoke read-only shared planning pages. Guests receive only explicitly selected information. Budget sharing is off by default; internal notes, contacts, assignees, expense details, and payment records are never included. Ownership transfer revokes existing public links. Public sharing controls are owner-only, separate from collaborator permissions.

The user-selected Editorial–Estate design is implemented across the homepage, authentication, and responsive workspace. The separate interactive demo still uses illustrative, temporary data; it is not the persisted workspace.

Event-type task templates are implemented: optional wedding, corporate, and private-event starter checklists, selection before import, optional event-relative deadline suggestions, editable imported tasks, and duplicate-import protection. Creating an empty workspace remains supported. Templates are suggestions, not mandatory planning requirements. Owners, planners/admins, and editors can import; viewers cannot. Suggested dates are opt-in, past suggestions remain unscheduled, and imported dates do not move automatically when the event date changes. Renaming or completing an imported task does not make it importable again; deleting it does.

The initial local-release hardening pass includes fresh-volume Docker setup verification, a cross-feature planning regression journey, account-action throttling, no-store/no-referrer protection for account and invitation pages, a patched Markdown dependency, mobile-menu keyboard focus containment, and local backup/restore guidance. Desktop and standard phone checks are complete for the menu correction; the additional short-screen check and broader browser/assistive-technology coverage remain follow-up validation, not a claim of accessibility conformance.

Production hosting, deployment, and real email delivery are deferred. Sandbox email remains optional for local invitation and password-reset testing. Existing production image files are retained for future use, not an active delivery target. Access remains free; enterprise features, billing, and platform administration stay deferred.

### Open decisions

- Future enterprise scope and commercial model.
- Whether hosted deployment is ever needed; it is not required for the local release.
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
- Persisted workflows: `app/Http/Controllers/Event*Controller.php` and `resources/js/Pages/Events/`.
- Public sharing boundary: `app/SharedEventData.php` and `resources/js/Pages/Shared/Show.vue`.
- Regression coverage: `tests/Feature/` and `tests/Unit/`; operational setup and email-testing instructions: `README.md`.
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
