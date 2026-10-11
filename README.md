# Softaware Customer Approval

`softaware/module-customer-approval` (Magento module `Softaware_CustomerApproval`)

New customer accounts wait for approval by the shop team before they can sign in. Works with Luma and Hyvä.

Documentation: [User guide](docs/user-guide.md) · [FAQ](docs/faq.md)

## Features

- **Approval rules per website**: approval for all new accounts or only for selected customer groups;
  auto-approve customer groups; auto-approve email domains (`yourcompany.co.uk`, `*.example.com`).
  Accounts created by an admin, through the admin/integration API or from the CLI are approved straight away.
  Every other new account gets its status from the rules, however it is created (registration form, checkout,
  REST, GraphQL, social login or another module saving through the customer repository).
  Customers that exist when the module is installed are approved.
- **Pending and rejected customers cannot sign in**, with a clear message:
  login form (Luma and Hyvä), checkout "Sign In" popup, REST (`POST /V1/integration/customer/token`) and GraphQL
  (`generateCustomerToken`) token generation, RSS feed login. Password-less sign-ins (social login, email
  confirmation link, auto sign-in after registration, Login as Customer on the storefront and GraphQL
  `generateCustomerTokenAsAdmin`, store switching) are refused as well.
- **After registration** the customer is not signed in: the "Thank you for registering" message is replaced by
  an approval message and the customer is sent to the login page or a CMS page of your choice.
  Optional notice on the registration form.
- **Admin**: "Approval Status" column and filter in the customer grid, mass approve/reject in the customer grid,
  Approve / Reject buttons and a status notice on the customer page (reject asks for an optional reason),
  an **Approval Queue** grid (pending and rejected accounts, keyword search, filters, status badges), the number
  of pending accounts in the menu, and a notice bar message while accounts are waiting.
- **Emails**: to the shop team when an account is waiting (configurable recipients); to the customer when approved
  or rejected (optionally with the reason). Templates can be customised under Marketing > Email Templates.
- Rejecting (or setting back to pending) signs the customer out of the storefront, ends "Remember Me" sessions and
  revokes their API tokens.
- Customers cannot change their own status (the attributes are not in any form and are protected on save).
- Admin REST API and CLI.

## Where in the admin

- Softaware > Customer Approval > Approval Queue (with pending count) / Settings
- Customers > All Customers: "Approval Status" column (status badges), Actions > Approve Account / Reject Account
- Customer edit page: Approve / Reject buttons, status notice
- Settings: Stores > Configuration > Softaware > Customer Approval (`softaware_customerapproval/*`)
- ACL: Softaware > Customer Approval > Approve and Reject Customers / Settings

## Configuration

| Path | Scope | Default |
|---|---|---|
| `softaware_customerapproval/general/enabled` | website | 0 |
| `softaware_customerapproval/general/mode` (`all` / `groups`) | website | all |
| `softaware_customerapproval/general/groups` | website | – |
| `softaware_customerapproval/general/auto_approve_groups` | website | – |
| `softaware_customerapproval/general/auto_approve_domains` | website | – |
| `softaware_customerapproval/frontend/*` (notice, messages, redirect page) | store view | see config.xml |
| `softaware_customerapproval/admin_notification/*` (enabled, recipients, template) | website | on, no recipients |
| `softaware_customerapproval/customer_notification/*` (sender, approved/rejected emails, include reason) | store view | on |

After installing, run `bin/magento indexer:reindex customer_grid` so the customer grid gets the new column.

## CLI

```
bin/magento softaware:customer-approval:status                          # list pending accounts
bin/magento softaware:customer-approval:status --status=rejected
bin/magento softaware:customer-approval:status jane@example.com --set=approved [--no-email]
bin/magento softaware:customer-approval:status 42 --set=rejected --reason="Trade customers only"
bin/magento softaware:customer-approval:status 42 --set=pending
```

## REST API (admin token, ACL `Softaware_CustomerApproval::manage`)

```
GET  /V1/softaware/customer-approval/:customerId            -> "pending" | "approved" | "rejected"
POST /V1/softaware/customer-approval/:customerId/approve     {"notify": true}
POST /V1/softaware/customer-approval/:customerId/reject      {"reason": "...", "notify": true}
POST /V1/softaware/customer-approval/:customerId/pending
```

Event `softaware_customer_approval_status_changed` (customer, previous_status, status, reason) is dispatched on
every change.

## Hyvä

The registration notice has a Hyvä template (`hyva_customer_account_create.xml`); the module registers itself for
the Tailwind build (`hyva_config_generate_before`), so run your theme's CSS build after installing. Login and
registration use Magento's controllers in both themes; checkout uses the Luma checkout (Hyvä Luma fallback).

## Compatibility

Magento Open Source / Adobe Commerce 2.4.7–2.4.9, PHP 8.2–8.5, Luma and Hyvä 1.3+.
Requires `softaware/module-core` ^1.3.

## Limitations

- Through the REST/GraphQL token endpoints Magento replaces every sign-in error with its generic message, and
  each refused attempt counts towards Magento's lockout for that account.
- Admin links in the shop-team email do not contain an admin secret key; with "Add Secret Key to URLs" on, the
  admin opens the dashboard first.
- Hiding prices from visitors who are not signed in is not part of this module.
