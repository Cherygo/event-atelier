# Budget implementation plan

## Scope

An event-scoped budget for weddings, corporate events, and private occasions. Owners, admins, and editors can maintain it; viewers can read it; outsiders receive no workspace data. No payment processing, currency conversion, bank integration, or public budget sharing.

## Delivery sequence

1. **Budget and expense planning** — explicit budget currency, optional target, categorized expense CRUD, estimates, notes, search, pagination, and a responsive ledger. Include backend, frontend, migration, and regression tests in one feature commit.
2. **Actual costs and payments** — optional actual cost, due date, individually recorded payments with dates and notes, outstanding balances, and overdue indicators. Include permissions and safeguards against overpayment, deleting paid expenses, or reducing actual cost below recorded payments in this feature commit.
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

- Existing vendor migrations checked against the running Docker PostgreSQL application: already applied; migrate reports nothing pending. Baseline: 89 backend tests pass, application health responds 200.
- Budget stages not yet delivered.
