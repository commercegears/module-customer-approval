# Customer Approval for Magento 2: FAQ

**Is approval switched on straight after installing?**
No. The module is installed switched off, so it does not suddenly block registrations. Turn it on per website under Stores > Configuration > Softaware > Customer Approval > Approval Rules > Enabled. Customers that exist when the module is installed are approved.

**Can only some customer groups need approval?**
Yes. Set "Require Approval For" to selected customer groups and choose the groups. The group is the one the new account is assigned to, by default the "Default Group" under Customers > Customer Configuration > Create New Account Options. With "All new accounts" you can instead pick groups that are approved straight away.

**Can I approve my own staff or known companies automatically?**
Yes, with "Auto-approve Email Domains": enter domains such as `yourcompany.co.uk`, one per line or comma-separated. `*.example.com` includes sub-domains.

**What does a pending customer see?**
After registering, the customer is not signed in. The usual "Thank you for registering" message is replaced by your approval message, and the customer is sent to the login page or a CMS page of your choice. When they try to sign in, they see the message for their status (pending or rejected). All texts can be changed per store view.

**Can a pending customer get in another way?**
No. Besides the sign-in form, the module refuses the checkout sign-in popup, REST and GraphQL customer tokens, the RSS feed login and password-less sign-ins such as social login, email confirmation links, the automatic sign-in after registration, Login as Customer and store switching. New accounts get their status from the rules however they are created, including checkout, REST, GraphQL and social login.

**Does it work with Hyvä?**
Yes. The registration notice has a Hyvä template, and sign-in and registration use Magento's own controllers in both themes, so the messages appear as usual. Checkout on Hyvä uses the Luma checkout fallback.

**Are accounts I create in the admin held for approval?**
No. Accounts created by an admin, through the admin or integration API, or from the command line are approved straight away.

**How do I approve or reject accounts?**
In the Approval Queue (Softaware > Customer Approval > Approval Queue), with the Approve and Reject buttons on the customer page, with the "Approve Account" and "Reject Account" mass actions in Customers > All Customers, with the admin REST API or with the `softaware:customer-approval:status` command.

**Can I tell the customer why the account was rejected?**
Yes. The Reject button on the customer page asks for an optional reason. With "Include Rejection Reason in Email" on, the reason is included in the "Account Not Approved" email.

**What happens when I reject an approved customer?**
The customer is signed out of the storefront, "Remember Me" sessions are ended and their API tokens are revoked. The same happens when you set a customer back to pending.

**Who gets told about new accounts?**
The addresses under "Email to Shop Team > Recipients" get an email for each new account waiting for approval. No email is sent while the field is empty. Customers get an email when their account is approved or not approved; both can be switched off.

**Can customers change their own status?**
No. The approval attributes are not in any customer form, and a status sent with a customer save (for example through the REST API) is ignored.

**Does it hide prices from visitors who are not signed in?**
No, that is not part of this module.
