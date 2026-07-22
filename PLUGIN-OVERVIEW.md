# Integration of Zoho People

**Plugin name:** Integration of Zoho People (internal code name `bitwelzp`)
**Version:** 1.0.2
**Built for:** WellQor
**Type:** Private, custom-built plugin for wellqor.com — not a public WordPress.org plugin

---

## 1. What this plugin is for, in one paragraph

WellQor keeps its staff records in **Zoho People** (an HR system). The website runs on **WordPress**. This plugin is the bridge between them. It does two jobs:

1. **Outward:** it pulls therapist records out of Zoho People and automatically builds a public profile page on the website for each eligible therapist.
2. **Inward:** it collects patient reviews on the website, holds them for administrator approval, and copies them into **Zoho Analytics** so the business can report on them.

None of this is manual content entry. Staff data lives in Zoho; the website mirrors it.

---

## 2. Getting connected (one-time setup)

An administrator opens the **"Zoho People"** menu in the WordPress admin sidebar, goes to the **Authorization** screen, and enters:

- Which Zoho data centre the account lives in (.com, .eu, .com.cn, .in, .com.au)
- A Client ID and Client Secret issued by Zoho
- (The website's homepage URL and redirect address are shown ready to copy into Zoho)

Clicking **Authorize** opens a Zoho login pop-up. The administrator grants permission — the plugin asks for full access to Zoho People *forms* — and Zoho hands back a permission code. The plugin swaps that for an **access pass** and a **renewal key**, both saved in the site's database.

**Staying connected:** the access pass is treated as good for 55 minutes. After that, the plugin silently renews it in the background before the next Zoho call. Administrators don't have to re-authorise. If renewal fails, the action stops with a "Failed to refresh access token" message.

**Two things to be aware of:**

- The **Zoho Analytics** side of the integration (used only for reviews) does *not* use these settings. Its credentials are written directly into the plugin's program files. See section 10.
- Although the data centre is configurable at sign-in, all the actual data requests are **hard-wired to the `.com` (US) Zoho region**. A European or Indian Zoho account would authenticate fine and then query the wrong region.

---

## 3. What happens after data is fetched from Zoho People — step by step

This is the heart of the plugin. The sequence below runs in full **every time** a sync happens.

### Step 1 — Something triggers a sync
There are three triggers, and they all run the same full process:

- **Daily**, automatically and unattended.
- **On demand**, when an admin clicks "Fetch data" on the All Employees screen.
- **After every single patient review submission** — the patient's browser waits while the entire sync runs.

### Step 2 — Employees are downloaded in batches
The plugin asks Zoho People for employee records **100 at a time**, then requests the next batch, and repeats until Zoho signals there are no more. Everything is held in memory before anything is saved.

### Step 3 — Everyone is filtered against three rules
A person is kept only if **all three** are true in Zoho People:

| Rule | Required value |
|---|---|
| Employee status | `Active` |
| Designation | `Clinical Therapist` **or** `Clinical Director` |
| Allow Telehealth Access | `true` |

Anyone failing any rule is **skipped entirely** — not stored, no page created.

> ⚠️ This is a *skip*, not a clean-up. If a therapist later leaves, changes role, or has telehealth switched off, the sync simply ignores them from then on. **Their existing database record and their published website page stay exactly as they are.** Someone has to switch that page off by hand (section 5). There is no automatic removal or archiving.

### Step 4 — The headshot photo is downloaded
For each kept person, the photo is downloaded from Zoho and saved into the WordPress uploads folder under whatever filename Zoho supplies. If Zoho has no photo or the download fails, the photo is left blank and the site later falls back to a generic placeholder image.

### Step 5 — A second Zoho lookup adds the clinical detail
The basic employee record doesn't carry the public-facing clinical information, so the plugin makes a **second, separate call per person** to Zoho People's *Clinician Profile* form to pull:

- Clinical competencies (shown on the site as "Specialities" / "Skills")
- Languages spoken
- Treatment modalities (shown as "Certifications")
- Cultural competencies
- Public bio

**How it finds the right profile:** Clinician Profile records are named like `"David - Giella - 1002"`, where the last part is the employee ID. The plugin searches on that ID, then:

- One record's name contains that exact ID → use it ✅
- Only one result came back but it isn't an exact match → use it, and write a warning to the site's error log ⚠️
- Several results and none exact → **skip the extra detail for that person entirely** ⚠️

That last rule is deliberate and correct: it is safer to publish a thin profile than to publish someone else's bio under the wrong name.

### Step 6 — The record is saved to the website's own database
The plugin keeps its own clinician table, keyed on the Zoho record ID. If that ID already exists the row is **updated**; if not it is **inserted**. That is what prevents duplicates on repeat syncs.

Stored per clinician: email, Zoho ID, employee ID, headshot filename, employment status, first name, last name, preferred name/nickname, clinical title, degree, designation, skills, where the advanced degree came from, languages, certifications, cultural competency, public bio, states licensed in, telehealth flag, the linked WordPress page, whether that page is switched on, and created/updated timestamps.

### Step 7 — A WordPress page is created or completely rewritten
Each clinician gets a normal WordPress **Page**. The plugin generates the whole page itself:

- Photo, name, clinical title, qualification
- Specialities, Treatment Modalities, Cultural Competencies, Languages Spoken
- Professional Bio, Education, States Licensed In
- Patient Satisfaction score and **"Review Highlights"** — the four most frequently chosen praise phrases across that clinician's approved reviews
- Featured (approved) reviews
- A fixed "Let's get started!" marketing block including the phone number (646) 687-4646

Page address is the therapist's first initial plus surname (for example `/JSmith`); the page title is "First Last info"; comments and pingbacks are switched off; the page is filed under **user ID 1** as the author regardless of who actually triggered the sync.

- **First time:** the page is created and published, and its ID is stored against the clinician.
- **Every sync after that:** the same page is rewritten, keeping whatever published/draft state it currently has.

> ⚠️ **Any manual edit made to a clinician page inside WordPress is wiped at the next sync.** These pages must be treated as read-only; changes belong in Zoho People.

> ℹ️ **The styling for these pages does not live in this plugin.** It sits in the *Code Snippets* plugin's footer. If Code Snippets is disabled, cleaned up, or that snippet is lost, every clinician page will render unstyled. This is a real operational dependency and should be documented for whoever maintains the site.

### Step 8 — Links are written back into Zoho People
The plugin then updates the employee's record **inside Zoho People**, filling in two fields:

- **Profile URL** — the public profile page
- **Review URL** — that clinician's personalised review form link

So staff can see and share the live links from Zoho itself.

> ⚠️ Two caveats. First, these links are always built as `https://wellqor.com/...` **regardless of which site is actually running the plugin** — so a staging or test site will write live-site links into Zoho. Second, on a **completely fresh install** (empty clinician table) this write-back step is skipped for the very first sync; the links only appear from the second sync onward.

### Step 9 — The screen refreshes, or nothing happens
If an admin triggered the sync, the refreshed clinician list is returned to the screen. If it was the daily automatic run, it finishes silently — problems are written only to the server error log, and **nobody is notified**.

---

## 4. Patient reviews — full lifecycle

### How a patient reaches the form
Reviews are collected on a page containing the shortcode `[welz]`. **The form only appears if the web address carries that clinician's ID** (`?zoho_id=…`). Visiting the review page without it shows a blank space. In practice patients arrive through the personalised Review URL the plugin wrote back into Zoho.

### What the form asks
- **Overall star rating** — 1 to 5
- **Descriptive phrases**, tick as many as apply: Knowledgeable, Supportive, Friendly, Helpful, Understanding, A good fit for me, Compassionate, Professional, Patient, Flexible, Competent, Empathetic
- **Review title** (free text)
- **Review description** (free text)
- **Age range** — Under 20 / 20–30 / 30–40 / 40–60 / 60–70 / 70+
- **Gender** — Male / Female / Other
- **First name** and **last initial**

No login or account is required.

### What happens the moment it's submitted
1. The review is saved to the website database with its status **forced to "pending" by the server**. A submitter cannot mark their own review approved — this is deliberately locked down.
2. The review is **immediately copied into Zoho Analytics** (the *Patient Review Data* table): rating, phrases, title, text, age range, gender, name, status and timestamp. **This happens before any approval** — an unapproved, potentially abusive review reaches Zoho Analytics regardless.
3. A **full clinician sync (all of section 3) runs there and then**, while the patient waits.
4. The patient lands on a thank-you page (`[welz-thank-you-page]`) with a "Back" button returning to the review form.

### What an administrator can do
From the **All Reviews** screen:

- See every review, newest first, with the clinician's name attached, searchable and sortable
- **Approve / un-approve** — a toggle, so an approved review can be pulled back down
- **Edit** a review's contents
- **Delete** a review — permanent, no recycle bin

Approving or editing also updates the matching row in Zoho Analytics. If the update can't find the row, the plugin inserts a new one instead — which can leave **duplicate rows** in Analytics. **Deleting a review on the website does not remove it from Zoho Analytics**; it stays there indefinitely.

### What the public sees
- Only **approved** reviews are ever displayed.
- On a clinician's profile page, the **first 3 reviews** are shown with a "Read More" button revealing the rest. (Technically all of them are already loaded in the page; the button just unhides them.)
- The `[welz-show-all-reviews]` page shows that clinician's complete approved list with their photo and details at the top.
- Star ratings are drawn as five stars filled to the score. A review with no rating shows no stars rather than showing zero.

---

## 5. What an administrator can do

Everything is under one **"Zoho People"** menu in the WordPress admin sidebar. The interface is a modern single-page app built into the plugin, with four screens:

| Screen | What it does |
|---|---|
| **All Employees** | The synced clinician list (13 columns, searchable, sortable, resizable). "Fetch data" button to sync now. A per-therapist Page Status toggle. Bulk delete. |
| **All Reviews** | Every patient review (12 columns). Approve/un-approve, edit, delete, bulk delete, search. |
| **Authorization** | Enter, save and re-do the Zoho connection. |
| **Settings** | A single switch: "Erase all data of this plugin on deletion". |

**Page Status toggle:** switching a therapist off sets their WordPress page to **draft** (invisible to the public); switching on **publishes** it again. The setting survives future syncs.

**Deleting a clinician** removes their row from the plugin's database — but **does not delete the WordPress page** already created for them. That page has to be removed separately or it stays live.

> ⚠️ The **"Erase all data on deletion" switch in Settings does nothing.** It saves its value, but the uninstall routine never reads it. See section 8.

> ℹ️ While any plugin admin screen is open, **all admin notices from every other plugin are hidden**. Update warnings, licence expiry notices and security alerts from other plugins will not be seen on these pages.

---

## 6. The three website shortcodes

| Shortcode | Purpose | Needs clinician ID in the URL? |
|---|---|---|
| `[welz]` | Patient review form, with the clinician's photo and name above it | Yes — blank without it |
| `[welz-show-all-reviews]` | Full list of that clinician's approved reviews | Yes — blank without it |
| `[welz-thank-you-page]` | Confirmation screen after submitting | Uses it for the "Back" link |

There are **no Gutenberg blocks, no widgets, and no custom post types**. Clinician profiles are ordinary WordPress Pages.

---

## 7. Automatic behaviour and schedule

- **Once per day**, unattended, the full clinician sync runs.
- **After every review submission**, a full sync also runs — synchronously, while the patient's browser waits.
- The daily job is created while the plugin is active and **cancelled when the plugin is deactivated**.
- The daily run is completely silent. If Zoho is down, credentials expire, or photos fail to download, the only record is a line in the server error log. **No email, no dashboard warning, nobody is told.**

---

## 8. Install, deactivate, uninstall

**On activation:** three database tables are created — connection details, clinicians, reviews. The plugin refuses to activate on very old server software (PHP below 5.6).

**On deactivation:** the daily sync job is cancelled and certain plugin-owned pages are set to draft. All data is kept.

**On uninstall (full removal):**
- The **clinician table is deleted**.
- The **Zoho connection details are kept** (deliberately left in place).
- **All patient reviews are kept** — the reviews table is not touched at all.
- **Every clinician page already created in WordPress is left published.**
- **Downloaded headshot images are left in the uploads folder.**

So "delete the plugin" does not clean up the site, and the Settings switch that appears to control this has no effect.

---

## 9. Rules, limits and restrictions — summary

**Who can do what**
- Only WordPress **Administrators** see the plugin menu.
- Patients need no account to leave a review.
- Every action is protected by a one-time security token; an expired one returns "Token expired". **There is no check anywhere that the person performing an administrative action is actually an administrator** — see section 10.

**Which staff get a website page**
- Active + Clinical Therapist or Clinical Director + telehealth access enabled. All three, in Zoho People.

**Hard-wired to WellQor**
- Links written back to Zoho always use `https://wellqor.com/...`, on any site.
- The review form path `/therapist-review-form/` and thank-you page path are fixed.
- The Zoho Analytics workspace, report and organisation are fixed in the code.
- Several images, the phone number, and the "Get Started" link on generated pages point at wellqor.com.
- Zoho data requests always go to the US Zoho region.

**Performance and volume**
- Every sync is a **full sync** — every clinician re-fetched, every photo re-downloaded, every page rewritten. There is no "only what changed" mode.
- Each clinician costs roughly **three separate Zoho requests** per sync (profile lookup, photo download, link write-back), on top of the batch requests. Around 100 therapists means 300+ requests inside a single run.
- There is **no pacing, no retry, and no handling of Zoho rate limits**. If Zoho throttles or errors, the plugin treats the error response as if it were data.
- Each request waits up to 30 seconds.
- There is **no lock** — the daily job, an admin clicking Fetch, and a patient submitting a review can all run syncs on top of each other.

**Not present in the plugin**
- No email notifications of any kind — nobody is alerted when a review needs approval.
- No spam protection, CAPTCHA, or rate limiting on the review form.
- No required fields on review submission — a completely blank review can be submitted, repeatedly.
- No duplicate-review prevention.
- No public therapist directory, search or filtering.
- The "Logs" module in the code is non-functional — it reads from a database table that is never created.

---

## 10. Things that need attention before this goes further

These are not stylistic opinions. Each has a real business, security or privacy consequence.

**1. Live Zoho Analytics credentials are written into the plugin's source code.**
The Analytics client secret and renewal key sit in plain text inside a program file, and are therefore in the code repository's history, in every backup, and on the server. Anyone with any of those has full access to that Zoho Analytics account. They must be **rotated in Zoho** (assume they are already exposed) and moved into settings or server configuration.

**2. Administrative actions are not permission-checked.**
Every plugin action — including *delete therapist*, *delete review*, *approve review*, *save Zoho credentials*, and *read back the stored Zoho credentials* — is registered for logged-out visitors as well as administrators, and the only barrier is a security token. A valid token is printed onto the **public review page** so the form can work. The practical exposure: **any logged-in user of any role** (even the lowest-level subscriber) who loads a review page receives a token that will pass, and could then trigger administrative actions, including retrieving the stored Zoho client secret and renewal key. Every administrative action needs a proper administrator check added.

**3. Real employee data is committed into the code repository.**
The `project resources/` folder contains roughly 4.4 MB of captured Zoho responses (`employees.json`, `clinicialProfile.json`) holding what appears to be genuine staff information — names, work email addresses, professional licence numbers, internal IDs. These should be removed from the repository and, if the repository has ever been shared, treated as a disclosure.

**4. Reviews reach Zoho Analytics before any human approves them.**
Reviews include a first name, last initial, age range, gender and free-text comments about a healthcare experience, and they are copied to Zoho Analytics the instant they're submitted. That flow — plus the fact that deleting a review on the site does not remove it from Analytics, and that reviews survive uninstalling the plugin — should be checked against WellQor's privacy notice and retention policy.

**5. The employee download loop has no safe stop condition.**
It stops only when Zoho returns an *error*. An empty-but-successful response would make it request the same batch forever, hanging the sync — silently, on the daily run. A "stop when no more records come back" condition would remove that risk.

**6. Submitting a review runs a full Zoho sync while the patient waits.**
With a large roster this means hundreds of Zoho calls and photo downloads inside one page request. It is slow for the patient, wasteful of the Zoho API quota, and a likely cause of timeouts as the practice grows. The sync should be moved to the background.

**7. Photo files are saved using a filename supplied by Zoho, unchecked.**
There is no check of the file type, size, or the filename itself before it is written to the uploads folder. A malformed value in a Zoho field could write outside the intended folder. This needs standard WordPress file-handling applied.

**8. Content coming from Zoho is placed onto pages without sanitising it.**
Bios, competencies and similar fields are inserted into the generated pages as-is. Anyone who can edit a Zoho People record could therefore inject markup or scripts into the public website. Likewise, one database lookup builds its query by pasting a value straight in rather than using WordPress's safe method.

**9. Failures are invisible.**
Photo download failures, ambiguous profile matches and Zoho API errors go to the server error log and nowhere else. A simple admin notice or email would close a real operational blind spot.

**10. Housekeeping.**
A development-mode JavaScript build shipped into the released assets; the app's build folder (`node_modules`) is committed to the repository; there is a large amount of unused code inherited from the "Bit Form" plugin this was forked from, including a whole unreachable Zoho CRM interface; and the admin page loads the entire clinician list, every review, and the Zoho credentials into the page on every visit.

---

## 11. One-page summary

| Question | Answer |
|---|---|
| What does it connect? | Zoho People (HR) → WordPress website → Zoho Analytics (reporting) |
| What does it publish? | An automatically generated profile page per eligible therapist |
| Who qualifies? | Active + Clinical Therapist/Director + telehealth enabled |
| How often does it sync? | Daily automatically, on demand, and after every review submission |
| What does it collect? | Reviews: star rating, praise phrases, title, comments, age range, gender, first name + last initial |
| Who approves reviews? | A WordPress administrator — nothing is public until approved |
| Where do reviews end up? | Website database **and** Zoho Analytics, the latter immediately on submission |
| Who can administer it? | Administrators see the menu; the underlying actions are not permission-checked |
| Biggest risks | Hard-coded live Analytics credentials; administrative actions without permission checks; real employee data committed to the repository |
| Hidden dependency | Clinician page styling lives in the Code Snippets plugin, not here |
