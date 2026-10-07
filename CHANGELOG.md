# Changelog

## 1.2.1 (2026-10-07)

### Fixed

- README: the module requires `softaware/module-core` ^1.0 (the README said ^1.2; composer.json was right).

## 1.2.0 (2026-10-07)

### Added

- Approval queue: keyword search (name, email address and the other indexed customer fields).

### Changed

- Approval queue: approval status shown as Magento status badges (Pending, Approved, Rejected), a one-line
  introduction, and, when no account is waiting, a short explanation with an "Open Settings" button instead of an
  empty table. Long email addresses wrap instead of widening the grid; the Website column is hidden by default
  (available under Columns) so the grid fits at 1024 px.
- Customer grid: the "Approval Status" column shows the same status badges; mass actions renamed to
  "Approve Account" / "Reject Account".
- Confirmation dialogs titled "Approve Account" / "Reject Account"; the queue's row "Reject" confirmation explains
  how to add a reason. Row action "View Customer".
- Notice bar and mass-action messages use proper singular/plural wording ("1 customer account is waiting for
  approval", "3 customer accounts approved") instead of "account(s)".
- Settings: Approval Rules opens first, every field explains what it does and its default, "Recipients" is
  validated as a comma-separated list of email addresses, and every field can be reset to its default
  ("Use system value").

### Fixed

- Approval queue: the status badges were not shown (raw status codes such as "pending" were displayed).

## 1.1.0 (2026-10-06)

### Changed

- Requires `softaware/module-core` instead of `softaware/module-base`. The admin menu and ACL now sit under `Softaware_Core::core` ("Softaware"); roles that had access keep it (migrated by module-core). After updating all SoftAware modules, `softaware/module-base` can be removed.

## 1.0.1 (2026-10-06)

### Security
- Accounts created directly through the customer repository on the storefront (Softaware Social Login sign-ups,
  other modules) got no approval status, which counts as approved, so they skipped approval entirely. New
  customers saved through the repository now get their status from the approval rules too (a status sent with
  the customer is ignored), and the shop team is notified.
- GraphQL `generateCustomerTokenAsAdmin` (Login as Customer for headless storefronts) issued tokens for pending
  and rejected customers. It is refused now, like the storefront "Login as Customer".
- "Login as Customer" and other sign-ins by customer id ignored the approval switch when it was enabled on a
  website only (the default scope was read). The customer's own website is used now.

### Fixed
- Rejecting or setting a customer back to pending now also ends their "Remember Me" (persistent cart) session.
- Email confirmation link of an account that still needs approval: the customer saw "Thank you for registering"
  next to the approval message and was sent to an empty account page; now only the approval message is shown on
  the sign-in page.
- Registrations outside the registration form (e.g. social sign-up) show the "needs approval" registration
  message instead of the "waiting for approval" error.
- Approved email: no longer tells customers to use "the password you chose", which customers who signed up
  through a social login do not have.

## 1.0.0

- Approval of new customer accounts per website: all accounts or selected customer groups, auto-approve groups
  and email domains; existing customers approved on install.
- Sign-in blocked for pending/rejected customers on Luma and Hyvä forms, checkout login, REST and GraphQL tokens,
  and password-less sign-ins.
- Registration flow message/redirect, registration form notice (Luma and Hyvä).
- Admin: customer grid column, filter and mass actions; approve/reject buttons with reason; approval queue;
  pending count in menu and notice bar.
- Emails to the shop team and to customers (approved/rejected); REST API; CLI.
