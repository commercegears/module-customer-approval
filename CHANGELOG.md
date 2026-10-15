# Changelog

## 2.1.0 (2026-10-15)
New: German translation of the customer-facing texts and e-mails.

## 2.0.2 (2026-10-12)
- Improvement: compatible with Magento 2.4.6 (PHP 8.2).

## 2.0.1 (2026-10-12)
- Improvement: user guide screenshots and links updated for the CommerceGears name

## 2.0.0 (2026-10-11)
- Improvement: renamed to CommerceGears (new Composer package, admin menu and settings paths)

## 1.2.4 (2026-10-11)
- Improvement: the approval queue has its own "nothing found" texts for a search or filter without results.
- Improvement: storefront messages and auto-approve domains are checked when saved (length, no HTML).

## 1.2.3 (2026-10-11)
- Fix: "Approve" in the approval queue no longer opens a 404 page and now asks for confirmation.

## 1.2.2 (2026-10-08)
- New: product listing, FAQ and user guide.

## 1.2.1 (2026-10-07)
- Fix: the README states the correct CommerceGears Core version.

## 1.2.0 (2026-10-07)
- New: keyword search on the approval queue.
- Improvement: status badges, short introduction and empty-state help on the approval queue.
- Improvement: clearer confirmations and messages with proper singular and plural wording.
- Improvement: settings explain every field and its default; recipients are checked as email addresses.

## 1.1.0 (2026-10-06)
- Improvement: requires CommerceGears Core instead of CommerceGears Base; menu and permissions move under "CommerceGears".

## 1.0.1 (2026-10-06)
- Fix: accounts created by other modules (for example social sign-up) now go through approval too.
- Fix: pending and rejected customers can no longer get in through "Login as Customer" tokens.
- Fix: rejecting a customer ends their "Remember Me" session.
- Improvement: clearer messages after email confirmation.

## 1.0.0 (2026-10-06)
- New: first release with approval of new accounts per website, auto-approve groups and email domains.
- New: sign-in blocked for pending and rejected customers on Luma, Hyvä, checkout, REST and GraphQL.
- New: approval queue, customer grid column and mass actions, approve and reject buttons with a reason.
- New: emails to the shop team and to customers, REST API and command line tools.
