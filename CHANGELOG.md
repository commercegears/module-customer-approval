# Changelog

## 1.2.4 (2026-10-11)
- Approval queue: its own "nothing found" texts for a search or filter without results.
- Storefront messages and auto-approve domains are checked on the server when saved (length, no HTML).

## 1.2.3 (2026-10-11)
- Approval queue: "Approve" asks for confirmation and no longer opens a 404 page.

## 1.2.2 (2026-10-08)
- Product listing, FAQ and user guide.

## 1.2.1 (2026-10-07)
- README: corrected the required softaware/module-core version.

## 1.2.0 (2026-10-07)
- Approval queue: keyword search, status badges, short introduction and empty-state help.
- Clearer confirmations and messages with proper singular and plural wording.
- Settings explain every field and its default; recipients are checked as email addresses.

## 1.1.0 (2026-10-06)
- Requires softaware/module-core instead of softaware/module-base; menu and permissions moved to "Softaware".

## 1.0.1 (2026-10-06)
- Accounts created by other modules (for example social sign-up) now go through approval too.
- Pending and rejected customers can no longer get in through "Login as Customer" tokens.
- Rejecting a customer ends their "Remember Me" session; clearer messages after email confirmation.

## 1.0.0 (2026-10-06)
- Approval of new accounts per website: all or selected groups, auto-approve groups and email domains.
- Sign-in blocked for pending and rejected customers on Luma, Hyvä, checkout, REST and GraphQL.
- Admin: approval queue, customer grid column and mass actions, approve and reject buttons with a reason.
- Emails to the shop team and to customers, REST API and CLI.
