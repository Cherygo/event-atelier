# Budget implementation plan

## Scope

An event-scoped budget for weddings, corporate events, and private occasions. Owners, admins, and editors can maintain it; viewers can read it; outsiders receive no workspace data. No payment processing, currency conversion, bank integration, or public budget sharing.

## Delivery sequence

1. **Budget and expense planning** — explicit budget currency, optional target, categorized expense CRUD, estimates, notes, search, pagination, and a responsive ledger. Deliver precise amount/storage foundations separately from the complete expense-management workflow, with regression tests in each commit.
2. **Actual costs and payments** — optional actual cost, due date, individually recorded payments with dates and notes, outstanding balances, and overdue indicators. Deliver actual costs/due dates separately from payment recording and its permissions and safeguards against overpayment, deleting paid expenses, or reducing actual cost below recorded payments.
3. **Budget summaries** — planned versus actual/category totals, forecast (actual where known, estimate otherwise), remaining target, and event overview integration. Calculations are server-owned and independent of list filters/pagination.
4. **Finish verification** — full regression suite, production build, actual PostgreSQL migration checks, desktop/mobile screenshots, and independent visual review. Later fixes are their own focused commits. Push every completed batch.

## Data and safety decisions

- Store budget amounts as integer minor units; parse decimal input without floating-point arithmetic. Preserve zero as a genuine cost and null as unknown.
- Support EUR, USD, GBP, CAD, AUD, CHF, and NZD, each with two decimal places. Require an explicit choice; no locale-based assumption.
- One currency per event budget. Prevent changes while expense records exist. Do not automatically import or convert vendor quotes.
- Use event-scoped route bindings and recheck authorization under the event transaction lock for mutations. Never trust a client-supplied event ID.
- Payments are a manual record, not a financial transaction. Record date, amount, and optional note; derive payment status, rather than allowing a contradictory manual status.
- Additive migrations only. Do not reset, reseed, or roll back the user's database. Exercise rollback/refresh only in isolated test databases.
- Keep demo previews separate from persisted event budgets.

## Acceptance checks

- Empty setup, saved target, zero amount, unknown actual cost, fractional amounts, over-budget, unpaid/part-paid/paid, and overdue cases.
- Invalid amounts, excess precision, unsupported/stale currency, cross-event records, unauthorized writes, and hostile extra attributes.
- Exact totals across all event records, unaffected by filters or pagination; payment totals cannot exceed actual cost.
- Keyboard focus, inline errors, pending states, mobile wrapping, no page-wide horizontal overflow, reduced motion, readable numeric alignment.

## Progress

Completed on 2026-09-27. The delivery was split into the following reviewable commits:

| Commit | Delivered |
| --- | --- |
| `8238c7b` | Implementation plan and budget surface direction. |
| `57875f5` | Exact integer-minor-unit amounts, supported currencies, event budget settings, expense storage, factories, and amount tests. |
| `15306b4` | Authorized budget settings and categorized expense CRUD, responsive ledger, inline editor, search, filtering, pagination, and regression tests. |
| `71b34bc` | Actual costs and due dates, preserving unknown costs separately from zero, with validation and regression tests. |
| `55db28e` | Manual payment records/history, payment status and outstanding balances, overdue indicators, and paid-expense/overpayment safeguards with regression tests. |
| `fce2944` | Workspace action spacing, confirmation layout, and payment-status styling corrections. |
| `ffa23c8` | Server-calculated category totals and event forecasts, remaining target, overview integration, and aggregate regression tests. |

### Verification

- All four budget migrations are applied to the running PostgreSQL application; no migrations remain pending. No user database reset, reseed, or rollback was performed.
- Full backend suite: **135 tests, 1,193 assertions**, passing on SQLite and an isolated PostgreSQL test database. The temporary PostgreSQL test database was removed after verification.
- JavaScript unit suite: **8 tests passing**. Production frontend build passes. Vite's runtime-resolution notices for the three existing font assets are non-blocking; all three are served successfully by the application.
- Browser checks cover setup, expense creation/editing, search, payment recording/removal, and paid-expense deletion protection. No JavaScript page errors or document-wide horizontal overflow at 320, 390, 1280, and 1440 pixels.
- Seven final desktop/mobile screenshots cover the ledger, category breakdown, expense editor, payment history, and overview. Independent finish review: **ship**, with no material findings or further corrections required.
- Independent documentation comparison passes: the implementation preserves the incumbent palette, typography, components, and interaction patterns. Existing design-system files were left unchanged. Their pre-existing preview-only implementation-status prose remains outdated and was not rewritten as part of this extension.
- The temporary browser-review account and its sample event, expenses, and payments were permanently deleted through the authenticated account-deletion flow. Database counts returned to the pre-review baseline; existing user records were preserved.

## Next stage

Build the configurable, read-only shared planning page: owners control which information is exposed through a secure, revocable share link. Public guests must never receive private workspace data, and budgets remain hidden by default. Event-scoped collaborator roles and email invitations already exist; this stage adds deliberate public sharing, not another invitation system.
