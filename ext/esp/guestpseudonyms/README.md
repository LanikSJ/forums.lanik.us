# guest-pseudonyms

A phpBB extension that shows **bogus / pseudonymous names to non-logged-in
visitors** (guests, and bots and crawlers alike), so members' real
usernames are never exposed publicly or indexed by search engines. Logged-in
members see real usernames as normal; guests and bots see a stable, per-member
nickname.

For a privacy-sensitive community — a support forum, or a group that would rather
not be findable by real name — this keeps members' chosen names out of the
public, crawlable surface of the board, without changing anything for the members
themselves.

![One topic rendered twice, side by side. On the left, a visitor who is not
logged in sees each author under a generated nickname; on the right, the same
three posts show the members' real usernames to a logged-in
member.](https://codeberg.org/elaurijssens/guest-pseudonyms/raw/branch/main/screenshots/01-guest-vs-member.png)

*The same topic, seen two ways. Nothing stored in the database differs between
them — only the copy rendered for the visitor looking at it.*

## Requirements

phpBB **3.3.15** or newer, and PHP **7.2** or newer (the minimum phpBB 3.3.15 itself requires). 3.3.15 is the release where
phpBB's profile-template event gained the variable the signature hiding reads;
on anything older that hiding would silently do nothing, so the extension
refuses to enable rather than tell a member their signature is hidden while it
is still on show.

After enabling on a board that already has members, run

```
php bin/phpbbcli.php esp:guestpseudonyms:backfill
```

to give every existing member a nickname in one pass. Nicknames are otherwise
created lazily, the first time a member is shown to a guest — which is fine for
display, but the member search and the A–Z strip can only match members who
already have one.

## What it does

Every member is assigned one **stable nickname**, generated from themed word
lists in the board's language (for example `Blauw_Lijster_Woud`). The same member
always appears under the same nickname, so a guest can still follow who said what
in a conversation — they just can't tie it back to a real identity.

Substitution happens at **render time**, at phpBB's central username-rendering
function (`get_username_string()`), so it covers usernames wherever the forum
shows them: post authors, topic and forum last-poster columns, the memberlist,
member profiles, the online list, feeds, and any other extension that renders
usernames through the same function. It also rewrites usernames inside **quoted
posts**. A handful of places read phpBB's raw username column instead of going
through that function — the profile page title and breadcrumb, the "Last bumped
by" line, the member live search, and the member search and A–Z filters — and
each of those is covered by its own hook, so a name a guest can see is also the
only name a guest can search for. Nothing stored in the database is changed —
only the copy rendered for
the current, not-logged-in visitor — so there is no rewriting of existing content,
and disabling the extension instantly restores real names everywhere.

Outgoing e-mails and notifications always use real names: they are addressed to a
specific, known recipient (a member, or the board admins), so pseudonymising them
would only cause confusion.

## Administrator settings (ACP)

The extension adds a **Guest pseudonyms** module to the Administration Control
Panel with a single **Settings** page:

- **Enable guest pseudonyms** — the master switch. When on, visitors who are not
  logged in see a nickname in place of each member's username. Search-engine
  crawlers are treated exactly like guests, so real names cannot end up in
  search results; there is deliberately no separate switch for them.
- **Resolve quote and mention names from the account** — for logged-in members,
  quotes and mentions show the referenced member's *current* username (resolved
  from their account rather than the text stored in the old post). Renaming a
  member — for example to `Anon20260101` on an account-deletion request — then
  updates their name in old quotes automatically, with no database edits. When
  off, quotes keep the name exactly as it was written.

> **A note on mentions.** phpBB 3.3 has no @mention feature of its own — it
> arrived in 4.0 — so the mention handling here only does anything on a board
> that has installed one. It is written for
> [paul999/mention](https://github.com/paul999/mention) (*Simple mentions*),
> whose `[smention u=…]` tags carry a user id and can therefore be mapped to a
> pseudonym reliably. Quotes are covered on every board, mentions or not.

**Member choices** are configured here too — each toggle enables or disables the
matching control in the member's User Control Panel:

- **Allow members to regenerate their nickname** — lets members pick a different
  nickname from their User Control Panel.
- **Allow members to opt out** — lets a member choose to show their real username
  to guests instead of a nickname.
- **Opt-out reminder interval (months)** — periodically remind opted-out members
  that their real name is public. Set to `0` to disable the reminder.
- **Re-protect if a reminder is ignored** — if an opted-out member does not
  re-confirm after a reminder, automatically hide their name again (fail-closed),
  so a forgotten opt-out does not leave a name exposed indefinitely.

## Member settings (UCP)

Each member gets a **Name privacy** page under **Profile** in their User Control
Panel:

- **Your guest nickname** — the name guests and search engines currently see in
  place of their username (members rarely see it themselves).
- **Generate a new nickname** — if the administrator allows it, the member can
  generate a fresh nickname, created in their own language.
- **What guests and search engines see** — if the administrator allows opt-out,
  the member chooses between three levels: hide my name and signature (the
  default), show my real username but hide my signature, or show both.

![The Name privacy page in the User Control Panel, showing the member's current
guest nickname, a button to generate a new one, and the three-way choice of what
visitors who are not logged in can
see.](https://codeberg.org/elaurijssens/guest-pseudonyms/raw/branch/main/screenshots/02-ucp-name-privacy.png)

Opted-out members are periodically reminded (at the interval set in the ACP) that
their real name is public, with a link back to this page.

## Languages

Names are generated from word lists in the language pack, so they appear in the
board's language. Ships with **English, Dutch, German, French, Spanish and
Portuguese**; any other phpBB language falls back to English per string, so
nothing breaks in an untranslated locale.

Adding a language is just dropping a `language/<iso>/` folder in place — no code
changes. The German, French, Spanish and Portuguese packs are first-pass
translations and would benefit from a native review.

## External / direct-database consumers

Substitution happens at **render time**, so anything that reads the phpBB
database directly — a WordPress widget, a custom report, an app — bypasses this
extension and sees real usernames (including the denormalised
`topic_first_poster_name` / `topic_last_poster_name` columns). For those cases,
the safer approach is to expose the data through pseudonymised, PII-redacted
database **views**, read by a least-privilege, SELECT-only database user scoped
to just those views, rather than letting the external consumer read the real
tables directly.
